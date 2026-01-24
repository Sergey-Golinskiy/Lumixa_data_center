<?php
/**
 * Audit Service - Activity logging
 */

namespace App\Services;

use App\Core\Application;
use App\Core\Database;

class AuditService
{
    private Application $app;
    private Database $db;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app->getDatabase();
    }

    /**
     * Log action
     */
    public function log(
        string $action,
        string $entityType,
        int $entityId,
        $oldValues = null,
        $newValues = null,
        ?int $userId = null
    ): void {
        try {
            $this->db->insert('audit_log', [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'request_id' => $this->app->getRequestId()
            ]);
        } catch (\Exception $e) {
            // Don't fail main operation if audit fails
            $this->app->getLogger()->error('Audit log failed', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get audit entries for entity
     */
    public function getForEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT al.*, u.name as user_name, u.email as user_email
             FROM audit_log al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.entity_type = ? AND al.entity_id = ?
             ORDER BY al.created_at DESC
             LIMIT {$limit}",
            [$entityType, $entityId]
        );
    }

    /**
     * Get recent audit entries
     */
    public function getRecent(int $limit = 100, array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['action'])) {
            $where[] = "al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['user_id'])) {
            $where[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = "al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "al.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "al.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereStr = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT al.*, u.name as user_name, u.email as user_email
             FROM audit_log al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$whereStr}
             ORDER BY al.created_at DESC
             LIMIT {$limit}",
            $params
        );
    }

    /**
     * Get audit entry by ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT al.*, u.name as user_name, u.email as user_email
             FROM audit_log al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE al.id = ?",
            [$id]
        );
    }

    /**
     * Paginate audit entries
     */
    public function paginate(int $page, int $perPage, array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['action'])) {
            $where[] = "al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['user_id'])) {
            $where[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = "al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        $whereStr = implode(' AND ', $where);

        // Count total
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM audit_log al WHERE {$whereStr}",
            $params
        );

        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        // Get entries
        $entries = $this->db->fetchAll(
            "SELECT al.*, u.name as user_name, u.email as user_email
             FROM audit_log al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$whereStr}
             ORDER BY al.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'entries' => $entries,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
    }
}
