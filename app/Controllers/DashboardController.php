<?php
/**
 * Dashboard Controller
 */

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index(): void
    {
        $this->requireAuth();

        // Get dashboard stats
        $stats = $this->getDashboardStats();

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats
        ]);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $stats = [
            'items' => 0,
            'products' => 0,
            'production_orders' => 0,
            'print_jobs_pending' => 0
        ];

        try {
            $db = $this->db();

            // Items count
            if ($db->tableExists('items')) {
                $stats['items'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM items WHERE is_active = 1");
            }

            // Products count
            if ($db->tableExists('products')) {
                $stats['products'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM products WHERE status = 'active'");
            }

            // Active production orders
            if ($db->tableExists('production_orders')) {
                $stats['production_orders'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM production_orders WHERE status IN ('draft', 'in_progress')"
                );
            }

            // Pending print jobs
            if ($db->tableExists('print_jobs')) {
                $stats['print_jobs_pending'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM print_jobs WHERE status IN ('pending', 'in_progress')"
                );
            }

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to get dashboard stats', [
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }
}
