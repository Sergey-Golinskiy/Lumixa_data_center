<?php
/**
 * Setup Controller - Installation wizard
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Application;
use App\Services\SetupService;

class SetupController extends Controller
{
    private SetupService $setupService;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->setupService = new SetupService($app);
        $this->view->setLayout('setup');
    }

    /**
     * Show setup wizard
     */
    public function index(): void
    {
        // If already installed, redirect to home
        if ($this->app->isInstalled()) {
            $this->redirect('/');
            return;
        }

        // Run environment checks
        $checks = $this->setupService->runChecks();

        $this->view('setup/index', [
            'title' => 'Installation',
            'checks' => $checks,
            'allPassed' => $this->setupService->allChecksPassed($checks),
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Run installation
     */
    public function install(): void
    {
        if ($this->app->isInstalled()) {
            $this->redirect('/');
            return;
        }

        // Validate CSRF
        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/setup');
            return;
        }

        // Check if dry run
        $dryRun = $this->post('dry_run') === '1';

        // Get form data
        $data = [
            'db_host' => $this->post('db_host', 'localhost'),
            'db_port' => (int)$this->post('db_port', 3306),
            'db_name' => $this->post('db_name', ''),
            'db_user' => $this->post('db_user', ''),
            'db_pass' => $this->post('db_pass', ''),
            'admin_email' => $this->post('admin_email', ''),
            'admin_password' => $this->post('admin_password', ''),
            'admin_name' => $this->post('admin_name', 'Administrator'),
        ];

        // Validate
        $errors = $this->validateSetupData($data);
        if (!empty($errors)) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/setup');
            return;
        }

        // Run environment checks first
        $checks = $this->setupService->runChecks($data);
        if (!$this->setupService->allChecksPassed($checks)) {
            $this->session->setFlash('error', 'Please fix all environment issues before installing.');
            $this->redirect('/setup');
            return;
        }

        if ($dryRun) {
            $this->session->setFlash('success', 'Dry run completed! All checks passed. You can now proceed with installation.');
            $this->redirect('/setup');
            return;
        }

        // Run installation
        try {
            $result = $this->setupService->install($data);

            if ($result['success']) {
                $this->app->markInstalled();
                $this->session->setFlash('success', 'Installation completed successfully! Please login.');
                $this->redirect('/login');
            } else {
                $this->session->setFlash('error', $result['error'] ?? 'Installation failed');
                $this->redirect('/setup');
            }
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Installation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->session->setFlash('error', 'Installation failed: ' . $e->getMessage());
            $this->redirect('/setup');
        }
    }

    /**
     * AJAX check endpoint
     */
    public function check(): void
    {
        $data = [
            'db_host' => $this->get('db_host', 'localhost'),
            'db_port' => (int)$this->get('db_port', 3306),
            'db_name' => $this->get('db_name', ''),
            'db_user' => $this->get('db_user', ''),
            'db_pass' => $this->get('db_pass', ''),
        ];

        $checks = $this->setupService->runChecks($data);

        $this->json([
            'success' => true,
            'checks' => $checks,
            'allPassed' => $this->setupService->allChecksPassed($checks)
        ]);
    }

    /**
     * Validate setup data
     */
    private function validateSetupData(array $data): array
    {
        $errors = [];

        if (empty($data['db_name'])) {
            $errors['db_name'] = 'Database name is required';
        }

        if (empty($data['db_user'])) {
            $errors['db_user'] = 'Database user is required';
        }

        if (empty($data['admin_email'])) {
            $errors['admin_email'] = 'Admin email is required';
        } elseif (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['admin_email'] = 'Invalid email format';
        }

        if (empty($data['admin_password'])) {
            $errors['admin_password'] = 'Admin password is required';
        } elseif (strlen($data['admin_password']) < 8) {
            $errors['admin_password'] = 'Password must be at least 8 characters';
        }

        return $errors;
    }
}
