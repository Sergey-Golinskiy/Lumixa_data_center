<?php

namespace App\Middleware;

/**
 * Guest Middleware
 * Only allows non-authenticated users
 */
class GuestMiddleware
{
    public function handle(): void
    {
        if (auth()) {
            redirect(url('dashboard'));
        }
    }
}
