<?php

namespace App\Services;

use App\Core\Database;

/**
 * Stock Service
 * Handles inventory operations, reservations, and stock movements
 */
class StockService
{
    private Database $db;
    private AuditService $auditService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->auditService = new AuditService();
    }

    /**
     * Get stock for item
     */
    public function getItemStock(int $itemId, ?int $warehouseId = null, ?int $lotId = null): array
    {
        $where = ['item_id = ?'];
        $params = [$itemId];

        if ($warehouseId) {
            $where[] = 'warehouse_id = ?';
            $params[] = $warehouseId;
        }

        if ($lotId !== null) {
            $where[] = ($lotId === 0 ? 'lot_id IS NULL' : 'lot_id = ?');
            if ($lotId !== 0) {
                $params[] = $lotId;
            }
        }

        $whereClause = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT s.*, w.name as warehouse_name, l.lot_number, l.color
             FROM stock s
             INNER JOIN warehouses w ON w.id = s.warehouse_id
             LEFT JOIN lots l ON l.id = s.lot_id
             WHERE {$whereClause}
             ORDER BY w.name, l.lot_number",
            $params
        );
    }

    /**
     * Get total stock for item
     */
    public function getTotalStock(int $itemId): array
    {
        $result = $this->db->fetch(
            "SELECT
                COALESCE(SUM(on_hand), 0) as on_hand,
                COALESCE(SUM(reserved), 0) as reserved,
                COALESCE(SUM(on_hand), 0) - COALESCE(SUM(reserved), 0) as available
             FROM stock WHERE item_id = ?",
            [$itemId]
        );

        return [
            'on_hand' => (float) $result['on_hand'],
            'reserved' => (float) $result['reserved'],
            'available' => (float) $result['available'],
        ];
    }

    /**
     * Get available quantity for item/lot in warehouse
     */
    public function getAvailable(int $itemId, int $warehouseId, ?int $lotId = null): float
    {
        $where = 'item_id = ? AND warehouse_id = ?';
        $params = [$itemId, $warehouseId];

        if ($lotId !== null) {
            $where .= ' AND lot_id = ?';
            $params[] = $lotId;
        } else {
            $where .= ' AND lot_id IS NULL';
        }

        $stock = $this->db->fetch(
            "SELECT on_hand, reserved FROM stock WHERE {$where}",
            $params
        );

        if (!$stock) {
            return 0;
        }

        return (float) $stock['on_hand'] - (float) $stock['reserved'];
    }

    /**
     * Check if sufficient stock is available
     */
    public function checkAvailability(int $itemId, float $quantity, int $warehouseId, ?int $lotId = null): array
    {
        $available = $this->getAvailable($itemId, $warehouseId, $lotId);

        return [
            'available' => $available,
            'requested' => $quantity,
            'sufficient' => $available >= $quantity,
            'shortage' => max(0, $quantity - $available),
        ];
    }

    /**
     * Update stock levels
     */
    public function updateStock(
        int $itemId,
        int $warehouseId,
        ?int $lotId,
        float $onHandDelta,
        float $reservedDelta = 0
    ): void {
        $lotCondition = $lotId ? 'lot_id = ?' : 'lot_id IS NULL';
        $params = [$itemId, $warehouseId];
        if ($lotId) {
            $params[] = $lotId;
        }

        // Check if stock record exists
        $existing = $this->db->fetch(
            "SELECT id, on_hand, reserved FROM stock
             WHERE item_id = ? AND warehouse_id = ? AND {$lotCondition}",
            $params
        );

        if ($existing) {
            // Update existing
            $newOnHand = (float) $existing['on_hand'] + $onHandDelta;
            $newReserved = (float) $existing['reserved'] + $reservedDelta;

            // Validate
            if ($newOnHand < 0 && !$this->allowNegativeStock()) {
                throw new \RuntimeException('Insufficient stock');
            }

            if ($newReserved < 0) {
                $newReserved = 0;
            }

            $this->db->update('stock', [
                'on_hand' => $newOnHand,
                'reserved' => $newReserved,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$existing['id']]);
        } else {
            // Create new
            if ($onHandDelta < 0 && !$this->allowNegativeStock()) {
                throw new \RuntimeException('Insufficient stock');
            }

            $this->db->insert('stock', [
                'item_id' => $itemId,
                'lot_id' => $lotId,
                'warehouse_id' => $warehouseId,
                'location_id' => null,
                'on_hand' => max(0, $onHandDelta),
                'reserved' => max(0, $reservedDelta),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Create reservation
     */
    public function createReservation(
        int $itemId,
        ?int $lotId,
        int $warehouseId,
        float $quantity,
        string $sourceType,
        int $sourceId
    ): int {
        // Check availability
        $check = $this->checkAvailability($itemId, $quantity, $warehouseId, $lotId);

        if (!$check['sufficient']) {
            throw new \RuntimeException(
                "Insufficient stock. Available: {$check['available']}, Requested: {$quantity}"
            );
        }

        // Create reservation record
        $reservationId = $this->db->insert('reservations', [
            'item_id' => $itemId,
            'lot_id' => $lotId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'status' => 'active',
            'fulfilled_quantity' => 0,
            'created_by' => userId(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Update stock reserved quantity
        $this->updateStock($itemId, $warehouseId, $lotId, 0, $quantity);

        return $reservationId;
    }

    /**
     * Fulfill reservation (consume reserved stock)
     */
    public function fulfillReservation(int $reservationId, float $actualQuantity): void
    {
        $reservation = $this->db->fetch(
            "SELECT * FROM reservations WHERE id = ?",
            [$reservationId]
        );

        if (!$reservation) {
            throw new \RuntimeException('Reservation not found');
        }

        if ($reservation['status'] !== 'active') {
            throw new \RuntimeException('Reservation is not active');
        }

        // Update reservation
        $this->db->update('reservations', [
            'status' => 'fulfilled',
            'fulfilled_quantity' => $actualQuantity,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$reservationId]);

        // Calculate the reserved amount to release
        $reservedAmount = (float) $reservation['quantity'];

        // Update stock: decrease on_hand and reserved
        $this->updateStock(
            $reservation['item_id'],
            $reservation['warehouse_id'],
            $reservation['lot_id'],
            -$actualQuantity,
            -$reservedAmount
        );
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation(int $reservationId): void
    {
        $reservation = $this->db->fetch(
            "SELECT * FROM reservations WHERE id = ?",
            [$reservationId]
        );

        if (!$reservation) {
            throw new \RuntimeException('Reservation not found');
        }

        if ($reservation['status'] !== 'active') {
            return; // Already cancelled or fulfilled
        }

        // Update reservation status
        $this->db->update('reservations', [
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$reservationId]);

        // Release reserved stock
        $this->updateStock(
            $reservation['item_id'],
            $reservation['warehouse_id'],
            $reservation['lot_id'],
            0,
            -(float) $reservation['quantity']
        );
    }

    /**
     * Get active reservations for source
     */
    public function getReservationsForSource(string $sourceType, int $sourceId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, i.name as item_name, i.sku, l.lot_number, l.color
             FROM reservations r
             INNER JOIN items i ON i.id = r.item_id
             LEFT JOIN lots l ON l.id = r.lot_id
             WHERE r.source_type = ? AND r.source_id = ? AND r.status = 'active'",
            [$sourceType, $sourceId]
        );
    }

    /**
     * Create stock posting (transaction log)
     */
    public function createPosting(
        int $documentId,
        int $documentLineId,
        int $itemId,
        ?int $lotId,
        int $warehouseId,
        float $quantity,
        string $direction,
        ?float $unitCost = null
    ): int {
        return $this->db->insert('stock_postings', [
            'document_id' => $documentId,
            'document_line_id' => $documentLineId,
            'item_id' => $itemId,
            'lot_id' => $lotId,
            'warehouse_id' => $warehouseId,
            'location_id' => null,
            'quantity' => abs($quantity),
            'direction' => $direction,
            'unit_cost' => $unitCost,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Calculate average cost for item
     */
    public function calculateAverageCost(int $itemId): float
    {
        $result = $this->db->fetch(
            "SELECT
                SUM(quantity * COALESCE(unit_cost, 0)) as total_value,
                SUM(quantity) as total_qty
             FROM stock_postings
             WHERE item_id = ? AND direction = 'in' AND unit_cost IS NOT NULL",
            [$itemId]
        );

        if (!$result || (float) $result['total_qty'] == 0) {
            // Fallback to default price
            $item = $this->db->fetch("SELECT default_price FROM items WHERE id = ?", [$itemId]);
            return (float) ($item['default_price'] ?? 0);
        }

        return (float) $result['total_value'] / (float) $result['total_qty'];
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(): array
    {
        return $this->db->fetchAll(
            "SELECT i.*, it.name as type_name, u.code as unit_code,
                    COALESCE(SUM(s.on_hand), 0) as total_on_hand,
                    COALESCE(SUM(s.reserved), 0) as total_reserved,
                    COALESCE(SUM(s.on_hand), 0) - COALESCE(SUM(s.reserved), 0) as available
             FROM items i
             INNER JOIN item_types it ON it.id = i.item_type_id
             INNER JOIN units u ON u.id = i.unit_id
             LEFT JOIN stock s ON s.item_id = i.id
             WHERE i.is_active = 1 AND i.min_stock_level > 0
             GROUP BY i.id
             HAVING total_on_hand < i.min_stock_level
             ORDER BY (total_on_hand / i.min_stock_level) ASC"
        );
    }

    /**
     * Check if negative stock is allowed
     */
    private function allowNegativeStock(): bool
    {
        $setting = $this->db->fetch(
            "SELECT value FROM settings WHERE `key` = 'stock.allow_negative'"
        );
        return $setting && $setting['value'] === '1';
    }
}
