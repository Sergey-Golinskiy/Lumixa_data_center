<?php
/**
 * Database - PDO wrapper with helper methods
 */

namespace App\Core;

use PDO;
use PDOStatement;
use PDOException;

class Database
{
    private ?PDO $pdo = null;
    private array $config;
    private int $queryCount = 0;
    private array $queryLog = [];
    private bool $inTransaction = false;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Connect to database
     */
    private function connect(): void
    {
        $host = $this->config['db_host'] ?? 'localhost';
        $port = $this->config['db_port'] ?? 3306;
        $dbname = $this->config['db_name'] ?? '';
        $charset = $this->config['db_charset'] ?? 'utf8mb4';
        $user = $this->config['db_user'] ?? 'root';
        $pass = $this->config['db_pass'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci"
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    /**
     * Execute a query
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $this->queryCount++;

        $start = microtime(true);

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        $duration = microtime(true) - $start;

        // Log query in debug mode
        if (($this->config['app_debug'] ?? false)) {
            $this->queryLog[] = [
                'sql' => $sql,
                'params' => $params,
                'duration' => $duration
            ];
        }

        return $stmt;
    }

    /**
     * Prepare statement
     */
    public function prepare(string $sql): PDOStatement
    {
        return $this->getConnection()->prepare($sql);
    }

    /**
     * Execute statement with params
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get single row
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get single column value
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }

    /**
     * Insert and return last insert ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->query($sql, array_values($data));

        return (int) $this->getConnection()->lastInsertId();
    }

    /**
     * Update rows
     */
    public function update(string $table, array $data, array $where): int
    {
        $setClauses = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $params[] = $value;
        }

        $whereClauses = [];
        foreach ($where as $column => $value) {
            $whereClauses[] = "{$column} = ?";
            $params[] = $value;
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $setClauses),
            implode(' AND ', $whereClauses)
        );

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete rows
     */
    public function delete(string $table, array $where): int
    {
        $whereClauses = [];
        $params = [];

        foreach ($where as $column => $value) {
            $whereClauses[] = "{$column} = ?";
            $params[] = $value;
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $table,
            implode(' AND ', $whereClauses)
        );

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        if ($this->inTransaction) {
            return false;
        }
        $this->inTransaction = $this->getConnection()->beginTransaction();
        return $this->inTransaction;
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        $this->inTransaction = false;
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        $this->inTransaction = false;
        return $this->getConnection()->rollBack();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        // Check both our flag and PDO's actual state (may differ due to implicit commits)
        return $this->inTransaction && $this->getConnection()->inTransaction();
    }

    /**
     * Safe commit - only commits if there's an active transaction
     *
     * MySQL/MariaDB implicitly commits on DDL statements (CREATE, ALTER, DROP),
     * which can leave the transaction in an inconsistent state. This method
     * safely handles that case by checking PDO's actual transaction state.
     */
    public function safeCommit(): bool
    {
        if ($this->getConnection()->inTransaction()) {
            $this->inTransaction = false;
            return $this->getConnection()->commit();
        }
        $this->inTransaction = false;
        return true; // No active transaction, but not an error
    }

    /**
     * Safe rollback - only rolls back if there's an active transaction
     *
     * Handles cases where MySQL implicitly committed due to DDL statements.
     */
    public function safeRollback(): bool
    {
        if ($this->getConnection()->inTransaction()) {
            $this->inTransaction = false;
            return $this->getConnection()->rollBack();
        }
        $this->inTransaction = false;
        return true; // No active transaction, but not an error
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Quote identifier
     */
    public function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * Test connection
     */
    public function testConnection(): bool
    {
        try {
            $this->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get query count
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Get query log
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Check if table exists
     * Note: Uses information_schema for reliable parameter binding
     */
    public function tableExists(string $table): bool
    {
        $dbName = $this->config['db_name'] ?? '';
        $sql = "SELECT COUNT(*) FROM information_schema.tables
                WHERE table_schema = ? AND table_name = ?";
        $result = $this->fetchColumn($sql, [$dbName, $table]);
        return (int)$result > 0;
    }

    /**
     * Check if column exists
     */
    public function columnExists(string $table, string $column): bool
    {
        $dbName = $this->config['db_name'] ?? '';
        $sql = "SELECT COUNT(*) FROM information_schema.columns
                WHERE table_schema = ? AND table_name = ? AND column_name = ?";
        $result = $this->fetchColumn($sql, [$dbName, $table, $column]);
        return (int)$result > 0;
    }

    /**
     * Get all tables
     */
    public function getTables(): array
    {
        $sql = "SHOW TABLES";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
