<?php
/**
 * BackupController - Database backup and restore management
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class BackupController extends Controller
{
    /**
     * List all backups
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->authorize('admin.backups.view');

        $backupPath = $this->app->storagePath('backups');

        // Ensure backup directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Get backup files
        $backups = [];
        $files = glob($backupPath . '/*.zip');

        if ($files) {
            foreach ($files as $file) {
                $filename = basename($file);
                $backups[] = [
                    'id' => md5($filename),
                    'filename' => $filename,
                    'path' => $file,
                    'size' => filesize($file),
                    'size_formatted' => $this->formatBytes(filesize($file)),
                    'created_at' => filemtime($file),
                    'created_at_formatted' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }

            // Sort by date, newest first
            usort($backups, fn($a, $b) => $b['created_at'] - $a['created_at']);
        }

        // Calculate total size
        $totalSize = array_sum(array_column($backups, 'size'));

        $this->view('admin/backups/index', [
            'title' => 'Backups',
            'backups' => $backups,
            'totalSize' => $this->formatBytes($totalSize),
            'backupCount' => count($backups),
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Create new backup
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->authorize('admin.backups.create');

        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $result = $this->createBackup();

            $this->logBackupAction('backup.created', 'backups', null, null, [
                'filename' => $result['filename'],
                'size' => $result['size']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup' => $result
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function download(string $id): void
    {
        $this->requireAuth();
        $this->authorize('admin.backups.view');

        $backup = $this->findBackup($id);

        if (!$backup) {
            http_response_code(404);
            echo 'Backup not found';
            return;
        }

        $this->logBackupAction('backup.downloaded', 'backups', $id, null, [
            'filename' => $backup['filename']
        ]);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . $backup['size']);
        header('Cache-Control: no-cache');

        readfile($backup['path']);
        exit;
    }

    /**
     * Restore from backup
     */
    public function restore(string $id): void
    {
        $this->requireAuth();
        $this->authorize('admin.backups.restore');

        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $backup = $this->findBackup($id);

        if (!$backup) {
            $this->json(['error' => 'Backup not found'], 404);
            return;
        }

        try {
            // Create a safety backup before restoring
            $safetyBackup = $this->createBackup('pre_restore');

            // Perform restore
            $result = $this->restoreFromBackup($backup['path']);

            $this->logBackupAction('backup.restored', 'backups', $id, null, [
                'restored_from' => $backup['filename'],
                'safety_backup' => $safetyBackup['filename']
            ]);

            $this->json([
                'success' => true,
                'message' => 'Backup restored successfully',
                'safety_backup' => $safetyBackup['filename'],
                'result' => $result
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete backup
     */
    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->authorize('admin.backups.delete');

        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $backup = $this->findBackup($id);

        if (!$backup) {
            $this->json(['error' => 'Backup not found'], 404);
            return;
        }

        try {
            unlink($backup['path']);

            $this->logBackupAction('backup.deleted', 'backups', $id, [
                'filename' => $backup['filename']
            ], null);

            $this->json([
                'success' => true,
                'message' => 'Backup deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a backup
     */
    private function createBackup(string $prefix = 'backup'): array
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('ZIP extension is not available');
        }

        $timestamp = date('Y-m-d_His');
        $filename = "{$prefix}_{$timestamp}.zip";
        $backupPath = $this->app->storagePath('backups');
        $filepath = $backupPath . '/' . $filename;

        // Ensure backup directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $zip = new \ZipArchive();
        $result = $zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new \Exception('Failed to create ZIP archive');
        }

        // Export database
        $sqlContent = $this->exportDatabase();
        $zip->addFromString('database.sql', $sqlContent);

        // Add manifest
        $manifest = [
            'created_at' => date('Y-m-d H:i:s'),
            'app_version' => $this->app->config('app_version', '1.0.0'),
            'php_version' => PHP_VERSION,
            'database' => $this->app->config('db_name'),
            'tables' => $this->getTableList()
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $zip->close();

        return [
            'filename' => $filename,
            'path' => $filepath,
            'size' => filesize($filepath),
            'size_formatted' => $this->formatBytes(filesize($filepath))
        ];
    }

    /**
     * Export database to SQL
     */
    private function exportDatabase(): string
    {
        $db = $this->db();
        $tables = $this->getTableList();

        $sql = "-- Lumixa Manufacturing System Database Backup\n";
        $sql .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . $this->app->config('db_name') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $createResult = $db->fetch("SHOW CREATE TABLE `{$table}`");
            $createStatement = $createResult['Create Table'] ?? '';

            $sql .= "-- Table: {$table}\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStatement . ";\n\n";

            // Get table data
            $rows = $db->fetchAll("SELECT * FROM `{$table}`");

            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';

                $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $rowValues[] = $value;
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }

                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        return $sql;
    }

    /**
     * Restore from backup file
     */
    private function restoreFromBackup(string $filepath): array
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('ZIP extension is not available');
        }

        $zip = new \ZipArchive();
        $result = $zip->open($filepath);

        if ($result !== true) {
            throw new \Exception('Failed to open backup archive');
        }

        // Read SQL content
        $sqlContent = $zip->getFromName('database.sql');
        $manifest = json_decode($zip->getFromName('manifest.json'), true);

        $zip->close();

        if (!$sqlContent) {
            throw new \Exception('Invalid backup: database.sql not found');
        }

        // Execute SQL statements
        $db = $this->db();
        $statements = $this->parseSqlStatements($sqlContent);

        $executed = 0;
        $errors = [];

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }

            try {
                $db->query($statement);
                $executed++;
            } catch (\Exception $e) {
                $errors[] = [
                    'statement' => substr($statement, 0, 100) . '...',
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'statements_executed' => $executed,
            'errors' => $errors,
            'manifest' => $manifest
        ];
    }

    /**
     * Parse SQL file into statements
     */
    private function parseSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            // Handle string boundaries
            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }

            // Statement delimiter
            if ($char === ';' && !$inString) {
                $statement = trim($current);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }

        // Don't forget last statement if it doesn't end with semicolon
        $statement = trim($current);
        if (!empty($statement)) {
            $statements[] = $statement;
        }

        return $statements;
    }

    /**
     * Get list of database tables
     */
    private function getTableList(): array
    {
        $db = $this->db();
        $result = $db->fetchAll("SHOW TABLES");

        $tables = [];
        foreach ($result as $row) {
            $tables[] = array_values($row)[0];
        }

        return $tables;
    }

    /**
     * Find backup by ID
     */
    private function findBackup(string $id): ?array
    {
        $backupPath = $this->app->storagePath('backups');
        $files = glob($backupPath . '/*.zip');

        if (!$files) {
            return null;
        }

        foreach ($files as $file) {
            $filename = basename($file);
            if (md5($filename) === $id) {
                return [
                    'id' => $id,
                    'filename' => $filename,
                    'path' => $file,
                    'size' => filesize($file),
                    'created_at' => filemtime($file)
                ];
            }
        }

        return null;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Audit log helper for backup operations
     */
    private function logBackupAction(string $action, string $table, ?string $recordId, ?array $oldData, ?array $newData): void
    {
        try {
            $this->db()->insert('audit_log', [
                'user_id' => $this->user()['id'] ?? null,
                'action' => $action,
                'entity_type' => $table,
                'entity_id' => (int)($recordId ?? 0),
                'old_values' => $oldData ? json_encode($oldData) : null,
                'new_values' => $newData ? json_encode($newData) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (\Exception $e) {
            // Silent fail for audit
        }
    }
}
