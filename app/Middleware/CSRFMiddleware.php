<?php
/**
 * CSRF Middleware - Validates CSRF tokens on POST requests
 */

namespace App\Middleware;

use App\Core\Application;

class CSRFMiddleware
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(): void
    {
        // Only check on POST, PUT, DELETE requests
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
            return;
        }

        // Check if CSRF is enabled
        if (!$this->app->config('csrf_enabled', true)) {
            return;
        }

        // Get token from request
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        // Verify token
        $session = $this->app->getSession();
        if (!$session->verifyCsrfToken($token)) {
            $this->app->getLogger()->warning('CSRF token validation failed', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? ''
            ]);

            http_response_code(403);

            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => true,
                    'message' => 'Invalid CSRF token. Please refresh the page and try again.'
                ]);
            } else {
                // Redirect back with error
                $session->setFlash('error', 'Invalid security token. Please try again.');
                $referer = $_SERVER['HTTP_REFERER'] ?? '/';
                header("Location: {$referer}");
            }

            exit;
        }
    }

    /**
     * Check if request is AJAX
     */
    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
