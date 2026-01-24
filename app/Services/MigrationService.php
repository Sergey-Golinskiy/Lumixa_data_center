<?php
/**
 * Migration Service - Database schema management
 */

namespace App\Services;

use App\Core\Application;
use PDO;

class MigrationService
{
    private Application $app;
    private PDO $db;
    private string $migrationsPath;

    public function __construct(Application $app, ?PDO $db = null)
    {
        $this->app = $app;
        $this->db = $db ?? $app->getDatabase()->getConnection();
        $this->migrationsPath = $app->storagePath('migrations');
    }

    /**
     * Run all pending migrations
     */
    public function runAll(): array
    {
        $results = [];

        // Create migrations table if not exists
        $this->createMigrationsTable();

        // Get pending migrations
        $pending = $this->getPendingMigrations();

        foreach ($pending as $migration) {
            $result = $this->runMigration($migration);
            $results[] = $result;

            if (!$result['success']) {
                break;
            }
        }

        return $results;
    }

    /**
     * Get pending migrations
     */
    public function getPendingMigrations(): array
    {
        $ran = $this->getRanMigrations();
        $all = $this->getAllMigrations();

        return array_diff($all, $ran);
    }

    /**
     * Get all migration files
     */
    public function getAllMigrations(): array
    {
        $migrations = [];

        if (!is_dir($this->migrationsPath)) {
            return $migrations;
        }

        $files = scandir($this->migrationsPath);

        foreach ($files as $file) {
            if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_(.+)\.sql$/', $file, $matches)) {
                $migrations[] = $matches[1];
            }
        }

        sort($migrations);

        return $migrations;
    }

    /**
     * Get already ran migrations
     */
    public function getRanMigrations(): array
    {
        try {
            $stmt = $this->db->query("SELECT migration FROM migrations ORDER BY id");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Run single migration
     *
     * Note: MySQL/MariaDB implicitly commits on DDL statements (CREATE, ALTER, DROP).
     * Therefore, we don't wrap DDL in transactions - they auto-commit anyway.
     * We only need to ensure the migration record is inserted after all statements succeed.
     */
    private function runMigration(string $migration): array
    {
        $file = $this->findMigrationFile($migration);

        if (!$file) {
            return [
                'migration' => $migration,
                'success' => false,
                'error' => 'Migration file not found'
            ];
        }

        try {
            $sql = file_get_contents($file);

            // Split by statements (handle ; inside strings)
            $statements = $this->splitStatements($sql);

            // Execute DDL statements without transaction wrapper
            // MySQL implicitly commits on DDL, so transactions are meaningless here
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $stmt = $this->db->query($statement);
                    if ($stmt->columnCount() > 0) {
                        $stmt->fetchAll();
                    }
                    $stmt->closeCursor();
                }
            }

            // Record migration (DML - could use transaction, but single INSERT is atomic)
            $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$migration, $this->getNextBatch()]);

            return [
                'migration' => $migration,
                'success' => true
            ];

        } catch (\Exception $e) {
            // Rollback only if we're still in a transaction (shouldn't happen with DDL)
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                'migration' => $migration,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find migration file by name
     */
    private function findMigrationFile(string $migration): ?string
    {
        $files = glob($this->migrationsPath . '/' . $migration . '_*.sql');
        return $files[0] ?? null;
    }

    /**
     * Split SQL into statements
     */
    private function splitStatements(string $sql): array
    {
        // Simple split by ; but not inside quotes
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar && $prev !== '\\') {
                $inString = false;
            }

            if ($char === ';' && !$inString) {
                $statements[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current)) {
            $statements[] = $current;
        }

        return $statements;
    }

    /**
     * Get next batch number
     */
    private function getNextBatch(): int
    {
        try {
            $stmt = $this->db->query("SELECT MAX(batch) FROM migrations");
            $max = $stmt->fetchColumn();
            return ($max ?? 0) + 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Create migrations table
     */
    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT UNSIGNED NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $this->db->exec($sql);
    }

    /**
     * Get current schema version
     */
    public function getCurrentVersion(): ?string
    {
        $migrations = $this->getRanMigrations();
        return end($migrations) ?: null;
    }

    /**
     * Check if migrations table exists
     */
    public function migrationsTableExists(): bool
    {
        try {
            $this->db->query("SELECT 1 FROM migrations LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
