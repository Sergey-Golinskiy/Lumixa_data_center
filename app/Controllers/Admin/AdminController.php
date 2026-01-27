<?php
/**
 * Admin Controller - Admin dashboard
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->authorize('admin.access');

        // Get admin stats
        $stats = $this->getAdminStats();

        // Get recent audit entries
        $recentAudit = $this->getRecentAudit();

        $this->view('admin/index', [
            'title' => $this->app->getTranslator()->get('admin_dashboard'),
            'stats' => $stats,
            'recentAudit' => $recentAudit
        ]);
    }

    /**
     * Get admin statistics
     */
    private function getAdminStats(): array
    {
        $stats = [
            // User Management
            'users' => 0,
            'active_users' => 0,
            'roles' => 0,

            // Catalog
            'products' => 0,
            'categories' => 0,
            'collections' => 0,

            // Warehouse
            'items' => 0,
            'partners' => 0,

            // Equipment
            'printers' => 0,

            // System
            'backups' => 0,
            'audit_entries' => 0,
            'audit_today' => 0
        ];

        try {
            $db = $this->db();

            // User stats
            $stats['users'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM users");
            $stats['active_users'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE is_active = 1");
            $stats['roles'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM roles");

            // Catalog stats
            if ($db->tableExists('products')) {
                $stats['products'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM products");
            }

            if ($db->tableExists('product_categories')) {
                $stats['categories'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM product_categories");
            }

            if ($db->tableExists('product_collections')) {
                $stats['collections'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM product_collections");
            }

            // Warehouse stats
            if ($db->tableExists('items')) {
                $stats['items'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM items");
            }

            if ($db->tableExists('partners')) {
                $stats['partners'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM partners");
            }

            // Equipment stats
            if ($db->tableExists('printers')) {
                $stats['printers'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM printers");
            }

            // System stats
            if ($db->tableExists('backups')) {
                $stats['backups'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM backups");
            }

            $stats['audit_entries'] = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM audit_log WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );

            $stats['audit_today'] = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM audit_log WHERE DATE(created_at) = CURDATE()"
            );

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to get admin stats', [
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }

    /**
     * Get recent audit entries
     */
    private function getRecentAudit(): array
    {
        try {
            return $this->db()->fetchAll(
                "SELECT al.*, u.name as user_name
                 FROM audit_log al
                 LEFT JOIN users u ON al.user_id = u.id
                 ORDER BY al.created_at DESC
                 LIMIT 5"
            );
        } catch (\Exception $e) {
            return [];
        }
    }
}
