<?php
/**
 * Batch Service - Inventory Batch Management
 * Handles batch tracking, allocation, and costing methods
 */

namespace App\Services\Warehouse;

use App\Core\Application;
use App\Core\Database;

class BatchService
{
    private Application $app;
    private Database $db;

    // Costing methods
    public const METHOD_FIFO = 'FIFO';
    public const METHOD_LIFO = 'LIFO';
    public const METHOD_WEIGHTED_AVG = 'WEIGHTED_AVG';
    public const METHOD_MANUAL = 'MANUAL';

    // Batch statuses
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DEPLETED = 'depleted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_QUARANTINE = 'quarantine';

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app->getDatabase();
    }

    /**
     * Create new inventory batch
     */
    public function createBatch(array $data): array
    {
        $this->db->beginTransaction();

        try {
            // Generate batch code if not provided
            if (empty($data['batch_code'])) {
                $data['batch_code'] = $this->generateBatchCode($data['item_id']);
            }

            // Create batch
            $batchId = $this->db->insert('inventory_batches', [
                'item_id' => $data['item_id'],
                'batch_code' => $data['batch_code'],
                'received_date' => $data['received_date'] ?? date('Y-m-d'),
                'supplier_id' => $data['supplier_id'] ?? null,
                'source_type' => $data['source_type'] ?? 'manual',
                'source_id' => $data['source_id'] ?? null,
                'qty_received' => $data['quantity'],
                'qty_available' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? 0,
                'status' => $data['status'] ?? self::STATUS_ACTIVE,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);

            // Create initial movement
            $this->db->insert('inventory_batch_movements', [
                'batch_id' => $batchId,
                'item_id' => $data['item_id'],
                'document_id' => $data['source_id'] ?? null,
                'document_line_id' => $data['document_line_id'] ?? null,
                'movement_type' => 'in',
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? 0,
                'balance_before' => 0,
                'balance_after' => $data['quantity']
            ]);

            $this->db->commit();

            return $this->getBatchById($batchId);
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get batch by ID
     */
    public function getBatchById(int $batchId): ?array
    {
        return $this->db->fetch(
            "SELECT b.*, i.sku, i.name as item_name, i.costing_method as default_costing_method,
                    p.name as supplier_name
             FROM inventory_batches b
             JOIN items i ON b.item_id = i.id
             LEFT JOIN partners p ON b.supplier_id = p.id
             WHERE b.id = ?",
            [$batchId]
        );
    }

    /**
     * Get available batches for an item
     */
    public function getAvailableBatches(int $itemId, ?string $status = null): array
    {
        $where = ['b.item_id = ?'];
        $params = [$itemId];

        if ($status) {
            $where[] = 'b.status = ?';
            $params[] = $status;
        } else {
            $where[] = 'b.status = ?';
            $params[] = self::STATUS_ACTIVE;
        }

        $where[] = 'b.qty_available > 0';

        $sql = "SELECT b.*,
                       p.name as supplier_name,
                       COALESCE(SUM(br.quantity), 0) as qty_reserved,
                       (b.qty_available - COALESCE(SUM(br.quantity), 0)) as qty_unreserved
                FROM inventory_batches b
                LEFT JOIN partners p ON b.supplier_id = p.id
                LEFT JOIN inventory_batch_reservations br ON b.id = br.batch_id AND br.status = 'active'
                WHERE " . implode(' AND ', $where) . "
                GROUP BY b.id
                HAVING qty_unreserved > 0
                ORDER BY b.received_date ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Allocate batches for issue using specified method
     */
    public function allocateBatches(
        int $itemId,
        float $quantity,
        string $method,
        ?int $documentId = null,
        ?int $documentLineId = null,
        ?array $manualAllocation = null
    ): array {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        // Check if item has enough available quantity
        $availableQty = $this->getAvailableQuantity($itemId);
        if ($availableQty < $quantity) {
            throw new \RuntimeException(
                "Insufficient quantity available. Required: {$quantity}, Available: {$availableQty}"
            );
        }

        $this->db->beginTransaction();

        try {
            $allocations = [];

            switch ($method) {
                case self::METHOD_FIFO:
                    $allocations = $this->allocateFIFO($itemId, $quantity);
                    break;

                case self::METHOD_LIFO:
                    $allocations = $this->allocateLIFO($itemId, $quantity);
                    break;

                case self::METHOD_WEIGHTED_AVG:
                    $allocations = $this->allocateWeightedAverage($itemId, $quantity);
                    break;

                case self::METHOD_MANUAL:
                    if (!$manualAllocation) {
                        throw new \InvalidArgumentException('Manual allocation requires batch selections');
                    }
                    $allocations = $this->allocateManual($itemId, $quantity, $manualAllocation);
                    break;

                default:
                    throw new \InvalidArgumentException("Unknown allocation method: {$method}");
            }

            // Verify total allocated equals requested
            $totalAllocated = array_sum(array_column($allocations, 'quantity'));
            if (abs($totalAllocated - $quantity) > 0.0001) {
                throw new \RuntimeException('Allocation mismatch');
            }

            $this->db->commit();

            return $allocations;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Allocate using FIFO (First In, First Out)
     */
    private function allocateFIFO(int $itemId, float $quantity): array
    {
        $batches = $this->db->fetchAll(
            "SELECT b.id, b.batch_code, b.qty_available, b.unit_cost, b.received_date,
                    (b.qty_available - COALESCE(SUM(br.quantity), 0)) as qty_unreserved
             FROM inventory_batches b
             LEFT JOIN inventory_batch_reservations br ON b.id = br.batch_id AND br.status = 'active'
             WHERE b.item_id = ? AND b.status = ? AND b.qty_available > 0
             GROUP BY b.id
             HAVING qty_unreserved > 0
             ORDER BY b.received_date ASC, b.id ASC",
            [$itemId, self::STATUS_ACTIVE]
        );

        return $this->distributeToBatches($batches, $quantity);
    }

    /**
     * Allocate using LIFO (Last In, First Out)
     */
    private function allocateLIFO(int $itemId, float $quantity): array
    {
        $batches = $this->db->fetchAll(
            "SELECT b.id, b.batch_code, b.qty_available, b.unit_cost, b.received_date,
                    (b.qty_available - COALESCE(SUM(br.quantity), 0)) as qty_unreserved
             FROM inventory_batches b
             LEFT JOIN inventory_batch_reservations br ON b.id = br.batch_id AND br.status = 'active'
             WHERE b.item_id = ? AND b.status = ? AND b.qty_available > 0
             GROUP BY b.id
             HAVING qty_unreserved > 0
             ORDER BY b.received_date DESC, b.id DESC",
            [$itemId, self::STATUS_ACTIVE]
        );

        return $this->distributeToBatches($batches, $quantity);
    }

    /**
     * Allocate using weighted average cost
     */
    private function allocateWeightedAverage(int $itemId, float $quantity): array
    {
        // Get weighted average cost
        $avgData = $this->db->fetch(
            "SELECT SUM(qty_available * unit_cost) / SUM(qty_available) as avg_cost,
                    SUM(qty_available) as total_qty
             FROM inventory_batches
             WHERE item_id = ? AND status = ? AND qty_available > 0",
            [$itemId, self::STATUS_ACTIVE]
        );

        if (!$avgData || $avgData['total_qty'] <= 0) {
            throw new \RuntimeException('No batches available for weighted average calculation');
        }

        $avgCost = (float)$avgData['avg_cost'];

        // Distribute proportionally across batches
        $batches = $this->db->fetchAll(
            "SELECT b.id, b.batch_code, b.qty_available, b.unit_cost, b.received_date,
                    (b.qty_available - COALESCE(SUM(br.quantity), 0)) as qty_unreserved
             FROM inventory_batches b
             LEFT JOIN inventory_batch_reservations br ON b.id = br.batch_id AND br.status = 'active'
             WHERE b.item_id = ? AND b.status = ? AND b.qty_available > 0
             GROUP BY b.id
             HAVING qty_unreserved > 0
             ORDER BY b.received_date ASC",
            [$itemId, self::STATUS_ACTIVE]
        );

        // Use FIFO for physical distribution, but with average cost
        $allocations = $this->distributeToBatches($batches, $quantity);

        // Override cost with weighted average
        foreach ($allocations as &$allocation) {
            $allocation['unit_cost'] = $avgCost;
            $allocation['total_cost'] = $allocation['quantity'] * $avgCost;
        }

        return $allocations;
    }

    /**
     * Allocate manually specified batches
     */
    private function allocateManual(int $itemId, float $quantity, array $manualAllocation): array
    {
        $allocations = [];
        $totalAllocated = 0;

        foreach ($manualAllocation as $alloc) {
            if (!isset($alloc['batch_id']) || !isset($alloc['quantity'])) {
                throw new \InvalidArgumentException('Manual allocation must specify batch_id and quantity');
            }

            $batch = $this->getBatchById((int)$alloc['batch_id']);

            if (!$batch || $batch['item_id'] != $itemId) {
                throw new \InvalidArgumentException("Invalid batch ID: {$alloc['batch_id']}");
            }

            if ($batch['status'] !== self::STATUS_ACTIVE) {
                throw new \RuntimeException("Batch {$batch['batch_code']} is not active");
            }

            $qtyToAllocate = (float)$alloc['quantity'];

            if ($qtyToAllocate > $batch['qty_available']) {
                throw new \RuntimeException(
                    "Insufficient quantity in batch {$batch['batch_code']}. " .
                    "Requested: {$qtyToAllocate}, Available: {$batch['qty_available']}"
                );
            }

            $allocations[] = [
                'batch_id' => $batch['id'],
                'batch_code' => $batch['batch_code'],
                'quantity' => $qtyToAllocate,
                'unit_cost' => (float)$batch['unit_cost'],
                'total_cost' => $qtyToAllocate * (float)$batch['unit_cost']
            ];

            $totalAllocated += $qtyToAllocate;
        }

        if (abs($totalAllocated - $quantity) > 0.0001) {
            throw new \InvalidArgumentException(
                "Manual allocation total ({$totalAllocated}) does not match requested quantity ({$quantity})"
            );
        }

        return $allocations;
    }

    /**
     * Helper: Distribute quantity across batches
     */
    private function distributeToBatches(array $batches, float $quantity): array
    {
        $allocations = [];
        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $available = (float)$batch['qty_unreserved'];
            $toAllocate = min($remaining, $available);

            $allocations[] = [
                'batch_id' => $batch['id'],
                'batch_code' => $batch['batch_code'],
                'quantity' => $toAllocate,
                'unit_cost' => (float)$batch['unit_cost'],
                'total_cost' => $toAllocate * (float)$batch['unit_cost']
            ];

            $remaining -= $toAllocate;
        }

        if ($remaining > 0.0001) {
            throw new \RuntimeException(
                "Insufficient quantity in batches. Remaining unallocated: {$remaining}"
            );
        }

        return $allocations;
    }

    /**
     * Process batch allocation (reduce quantities and create movements)
     */
    public function processBatchAllocation(
        array $allocations,
        int $documentId,
        int $documentLineId,
        string $method,
        int $userId
    ): void {
        $this->db->beginTransaction();

        try {
            foreach ($allocations as $allocation) {
                $batchId = $allocation['batch_id'];
                $quantity = $allocation['quantity'];
                $unitCost = $allocation['unit_cost'];

                // Get current batch
                $batch = $this->getBatchById($batchId);
                if (!$batch) {
                    throw new \RuntimeException("Batch not found: {$batchId}");
                }

                // Check availability
                if ($batch['qty_available'] < $quantity) {
                    throw new \RuntimeException(
                        "Insufficient quantity in batch {$batch['batch_code']}"
                    );
                }

                // Reduce batch quantity
                $newQty = $batch['qty_available'] - $quantity;
                $this->db->update('inventory_batches', [
                    'qty_available' => $newQty,
                    'status' => $newQty <= 0 ? self::STATUS_DEPLETED : $batch['status']
                ], ['id' => $batchId]);

                // Create batch movement
                $this->db->insert('inventory_batch_movements', [
                    'batch_id' => $batchId,
                    'item_id' => $batch['item_id'],
                    'document_id' => $documentId,
                    'document_line_id' => $documentLineId,
                    'movement_type' => 'out',
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'balance_before' => $batch['qty_available'],
                    'balance_after' => $newQty
                ]);

                // Create allocation record
                $this->db->insert('inventory_issue_allocations', [
                    'document_id' => $documentId,
                    'document_line_id' => $documentLineId,
                    'batch_id' => $batchId,
                    'allocation_method' => $method,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $allocation['total_cost'],
                    'allocated_by' => $userId
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get available quantity for item
     */
    public function getAvailableQuantity(int $itemId): float
    {
        $result = $this->db->fetch(
            "SELECT SUM(b.qty_available - COALESCE(r.qty_reserved, 0)) as available
             FROM inventory_batches b
             LEFT JOIN (
                 SELECT batch_id, SUM(quantity) as qty_reserved
                 FROM inventory_batch_reservations
                 WHERE status = 'active'
                 GROUP BY batch_id
             ) r ON b.id = r.batch_id
             WHERE b.item_id = ? AND b.status = ?",
            [$itemId, self::STATUS_ACTIVE]
        );

        return (float)($result['available'] ?? 0);
    }

    /**
     * Get batch history for item
     */
    public function getBatchHistory(int $itemId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT bm.*, b.batch_code, b.unit_cost as batch_cost,
                    d.document_number, d.type as document_type
             FROM inventory_batch_movements bm
             JOIN inventory_batches b ON bm.batch_id = b.id
             LEFT JOIN documents d ON bm.document_id = d.id
             WHERE bm.item_id = ?
             ORDER BY bm.created_at DESC
             LIMIT ?",
            [$itemId, (int)$limit]
        );
    }

    /**
     * Generate unique batch code
     */
    private function generateBatchCode(int $itemId): string
    {
        $item = $this->db->fetch("SELECT sku FROM items WHERE id = ?", [$itemId]);
        $sku = $item ? $item['sku'] : 'ITEM';

        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

        return "{$sku}-{$date}-{$random}";
    }

    /**
     * Get costing method for item
     */
    public function getItemCostingMethod(int $itemId): string
    {
        $item = $this->db->fetch(
            "SELECT costing_method, allow_method_override FROM items WHERE id = ?",
            [$itemId]
        );

        return $item ? $item['costing_method'] : self::METHOD_FIFO;
    }

    /**
     * Check if method override is allowed for item
     */
    public function isMethodOverrideAllowed(int $itemId): bool
    {
        $item = $this->db->fetch(
            "SELECT allow_method_override FROM items WHERE id = ?",
            [$itemId]
        );

        return $item && (bool)$item['allow_method_override'];
    }

    /**
     * Get batch allocation details for document
     */
    public function getDocumentAllocations(int $documentId): array
    {
        return $this->db->fetchAll(
            "SELECT ia.*, b.batch_code, b.received_date, b.supplier_id,
                    dl.item_id, i.sku, i.name as item_name,
                    u.name as allocated_by_name
             FROM inventory_issue_allocations ia
             JOIN inventory_batches b ON ia.batch_id = b.id
             JOIN document_lines dl ON ia.document_line_id = dl.id
             JOIN items i ON dl.item_id = i.id
             LEFT JOIN users u ON ia.allocated_by = u.id
             WHERE ia.document_id = ?
             ORDER BY dl.line_number, ia.id",
            [$documentId]
        );
    }
}
