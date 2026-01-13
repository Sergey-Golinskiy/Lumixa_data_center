<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Database Class
 * Handles MySQL connection and queries using PDO
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private array $config = [];

    private function __construct()
    {
        $this->config = [
            'host' => config('database.host', 'localhost'),
            'port' => config('database.port', 3306),
            'database' => config('database.database', 'lms'),
            'username' => config('database.username', 'root'),
            'password' => config('database.password', ''),
            'charset' => config('database.charset', 'utf8mb4'),
            'collation' => config('database.collation', 'utf8mb4_unicode_ci'),
        ];
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
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
     * Establish database connection
     */
    private function connect(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['collation']}"
        ];

        $this->pdo = new PDO(
            $dsn,
            $this->config['username'],
            $this->config['password'],
            $options
        );
    }

    /**
     * Test database connection
     */
    public static function testConnection(array $config): array
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d',
                $config['host'] ?? 'localhost',
                $config['port'] ?? 3306
            );

            $pdo = new PDO(
                $dsn,
                $config['username'] ?? 'root',
                $config['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Check if database exists
            $dbName = $config['database'] ?? 'lms';
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");

            return [
                'success' => true,
                'database_exists' => $stmt->rowCount() > 0,
                'message' => 'Connection successful'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'database_exists' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create database if not exists
     */
    public static function createDatabase(array $config): bool
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d',
                $config['host'] ?? 'localhost',
                $config['port'] ?? 3306
            );

            $pdo = new PDO(
                $dsn,
                $config['username'] ?? 'root',
                $config['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $dbName = $config['database'] ?? 'lms';
            $charset = $config['charset'] ?? 'utf8mb4';
            $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");

            return true;

        } catch (PDOException $e) {
            logError('Failed to create database', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Execute query and return statement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute query and return all rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Execute query and return single row
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Execute query and return single value
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0)
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    /**
     * Execute INSERT and return last insert ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $this->query($sql, array_values($data));

        return (int) $this->getConnection()->lastInsertId();
    }

    /**
     * Execute UPDATE
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "`{$column}` = ?";
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $set),
            $where
        );

        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));

        return $stmt->rowCount();
    }

    /**
     * Execute DELETE
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Execute callback in transaction
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): int
    {
        return (int) $this->getConnection()->lastInsertId();
    }

    /**
     * Quote identifier (table/column name)
     */
    public function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * Execute raw SQL
     */
    public function exec(string $sql): int
    {
        return $this->getConnection()->exec($sql);
    }
}
