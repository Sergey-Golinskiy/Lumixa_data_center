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
            "SELECT dl.*, i.sku, i.name as item_name, i.unit
             FROM document_lines dl
             JOIN items i ON dl.item_id = i.id
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
                'costing_method' => $data['costing_method'] ?? null,
                'issue_source_type' => $data['issue_source_type'] ?? null,
                'issue_source_id' => $data['issue_source_id'] ?? null,
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
                'notes' => $data['notes'] ?? '',
                'costing_method' => $data['costing_method'] ?? ($doc['costing_method'] ?? null),
                'issue_source_type' => $data['issue_source_type'] ?? ($doc['issue_source_type'] ?? null),
                'issue_source_id' => $data['issue_source_id'] ?? ($doc['issue_source_id'] ?? null)
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

            if (in_array($doc['type'], ['issue', 'adjustment', 'stocktake'], true)) {
                $totalAmount = (float)$this->db->fetchColumn(
                    "SELECT SUM(total_price) FROM document_lines WHERE document_id = ?",
                    [$doc['id']]
                );
                $this->db->update('documents', [
                    'total_amount' => $totalAmount
                ], ['id' => $doc['id']]);
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
        $quantity = (float)$line['quantity'];
        $unitPrice = (float)$line['unit_price'];

        // Get current balance
        $balance = $this->getOrCreateBalance($itemId);
        $balanceBefore = (float)$balance['on_hand'];
        $balanceAfter = $balanceBefore;
        $movementType = 'in';
        $movementQuantity = $quantity;
        $movementUnitCost = $unitPrice;

        switch ($doc['type']) {
            case 'receipt':
                // Increase stock
                $balanceAfter = $balanceBefore + $quantity;
                $this->updateBalance($balance['id'], $quantity, 0, $unitPrice, 'in');
                $movementType = 'in';
                $batchId = $this->createBatch($itemId, $quantity, $unitPrice, $doc, $line);
                $this->recordBatchMovement($batchId, $itemId, $doc, $line, 'in', $quantity, $unitPrice);
                break;

            case 'issue':
                // Decrease stock
                if ($balanceBefore < $quantity) {
                    throw new \Exception("Insufficient stock for item {$line['item_name']}");
                }
                [$unitCost, $totalCost] = $this->issueWithCosting($itemId, $quantity, $doc, $line);
                $balanceAfter = $balanceBefore - $quantity;
                $this->updateBalance($balance['id'], -$quantity, 0, $unitCost, 'out');
                $movementType = 'out';
                $movementUnitCost = $unitCost;
                $this->updateLineCost((int)$line['id'], $unitCost, $totalCost);
                break;

            case 'adjustment':
            case 'stocktake':
                // Set to absolute value (quantity is the new balance)
                $diff = $quantity - $balanceBefore;
                $balanceAfter = $quantity;
                if ($diff > 0) {
                    $this->updateBalance($balance['id'], $diff, 0, $unitPrice, 'in');
                    $batchId = $this->createBatch($itemId, $diff, $unitPrice, $doc, $line);
                    $this->recordBatchMovement($batchId, $itemId, $doc, $line, 'in', $diff, $unitPrice);
                    $movementType = 'in';
                    $movementQuantity = $diff;
                    $movementUnitCost = $unitPrice;
                    $this->updateLineCost((int)$line['id'], $unitPrice, $diff * $unitPrice);
                } elseif ($diff < 0) {
                    $issueQty = abs($diff);
                    if ($balanceBefore < $issueQty) {
                        throw new \Exception("Insufficient stock for item {$line['item_name']}");
                    }
                    [$unitCost, $totalCost] = $this->issueWithCosting($itemId, $issueQty, $doc, $line);
                    $this->updateBalance($balance['id'], -$issueQty, 0, $unitCost, 'out');
                    $movementType = 'out';
                    $movementQuantity = $issueQty;
                    $movementUnitCost = $unitCost;
                    $this->updateLineCost((int)$line['id'], $unitCost, $totalCost);
                } else {
                    $movementQuantity = 0;
                    $movementType = 'adjust';
                    $movementUnitCost = $balance['avg_cost'] ?? 0;
                }
                break;

            default:
                throw new \Exception("Unknown document type: {$doc['type']}");
        }

        // Create movement record
        if ($movementQuantity > 0 || $movementType === 'adjust') {
            $this->db->insert('stock_movements', [
                'document_id' => $doc['id'],
                'document_line_id' => $line['id'],
                'item_id' => $itemId,
                'movement_type' => $movementType,
                'quantity' => $movementQuantity,
                'unit_cost' => $movementUnitCost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter
            ]);
        }
    }

    /**
     * Get or create stock balance
     */
    private function getOrCreateBalance(int $itemId): array
    {
        $balance = $this->db->fetch(
            "SELECT * FROM stock_balances WHERE item_id = ? FOR UPDATE",
            [$itemId]
        );

        if (!$balance) {
            $id = $this->db->insert('stock_balances', [
                'item_id' => $itemId,
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
            $balance = $this->getOrCreateBalance($movement['item_id']);

            // Reverse the movement
            if ($movement['movement_type'] === 'adjust') {
                continue;
            }
            if ($movement['movement_type'] === 'in') {
                $this->updateBalance($balance['id'], -$movement['quantity'], 0, 0, 'out');
            } else {
                $this->updateBalance($balance['id'], $movement['quantity'], 0, $movement['unit_cost'], 'in');
            }
        }

        $this->reverseBatchMovements($doc['id']);
    }

    private function getSetting(string $key, $default = null)
    {
        if (!$this->db->tableExists('settings')) {
            return $default;
        }

        $value = $this->db->fetchColumn(
            "SELECT value FROM settings WHERE `key` = ? LIMIT 1",
            [$key]
        );

        if ($value === false || $value === null) {
            return $default;
        }

        return $value;
    }

    private function resolveCostingMethod(array $doc): string
    {
        $default = strtoupper((string)$this->getSetting('inventory_issue_method', 'FIFO'));
        $allowOverride = (string)$this->getSetting('inventory_allow_issue_method_override', '1') === '1';
        $method = $default;

        if ($allowOverride && !empty($doc['costing_method'])) {
            $method = strtoupper(trim((string)$doc['costing_method']));
        }

        $allowed = ['FIFO', 'LIFO', 'AVG', 'MANUAL'];
        if (!in_array($method, $allowed, true)) {
            $method = 'FIFO';
        }

        return $method;
    }

    private function issueWithCosting(int $itemId, float $quantity, array $doc, array $line): array
    {
        if (!$this->db->tableExists('inventory_batches')) {
            return [(float)$line['unit_price'], $quantity * (float)$line['unit_price']];
        }

        $method = $this->resolveCostingMethod($doc);
        $remaining = $quantity;
        $allocations = [];

        if ($method === 'MANUAL') {
            $manual = $line['batch_allocations'] ?? [];
            if (empty($manual)) {
                throw new \Exception('Manual batch selection required for this issue');
            }

            $manualTotal = 0;
            foreach ($manual as $allocation) {
                $manualTotal += (float)$allocation['quantity'];
            }
            if (abs($manualTotal - $quantity) > 0.0001) {
                throw new \Exception('Manual batch quantities must match issued quantity');
            }

            foreach ($manual as $allocation) {
                $batchId = (int)$allocation['batch_id'];
                $qty = (float)$allocation['quantity'];
                $batch = $this->db->fetch(
                    "SELECT * FROM inventory_batches WHERE id = ? AND item_id = ? FOR UPDATE",
                    [$batchId, $itemId]
                );
                if (!$batch) {
                    throw new \Exception('Batch not found for manual selection');
                }
                if ((float)$batch['qty_available'] < $qty) {
                    throw new \Exception('Insufficient quantity in selected batch');
                }

                $this->updateBatchQuantity($batch, -$qty);
                $this->recordBatchMovement((int)$batch['id'], $itemId, $doc, $line, 'out', $qty, (float)$batch['unit_cost']);
                $this->recordIssueAllocation($doc, $line, (int)$batch['id'], $qty, (float)$batch['unit_cost']);
                $allocations[] = [
                    'quantity' => $qty,
                    'unit_cost' => (float)$batch['unit_cost']
                ];
                $remaining -= $qty;
            }
        } else {
            $orderBy = $method === 'LIFO' ? 'received_date DESC, id DESC' : 'received_date ASC, id ASC';
            $batches = $this->db->fetchAll(
                "SELECT * FROM inventory_batches WHERE item_id = ? AND qty_available > 0 ORDER BY {$orderBy} FOR UPDATE",
                [$itemId]
            );

            $totalAvailable = 0;
            $totalValue = 0;
            foreach ($batches as $batch) {
                $totalAvailable += (float)$batch['qty_available'];
                $totalValue += (float)$batch['qty_available'] * (float)$batch['unit_cost'];
            }

            if ($totalAvailable + 0.0001 < $quantity) {
                throw new \Exception('Insufficient batch stock for issue');
            }

            $avgCost = $totalAvailable > 0 ? $totalValue / $totalAvailable : 0;

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $available = (float)$batch['qty_available'];
                if ($available <= 0) {
                    continue;
                }

                $issueQty = min($remaining, $available);
                $unitCost = $method === 'AVG' ? $avgCost : (float)$batch['unit_cost'];
                $this->updateBatchQuantity($batch, -$issueQty);
                $this->recordBatchMovement((int)$batch['id'], $itemId, $doc, $line, 'out', $issueQty, $unitCost);
                $this->recordIssueAllocation($doc, $line, (int)$batch['id'], $issueQty, $unitCost);
                $allocations[] = [
                    'quantity' => $issueQty,
                    'unit_cost' => $unitCost
                ];
                $remaining -= $issueQty;
            }
        }

        $totalCost = 0;
        foreach ($allocations as $allocation) {
            $totalCost += $allocation['quantity'] * $allocation['unit_cost'];
        }
        $unitCost = $quantity > 0 ? $totalCost / $quantity : 0;

        return [$unitCost, $totalCost];
    }

    private function updateLineCost(int $lineId, float $unitCost, float $totalCost): void
    {
        $this->db->update('document_lines', [
            'unit_price' => $unitCost,
            'total_price' => $totalCost
        ], ['id' => $lineId]);
    }

    private function createBatch(int $itemId, float $quantity, float $unitCost, array $doc, array $line): int
    {
        $batchCode = $this->generateBatchCode($doc, $line);
        return (int)$this->db->insert('inventory_batches', [
            'item_id' => $itemId,
            'batch_code' => $batchCode,
            'received_date' => $doc['document_date'] ?? date('Y-m-d'),
            'supplier_id' => $doc['partner_id'] ?? null,
            'source_type' => $doc['type'] ?? 'receipt',
            'source_id' => $doc['id'] ?? null,
            'qty_received' => $quantity,
            'qty_available' => $quantity,
            'unit_cost' => $unitCost
        ]);
    }

    private function generateBatchCode(array $doc, array $line): string
    {
        $docNumber = $doc['document_number'] ?? 'DOC';
        $lineNumber = $line['line_number'] ?? $line['id'] ?? '1';
        return strtoupper($docNumber . '-' . $lineNumber);
    }

    private function updateBatchQuantity(array $batch, float $qtyChange): void
    {
        $newQty = (float)$batch['qty_available'] + $qtyChange;
        if ($newQty < -0.0001) {
            throw new \Exception('Batch quantity cannot be negative');
        }
        $this->db->update('inventory_batches', [
            'qty_available' => $newQty
        ], ['id' => $batch['id']]);
    }

    private function recordBatchMovement(int $batchId, int $itemId, array $doc, array $line, string $type, float $quantity, float $unitCost): void
    {
        $batch = $this->db->fetch("SELECT qty_available FROM inventory_batches WHERE id = ?", [$batchId]);
        $balanceAfter = (float)($batch['qty_available'] ?? 0);
        $balanceBefore = $type === 'in' ? $balanceAfter - $quantity : $balanceAfter + $quantity;

        $this->db->insert('inventory_batch_movements', [
            'batch_id' => $batchId,
            'item_id' => $itemId,
            'document_id' => $doc['id'] ?? null,
            'document_line_id' => $line['id'] ?? null,
            'movement_type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter
        ]);
    }

    private function recordIssueAllocation(array $doc, array $line, int $batchId, float $quantity, float $unitCost): void
    {
        $this->db->insert('inventory_issue_allocations', [
            'document_id' => $doc['id'],
            'document_line_id' => $line['id'],
            'batch_id' => $batchId,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost
        ]);
    }

    private function reverseBatchMovements(int $documentId): void
    {
        if (!$this->db->tableExists('inventory_batch_movements')) {
            return;
        }

        $movements = $this->db->fetchAll(
            "SELECT * FROM inventory_batch_movements WHERE document_id = ? ORDER BY id DESC",
            [$documentId]
        );

        foreach ($movements as $movement) {
            $batch = $this->db->fetch("SELECT * FROM inventory_batches WHERE id = ? FOR UPDATE", [$movement['batch_id']]);
            if (!$batch) {
                continue;
            }

            $qtyChange = $movement['movement_type'] === 'in'
                ? -(float)$movement['quantity']
                : (float)$movement['quantity'];

            $this->updateBatchQuantity($batch, $qtyChange);
        }

        $this->db->delete('inventory_issue_allocations', ['document_id' => $documentId]);
    }

    public function getBatchAllocations(int $documentId): array
    {
        if (!$this->db->tableExists('inventory_issue_allocations')) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT ia.*, b.batch_code, i.sku, i.name as item_name
             FROM inventory_issue_allocations ia
             JOIN inventory_batches b ON ia.batch_id = b.id
             JOIN items i ON b.item_id = i.id
             WHERE ia.document_id = ?
             ORDER BY ia.document_line_id, ia.id",
            [$documentId]
        );
    }
}
