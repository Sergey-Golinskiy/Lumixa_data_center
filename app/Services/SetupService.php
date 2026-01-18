<?php
/**
 * Setup Service - Installation logic
 */

namespace App\Services;

use App\Core\Application;
use App\Core\Database;
use PDO;
use PDOException;

class SetupService
{
    private Application $app;

    // Required PHP extensions
    private array $requiredExtensions = [
        'pdo_mysql' => 'MySQL database support',
        'mbstring' => 'Multibyte string support',
        'openssl' => 'Encryption support',
        'json' => 'JSON support',
        'zip' => 'Archive support for backups',
    ];

    // Optional extensions
    private array $optionalExtensions = [
        'curl' => 'HTTP client support',
        'gd' => 'Image processing',
    ];

    // Required writable directories
    private array $writableDirs = [
        'storage',
        'storage/logs',
        'storage/backups',
        'storage/cache',
        'storage/migrations',
        'public/uploads',
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Run all environment checks
     */
    public function runChecks(array $dbConfig = []): array
    {
        $checks = [];

        // PHP Version
        $checks['php_version'] = $this->checkPhpVersion();

        // PHP Extensions
        $checks['extensions'] = $this->checkExtensions();

        // Writable Directories
        $checks['directories'] = $this->checkDirectories();

        // Database Connection
        if (!empty($dbConfig['db_name'])) {
            $checks['database'] = $this->checkDatabase($dbConfig);
        }

        // Timezone
        $checks['timezone'] = $this->checkTimezone();

        return $checks;
    }

    /**
     * Check if all checks passed
     */
    public function allChecksPassed(array $checks): bool
    {
        foreach ($checks as $category => $items) {
            if (isset($items['status']) && $items['status'] === false) {
                return false;
            }
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (isset($item['status']) && $item['status'] === false && ($item['required'] ?? true)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check PHP version
     */
    private function checkPhpVersion(): array
    {
        $required = '8.2.0';
        $current = PHP_VERSION;
        $passed = version_compare($current, $required, '>=');

        return [
            'name' => 'PHP Version',
            'required' => $required . '+',
            'current' => $current,
            'status' => $passed,
            'message' => $passed ? 'OK' : "PHP {$required} or higher is required",
            'fix' => $passed ? null : 'Upgrade PHP to version 8.2 or higher'
        ];
    }

    /**
     * Check PHP extensions
     */
    private function checkExtensions(): array
    {
        $results = [];

        foreach ($this->requiredExtensions as $ext => $description) {
            $loaded = extension_loaded($ext);
            $results[] = [
                'name' => $ext,
                'description' => $description,
                'status' => $loaded,
                'required' => true,
                'message' => $loaded ? 'Installed' : 'Missing',
                'fix' => $loaded ? null : "Install PHP extension: sudo apt install php-{$ext}"
            ];
        }

        foreach ($this->optionalExtensions as $ext => $description) {
            $loaded = extension_loaded($ext);
            $results[] = [
                'name' => $ext,
                'description' => $description,
                'status' => $loaded,
                'required' => false,
                'message' => $loaded ? 'Installed' : 'Not installed (optional)',
                'fix' => $loaded ? null : "Install PHP extension: sudo apt install php-{$ext}"
            ];
        }

        return $results;
    }

    /**
     * Check writable directories
     */
    private function checkDirectories(): array
    {
        $results = [];
        $basePath = $this->app->basePath();

        foreach ($this->writableDirs as $dir) {
            $fullPath = $basePath . '/' . $dir;
            $exists = is_dir($fullPath);
            $writable = $exists && is_writable($fullPath);

            $status = $writable;
            $message = $writable ? 'Writable' : ($exists ? 'Not writable' : 'Does not exist');

            $results[] = [
                'name' => $dir,
                'path' => $fullPath,
                'exists' => $exists,
                'writable' => $writable,
                'status' => $status,
                'required' => true,
                'message' => $message,
                'fix' => $status ? null : "mkdir -p {$fullPath} && chmod 775 {$fullPath}"
            ];
        }

        return $results;
    }

    /**
     * Check database connection
     */
    private function checkDatabase(array $config): array
    {
        $result = [
            'name' => 'Database Connection',
            'host' => $config['db_host'] ?? 'localhost',
            'database' => $config['db_name'] ?? '',
            'user' => $config['db_user'] ?? '',
            'status' => false,
            'required' => true,
            'message' => '',
            'fix' => null
        ];

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;charset=utf8mb4',
                $config['db_host'] ?? 'localhost',
                $config['db_port'] ?? 3306
            );

            $pdo = new PDO($dsn, $config['db_user'] ?? '', $config['db_pass'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Test query
            $pdo->query('SELECT 1');

            // Check if database exists
            $dbName = $config['db_name'];
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");
            $dbExists = $stmt->rowCount() > 0;

            if (!$dbExists) {
                // Try to create database
                try {
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $result['message'] = 'Connected, database created';
                } catch (PDOException $e) {
                    $result['message'] = 'Connected, but cannot create database';
                    $result['fix'] = "CREATE DATABASE {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                    return $result;
                }
            } else {
                $result['message'] = 'Connected successfully';
            }

            $result['status'] = true;

        } catch (PDOException $e) {
            $result['message'] = 'Connection failed: ' . $e->getMessage();
            $result['fix'] = 'Check database credentials and ensure MySQL is running';
        }

        return $result;
    }

    /**
     * Check timezone
     */
    private function checkTimezone(): array
    {
        $timezone = date_default_timezone_get();
        $valid = in_array($timezone, timezone_identifiers_list());

        return [
            'name' => 'Timezone',
            'current' => $timezone,
            'status' => $valid,
            'required' => true,
            'message' => $valid ? 'Valid timezone' : 'Invalid timezone',
            'fix' => $valid ? null : "Set timezone in config/config.php"
        ];
    }

    /**
     * Run installation
     */
    public function install(array $data): array
    {
        // Update config file
        $this->updateConfig($data);

        // Connect to database
        $db = $this->connectDatabase($data);

        // Run migrations
        $this->runMigrations($db);

        // Create admin user
        $this->createAdminUser($db, $data);

        // Seed initial data
        $this->seedData($db);

        return ['success' => true];
    }

    /**
     * Update config file
     */
    private function updateConfig(array $data): void
    {
        $configPath = $this->app->basePath('config/config.php');
        $config = file_get_contents($configPath);

        $replacements = [
            "'db_host' => 'localhost'" => "'db_host' => '" . addslashes($data['db_host']) . "'",
            "'db_port' => 3306" => "'db_port' => " . (int)$data['db_port'],
            "'db_name' => 'lms_db'" => "'db_name' => '" . addslashes($data['db_name']) . "'",
            "'db_user' => 'root'" => "'db_user' => '" . addslashes($data['db_user']) . "'",
            "'db_pass' => ''" => "'db_pass' => '" . addslashes($data['db_pass']) . "'",
        ];

        foreach ($replacements as $search => $replace) {
            $config = str_replace($search, $replace, $config);
        }

        file_put_contents($configPath, $config);
    }

    /**
     * Connect to database
     */
    private function connectDatabase(array $data): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $data['db_host'],
            $data['db_port'],
            $data['db_name']
        );

        return new PDO($dsn, $data['db_user'], $data['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /**
     * Run database migrations
     */
    private function runMigrations(PDO $db): void
    {
        $migrationService = new MigrationService($this->app, $db);
        $migrationService->runAll();
    }

    /**
     * Create admin user
     */
    private function createAdminUser(PDO $db, array $data): void
    {
        $passwordHash = password_hash($data['admin_password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO users (email, password, name, is_active, must_change_password, created_at, updated_at)
            VALUES (?, ?, ?, 1, 0, NOW(), NOW())
        ");
        $stmt->execute([$data['admin_email'], $passwordHash, $data['admin_name']]);

        $userId = $db->lastInsertId();

        // Assign admin role
        $stmt = $db->prepare("SELECT id FROM roles WHERE slug = 'admin'");
        $stmt->execute();
        $role = $stmt->fetch();

        if ($role) {
            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $role['id']]);
        }

        // Log installation
        $stmt = $db->prepare("
            INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address, created_at)
            VALUES (?, 'system.installed', 'system', 0, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            json_encode(['version' => $this->app->config('app_version')]),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    /**
     * Seed initial data
     */
    private function seedData(PDO $db): void
    {
        // Roles are created in migration
        // Permissions are created in migration
        // We just need to assign permissions to roles

        // Get all permissions
        $stmt = $db->query("SELECT id, code FROM permissions");
        $permissions = $stmt->fetchAll();

        // Get admin role
        $stmt = $db->prepare("SELECT id FROM roles WHERE slug = 'admin'");
        $stmt->execute();
        $adminRole = $stmt->fetch();

        if ($adminRole) {
            // Admin gets all permissions
            $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissions as $perm) {
                $stmt->execute([$adminRole['id'], $perm['id']]);
            }
        }

        // Manager role permissions
        $stmt = $db->prepare("SELECT id FROM roles WHERE slug = 'manager'");
        $stmt->execute();
        $managerRole = $stmt->fetch();

        if ($managerRole) {
            $managerPerms = [
                'dashboard.view', 'catalog.%', 'production.%', 'costing.%',
                'warehouse.items.view', 'warehouse.stock.view'
            ];
            foreach ($permissions as $perm) {
                foreach ($managerPerms as $pattern) {
                    if ($pattern === $perm['code'] || fnmatch($pattern, $perm['code'])) {
                        $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                        $stmt->execute([$managerRole['id'], $perm['id']]);
                        break;
                    }
                }
            }
        }

        // Accountant role permissions
        $stmt = $db->prepare("SELECT id FROM roles WHERE slug = 'accountant'");
        $stmt->execute();
        $accountantRole = $stmt->fetch();

        if ($accountantRole) {
            $accountantPerms = [
                'dashboard.view', 'warehouse.%', 'costing.view'
            ];
            foreach ($permissions as $perm) {
                foreach ($accountantPerms as $pattern) {
                    if ($pattern === $perm['code'] || fnmatch($pattern, $perm['code'])) {
                        $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                        $stmt->execute([$accountantRole['id'], $perm['id']]);
                        break;
                    }
                }
            }
        }

        // Worker role permissions
        $stmt = $db->prepare("SELECT id FROM roles WHERE slug = 'worker'");
        $stmt->execute();
        $workerRole = $stmt->fetch();

        if ($workerRole) {
            $workerPerms = [
                'dashboard.view',
                'production.tasks.view', 'production.tasks.update',
                'production.print-queue.view', 'production.print-queue.update'
            ];
            foreach ($permissions as $perm) {
                if (in_array($perm['code'], $workerPerms)) {
                    $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$workerRole['id'], $perm['id']]);
                }
            }
        }
    }
}
