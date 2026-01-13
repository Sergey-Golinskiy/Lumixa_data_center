<?php

namespace App\Services;

use App\Core\Database;

/**
 * Audit Log Service
 */
class AuditService
{
    /**
     * Log an action
     */
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): int {
        $db = Database::getInstance();

        return $db->insert('audit_log', [
            'user_id' => userId(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Log entity creation
     */
    public function logCreate(string $entityType, int $entityId, array $data, ?string $description = null): void
    {
        $this->log('create', $entityType, $entityId, $description ?? "{$entityType} created", null, $data);
    }

    /**
     * Log entity update
     */
    public function logUpdate(string $entityType, int $entityId, array $oldData, array $newData, ?string $description = null): void
    {
        // Calculate diff
        $changes = $this->getDiff($oldData, $newData);

        if (!empty($changes['old']) || !empty($changes['new'])) {
            $this->log('update', $entityType, $entityId, $description ?? "{$entityType} updated", $changes['old'], $changes['new']);
        }
    }

    /**
     * Log entity deletion
     */
    public function logDelete(string $entityType, int $entityId, array $data, ?string $description = null): void
    {
        $this->log('delete', $entityType, $entityId, $description ?? "{$entityType} deleted", $data, null);
    }

    /**
     * Log document posting
     */
    public function logPosting(string $documentType, int $documentId, ?string $documentNumber = null): void
    {
        $description = $documentNumber
            ? "Document {$documentNumber} posted"
            : "Document posted";

        $this->log('post', $documentType, $documentId, $description);
    }

    /**
     * Log status change
     */
    public function logStatusChange(string $entityType, int $entityId, string $fromStatus, string $toStatus): void
    {
        $this->log(
            'status_change',
            $entityType,
            $entityId,
            "Status changed from {$fromStatus} to {$toStatus}",
            ['status' => $fromStatus],
            ['status' => $toStatus]
        );
    }

    /**
     * Get audit logs with filters
     */
    public function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $db = Database::getInstance();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = ?';
            $params[] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['entity_id'])) {
            $where[] = 'entity_id = ?';
            $params[] = $filters['entity_id'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT a.*, u.username, u.first_name, u.last_name
                FROM audit_log a
                LEFT JOIN users u ON u.id = a.user_id
                WHERE {$whereClause}
                ORDER BY a.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $db->fetchAll($sql, $params);
    }

    /**
     * Count logs with filters
     */
    public function countLogs(array $filters = []): int
    {
        $db = Database::getInstance();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = ?';
            $params[] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        $whereClause = implode(' AND ', $where);

        return (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM audit_log WHERE {$whereClause}",
            $params
        );
    }

    /**
     * Get log entry by ID
     */
    public function getLog(int $id): ?array
    {
        $db = Database::getInstance();

        return $db->fetch(
            "SELECT a.*, u.username, u.first_name, u.last_name
             FROM audit_log a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.id = ?",
            [$id]
        );
    }

    /**
     * Get entity history
     */
    public function getEntityHistory(string $entityType, int $entityId, int $limit = 20): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            "SELECT a.*, u.username, u.first_name, u.last_name
             FROM audit_log a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.entity_type = ? AND a.entity_id = ?
             ORDER BY a.created_at DESC
             LIMIT {$limit}",
            [$entityType, $entityId]
        );
    }

    /**
     * Calculate diff between old and new values
     */
    private function getDiff(array $old, array $new): array
    {
        $oldDiff = [];
        $newDiff = [];

        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $old) || $old[$key] !== $value) {
                $oldDiff[$key] = $old[$key] ?? null;
                $newDiff[$key] = $value;
            }
        }

        return ['old' => $oldDiff, 'new' => $newDiff];
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): ?string
    {
        $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return null;
    }
}
