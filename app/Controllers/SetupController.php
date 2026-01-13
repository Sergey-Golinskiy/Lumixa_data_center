<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

/**
 * Setup Wizard Controller
 */
class SetupController extends Controller
{
    /**
     * Show setup wizard
     */
    public function index(): void
    {
        // If already installed, redirect
        if (isInstalled()) {
            $this->redirect('login', ['info' => 'System is already installed']);
        }

        $this->setLayout('setup');
        $this->view('setup/index', [
            'title' => 'Setup Wizard',
            'step' => input('step', 1),
            'errors' => $this->getValidationErrors()
        ]);
    }

    /**
     * Test database connection
     */
    public function testDatabase(): void
    {
        $this->validateCsrfOrAbort();

        $config = [
            'host' => input('db_host', 'localhost'),
            'port' => input('db_port', 3306),
            'database' => input('db_name', 'lms'),
            'username' => input('db_user', 'root'),
            'password' => input('db_pass', ''),
        ];

        $result = Database::testConnection($config);

        $this->json($result);
    }

    /**
     * Process setup
     */
    public function store(): void
    {
        $this->validateCsrfOrAbort();

        // If already installed, redirect
        if (isInstalled()) {
            $this->redirect('login');
        }

        $step = (int) input('step', 1);

        switch ($step) {
            case 1:
                $this->processStep1();
                break;
            case 2:
                $this->processStep2();
                break;
            case 3:
                $this->processStep3();
                break;
            default:
                $this->redirect('setup');
        }
    }

    /**
     * Step 1: Database configuration
     */
    private function processStep1(): void
    {
        $config = [
            'host' => input('db_host', 'localhost'),
            'port' => (int) input('db_port', 3306),
            'database' => input('db_name', 'lms'),
            'username' => input('db_user', 'root'),
            'password' => input('db_pass', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        // Test connection
        $result = Database::testConnection($config);

        if (!$result['success']) {
            $this->storeOldInput();
            $this->redirect('setup?step=1', ['error' => 'Database connection failed: ' . $result['message']]);
        }

        // Create database if not exists
        if (!$result['database_exists']) {
            if (!Database::createDatabase($config)) {
                $this->storeOldInput();
                $this->redirect('setup?step=1', ['error' => 'Failed to create database']);
            }
        }

        // Store config temporarily in session
        $_SESSION['setup_db_config'] = $config;

        $this->redirect('setup?step=2', ['success' => 'Database connection successful']);
    }

    /**
     * Step 2: Admin user creation
     */
    private function processStep2(): void
    {
        if (empty($_SESSION['setup_db_config'])) {
            $this->redirect('setup?step=1', ['error' => 'Please configure database first']);
        }

        $adminUsername = trim(input('admin_username', ''));
        $adminEmail = trim(input('admin_email', ''));
        $adminPassword = input('admin_password', '');
        $adminPasswordConfirm = input('admin_password_confirm', '');
        $adminFirstName = trim(input('admin_first_name', ''));
        $adminLastName = trim(input('admin_last_name', ''));

        // Validation
        $errors = [];

        if (empty($adminUsername)) {
            $errors['admin_username'] = 'Username is required';
        } elseif (strlen($adminUsername) < 3) {
            $errors['admin_username'] = 'Username must be at least 3 characters';
        }

        if (empty($adminEmail)) {
            $errors['admin_email'] = 'Email is required';
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['admin_email'] = 'Invalid email address';
        }

        if (empty($adminPassword)) {
            $errors['admin_password'] = 'Password is required';
        } elseif (strlen($adminPassword) < 8) {
            $errors['admin_password'] = 'Password must be at least 8 characters';
        }

        if ($adminPassword !== $adminPasswordConfirm) {
            $errors['admin_password_confirm'] = 'Passwords do not match';
        }

        if (empty($adminFirstName)) {
            $errors['admin_first_name'] = 'First name is required';
        }

        if ($errors) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = $errors;
            $this->redirect('setup?step=2', ['error' => 'Please fix the errors below']);
        }

        // Store admin data temporarily
        $_SESSION['setup_admin'] = [
            'username' => $adminUsername,
            'email' => $adminEmail,
            'password' => $adminPassword,
            'first_name' => $adminFirstName,
            'last_name' => $adminLastName,
        ];

        $this->redirect('setup?step=3', ['success' => 'Admin user configured']);
    }

    /**
     * Step 3: Run migrations and complete setup
     */
    private function processStep3(): void
    {
        if (empty($_SESSION['setup_db_config']) || empty($_SESSION['setup_admin'])) {
            $this->redirect('setup?step=1', ['error' => 'Please complete previous steps first']);
        }

        $dbConfig = $_SESSION['setup_db_config'];
        $adminData = $_SESSION['setup_admin'];
        $appName = trim(input('app_name', 'Lumixa Manufacturing System'));
        $appUrl = trim(input('app_url', ''));

        try {
            // Write config file
            $this->writeConfigFile($dbConfig, $appName, $appUrl);

            // Reload config
            $GLOBALS['lms_config'] = require LMS_ROOT . '/config/app.php';

            // Run migrations
            $this->runMigrations();

            // Run seeds
            $this->runSeeds();

            // Create admin user
            $this->createAdminUser($adminData);

            // Mark as installed
            $this->markAsInstalled();

            // Clean up session
            unset($_SESSION['setup_db_config'], $_SESSION['setup_admin']);

            $this->redirect('login', ['success' => 'Setup completed! You can now log in.']);

        } catch (\Exception $e) {
            logError('Setup failed', ['error' => $e->getMessage()]);
            $this->redirect('setup?step=3', ['error' => 'Setup failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Write configuration file
     */
    private function writeConfigFile(array $dbConfig, string $appName, string $appUrl): void
    {
        $configContent = "<?php
/**
 * LMS Configuration
 * Generated by Setup Wizard
 */

return [
    'app' => [
        'name' => " . var_export($appName, true) . ",
        'url' => " . var_export($appUrl, true) . ",
        'debug' => false,
        'timezone' => 'Europe/Kiev',
        'base_path' => '',
    ],

    'database' => [
        'host' => " . var_export($dbConfig['host'], true) . ",
        'port' => " . var_export($dbConfig['port'], true) . ",
        'database' => " . var_export($dbConfig['database'], true) . ",
        'username' => " . var_export($dbConfig['username'], true) . ",
        'password' => " . var_export($dbConfig['password'], true) . ",
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],

    'session' => [
        'lifetime' => 120,
        'cookie' => 'lms_session',
    ],

    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
    ],
];
";

        $configPath = LMS_ROOT . '/config/app.php';

        if (file_put_contents($configPath, $configContent) === false) {
            throw new \RuntimeException('Failed to write config file');
        }
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): void
    {
        $db = Database::getInstance();
        $migrationsPath = LMS_ROOT . '/database/migrations';

        $files = glob($migrationsPath . '/*.sql');
        sort($files);

        foreach ($files as $file) {
            $sql = file_get_contents($file);

            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $db->exec($statement);
                }
            }
        }
    }

    /**
     * Run database seeds
     */
    private function runSeeds(): void
    {
        $db = Database::getInstance();
        $seedsPath = LMS_ROOT . '/database/seeds';

        $files = glob($seedsPath . '/*.sql');
        sort($files);

        foreach ($files as $file) {
            $sql = file_get_contents($file);

            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $db->exec($statement);
                }
            }
        }
    }

    /**
     * Create admin user
     */
    private function createAdminUser(array $data): void
    {
        $db = Database::getInstance();

        // Create user
        $userId = $db->insert('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'is_active' => 1,
            'must_change_password' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Get admin role ID
        $adminRole = $db->fetch("SELECT id FROM roles WHERE code = 'admin'");

        if ($adminRole) {
            // Assign admin role
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $adminRole['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Log creation
        $db->insert('audit_log', [
            'user_id' => $userId,
            'action' => 'setup_complete',
            'entity_type' => 'system',
            'entity_id' => null,
            'description' => 'System setup completed',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark system as installed
     */
    private function markAsInstalled(): void
    {
        $installFile = LMS_ROOT . '/storage/.installed';
        file_put_contents($installFile, date('Y-m-d H:i:s'));
    }

    /**
     * Run migrations via AJAX
     */
    public function migrate(): void
    {
        $this->validateCsrfOrAbort();

        if (empty($_SESSION['setup_db_config'])) {
            $this->json(['success' => false, 'message' => 'Database not configured']);
        }

        try {
            // Temporarily set config
            $GLOBALS['lms_config']['database'] = $_SESSION['setup_db_config'];

            $this->runMigrations();
            $this->runSeeds();

            $this->json(['success' => true, 'message' => 'Migrations completed']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
