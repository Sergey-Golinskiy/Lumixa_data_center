<?php
/**
 * ErrorHandler - Global error and exception handling
 */

namespace App\Core;

class ErrorHandler
{
    private static ?Application $app = null;

    /**
     * Register error handlers
     */
    public static function register(Application $app): void
    {
        self::$app = $app;

        // Set error reporting
        error_reporting(E_ALL);

        // Don't display errors by default
        ini_set('display_errors', '0');

        // Register handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(\Throwable $e): void
    {
        $requestId = self::$app?->getRequestId() ?? 'unknown';

        // Log the error
        if (self::$app) {
            try {
                $logger = self::$app->getLogger();
                $logger->exception($e, [
                    'url' => $_SERVER['REQUEST_URI'] ?? 'cli',
                    'method' => $_SERVER['REQUEST_METHOD'] ?? 'cli',
                ]);
            } catch (\Throwable $logError) {
                // Fallback to error_log
                error_log("Exception: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            }
        }

        // Clear any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Send appropriate response
        if (self::isAjax()) {
            self::sendJsonError($e, $requestId);
        } else {
            self::sendHtmlError($e, $requestId);
        }
    }

    /**
     * Handle fatal errors on shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleException(
                new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                )
            );
        }
    }

    /**
     * Send JSON error response
     */
    private static function sendJsonError(\Throwable $e, string $requestId): void
    {
        http_response_code(500);
        header('Content-Type: application/json');

        $response = [
            'error' => true,
            'message' => 'An error occurred',
            'request_id' => $requestId,
        ];

        if (self::isDebug() && self::isAdmin()) {
            $response['debug'] = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send HTML error response
     */
    private static function sendHtmlError(\Throwable $e, string $requestId): void
    {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');

        $showDetails = self::isDebug() && self::isAdmin();

        $html = self::getErrorHtml($e, $requestId, $showDetails);
        echo $html;
        exit;
    }

    /**
     * Generate error HTML
     */
    private static function getErrorHtml(\Throwable $e, string $requestId, bool $showDetails): string
    {
        $title = 'Error';
        $appName = self::$app?->config('app_name') ?? 'Lumixa LMS';

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($appName) ?></title>
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
                .error-container {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    max-width: 800px;
                    width: 100%;
                    overflow: hidden;
                }
                .error-header {
                    background: #dc3545;
                    color: white;
                    padding: 20px 30px;
                }
                .error-header h1 {
                    font-size: 24px;
                    font-weight: 500;
                }
                .error-body {
                    padding: 30px;
                }
                .error-message {
                    font-size: 18px;
                    color: #333;
                    margin-bottom: 20px;
                }
                .error-id {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 4px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
                .error-id strong {
                    color: #495057;
                }
                .error-id code {
                    background: #e9ecef;
                    padding: 2px 6px;
                    border-radius: 3px;
                    font-family: monospace;
                }
                .error-help {
                    color: #6c757d;
                    font-size: 14px;
                }
                .error-help a {
                    color: #007bff;
                }
                .error-details {
                    margin-top: 20px;
                    border-top: 1px solid #dee2e6;
                    padding-top: 20px;
                }
                .error-details h3 {
                    font-size: 16px;
                    color: #495057;
                    margin-bottom: 10px;
                }
                .error-details pre {
                    background: #212529;
                    color: #f8f9fa;
                    padding: 15px;
                    border-radius: 4px;
                    overflow-x: auto;
                    font-size: 13px;
                    line-height: 1.5;
                }
                .trace-line {
                    color: #6c757d;
                }
                .trace-file {
                    color: #ffc107;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-header">
                    <h1>Something went wrong</h1>
                </div>
                <div class="error-body">
                    <p class="error-message">
                        We're sorry, but something went wrong on our end.
                    </p>

                    <div class="error-id">
                        <strong>Error ID:</strong> <code><?= htmlspecialchars($requestId) ?></code>
                    </div>

                    <p class="error-help">
                        Please save this Error ID and contact support if the problem persists.<br>
                        You can also check <a href="/admin/diagnostics">Diagnostics</a> for more information.
                    </p>

                    <?php if ($showDetails): ?>
                    <div class="error-details">
                        <h3>Debug Information (Admin Only)</h3>
                        <pre><?php
                            echo htmlspecialchars(get_class($e)) . "\n";
                            echo htmlspecialchars($e->getMessage()) . "\n\n";
                            echo "<span class='trace-file'>" . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</span>\n\n";
                            echo "Stack Trace:\n";
                            foreach (explode("\n", $e->getTraceAsString()) as $line) {
                                echo "<span class='trace-line'>" . htmlspecialchars($line) . "</span>\n";
                            }
                        ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Check if request is AJAX
     */
    private static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if debug mode is enabled
     */
    private static function isDebug(): bool
    {
        return (bool)(self::$app?->config('app_debug') ?? false);
    }

    /**
     * Check if current user is admin
     */
    private static function isAdmin(): bool
    {
        if (!self::$app) {
            return false;
        }

        try {
            $session = self::$app->getSession();
            $user = $session->get('user');

            if (!$user) {
                return false;
            }

            return in_array('admin', $user['roles'] ?? []);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
