<?php

namespace App\Services;

use App\Core\Database;

/**
 * Document Service
 * Handles warehouse document operations including posting
 */
class DocumentService
{
    private Database $db;
    private StockService $stockService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->stockService = new StockService();
        $this->auditService = new AuditService();
    }

    /**
     * Generate document number
     */
    public function generateDocumentNumber(string $typeCode): string
    {
        $prefix = strtoupper(substr($typeCode, 0, 3));
        $year = date('Y');
        $month = date('m');

        // Get last number for this type and month
        $last = $this->db->fetch(
            "SELECT document_number FROM documents d
             INNER JOIN document_types dt ON dt.id = d.document_type_id
             WHERE dt.code = ? AND document_number LIKE ?
             ORDER BY document_number DESC LIMIT 1",
            [$typeCode, "{$prefix}-{$year}{$month}-%"]
        );

        if ($last) {
            // Extract sequence number and increment
            $parts = explode('-', $last['document_number']);
            $seq = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $seq);
    }

    /**
     * Create document
     */
    public function createDocument(array $data): int
    {
        $typeCode = $data['document_type'] ?? '';
        $type = $this->db->fetch(
            "SELECT * FROM document_types WHERE code = ?",
            [$typeCode]
        );

        if (!$type) {
            throw new \RuntimeException('Invalid document type');
        }

        $documentNumber = $this->generateDocumentNumber($typeCode);

        $documentId = $this->db->insert('documents', [
            'document_number' => $documentNumber,
            'document_type_id' => $type['id'],
            'status' => 'draft',
            'warehouse_id' => $data['warehouse_id'],
            'target_warehouse_id' => $data['target_warehouse_id'] ?? null,
            'partner_id' => $data['partner_id'] ?? null,
            'document_date' => $data['document_date'] ?? date('Y-m-d'),
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => userId(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->logCreate('document', $documentId, [
            'document_number' => $documentNumber,
            'type' => $typeCode,
        ]);

        return $documentId;
    }

    /**
     * Add line to document
     */
    public function addLine(int $documentId, array $lineData): int
    {
        // Get max line number
        $maxLine = $this->db->fetchColumn(
            "SELECT COALESCE(MAX(line_number), 0) FROM document_lines WHERE document_id = ?",
            [$documentId]
        );

        $totalPrice = null;
        if (isset($lineData['unit_price']) && isset($lineData['quantity'])) {
            $totalPrice = (float) $lineData['unit_price'] * (float) $lineData['quantity'];
        }

        return $this->db->insert('document_lines', [
            'document_id' => $documentId,
            'line_number' => $maxLine + 1,
            'item_id' => $lineData['item_id'],
            'lot_id' => $lineData['lot_id'] ?? null,
            'quantity' => $lineData['quantity'],
            'unit_price' => $lineData['unit_price'] ?? null,
            'total_price' => $totalPrice,
            'notes' => $lineData['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update line
     */
    public function updateLine(int $lineId, array $lineData): void
    {
        $totalPrice = null;
        if (isset($lineData['unit_price']) && isset($lineData['quantity'])) {
            $totalPrice = (float) $lineData['unit_price'] * (float) $lineData['quantity'];
        }

        $this->db->update('document_lines', [
            'item_id' => $lineData['item_id'],
            'lot_id' => $lineData['lot_id'] ?? null,
            'quantity' => $lineData['quantity'],
            'unit_price' => $lineData['unit_price'] ?? null,
            'total_price' => $totalPrice,
            'notes' => $lineData['notes'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$lineId]);
    }

    /**
     * Delete line
     */
    public function deleteLine(int $lineId): void
    {
        $this->db->delete('document_lines', 'id = ?', [$lineId]);
    }

    /**
     * Get document with lines
     */
    public function getDocument(int $documentId): ?array
    {
        $document = $this->db->fetch(
            "SELECT d.*, dt.code as type_code, dt.name as type_name, dt.direction,
                    w.name as warehouse_name, tw.name as target_warehouse_name,
                    p.name as partner_name,
                    uc.username as created_by_name,
                    up.username as posted_by_name
             FROM documents d
             INNER JOIN document_types dt ON dt.id = d.document_type_id
             INNER JOIN warehouses w ON w.id = d.warehouse_id
             LEFT JOIN warehouses tw ON tw.id = d.target_warehouse_id
             LEFT JOIN partners p ON p.id = d.partner_id
             LEFT JOIN users uc ON uc.id = d.created_by
             LEFT JOIN users up ON up.id = d.posted_by
             WHERE d.id = ?",
            [$documentId]
        );

        if (!$document) {
            return null;
        }

        $document['lines'] = $this->db->fetchAll(
            "SELECT dl.*, i.sku, i.name as item_name, u.code as unit_code,
                    l.lot_number, l.color
             FROM document_lines dl
             INNER JOIN items i ON i.id = dl.item_id
             INNER JOIN units u ON u.id = i.unit_id
             LEFT JOIN lots l ON l.id = dl.lot_id
             WHERE dl.document_id = ?
             ORDER BY dl.line_number",
            [$documentId]
        );

        return $document;
    }

    /**
     * Post document (process stock movements)
     */
    public function postDocument(int $documentId): array
    {
        $document = $this->getDocument($documentId);

        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }

        if ($document['status'] !== 'draft') {
            return ['success' => false, 'message' => 'Document is not in draft status'];
        }

        if (empty($document['lines'])) {
            return ['success' => false, 'message' => 'Document has no lines'];
        }

        try {
            $this->db->beginTransaction();

            foreach ($document['lines'] as $line) {
                $this->processLine($document, $line);
            }

            // Update document status
            $this->db->update('documents', [
                'status' => 'posted',
                'posted_at' => date('Y-m-d H:i:s'),
                'posted_by' => userId(),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$documentId]);

            $this->db->commit();

            $this->auditService->logPosting(
                'document',
                $documentId,
                $document['document_number']
            );

            return ['success' => true, 'message' => 'Document posted successfully'];

        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process document line for posting
     */
    private function processLine(array $document, array $line): void
    {
        $direction = $document['direction'];
        $warehouseId = $document['warehouse_id'];
        $targetWarehouseId = $document['target_warehouse_id'];
        $quantity = (float) $line['quantity'];
        $unitCost = $line['unit_price'];

        switch ($direction) {
            case 'in':
                // Receipt - increase stock
                $this->stockService->updateStock(
                    $line['item_id'],
                    $warehouseId,
                    $line['lot_id'],
                    $quantity
                );
                $this->stockService->createPosting(
                    $document['id'],
                    $line['id'],
                    $line['item_id'],
                    $line['lot_id'],
                    $warehouseId,
                    $quantity,
                    'in',
                    $unitCost
                );
                break;

            case 'out':
                // Issue - decrease stock
                $available = $this->stockService->getAvailable(
                    $line['item_id'],
                    $warehouseId,
                    $line['lot_id']
                );

                if ($available < $quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$line['item_name']}. Available: {$available}, Required: {$quantity}"
                    );
                }

                $this->stockService->updateStock(
                    $line['item_id'],
                    $warehouseId,
                    $line['lot_id'],
                    -$quantity
                );
                $this->stockService->createPosting(
                    $document['id'],
                    $line['id'],
                    $line['item_id'],
                    $line['lot_id'],
                    $warehouseId,
                    $quantity,
                    'out',
                    $unitCost
                );
                break;

            case 'transfer':
                // Transfer - move between warehouses
                if (!$targetWarehouseId) {
                    throw new \RuntimeException('Target warehouse is required for transfers');
                }

                $available = $this->stockService->getAvailable(
                    $line['item_id'],
                    $warehouseId,
                    $line['lot_id']
                );

                if ($available < $quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$line['item_name']}. Available: {$available}, Required: {$quantity}"
                    );
                }

                // Decrease from source
                $this->stockService->updateStock(
                    $line['item_id'],
                    $warehouseId,
                    $line['lot_id'],
                    -$quantity
                );
                $this->stockService->createPosting(
                    $document['id'],
                    $line['id'],
                    $line['item_id'],
                    $line['lot_id'],
                    $warehouseId,
                    $quantity,
                    'out',
                    $unitCost
                );

                // Increase in target
                $this->stockService->updateStock(
                    $line['item_id'],
                    $targetWarehouseId,
                    $line['lot_id'],
                    $quantity
                );
                $this->stockService->createPosting(
                    $document['id'],
                    $line['id'],
                    $line['item_id'],
                    $line['lot_id'],
                    $targetWarehouseId,
                    $quantity,
                    'in',
                    $unitCost
                );
                break;

            case 'adjust':
                // Stocktake adjustment - set to specified quantity
                $stock = $this->stockService->getItemStock(
                    $line['item_id'],
                    $warehouseId,
                    $line['lot_id']
                );
                $currentOnHand = !empty($stock) ? (float) $stock[0]['on_hand'] : 0;
                $adjustment = $quantity - $currentOnHand;

                if ($adjustment != 0) {
                    $this->stockService->updateStock(
                        $line['item_id'],
                        $warehouseId,
                        $line['lot_id'],
                        $adjustment
                    );
                    $this->stockService->createPosting(
                        $document['id'],
                        $line['id'],
                        $line['item_id'],
                        $line['lot_id'],
                        $warehouseId,
                        abs($adjustment),
                        $adjustment > 0 ? 'in' : 'out',
                        $unitCost
                    );
                }
                break;
        }
    }

    /**
     * Cancel posted document
     */
    public function cancelDocument(int $documentId): array
    {
        $document = $this->getDocument($documentId);

        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }

        if ($document['status'] !== 'posted') {
            return ['success' => false, 'message' => 'Only posted documents can be cancelled'];
        }

        try {
            $this->db->beginTransaction();

            // Reverse all postings
            $postings = $this->db->fetchAll(
                "SELECT * FROM stock_postings WHERE document_id = ?",
                [$documentId]
            );

            foreach ($postings as $posting) {
                $multiplier = $posting['direction'] === 'in' ? -1 : 1;
                $this->stockService->updateStock(
                    $posting['item_id'],
                    $posting['warehouse_id'],
                    $posting['lot_id'],
                    $multiplier * (float) $posting['quantity']
                );
            }

            // Update document status
            $this->db->update('documents', [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancelled_by' => userId(),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$documentId]);

            $this->db->commit();

            $this->auditService->log(
                'cancel',
                'document',
                $documentId,
                "Document {$document['document_number']} cancelled"
            );

            return ['success' => true, 'message' => 'Document cancelled successfully'];

        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
