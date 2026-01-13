<?php

namespace App\Core;

/**
 * Main Application Class
 * Handles routing and request dispatching
 */
class Application
{
    private Router $router;
    private static ?Application $instance = null;

    public function __construct()
    {
        self::$instance = $this;
        $this->router = new Router();
    }

    public static function getInstance(): ?Application
    {
        return self::$instance;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            // Start session
            $this->startSession();

            // Get request path
            $path = $this->getRequestPath();
            $method = requestMethod();

            // Check if setup is needed
            if (!isInstalled() && $path !== '/setup' && strpos($path, '/setup') !== 0) {
                redirect(url('setup'));
            }

            // Load routes
            $this->loadRoutes();

            // Dispatch request
            $this->router->dispatch($method, $path);

        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Start session with secure settings
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get the request path
     */
    private function getRequestPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path if configured
        $basePath = config('app.base_path', '');
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Load application routes
     */
    private function loadRoutes(): void
    {
        $routesFile = LMS_ROOT . '/app/routes.php';
        if (file_exists($routesFile)) {
            $router = $this->router;
            require $routesFile;
        }
    }

    /**
     * Handle exceptions
     */
    private function handleException(\Exception $e): void
    {
        logError($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        if (config('app.debug', false)) {
            echo '<h1>Error</h1>';
            echo '<p>' . e($e->getMessage()) . '</p>';
            echo '<pre>' . e($e->getTraceAsString()) . '</pre>';
        } else {
            abort(500, 'An error occurred. Please try again later.');
        }
    }
}
