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

        $this->view('admin/index', [
            'title' => 'Admin Dashboard',
            'stats' => $stats
        ]);
    }

    /**
     * Get admin statistics
     */
    private function getAdminStats(): array
    {
        $stats = [
            'users' => 0,
            'roles' => 0,
            'backups' => 0,
            'audit_entries' => 0
        ];

        try {
            $db = $this->db();

            $stats['users'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM users");
            $stats['roles'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM roles");

            if ($db->tableExists('backups')) {
                $stats['backups'] = (int)$db->fetchColumn("SELECT COUNT(*) FROM backups");
            }

            $stats['audit_entries'] = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM audit_log WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );

        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to get admin stats', [
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }
}
