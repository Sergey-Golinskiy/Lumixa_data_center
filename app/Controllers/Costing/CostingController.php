<?php
/**
 * CostingController - Cost analysis and reporting
 */

namespace App\Controllers\Costing;

use App\Core\Controller;

class CostingController extends Controller
{
    /**
     * Costing dashboard
     */
    public function index(): void
    {
        $this->requirePermission('costing.view');

        // Get summary stats
        $totalVariants = $this->db()->fetchColumn("SELECT COUNT(*) FROM variants WHERE is_active = 1");
        $variantsWithCost = $this->db()->fetchColumn("SELECT COUNT(*) FROM variant_costs WHERE total_cost > 0");
        $completedOrders = $this->db()->fetchColumn("SELECT COUNT(*) FROM production_orders WHERE status = 'completed'");

        // Get recent cost variances
        // Note: actual_cost calculated from actual_quantity * unit_cost for materials
        // Labor cost currently not tracked in production_tasks, using 0
        $recentVariances = $this->db()->fetchAll(
            "SELECT po.order_number, v.sku, v.name,
                    vc.total_cost as planned_cost,
                    COALESCE(mc.actual_material_cost, 0) as actual_cost,
                    po.quantity, po.completed_at
             FROM production_orders po
             JOIN variants v ON po.variant_id = v.id
             LEFT JOIN variant_costs vc ON v.id = vc.variant_id
             LEFT JOIN (
                SELECT order_id, SUM(actual_quantity * unit_cost) as actual_material_cost
                FROM material_consumption
                GROUP BY order_id
             ) mc ON po.id = mc.order_id
             WHERE po.status = 'completed'
             ORDER BY po.completed_at DESC
             LIMIT 10"
        );

        // Calculate variances
        foreach ($recentVariances as &$row) {
            $planned = ($row['planned_cost'] ?? 0) * $row['quantity'];
            $actual = $row['actual_cost'] ?? 0;
            $row['variance'] = $actual - $planned;
            $row['variance_percent'] = $planned > 0 ? (($actual - $planned) / $planned) * 100 : 0;
        }

        $this->render('costing/index', [
            'title' => 'Cost Analysis',
            'totalVariants' => $totalVariants,
            'variantsWithCost' => $variantsWithCost,
            'completedOrders' => $completedOrders,
            'recentVariances' => $recentVariances
        ]);
    }

    /**
     * Planned costs from BOM/Routing
     */
    public function plan(): void
    {
        $this->requirePermission('costing.view');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $search = trim($_GET['search'] ?? '');

        $where = ['v.is_active = 1'];
        $params = [];

        if ($search) {
            $where[] = "(v.sku LIKE ? OR v.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM variants v WHERE {$whereClause}",
            $params
        );

        $offset = ($page - 1) * $perPage;
        $variants = $this->db()->fetchAll(
            "SELECT v.id, v.sku, v.name, v.unit,
                    vc.material_cost, vc.labor_cost, vc.overhead_cost, vc.total_cost,
                    vc.calculated_at,
                    b.id as bom_id, b.name as bom_name,
                    r.id as routing_id, r.name as routing_name
             FROM variants v
             LEFT JOIN variant_costs vc ON v.id = vc.variant_id
             LEFT JOIN bom b ON v.id = b.variant_id AND b.status = 'active'
             LEFT JOIN routing r ON v.id = r.variant_id AND r.status = 'active'
             WHERE {$whereClause}
             ORDER BY v.sku
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('costing/plan', [
            'title' => 'Planned Costs',
            'variants' => $variants,
            'search' => $search,
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Actual costs from production orders
     */
    public function actual(): void
    {
        $this->requirePermission('costing.view');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $dateFrom = $_GET['from'] ?? date('Y-m-01');
        $dateTo = $_GET['to'] ?? date('Y-m-d');

        $offset = ($page - 1) * $perPage;

        // Note: material cost = actual_quantity * unit_cost
        // Labor time calculated from actual_start/actual_end, cost not tracked
        $orders = $this->db()->fetchAll(
            "SELECT po.id, po.order_number, po.quantity, po.completed_quantity, po.completed_at,
                    v.sku, v.name as variant_name,
                    vc.total_cost as planned_cost,
                    COALESCE(mc.material_cost, 0) as actual_material_cost,
                    COALESCE(mc.material_count, 0) as material_count,
                    0 as actual_labor_cost,
                    COALESCE(pt.labor_minutes, 0) as labor_minutes,
                    COALESCE(pt.task_count, 0) as task_count
             FROM production_orders po
             JOIN variants v ON po.variant_id = v.id
             LEFT JOIN variant_costs vc ON v.id = vc.variant_id
             LEFT JOIN (
                SELECT order_id,
                       SUM(actual_quantity * unit_cost) as material_cost,
                       COUNT(*) as material_count
                FROM material_consumption
                GROUP BY order_id
             ) mc ON po.id = mc.order_id
             LEFT JOIN (
                SELECT order_id,
                       SUM(TIMESTAMPDIFF(MINUTE, actual_start, actual_end)) as labor_minutes,
                       COUNT(*) as task_count
                FROM production_tasks
                WHERE actual_start IS NOT NULL AND actual_end IS NOT NULL
                GROUP BY order_id
             ) pt ON po.id = pt.order_id
             WHERE po.status = 'completed'
               AND DATE(po.completed_at) BETWEEN ? AND ?
             ORDER BY po.completed_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$dateFrom, $dateTo]
        );

        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM production_orders po
             WHERE po.status = 'completed' AND DATE(po.completed_at) BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );

        // Calculate totals
        foreach ($orders as &$order) {
            $order['actual_total'] = $order['actual_material_cost'] + $order['actual_labor_cost'];
            $order['planned_total'] = ($order['planned_cost'] ?? 0) * $order['completed_quantity'];
            $order['variance'] = $order['actual_total'] - $order['planned_total'];
        }

        $this->render('costing/actual', [
            'title' => 'Actual Costs',
            'orders' => $orders,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Plan vs Actual comparison
     */
    public function compare(): void
    {
        $this->requirePermission('costing.view');

        $dateFrom = $_GET['from'] ?? date('Y-m-01');
        $dateTo = $_GET['to'] ?? date('Y-m-d');

        // Aggregate by variant
        // Note: material cost = actual_quantity * unit_cost, labor cost not tracked
        $comparison = $this->db()->fetchAll(
            "SELECT v.id, v.sku, v.name,
                    SUM(po.completed_quantity) as total_produced,
                    vc.total_cost as unit_planned_cost,
                    SUM(po.completed_quantity) * COALESCE(vc.total_cost, 0) as total_planned_cost,
                    COALESCE(SUM(mc.material_cost), 0) as total_material_cost,
                    0 as total_labor_cost
             FROM production_orders po
             JOIN variants v ON po.variant_id = v.id
             LEFT JOIN variant_costs vc ON v.id = vc.variant_id
             LEFT JOIN (
                SELECT order_id, SUM(actual_quantity * unit_cost) as material_cost
                FROM material_consumption
                GROUP BY order_id
             ) mc ON po.id = mc.order_id
             WHERE po.status = 'completed'
               AND DATE(po.completed_at) BETWEEN ? AND ?
             GROUP BY v.id, v.sku, v.name, vc.total_cost
             ORDER BY SUM(po.completed_quantity) DESC",
            [$dateFrom, $dateTo]
        );

        $totals = [
            'produced' => 0,
            'planned' => 0,
            'actual' => 0,
            'material' => 0,
            'labor' => 0
        ];

        foreach ($comparison as &$row) {
            $row['total_actual_cost'] = $row['total_material_cost'] + $row['total_labor_cost'];
            $row['variance'] = $row['total_actual_cost'] - $row['total_planned_cost'];
            $row['variance_percent'] = $row['total_planned_cost'] > 0
                ? (($row['total_actual_cost'] - $row['total_planned_cost']) / $row['total_planned_cost']) * 100
                : 0;
            $row['unit_actual_cost'] = $row['total_produced'] > 0
                ? $row['total_actual_cost'] / $row['total_produced']
                : 0;

            $totals['produced'] += $row['total_produced'];
            $totals['planned'] += $row['total_planned_cost'];
            $totals['actual'] += $row['total_actual_cost'];
            $totals['material'] += $row['total_material_cost'];
            $totals['labor'] += $row['total_labor_cost'];
        }

        $totals['variance'] = $totals['actual'] - $totals['planned'];
        $totals['variance_percent'] = $totals['planned'] > 0
            ? (($totals['actual'] - $totals['planned']) / $totals['planned']) * 100
            : 0;

        $this->render('costing/compare', [
            'title' => 'Plan vs Actual',
            'comparison' => $comparison,
            'totals' => $totals,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
    }

    /**
     * Detailed cost breakdown for variant
     */
    public function variant(string $id): void
    {
        $this->requirePermission('costing.view');

        $variant = $this->db()->fetch(
            "SELECT v.*, vc.material_cost, vc.labor_cost, vc.overhead_cost, vc.total_cost, vc.calculated_at
             FROM variants v
             LEFT JOIN variant_costs vc ON v.id = vc.variant_id
             WHERE v.id = ?",
            [$id]
        );

        if (!$variant) {
            $this->notFound();
        }

        // Get active BOM with lines
        $bom = $this->db()->fetch(
            "SELECT * FROM bom WHERE variant_id = ? AND status = 'active'",
            [$id]
        );

        $bomLines = [];
        if ($bom) {
            $bomLines = $this->db()->fetchAll(
                "SELECT bl.*, i.sku, i.name, i.unit, i.price
                 FROM bom_lines bl
                 JOIN items i ON bl.item_id = i.id
                 WHERE bl.bom_id = ?
                 ORDER BY bl.sequence",
                [$bom['id']]
            );

            foreach ($bomLines as &$line) {
                $line['line_cost'] = $line['quantity'] * ($line['price'] ?? 0);
            }
        }

        // Get active routing with operations
        $routing = $this->db()->fetch(
            "SELECT * FROM routing WHERE variant_id = ? AND status = 'active'",
            [$id]
        );

        $operations = [];
        if ($routing) {
            $operations = $this->db()->fetchAll(
                "SELECT ro.*, wc.name as work_center_name, wc.hour_rate
                 FROM routing_operations ro
                 LEFT JOIN work_centers wc ON ro.work_center_id = wc.id
                 WHERE ro.routing_id = ?
                 ORDER BY ro.sequence",
                [$routing['id']]
            );

            foreach ($operations as &$op) {
                $op['operation_cost'] = ($op['setup_time'] + $op['run_time']) / 60 * ($op['hour_rate'] ?? 0);
            }
        }

        // Get production history
        // Note: material cost = actual_quantity * unit_cost, labor cost not tracked
        $history = $this->db()->fetchAll(
            "SELECT po.id, po.order_number, po.completed_quantity, po.completed_at,
                    COALESCE(mc.material_cost, 0) as actual_material,
                    0 as actual_labor
             FROM production_orders po
             LEFT JOIN (
                SELECT order_id, SUM(actual_quantity * unit_cost) as material_cost
                FROM material_consumption
                GROUP BY order_id
             ) mc ON po.id = mc.order_id
             WHERE po.variant_id = ? AND po.status = 'completed'
             ORDER BY po.completed_at DESC
             LIMIT 20",
            [$id]
        );

        foreach ($history as &$row) {
            $row['total_actual'] = $row['actual_material'] + $row['actual_labor'];
            $row['unit_cost'] = $row['completed_quantity'] > 0
                ? $row['total_actual'] / $row['completed_quantity']
                : 0;
        }

        $this->render('costing/variant', [
            'title' => "Cost Analysis: {$variant['sku']}",
            'variant' => $variant,
            'bom' => $bom,
            'bomLines' => $bomLines,
            'routing' => $routing,
            'operations' => $operations,
            'history' => $history
        ]);
    }
}
