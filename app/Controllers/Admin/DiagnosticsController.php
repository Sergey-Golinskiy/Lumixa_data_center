<?php
/**
 * Diagnostics Controller - System diagnostics dashboard
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Application;
use App\Services\MigrationService;
use App\Services\SetupService;

class DiagnosticsController extends Controller
{
    /**
     * Show diagnostics dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->authorize('admin.diagnostics.view');

        // Check debug mode
        $isDebug = $this->app->isDebug();

        // Gather diagnostics data
        $data = [
            'title' => 'System Diagnostics',
            'csrfToken' => $this->csrfToken(),
            'isDebug' => $isDebug,

            // Environment
            'environment' => $this->getEnvironmentInfo(),

            // Directory checks
            'directories' => $this->checkDirectories(),

            // PHP extensions
            'extensions' => $this->checkExtensions(),

            // Database
            'database' => $this->checkDatabase(),

            // Migrations
            'migrations' => $this->getMigrationStatus(),

            // Recent logs
            'recentLogs' => $this->getRecentLogs(50)
        ];

        $this->view('admin/diagnostics/index', $data);
    }

    /**
     * Run self-tests
     */
    public function runTests(): void
    {
        $this->requireAuth();
        $this->authorize('admin.diagnostics.view');

        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $tests = [];

        // Test 1: Database SELECT
        $tests[] = $this->testDatabaseSelect();

        // Test 2: Database Transaction
        $tests[] = $this->testDatabaseTransaction();

        // Test 3: File write to storage/logs
        $tests[] = $this->testFileWrite('storage/logs');

        // Test 4: File write to storage/backups
        $tests[] = $this->testFileWrite('storage/backups');

        // Test 5: CSRF token generation
        $tests[] = $this->testCsrfGeneration();

        // Test 6: Zip creation
        $tests[] = $this->testZipCreation();

        $allPassed = array_reduce($tests, fn($carry, $test) => $carry && $test['status'], true);

        $this->json([
            'success' => true,
            'all_passed' => $allPassed,
            'tests' => $tests
        ]);
    }

    /**
     * Run pending migrations
     */
    public function runMigrations(): void
    {
        $this->requireAuth();
        $this->authorize('admin.diagnostics.view');

        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $migrationService = new MigrationService($this->app);
            $results = $migrationService->runAll();

            $success = array_reduce($results, fn($carry, $r) => $carry && $r['success'], true);

            $this->json([
                'success' => $success,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View logs
     */
    public function logs(): void
    {
        $this->requireAuth();
        $this->authorize('admin.diagnostics.view');

        $level = $this->get('level', null);
        $lines = min((int)$this->get('lines', 100), 500);

        $logger = $this->app->getLogger();
        $logs = $logger->tail($lines, $level);

        $this->json([
            'success' => true,
            'count' => count($logs),
            'logs' => $logs
        ]);
    }

    /**
     * Download logs
     */
    public function downloadLogs(): void
    {
        $this->requireAuth();
        $this->authorize('admin.diagnostics.view');

        $logFile = $this->app->storagePath('logs/app.log');

        if (!file_exists($logFile)) {
            http_response_code(404);
            echo 'Log file not found';
            return;
        }

        $filename = 'lms_logs_' . date('Y-m-d_His') . '.log';

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($logFile));

        readfile($logFile);
        exit;
    }

    /**
     * Get environment info
     */
    private function getEnvironmentInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'app_version' => $this->app->config('app_version', '1.0.0'),
            'app_env' => $this->app->config('app_env', 'prod'),
            'app_debug' => $this->app->config('app_debug', false),
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'base_path' => $this->app->basePath(),
            'storage_path' => $this->app->storagePath(),
        ];
    }

    /**
     * Check directories
     */
    private function checkDirectories(): array
    {
        $dirs = [
            'storage' => $this->app->storagePath(),
            'storage/logs' => $this->app->storagePath('logs'),
            'storage/backups' => $this->app->storagePath('backups'),
            'storage/cache' => $this->app->storagePath('cache'),
            'public/uploads' => $this->app->config('upload_path'),
        ];

        $results = [];
        foreach ($dirs as $name => $path) {
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);

            $results[$name] = [
                'path' => $path,
                'exists' => $exists,
                'writable' => $writable,
                'status' => $writable,
                'fix' => $writable ? null : "mkdir -p {$path} && chmod 775 {$path}"
            ];
        }

        return $results;
    }

    /**
     * Check PHP extensions
     */
    private function checkExtensions(): array
    {
        $required = [
            'pdo_mysql' => 'MySQL database support',
            'mbstring' => 'Multibyte string support',
            'openssl' => 'Encryption support',
            'json' => 'JSON support',
            'zip' => 'Archive support (backups)',
        ];

        $optional = [
            'curl' => 'HTTP client',
            'gd' => 'Image processing',
        ];

        $results = [];

        foreach ($required as $ext => $desc) {
            $loaded = extension_loaded($ext);
            $results[$ext] = [
                'name' => $ext,
                'description' => $desc,
                'loaded' => $loaded,
                'required' => true,
                'status' => $loaded,
                'fix' => $loaded ? null : "sudo apt install php-{$ext}"
            ];
        }

        foreach ($optional as $ext => $desc) {
            $loaded = extension_loaded($ext);
            $results[$ext] = [
                'name' => $ext,
                'description' => $desc,
                'loaded' => $loaded,
                'required' => false,
                'status' => true,
                'fix' => null
            ];
        }

        return $results;
    }

    /**
     * Check database
     */
    private function checkDatabase(): array
    {
        try {
            $db = $this->db();
            $start = microtime(true);
            $db->query("SELECT 1");
            $duration = round((microtime(true) - $start) * 1000, 2);

            // Get table count
            $tables = $db->getTables();

            return [
                'status' => true,
                'message' => 'Connected',
                'host' => $this->app->config('db_host'),
                'database' => $this->app->config('db_name'),
                'user' => $this->app->config('db_user'),
                'response_time_ms' => $duration,
                'table_count' => count($tables),
                'tables' => $tables
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'host' => $this->app->config('db_host'),
                'database' => $this->app->config('db_name'),
            ];
        }
    }

    /**
     * Get migration status
     */
    private function getMigrationStatus(): array
    {
        try {
            $service = new MigrationService($this->app);

            $ran = $service->getRanMigrations();
            $pending = $service->getPendingMigrations();
            $current = $service->getCurrentVersion();

            return [
                'status' => empty($pending),
                'current_version' => $current,
                'ran_count' => count($ran),
                'pending_count' => count($pending),
                'ran' => $ran,
                'pending' => $pending
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get recent logs
     */
    private function getRecentLogs(int $count): array
    {
        $logger = $this->app->getLogger();
        return $logger->tail($count);
    }

    /**
     * Test database SELECT
     */
    private function testDatabaseSelect(): array
    {
        try {
            $start = microtime(true);
            $this->db()->query("SELECT 1");
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'name' => 'Database SELECT',
                'status' => true,
                'message' => "OK ({$duration}ms)"
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Database SELECT',
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test database transaction
     */
    private function testDatabaseTransaction(): array
    {
        try {
            $db = $this->db();
            $db->beginTransaction();

            // Create temp table, insert, select, rollback
            $db->query("CREATE TEMPORARY TABLE _test_trans (id INT)");
            $db->query("INSERT INTO _test_trans VALUES (1)");
            $result = $db->fetchColumn("SELECT id FROM _test_trans");

            $db->rollback();

            return [
                'name' => 'Database Transaction',
                'status' => $result === 1 || $result === '1',
                'message' => 'OK'
            ];
        } catch (\Exception $e) {
            if ($this->db()->inTransaction()) {
                $this->db()->rollback();
            }
            return [
                'name' => 'Database Transaction',
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test file write
     */
    private function testFileWrite(string $dir): array
    {
        $path = $this->app->basePath($dir);
        $testFile = $path . '/_test_' . time() . '.tmp';

        try {
            $written = file_put_contents($testFile, 'test');

            if ($written === false) {
                return [
                    'name' => "File Write ({$dir})",
                    'status' => false,
                    'message' => 'Write failed'
                ];
            }

            $content = file_get_contents($testFile);
            unlink($testFile);

            return [
                'name' => "File Write ({$dir})",
                'status' => $content === 'test',
                'message' => 'OK'
            ];
        } catch (\Exception $e) {
            @unlink($testFile);
            return [
                'name' => "File Write ({$dir})",
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test CSRF generation
     */
    private function testCsrfGeneration(): array
    {
        try {
            $token1 = bin2hex(random_bytes(32));
            $token2 = bin2hex(random_bytes(32));

            return [
                'name' => 'CSRF Token Generation',
                'status' => strlen($token1) === 64 && $token1 !== $token2,
                'message' => 'OK'
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'CSRF Token Generation',
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test ZIP creation
     */
    private function testZipCreation(): array
    {
        if (!extension_loaded('zip')) {
            return [
                'name' => 'ZIP Creation',
                'status' => false,
                'message' => 'zip extension not loaded'
            ];
        }

        $testFile = $this->app->storagePath('backups/_test_' . time() . '.zip');

        try {
            $zip = new \ZipArchive();
            $result = $zip->open($testFile, \ZipArchive::CREATE);

            if ($result !== true) {
                return [
                    'name' => 'ZIP Creation',
                    'status' => false,
                    'message' => 'Failed to create archive'
                ];
            }

            $zip->addFromString('test.txt', 'test content');
            $zip->close();

            $exists = file_exists($testFile);
            @unlink($testFile);

            return [
                'name' => 'ZIP Creation',
                'status' => $exists,
                'message' => $exists ? 'OK' : 'File not created'
            ];
        } catch (\Exception $e) {
            @unlink($testFile);
            return [
                'name' => 'ZIP Creation',
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
