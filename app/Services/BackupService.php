<?php

namespace App\Services;

use App\Core\Database;

/**
 * Backup Service
 * Handles database and file backup/restore operations
 */
class BackupService
{
    private Database $db;
    private AuditService $auditService;
    private string $backupPath;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->auditService = new AuditService();
        $this->backupPath = LMS_ROOT . '/storage/backups';

        // Ensure backup directory exists
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Create backup
     */
    public function createBackup(array $options = []): array
    {
        $includeDb = $options['include_db'] ?? true;
        $includeFiles = $options['include_files'] ?? false;
        $notes = $options['notes'] ?? null;
        $tables = $options['tables'] ?? []; // Empty = all tables

        $timestamp = date('Y-m-d_His');
        $filename = "lms_backup_{$timestamp}.zip";
        $filepath = $this->backupPath . '/' . $filename;

        // Create backup record
        $backupId = $this->db->insert('backups', [
            'filename' => $filename,
            'file_path' => $filepath,
            'type' => 'manual',
            'includes_db' => $includeDb ? 1 : 0,
            'includes_files' => $includeFiles ? 1 : 0,
            'db_tables' => !empty($tables) ? json_encode($tables) : null,
            'status' => 'in_progress',
            'notes' => $notes,
            'created_by' => userId(),
            'started_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        try {
            // Create temp directory for backup contents
            $tempDir = sys_get_temp_dir() . '/lms_backup_' . $timestamp;
            mkdir($tempDir, 0755, true);

            // Create manifest
            $manifest = [
                'version' => '1.0',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => userId(),
                'includes_db' => $includeDb,
                'includes_files' => $includeFiles,
                'tables' => $tables,
            ];

            // Export database
            if ($includeDb) {
                $dbFile = $tempDir . '/database.sql';
                $this->exportDatabase($dbFile, $tables);
                $manifest['db_file'] = 'database.sql';
            }

            // Copy files
            if ($includeFiles) {
                $filesDir = $tempDir . '/files';
                mkdir($filesDir, 0755, true);
                $this->copyDirectory(LMS_ROOT . '/public/uploads', $filesDir . '/uploads');
                $manifest['files_dir'] = 'files';
            }

            // Write manifest
            file_put_contents(
                $tempDir . '/manifest.json',
                json_encode($manifest, JSON_PRETTY_PRINT)
            );

            // Create ZIP archive
            $this->createZipArchive($tempDir, $filepath);

            // Get file size
            $fileSize = filesize($filepath);

            // Update backup record
            $this->db->update('backups', [
                'file_size' => $fileSize,
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$backupId]);

            // Cleanup temp directory
            $this->removeDirectory($tempDir);

            $this->auditService->log('backup_created', 'backup', $backupId, 'Backup created successfully');

            return [
                'success' => true,
                'backup_id' => $backupId,
                'filename' => $filename,
                'file_size' => $fileSize,
            ];

        } catch (\Exception $e) {
            // Update backup record with error
            $this->db->update('backups', [
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$backupId]);

            // Cleanup
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->removeDirectory($tempDir);
            }
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(int $backupId): array
    {
        $backup = $this->db->fetch("SELECT * FROM backups WHERE id = ?", [$backupId]);

        if (!$backup) {
            return ['success' => false, 'message' => 'Backup not found'];
        }

        if ($backup['status'] !== 'completed') {
            return ['success' => false, 'message' => 'Backup is not in completed status'];
        }

        if (!file_exists($backup['file_path'])) {
            return ['success' => false, 'message' => 'Backup file not found'];
        }

        // Create restore record
        $restoreId = $this->db->insert('backup_restores', [
            'backup_id' => $backupId,
            'status' => 'in_progress',
            'restored_by' => userId(),
            'started_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        try {
            // Extract backup
            $tempDir = sys_get_temp_dir() . '/lms_restore_' . time();
            $this->extractZipArchive($backup['file_path'], $tempDir);

            // Read manifest
            $manifestFile = $tempDir . '/manifest.json';
            if (!file_exists($manifestFile)) {
                throw new \RuntimeException('Invalid backup: manifest not found');
            }

            $manifest = json_decode(file_get_contents($manifestFile), true);

            // Restore database
            if (!empty($manifest['db_file'])) {
                $dbFile = $tempDir . '/' . $manifest['db_file'];
                if (file_exists($dbFile)) {
                    $this->importDatabase($dbFile);
                }
            }

            // Restore files
            if (!empty($manifest['files_dir'])) {
                $filesDir = $tempDir . '/' . $manifest['files_dir'];
                if (is_dir($filesDir . '/uploads')) {
                    // Backup current uploads
                    $currentUploads = LMS_ROOT . '/public/uploads';
                    $backupUploads = LMS_ROOT . '/public/uploads_backup_' . time();

                    if (is_dir($currentUploads)) {
                        rename($currentUploads, $backupUploads);
                    }

                    // Copy restored files
                    $this->copyDirectory($filesDir . '/uploads', $currentUploads);
                }
            }

            // Update restore record
            $this->db->update('backup_restores', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$restoreId]);

            // Cleanup
            $this->removeDirectory($tempDir);

            $this->auditService->log('backup_restored', 'backup', $backupId, 'Backup restored successfully');

            return [
                'success' => true,
                'message' => 'Backup restored successfully',
            ];

        } catch (\Exception $e) {
            // Update restore record with error
            $this->db->update('backup_restores', [
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$restoreId]);

            // Cleanup
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->removeDirectory($tempDir);
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Export database to SQL file
     */
    private function exportDatabase(string $filepath, array $tables = []): void
    {
        $config = config('database');

        // Get all tables if not specified
        if (empty($tables)) {
            $result = $this->db->fetchAll("SHOW TABLES");
            $tables = array_map(fn($r) => array_values($r)[0], $result);
        }

        $output = "-- LMS Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Get create table statement
            $createTable = $this->db->fetch("SHOW CREATE TABLE `{$table}`");
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $createTable['Create Table'] . ";\n\n";

            // Get data
            $rows = $this->db->fetchAll("SELECT * FROM `{$table}`");

            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';

                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, array_values($row));

                    $output .= "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($filepath, $output);
    }

    /**
     * Import database from SQL file
     */
    private function importDatabase(string $filepath): void
    {
        $sql = file_get_contents($filepath);

        // Split into statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                $this->db->exec($statement);
            }
        }
    }

    /**
     * Create ZIP archive
     */
    private function createZipArchive(string $sourceDir, string $zipFile): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Failed to create ZIP archive');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

    /**
     * Extract ZIP archive
     */
    private function extractZipArchive(string $zipFile, string $destDir): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipFile) !== true) {
            throw new \RuntimeException('Failed to open ZIP archive');
        }

        $zip->extractTo($destDir);
        $zip->close();
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath = $source . '/' . $file;
            $destPath = $dest . '/' . $file;

            if (is_dir($srcPath)) {
                $this->copyDirectory($srcPath, $destPath);
            } else {
                copy($srcPath, $destPath);
            }
        }
        closedir($dir);
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Get backup list
     */
    public function getBackups(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT b.*, u.username as created_by_name
             FROM backups b
             LEFT JOIN users u ON u.id = b.created_by
             ORDER BY b.created_at DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    /**
     * Get backup by ID
     */
    public function getBackup(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT b.*, u.username as created_by_name
             FROM backups b
             LEFT JOIN users u ON u.id = b.created_by
             WHERE b.id = ?",
            [$id]
        );
    }

    /**
     * Delete backup
     */
    public function deleteBackup(int $id): bool
    {
        $backup = $this->getBackup($id);

        if (!$backup) {
            return false;
        }

        // Delete file
        if (file_exists($backup['file_path'])) {
            unlink($backup['file_path']);
        }

        // Delete record
        $this->db->delete('backups', 'id = ?', [$id]);

        $this->auditService->log('backup_deleted', 'backup', $id, 'Backup deleted');

        return true;
    }

    /**
     * Cleanup old backups
     */
    public function cleanupOldBackups(int $retentionDays = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));

        $oldBackups = $this->db->fetchAll(
            "SELECT id, file_path FROM backups WHERE created_at < ? AND type = 'scheduled'",
            [$cutoffDate]
        );

        $deleted = 0;
        foreach ($oldBackups as $backup) {
            if ($this->deleteBackup($backup['id'])) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
