<?php
/**
 * StockController - Stock balances and movements
 */

namespace App\Controllers\Warehouse;

use App\Core\Controller;

class StockController extends Controller
{
    /**
     * Stock balances overview
     */
    public function index(): void
    {
        $this->requirePermission('warehouse.stock.view');

        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $showEmpty = isset($_GET['show_empty']);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(i.sku LIKE ? OR i.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($category) {
            $where[] = "i.type = ?";
            $params[] = $category;
        }

        if (!$showEmpty) {
            $where[] = "sb.on_hand > 0";
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $total = $this->db()->fetchColumn(
            "SELECT COUNT(DISTINCT i.id) FROM items i
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE {$whereClause}",
            $params
        );

        // Get stock summary by item
        $offset = ($page - 1) * $perPage;
        $stocks = $this->db()->fetchAll(
            "SELECT i.id, i.sku, i.name, i.unit, i.type,
                    COALESCE(SUM(sb.on_hand), 0) as total_quantity,
                    COALESCE(SUM(sb.reserved), 0) as total_reserved,
                    COALESCE(SUM(sb.on_hand * sb.avg_cost), 0) as total_value,
                    COUNT(DISTINCT sb.lot_id) as lot_count
             FROM items i
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE {$whereClause}
             GROUP BY i.id, i.sku, i.name, i.unit, i.type
             ORDER BY i.sku
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get categories for filter
        $categories = $this->db()->fetchAll(
            "SELECT DISTINCT type FROM items WHERE type IS NOT NULL ORDER BY type"
        );

        // Summary totals
        $summary = $this->db()->fetch(
            "SELECT COUNT(DISTINCT i.id) as item_count,
                    SUM(sb.on_hand) as total_quantity,
                    SUM(sb.on_hand * sb.avg_cost) as total_value
             FROM items i
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE sb.on_hand > 0"
        );

        $this->render('warehouse/stock/index', [
            'title' => 'Stock Balances',
            'stocks' => $stocks,
            'categories' => $categories,
            'search' => $search,
            'category' => $category,
            'showEmpty' => $showEmpty,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'summary' => $summary
        ]);
    }

    /**
     * Stock details for specific item
     */
    public function show(string $id): void
    {
        $this->requirePermission('warehouse.stock.view');

        $item = $this->db()->fetch(
            "SELECT * FROM items WHERE id = ?",
            [$id]
        );

        if (!$item) {
            $this->notFound();
        }

        // Get stock by lot
        $stockByLot = $this->db()->fetchAll(
            "SELECT sb.*, l.lot_number, l.expiry_date, l.status as lot_status
             FROM stock_balances sb
             LEFT JOIN lots l ON sb.lot_id = l.id
             WHERE sb.item_id = ?
             ORDER BY sb.on_hand DESC",
            [$id]
        );

        // Get recent movements
        $movements = $this->db()->fetchAll(
            "SELECT sm.*, d.document_number, d.type as document_type, l.lot_number
             FROM stock_movements sm
             JOIN documents d ON sm.document_id = d.id
             LEFT JOIN lots l ON sm.lot_id = l.id
             WHERE sm.item_id = ?
             ORDER BY sm.created_at DESC
             LIMIT 100",
            [$id]
        );

        // Calculate totals
        $totals = $this->db()->fetch(
            "SELECT SUM(on_hand) as total_quantity,
                    SUM(reserved) as total_reserved,
                    SUM(on_hand * avg_cost) as total_value
             FROM stock_balances
             WHERE item_id = ?",
            [$id]
        );

        $this->render('warehouse/stock/show', [
            'title' => "Stock: {$item['sku']}",
            'item' => $item,
            'stockByLot' => $stockByLot,
            'movements' => $movements,
            'totals' => $totals
        ]);
    }

    /**
     * Stock movements report
     */
    public function movements(): void
    {
        $this->requirePermission('warehouse.stock.view');

        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $itemId = $_GET['item_id'] ?? '';
        $direction = $_GET['direction'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;

        // Build query
        $where = ["sm.created_at >= ?", "sm.created_at <= ?"];
        $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];

        if ($itemId) {
            $where[] = "sm.item_id = ?";
            $params[] = $itemId;
        }

        if ($direction) {
            $where[] = "sm.movement_type = ?";
            $params[] = $direction;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM stock_movements sm WHERE {$whereClause}",
            $params
        );

        // Get movements
        $offset = ($page - 1) * $perPage;
        $movements = $this->db()->fetchAll(
            "SELECT sm.*, i.sku, i.name as item_name, i.unit,
                    d.document_number, d.type as document_type,
                    l.lot_number
             FROM stock_movements sm
             JOIN items i ON sm.item_id = i.id
             JOIN documents d ON sm.document_id = d.id
             LEFT JOIN lots l ON sm.lot_id = l.id
             WHERE {$whereClause}
             ORDER BY sm.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get items for filter
        $items = $this->db()->fetchAll("SELECT id, sku, name FROM items ORDER BY sku");

        // Summary
        $inTotal = $this->db()->fetch(
            "SELECT SUM(quantity) as qty, SUM(quantity * unit_cost) as value
             FROM stock_movements sm WHERE {$whereClause} AND movement_type = 'in'",
            $params
        );
        $outTotal = $this->db()->fetch(
            "SELECT SUM(quantity) as qty, SUM(quantity * unit_cost) as value
             FROM stock_movements sm WHERE {$whereClause} AND movement_type = 'out'",
            $params
        );

        $this->render('warehouse/stock/movements', [
            'title' => 'Stock Movements',
            'movements' => $movements,
            'items' => $items,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'itemId' => $itemId,
            'direction' => $direction,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'inTotal' => $inTotal,
            'outTotal' => $outTotal
        ]);
    }

    /**
     * Low stock report
     */
    public function lowStock(): void
    {
        $this->requirePermission('warehouse.stock.view');

        $items = $this->db()->fetchAll(
            "SELECT i.*,
                    COALESCE(SUM(sb.on_hand), 0) as current_stock,
                    COALESCE(SUM(sb.reserved), 0) as reserved
             FROM items i
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE i.is_active = 1 AND i.min_stock > 0
             GROUP BY i.id
             HAVING current_stock <= i.min_stock
             ORDER BY (current_stock / i.min_stock) ASC"
        );

        $this->render('warehouse/stock/low-stock', [
            'title' => 'Low Stock Report',
            'items' => $items
        ]);
    }

    /**
     * Inventory valuation report
     */
    public function valuation(): void
    {
        $this->requirePermission('warehouse.stock.view');

        $category = $_GET['category'] ?? '';

        // Build query
        $where = ['sb.on_hand > 0'];
        $params = [];

        if ($category) {
            $where[] = "i.type = ?";
            $params[] = $category;
        }

        $whereClause = implode(' AND ', $where);

        // Get valuation by item
        $valuation = $this->db()->fetchAll(
            "SELECT i.id, i.sku, i.name, i.unit, i.type,
                    SUM(sb.on_hand) as quantity,
                    AVG(sb.avg_cost) as avg_cost,
                    SUM(sb.on_hand * sb.avg_cost) as total_value
             FROM items i
             JOIN stock_balances sb ON i.id = sb.item_id
             WHERE {$whereClause}
             GROUP BY i.id, i.sku, i.name, i.unit, i.type
             ORDER BY total_value DESC",
            $params
        );

        // Get categories
        $categories = $this->db()->fetchAll(
            "SELECT DISTINCT type FROM items WHERE type IS NOT NULL ORDER BY type"
        );

        // Grand total
        $grandTotal = array_sum(array_column($valuation, 'total_value'));

        // Category breakdown
        $byCategory = [];
        foreach ($valuation as $item) {
            $cat = $item['type'] ?: 'Uncategorized';
            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = ['count' => 0, 'value' => 0];
            }
            $byCategory[$cat]['count']++;
            $byCategory[$cat]['value'] += $item['total_value'];
        }

        $this->render('warehouse/stock/valuation', [
            'title' => 'Inventory Valuation',
            'valuation' => $valuation,
            'categories' => $categories,
            'category' => $category,
            'grandTotal' => $grandTotal,
            'byCategory' => $byCategory
        ]);
    }
}
