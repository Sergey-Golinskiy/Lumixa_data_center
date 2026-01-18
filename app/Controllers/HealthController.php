<?php
/**
 * Health Controller - Health check endpoints
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Application;

class HealthController extends Controller
{
    /**
     * Basic health check - returns 200 OK
     */
    public function index(): void
    {
        http_response_code(200);
        header('Content-Type: text/plain');
        echo 'OK';
        exit;
    }

    /**
     * Detailed health check - requires admin + debug
     */
    public function details(): void
    {
        // Check authorization
        if (!$this->canViewDetails()) {
            http_response_code(403);
            $this->json([
                'error' => 'Access denied',
                'message' => 'Requires admin access and debug mode enabled'
            ], 403);
            return;
        }

        $checks = $this->runHealthChecks();

        $allPassed = true;
        foreach ($checks as $check) {
            if (!$check['status']) {
                $allPassed = false;
                break;
            }
        }

        $this->json([
            'status' => $allPassed ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'request_id' => $this->app->getRequestId(),
            'checks' => $checks
        ], $allPassed ? 200 : 503);
    }

    /**
     * Check if user can view details
     */
    private function canViewDetails(): bool
    {
        // Must be in debug mode
        if (!$this->app->isDebug()) {
            return false;
        }

        // Must be authenticated
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Must be admin
        return $this->hasRole('admin');
    }

    /**
     * Run all health checks
     */
    private function runHealthChecks(): array
    {
        $checks = [];

        // Database
        $checks['database'] = $this->checkDatabase();

        // Migrations
        $checks['migrations'] = $this->checkMigrations();

        // Writable directories
        $checks['storage'] = $this->checkStorage();
        $checks['logs'] = $this->checkLogs();
        $checks['backups'] = $this->checkBackups();
        $checks['uploads'] = $this->checkUploads();

        // PHP Extensions
        $checks['extensions'] = $this->checkExtensions();

        return $checks;
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): array
    {
        try {
            $db = $this->app->getDatabase();
            $start = microtime(true);
            $db->query("SELECT 1");
            $duration = round((microtime(true) - $start) * 1000, 2);

            return [
                'name' => 'Database Connection',
                'status' => true,
                'message' => "Connected ({$duration}ms)",
                'details' => [
                    'host' => $this->app->config('db_host'),
                    'database' => $this->app->config('db_name'),
                    'response_time_ms' => $duration
                ]
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Database Connection',
                'status' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check migrations status
     */
    private function checkMigrations(): array
    {
        try {
            $migrationService = new \App\Services\MigrationService($this->app);
            $pending = $migrationService->getPendingMigrations();
            $current = $migrationService->getCurrentVersion();

            $hasPending = count($pending) > 0;

            return [
                'name' => 'Database Migrations',
                'status' => !$hasPending,
                'message' => $hasPending ? count($pending) . ' pending' : 'Up to date',
                'details' => [
                    'current_version' => $current,
                    'pending_count' => count($pending),
                    'pending' => $pending
                ]
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Database Migrations',
                'status' => false,
                'message' => 'Check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check storage directory
     */
    private function checkStorage(): array
    {
        $path = $this->app->storagePath();
        return $this->checkDirectory('Storage Directory', $path);
    }

    /**
     * Check logs directory
     */
    private function checkLogs(): array
    {
        $path = $this->app->storagePath('logs');
        return $this->checkDirectory('Logs Directory', $path);
    }

    /**
     * Check backups directory
     */
    private function checkBackups(): array
    {
        $path = $this->app->storagePath('backups');
        return $this->checkDirectory('Backups Directory', $path);
    }

    /**
     * Check uploads directory
     */
    private function checkUploads(): array
    {
        $path = $this->app->config('upload_path');
        return $this->checkDirectory('Uploads Directory', $path);
    }

    /**
     * Check directory is writable
     */
    private function checkDirectory(string $name, string $path): array
    {
        $exists = is_dir($path);
        $writable = $exists && is_writable($path);

        return [
            'name' => $name,
            'status' => $writable,
            'message' => $writable ? 'Writable' : ($exists ? 'Not writable' : 'Does not exist'),
            'details' => [
                'path' => $path,
                'exists' => $exists,
                'writable' => $writable
            ]
        ];
    }

    /**
     * Check PHP extensions
     */
    private function checkExtensions(): array
    {
        $required = ['pdo_mysql', 'mbstring', 'openssl', 'json', 'zip'];
        $missing = [];

        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        return [
            'name' => 'PHP Extensions',
            'status' => empty($missing),
            'message' => empty($missing) ? 'All loaded' : 'Missing: ' . implode(', ', $missing),
            'details' => [
                'required' => $required,
                'missing' => $missing
            ]
        ];
    }
}
