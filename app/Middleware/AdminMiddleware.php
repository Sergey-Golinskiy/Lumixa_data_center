<?php

namespace App\Middleware;

/**
 * Admin Middleware
 * Requires user to have admin role
 */
class AdminMiddleware
{
    public function handle(): void
    {
        if (!auth()) {
            if (isAjax()) {
                jsonResponse(['error' => 'Unauthorized'], 401);
            }
            redirect(url('login'));
        }

        $user = auth();
        if (!in_array('admin', $user['roles'] ?? [])) {
            abort(403, 'Admin access required');
        }
    }
}
