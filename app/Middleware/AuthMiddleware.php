<?php

namespace App\Middleware;

/**
 * Authentication Middleware
 * Requires user to be logged in
 */
class AuthMiddleware
{
    public function handle(): void
    {
        if (!auth()) {
            if (isAjax()) {
                jsonResponse(['error' => 'Unauthorized'], 401);
            }
            setFlash('error', 'Please log in to continue');
            redirect(url('login'));
        }
    }
}
