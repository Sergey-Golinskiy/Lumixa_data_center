<?php
/**
 * Auth Middleware - Requires authentication
 */

namespace App\Middleware;

use App\Core\Application;
use App\Core\View;

class AuthMiddleware
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(): void
    {
        $session = $this->app->getSession();
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Please login to continue');
            header('Location: /login');
            exit;
        }

        // Check if session is still valid
        if ($this->isSessionExpired($user)) {
            $session->destroy();
            $session->start();
            $session->setFlash('error', 'Your session has expired. Please login again.');
            header('Location: /login');
            exit;
        }

        // Update last activity
        $session->set('last_activity', time());
    }

    /**
     * Check if session has expired
     */
    private function isSessionExpired(array $user): bool
    {
        $session = $this->app->getSession();
        $lastActivity = $session->get('last_activity', 0);
        $lifetime = $this->app->config('session_lifetime', 86400);

        return (time() - $lastActivity) > $lifetime;
    }
}
