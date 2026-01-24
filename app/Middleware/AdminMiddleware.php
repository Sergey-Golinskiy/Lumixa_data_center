<?php
/**
 * Admin Middleware - Requires admin role
 */

namespace App\Middleware;

use App\Core\Application;
use App\Core\View;

class AdminMiddleware
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

        // First check authentication
        if (!$user) {
            $session->setFlash('error', 'Please login to continue');
            header('Location: /login');
            exit;
        }

        // Check admin role
        $roles = $user['roles'] ?? [];
        if (!in_array('admin', $roles)) {
            http_response_code(403);

            $view = new View($this->app);
            echo $view->render('errors/403', [
                'title' => 'Access Denied',
                'message' => 'You need administrator privileges to access this area.'
            ]);
            exit;
        }
    }
}
