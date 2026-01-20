<?php
/**
 * Lumixa Manufacturing System
 *
 * Entry point for the application
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load global helpers
require_once BASE_PATH . '/app/helpers.php';

// Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to path
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    // Check if class uses App namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get relative class name
    $relativeClass = substr($class, $len);

    // Convert to file path
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Require file if exists
    if (file_exists($file)) {
        require $file;
    }
});

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'self'");

// Enforce HTTPS in production
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Start output buffering
ob_start();

try {
    // Create and boot application
    $app = \App\Core\Application::getInstance();
    $app->boot();

    // Run application
    $app->run();

} catch (\Throwable $e) {
    // Emergency error handling if application failed to boot
    http_response_code(500);

    // Try to log error
    $logFile = BASE_PATH . '/storage/logs/app.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $message = "[{$timestamp}] [CRITICAL] Bootstrap failed: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
    $message .= $e->getTraceAsString() . "\n\n";

    @file_put_contents($logFile, $message, FILE_APPEND);

    // Show error page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Lumixa LMS</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #f5f5f5;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .error-box {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 600px;
                width: 100%;
                padding: 40px;
                text-align: center;
            }
            .error-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h1 {
                font-size: 24px;
                color: #333;
                margin-bottom: 15px;
            }
            p {
                color: #666;
                margin-bottom: 20px;
            }
            .error-details {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                padding: 15px;
                text-align: left;
                margin-top: 20px;
                font-size: 13px;
            }
            .error-details pre {
                background: #212529;
                color: #f8f9fa;
                padding: 10px;
                border-radius: 4px;
                overflow-x: auto;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <div class="error-icon">⚠️</div>
            <h1>Application Error</h1>
            <p>The application failed to start. Please check the configuration and logs.</p>
            <p>If this is a fresh installation, try accessing <a href="/setup">/setup</a></p>

            <?php if (ini_get('display_errors') || (isset($_GET['debug']) && $_GET['debug'] === 'show')): ?>
            <div class="error-details">
                <strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?>
                <pre><?= htmlspecialchars($e->getFile() . ':' . $e->getLine()) ?>

<?= htmlspecialchars($e->getTraceAsString()) ?></pre>
            </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

// Flush output buffer
ob_end_flush();
