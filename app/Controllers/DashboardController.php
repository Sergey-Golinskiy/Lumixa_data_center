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

        // Get comprehensive dashboard stats
        $stats = $this->getDashboardStats();

        // Get recent activities
        $recentOrders = $this->getRecentOrders();
        $recentDocuments = $this->getRecentDocuments();
        $lowStockItems = $this->getLowStockItems();
        $activePrintJobs = $this->getActivePrintJobs();
        $recentProducts = $this->getRecentProducts();

        $this->view('dashboard/index', [
            'title' => $this->app->getTranslator()->get('dashboard'),
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentDocuments' => $recentDocuments,
            'lowStockItems' => $lowStockItems,
            'activePrintJobs' => $activePrintJobs,
            'recentProducts' => $recentProducts
        ]);
    }

    /**
     * Get comprehensive dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $stats = [
            // Warehouse
            'items_total' => 0,
            'items_active' => 0,
            'stock_value' => 0,
            'low_stock_count' => 0,
            'documents_today' => 0,
            'partners_total' => 0,

            // Catalog
            'products_total' => 0,
            'products_active' => 0,
            'details_total' => 0,
            'details_printed' => 0,
            'variants_total' => 0,
            'categories_total' => 0,
            'collections_total' => 0,

            // Production
            'orders_active' => 0,
            'orders_completed_month' => 0,
            'tasks_pending' => 0,
            'tasks_in_progress' => 0,
            'print_jobs_queued' => 0,
            'print_jobs_printing' => 0,
            'print_jobs_completed_today' => 0,

            // Equipment
            'printers_total' => 0,
            'printers_active' => 0
        ];

        try {
            $db = $this->db();

            // ===== WAREHOUSE STATS =====
            if ($db->tableExists('items')) {
                $stats['items_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM items");
                $stats['items_active'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM items WHERE is_active = 1");
            }

            if ($db->tableExists('stock')) {
                $stats['stock_value'] = (float)$db->fetchColumn(
                    "SELECT COALESCE(SUM(quantity * unit_cost), 0) FROM stock WHERE quantity > 0"
                ) ?: 0;

                $stats['low_stock_count'] = (int)$db->fetchColumn(
                    "SELECT COUNT(DISTINCT s.item_id) FROM stock s
                     JOIN items i ON s.item_id = i.id
                     WHERE s.quantity <= i.min_stock AND i.min_stock > 0"
                );
            }

            if ($db->tableExists('documents')) {
                $stats['documents_today'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM documents WHERE DATE(created_at) = CURDATE()"
                );
            }

            if ($db->tableExists('partners')) {
                $stats['partners_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM partners");
            }

            // ===== CATALOG STATS =====
            if ($db->tableExists('products')) {
                $stats['products_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM products");
                $stats['products_active'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM products WHERE is_active = 1"
                );
            }

            if ($db->tableExists('details')) {
                $stats['details_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM details");
                $stats['details_printed'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM details WHERE detail_type = 'printed'"
                );
            }

            if ($db->tableExists('variants')) {
                $stats['variants_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM variants");
            }

            if ($db->tableExists('product_categories')) {
                $stats['categories_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM product_categories");
            }

            if ($db->tableExists('product_collections')) {
                $stats['collections_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM product_collections");
            }

            // ===== PRODUCTION STATS =====
            if ($db->tableExists('production_orders')) {
                $stats['orders_active'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM production_orders WHERE status IN ('draft', 'in_progress')"
                );
                $stats['orders_completed_month'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM production_orders
                     WHERE status = 'completed'
                     AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
                );
            }

            if ($db->tableExists('production_tasks')) {
                $stats['tasks_pending'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM production_tasks WHERE status = 'pending'"
                );
                $stats['tasks_in_progress'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM production_tasks WHERE status = 'in_progress'"
                );
            }

            if ($db->tableExists('print_queue')) {
                $stats['print_jobs_queued'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM print_queue WHERE status = 'queued'"
                );
                $stats['print_jobs_printing'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM print_queue WHERE status = 'printing'"
                );
                $stats['print_jobs_completed_today'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM print_queue
                     WHERE status = 'completed' AND DATE(completed_at) = CURDATE()"
                );
            }

            // ===== EQUIPMENT STATS =====
            if ($db->tableExists('printers')) {
                $stats['printers_total'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM printers");
                $stats['printers_active'] = (int)$db->fetchColumn(
                    "SELECT COUNT(*) FROM printers WHERE is_active = 1"
                );
            }

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to get dashboard stats', [
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }

    /**
     * Get recent production orders
     */
    private function getRecentOrders(): array
    {
        try {
            if (!$this->db()->tableExists('production_orders')) {
                return [];
            }

            return $this->db()->fetchAll(
                "SELECT po.*, v.sku as variant_sku
                 FROM production_orders po
                 LEFT JOIN variants v ON po.variant_id = v.id
                 ORDER BY po.created_at DESC
                 LIMIT 5"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get recent warehouse documents
     */
    private function getRecentDocuments(): array
    {
        try {
            if (!$this->db()->tableExists('documents')) {
                return [];
            }

            return $this->db()->fetchAll(
                "SELECT d.*, u.name as created_by_name, p.name as partner_name
                 FROM documents d
                 LEFT JOIN users u ON d.created_by = u.id
                 LEFT JOIN partners p ON d.partner_id = p.id
                 ORDER BY d.created_at DESC
                 LIMIT 5"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get low stock items
     */
    private function getLowStockItems(): array
    {
        try {
            if (!$this->db()->tableExists('stock') || !$this->db()->tableExists('items')) {
                return [];
            }

            return $this->db()->fetchAll(
                "SELECT i.*, COALESCE(SUM(s.quantity), 0) as current_stock
                 FROM items i
                 LEFT JOIN stock s ON i.id = s.item_id
                 WHERE i.min_stock > 0 AND i.is_active = 1
                 GROUP BY i.id
                 HAVING current_stock <= i.min_stock
                 ORDER BY (current_stock / i.min_stock) ASC
                 LIMIT 5"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get active print jobs
     */
    private function getActivePrintJobs(): array
    {
        try {
            if (!$this->db()->tableExists('print_queue')) {
                return [];
            }

            return $this->db()->fetchAll(
                "SELECT pq.*, pr.name as printer_name, v.sku as variant_sku
                 FROM print_queue pq
                 LEFT JOIN printers pr ON pq.printer_id = pr.id
                 LEFT JOIN variants v ON pq.variant_id = v.id
                 WHERE pq.status IN ('queued', 'printing')
                 ORDER BY pq.priority DESC, pq.created_at ASC
                 LIMIT 5"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get recently added/updated products
     */
    private function getRecentProducts(): array
    {
        try {
            if (!$this->db()->tableExists('products')) {
                return [];
            }

            return $this->db()->fetchAll(
                "SELECT p.*, pc.name as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 ORDER BY COALESCE(p.updated_at, p.created_at) DESC
                 LIMIT 5"
            );
        } catch (\Exception $e) {
            return [];
        }
    }
}
