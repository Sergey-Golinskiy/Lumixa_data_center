<?php
/**
 * Document Service - Warehouse document management and posting engine
 */

namespace App\Services\Warehouse;

use App\Core\Application;
use App\Core\Database;
use App\Services\AuditService;

class DocumentService
{
    private Application $app;
    private Database $db;
    private AuditService $audit;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app->getDatabase();
        $this->audit = new AuditService($app);
    }

    /**
     * Get document types
     */
    public function getTypes(): array
    {
        return [
            'receipt' => 'Receipt (Incoming)',
            'issue' => 'Issue (Outgoing)',
            'transfer' => 'Transfer',
            'stocktake' => 'Stocktake',
            'adjustment' => 'Adjustment'
        ];
    }

    /**
     * Paginate documents
     */
    public function paginate(int $page, int $perPage, array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "d.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = "d.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "d.document_number LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "d.document_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "d.document_date <= ?";
            $params[] = $filters['date_to'];
        }

        $whereStr = implode(' AND ', $where);

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM documents d WHERE {$whereStr}",
            $params
        );

        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $documents = $this->db->fetchAll(
            "SELECT d.*, p.name as partner_name, u.name as created_by_name
             FROM documents d
             LEFT JOIN partners p ON d.partner_id = p.id
             LEFT JOIN users u ON d.created_by = u.id
             WHERE {$whereStr}
             ORDER BY d.document_date DESC, d.id DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'documents' => $documents,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT d.*, p.name as partner_name,
                    uc.name as created_by_name,
                    up.name as posted_by_name,
                    ux.name as cancelled_by_name
             FROM documents d
             LEFT JOIN partners p ON d.partner_id = p.id
             LEFT JOIN users uc ON d.created_by = uc.id
             LEFT JOIN users up ON d.posted_by = up.id
             LEFT JOIN users ux ON d.cancelled_by = ux.id
             WHERE d.id = ?",
            [$id]
        );
    }

    /**
     * Get document lines
     */
    public function getLines(int $documentId): array
    {
        return $this->db->fetchAll(
            "SELECT dl.*, i.sku, i.name as item_name, i.unit, l.lot_number, l.color
             FROM document_lines dl
             JOIN items i ON dl.item_id = i.id
             LEFT JOIN lots l ON dl.lot_id = l.id
             WHERE dl.document_id = ?
             ORDER BY dl.line_number",
            [$documentId]
        );
    }

    /**
     * Generate document number
     */
    public function generateNumber(string $type): string
    {
        $year = date('Y');

        // Get and update sequence
        $seq = $this->db->fetch(
            "SELECT * FROM document_sequences WHERE type = ? FOR UPDATE",
            [$type]
        );

        if (!$seq) {
            throw new \Exception("Document sequence not found for type: {$type}");
        }

        // Reset sequence if new year
        if ($seq['year'] != $year) {
            $this->db->update('document_sequences', [
                'current_number' => 1,
                'year' => $year
            ], ['id' => $seq['id']]);
            $number = 1;
        } else {
            $number = $seq['current_number'] + 1;
            $this->db->update('document_sequences', [
                'current_number' => $number
            ], ['id' => $seq['id']]);
        }

        return sprintf('%s-%d-%05d', $seq['prefix'], $year, $number);
    }

    /**
     * Create document
     */
    public function create(array $data, array $lines, int $userId): array
    {
        $this->db->beginTransaction();

        try {
            $docNumber = $this->generateNumber($data['type']);

            $docId = $this->db->insert('documents', [
                'document_number' => $docNumber,
                'type' => $data['type'],
                'status' => 'draft',
                'partner_id' => $data['partner_id'] ?? null,
                'document_date' => $data['document_date'],
                'notes' => $data['notes'] ?? '',
                'created_by' => $userId
            ]);

            $totalAmount = 0;
            $lineNum = 1;

            foreach ($lines as $line) {
                $lineTotal = $line['quantity'] * ($line['unit_price'] ?? 0);
                $totalAmount += $lineTotal;

                $this->db->insert('document_lines', [
                    'document_id' => $docId,
                    'line_number' => $lineNum++,
                    'item_id' => $line['item_id'],
                    'lot_id' => $line['lot_id'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'] ?? 0,
                    'total_price' => $lineTotal,
                    'notes' => $line['notes'] ?? ''
                ]);
            }

            $this->db->update('documents', [
                'total_amount' => $totalAmount
            ], ['id' => $docId]);

            $this->audit->log('document.created', 'document', $docId, null, [
                'type' => $data['type'],
                'document_number' => $docNumber
            ], $userId);

            $this->db->commit();

            return $this->findById($docId);

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Update document (only draft)
     */
    public function update(int $id, array $data, array $lines, int $userId): void
    {
        $doc = $this->findById($id);

        if (!$doc) {
            throw new \Exception('Document not found');
        }

        if ($doc['status'] !== 'draft') {
            throw new \Exception('Only draft documents can be edited');
        }

        $this->db->beginTransaction();

        try {
            $this->db->update('documents', [
                'partner_id' => $data['partner_id'] ?? null,
                'document_date' => $data['document_date'],
                'notes' => $data['notes'] ?? ''
            ], ['id' => $id]);

            // Delete existing lines
            $this->db->delete('document_lines', ['document_id' => $id]);

            $totalAmount = 0;
            $lineNum = 1;

            foreach ($lines as $line) {
                $lineTotal = $line['quantity'] * ($line['unit_price'] ?? 0);
                $totalAmount += $lineTotal;

                $this->db->insert('document_lines', [
                    'document_id' => $id,
                    'line_number' => $lineNum++,
                    'item_id' => $line['item_id'],
                    'lot_id' => $line['lot_id'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'] ?? 0,
                    'total_price' => $lineTotal,
                    'notes' => $line['notes'] ?? ''
                ]);
            }

            $this->db->update('documents', [
                'total_amount' => $totalAmount
            ], ['id' => $id]);

            $this->audit->log('document.updated', 'document', $id, null, null, $userId);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Post document - POSTING ENGINE
     */
    public function post(int $id, int $userId): void
    {
        $doc = $this->findById($id);

        if (!$doc) {
            throw new \Exception('Document not found');
        }

        if ($doc['status'] !== 'draft') {
            throw new \Exception('Only draft documents can be posted');
        }

        $lines = $this->getLines($id);

        if (empty($lines)) {
            throw new \Exception('Cannot post document without lines');
        }

        $this->db->beginTransaction();

        try {
            foreach ($lines as $line) {
                $this->processLine($doc, $line);
            }

            // Update document status
            $this->db->update('documents', [
                'status' => 'posted',
                'posted_at' => date('Y-m-d H:i:s'),
                'posted_by' => $userId
            ], ['id' => $id]);

            $this->audit->log('document.posted', 'document', $id, null, [
                'type' => $doc['type'],
                'document_number' => $doc['document_number'],
                'total_amount' => $doc['total_amount']
            ], $userId);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Process single line for posting
     */
    private function processLine(array $doc, array $line): void
    {
        $itemId = $line['item_id'];
        $lotId = $line['lot_id'];
        $quantity = (float)$line['quantity'];
        $unitPrice = (float)$line['unit_price'];

        // Get current balance
        $balance = $this->getOrCreateBalance($itemId, $lotId);
        $balanceBefore = (float)$balance['on_hand'];

        switch ($doc['type']) {
            case 'receipt':
                // Increase stock
                $balanceAfter = $balanceBefore + $quantity;
                $this->updateBalance($balance['id'], $quantity, 0, $unitPrice, 'in');
                $movementType = 'in';
                break;

            case 'issue':
                // Decrease stock
                if ($balanceBefore < $quantity) {
                    throw new \Exception("Insufficient stock for item {$line['item_name']}");
                }
                $balanceAfter = $balanceBefore - $quantity;
                $this->updateBalance($balance['id'], -$quantity, 0, $unitPrice, 'out');
                $movementType = 'out';
                break;

            case 'adjustment':
                // Set to absolute value (quantity is the new balance)
                $diff = $quantity - $balanceBefore;
                $balanceAfter = $quantity;
                $this->setBalance($balance['id'], $quantity);
                $movementType = $diff >= 0 ? 'in' : 'out';
                $quantity = abs($diff);
                break;

            case 'stocktake':
                // Same as adjustment
                $diff = $quantity - $balanceBefore;
                $balanceAfter = $quantity;
                $this->setBalance($balance['id'], $quantity);
                $movementType = $diff >= 0 ? 'in' : 'out';
                $quantity = abs($diff);
                break;

            default:
                throw new \Exception("Unknown document type: {$doc['type']}");
        }

        // Create movement record
        $this->db->insert('stock_movements', [
            'document_id' => $doc['id'],
            'document_line_id' => $line['id'],
            'item_id' => $itemId,
            'lot_id' => $lotId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'unit_cost' => $unitPrice,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter
        ]);
    }

    /**
     * Get or create stock balance
     */
    private function getOrCreateBalance(int $itemId, ?int $lotId): array
    {
        $where = $lotId
            ? "item_id = ? AND lot_id = ?"
            : "item_id = ? AND lot_id IS NULL";
        $params = $lotId ? [$itemId, $lotId] : [$itemId];

        $balance = $this->db->fetch(
            "SELECT * FROM stock_balances WHERE {$where} FOR UPDATE",
            $params
        );

        if (!$balance) {
            $id = $this->db->insert('stock_balances', [
                'item_id' => $itemId,
                'lot_id' => $lotId,
                'on_hand' => 0,
                'reserved' => 0,
                'avg_cost' => 0
            ]);

            $balance = $this->db->fetch("SELECT * FROM stock_balances WHERE id = ?", [$id]);
        }

        return $balance;
    }

    /**
     * Update stock balance
     */
    private function updateBalance(int $balanceId, float $qtyChange, float $reservedChange, float $unitCost, string $type): void
    {
        $balance = $this->db->fetch("SELECT * FROM stock_balances WHERE id = ?", [$balanceId]);

        $newOnHand = (float)$balance['on_hand'] + $qtyChange;
        $newReserved = (float)$balance['reserved'] + $reservedChange;

        // Calculate new average cost (for receipts)
        $newAvgCost = $balance['avg_cost'];
        if ($type === 'in' && $qtyChange > 0) {
            $totalValue = ($balance['on_hand'] * $balance['avg_cost']) + ($qtyChange * $unitCost);
            $newAvgCost = $newOnHand > 0 ? $totalValue / $newOnHand : 0;
        }

        $this->db->update('stock_balances', [
            'on_hand' => $newOnHand,
            'reserved' => $newReserved,
            'avg_cost' => $newAvgCost,
            'last_movement_at' => date('Y-m-d H:i:s')
        ], ['id' => $balanceId]);
    }

    /**
     * Set absolute stock balance
     */
    private function setBalance(int $balanceId, float $newOnHand): void
    {
        $this->db->update('stock_balances', [
            'on_hand' => $newOnHand,
            'last_movement_at' => date('Y-m-d H:i:s')
        ], ['id' => $balanceId]);
    }

    /**
     * Cancel document
     */
    public function cancel(int $id, string $reason, int $userId): void
    {
        $doc = $this->findById($id);

        if (!$doc) {
            throw new \Exception('Document not found');
        }

        if ($doc['status'] === 'cancelled') {
            throw new \Exception('Document is already cancelled');
        }

        $this->db->beginTransaction();

        try {
            // If posted, reverse the movements
            if ($doc['status'] === 'posted') {
                $this->reversePosting($doc);
            }

            $this->db->update('documents', [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancelled_by' => $userId,
                'cancel_reason' => $reason
            ], ['id' => $id]);

            $this->audit->log('document.cancelled', 'document', $id, null, [
                'reason' => $reason
            ], $userId);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Reverse document posting
     */
    private function reversePosting(array $doc): void
    {
        $movements = $this->db->fetchAll(
            "SELECT * FROM stock_movements WHERE document_id = ?",
            [$doc['id']]
        );

        foreach ($movements as $movement) {
            $balance = $this->getOrCreateBalance($movement['item_id'], $movement['lot_id']);

            // Reverse the movement
            if ($movement['movement_type'] === 'in') {
                $this->updateBalance($balance['id'], -$movement['quantity'], 0, 0, 'out');
            } else {
                $this->updateBalance($balance['id'], $movement['quantity'], 0, $movement['unit_cost'], 'in');
            }
        }
    }
}
