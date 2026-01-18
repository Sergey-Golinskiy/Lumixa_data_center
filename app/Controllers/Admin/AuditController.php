<?php
/**
 * AuditController - Audit log viewer
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class AuditController extends Controller
{
    /**
     * List audit entries
     */
    public function index(): void
    {
        $this->requirePermission('admin.audit.view');

        $page = max(1, (int)$this->get('page', 1));
        $perPage = 50;

        $filters = [
            'user_id' => $this->get('user_id', ''),
            'action' => $this->get('action', ''),
            'table' => $this->get('table', ''),
            'from' => $this->get('from', ''),
            'to' => $this->get('to', '')
        ];

        $where = ['1=1'];
        $params = [];

        if ($filters['user_id']) {
            $where[] = "a.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if ($filters['action']) {
            $where[] = "a.action LIKE ?";
            $params[] = "%{$filters['action']}%";
        }

        if ($filters['table']) {
            $where[] = "a.entity_type = ?";
            $params[] = $filters['table'];
        }

        if ($filters['from']) {
            $where[] = "DATE(a.created_at) >= ?";
            $params[] = $filters['from'];
        }

        if ($filters['to']) {
            $where[] = "DATE(a.created_at) <= ?";
            $params[] = $filters['to'];
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db()->fetchColumn("SELECT COUNT(*) FROM audit_log a WHERE {$whereClause}", $params);

        $offset = ($page - 1) * $perPage;
        $entries = $this->db()->fetchAll(
            "SELECT a.*, u.name
             FROM audit_log a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE {$whereClause}
             ORDER BY a.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get filter options
        $users = $this->db()->fetchAll("SELECT id, name FROM users ORDER BY name");
        $tables = $this->db()->fetchAll("SELECT DISTINCT entity_type FROM audit_log ORDER BY entity_type");
        $actions = $this->db()->fetchAll("SELECT DISTINCT action FROM audit_log ORDER BY action");

        $this->view('admin/audit/index', [
            'title' => 'Audit Log',
            'entries' => $entries,
            'filters' => $filters,
            'users' => $users,
            'tables' => array_column($tables, 'entity_type'),
            'actions' => array_column($actions, 'action'),
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show audit entry details
     */
    public function show(string $id): void
    {
        $this->requirePermission('admin.audit.view');

        $entry = $this->db()->fetch(
            "SELECT a.*, u.name
             FROM audit_log a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.id = ?",
            [$id]
        );

        if (!$entry) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Entry Not Found']);
            return;
        }

        // Decode JSON data
        $entry['old_data_decoded'] = $entry['old_values'] ? json_decode($entry['old_values'], true) : null;
        $entry['new_data_decoded'] = $entry['new_values'] ? json_decode($entry['new_values'], true) : null;

        $this->view('admin/audit/show', [
            'title' => 'Audit Entry #' . $id,
            'entry' => $entry
        ]);
    }
}
