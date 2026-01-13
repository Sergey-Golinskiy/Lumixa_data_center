<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

/**
 * Dashboard Controller
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index(): void
    {
        $this->setLayout('main');

        $stats = $this->getDashboardStats();
        $recentActivity = $this->getRecentActivity();
        $pendingTasks = $this->getPendingTasks();
        $lowStockItems = $this->getLowStockItems();

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'pendingTasks' => $pendingTasks,
            'lowStockItems' => $lowStockItems,
        ]);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $db = Database::getInstance();

        $stats = [];

        // Total items
        $stats['total_items'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM items WHERE is_active = 1"
        );

        // Total products
        $stats['total_products'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM products WHERE status = 'active'"
        );

        // Active production orders
        $stats['active_orders'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM production_orders po
             INNER JOIN production_statuses ps ON ps.id = po.status_id
             WHERE ps.code IN ('planned', 'in_progress')"
        );

        // Pending tasks
        $stats['pending_tasks'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM production_tasks pt
             INNER JOIN task_statuses ts ON ts.id = pt.status_id
             WHERE ts.code IN ('pending', 'assigned')"
        );

        // Print jobs in queue
        $stats['print_queue'] = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM print_jobs pj
             INNER JOIN print_job_statuses pjs ON pjs.id = pj.status_id
             WHERE pjs.code IN ('queued', 'reserved')"
        );

        // Low stock items count
        $stats['low_stock'] = (int) $db->fetchColumn(
            "SELECT COUNT(DISTINCT i.id) FROM items i
             LEFT JOIN stock s ON s.item_id = i.id
             WHERE i.is_active = 1 AND i.min_stock_level > 0
             AND COALESCE(s.on_hand, 0) < i.min_stock_level"
        );

        return $stats;
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            "SELECT a.*, u.username, u.first_name, u.last_name
             FROM audit_log a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT 10"
        );
    }

    /**
     * Get pending tasks for current user
     */
    private function getPendingTasks(): array
    {
        $db = Database::getInstance();
        $userId = userId();

        $sql = "SELECT pt.*, ts.name as status_name, ts.color as status_color,
                       ot.name as operation_name
                FROM production_tasks pt
                INNER JOIN task_statuses ts ON ts.id = pt.status_id
                LEFT JOIN operation_types ot ON ot.id = pt.operation_type_id
                WHERE ts.code IN ('pending', 'assigned', 'in_progress')";

        // If not admin/manager, only show assigned tasks
        $user = auth();
        if (!in_array('admin', $user['roles']) && !in_array('manager', $user['roles'])) {
            $sql .= " AND pt.assigned_to = ?";
            return $db->fetchAll($sql . " ORDER BY pt.priority DESC, pt.planned_start_date ASC LIMIT 5", [$userId]);
        }

        return $db->fetchAll($sql . " ORDER BY pt.priority DESC, pt.planned_start_date ASC LIMIT 10");
    }

    /**
     * Get low stock items
     */
    private function getLowStockItems(): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            "SELECT i.*, it.name as type_name, u.code as unit_code,
                    COALESCE(SUM(s.on_hand), 0) as total_on_hand,
                    COALESCE(SUM(s.reserved), 0) as total_reserved
             FROM items i
             INNER JOIN item_types it ON it.id = i.item_type_id
             INNER JOIN units u ON u.id = i.unit_id
             LEFT JOIN stock s ON s.item_id = i.id
             WHERE i.is_active = 1 AND i.min_stock_level > 0
             GROUP BY i.id
             HAVING total_on_hand < i.min_stock_level
             ORDER BY (total_on_hand / i.min_stock_level) ASC
             LIMIT 10"
        );
    }
}
