<?php
/**
 * Session - Secure session management
 */

namespace App\Core;

class Session
{
    private array $config;
    private bool $started = false;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Start session
     */
    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        // Configure session
        $lifetime = $this->config['session_lifetime'] ?? 86400;
        $name = $this->config['session_name'] ?? 'lms_session';

        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', $lifetime);

        // Use secure cookies if HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            ini_set('session.cookie_secure', '1');
        }

        session_name($name);
        session_start();

        $this->started = true;

        // Regenerate ID periodically
        $this->regenerateIfNeeded();
    }

    /**
     * Regenerate session ID if needed
     */
    private function regenerateIfNeeded(): void
    {
        $lastRegenerate = $_SESSION['_last_regenerate'] ?? 0;
        $interval = 1800; // 30 minutes

        if (time() - $lastRegenerate > $interval) {
            session_regenerate_id(true);
            $_SESSION['_last_regenerate'] = time();
        }
    }

    /**
     * Get session value
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all(): array
    {
        return $_SESSION ?? [];
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * Destroy session
     */
    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Strict'
                ]
            );
        }

        session_destroy();
        $this->started = false;
    }

    /**
     * Regenerate session ID
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regenerate'] = time();
    }

    /**
     * Set flash message
     */
    public function setFlash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash message (one-time read)
     */
    public function flash(string $key)
    {
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if flash message exists
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Get all flash messages
     */
    public function getFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    /**
     * Store old input
     */
    public function flashInput(array $input): void
    {
        $_SESSION['_old_input'] = $input;
    }

    /**
     * Get old input
     */
    public function getOldInput(?string $key = null, $default = null)
    {
        $old = $_SESSION['_old_input'] ?? [];

        if ($key === null) {
            return $old;
        }

        $value = $old[$key] ?? $default;

        return $value;
    }

    /**
     * Clear old input
     */
    public function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }

    /**
     * Store validation errors
     */
    public function setErrors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return $errors;
    }

    /**
     * Get CSRF token
     */
    public function getCsrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }

    /**
     * Regenerate CSRF token
     */
    public function regenerateCsrfToken(): string
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf_token'];
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        return session_id();
    }
}
