<?php

namespace App\Middleware;

/**
 * CSRF Protection Middleware
 */
class CsrfMiddleware
{
    public function handle(): void
    {
        // Only check POST, PUT, DELETE requests
        if (in_array(requestMethod(), ['POST', 'PUT', 'DELETE'])) {
            if (!validateCsrf()) {
                if (isAjax()) {
                    jsonResponse(['error' => 'CSRF token mismatch'], 403);
                }
                abort(403, 'CSRF token mismatch');
            }
        }
    }
}
