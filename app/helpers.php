<?php
/**
 * Global Helper Functions
 *
 * This file is loaded early in the bootstrap process and provides
 * commonly used helper functions available throughout the application.
 */

if (!function_exists('h')) {
    /**
     * Escape HTML entities for safe output
     *
     * @param mixed $value The value to escape
     * @return string Escaped string
     */
    function h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    /**
     * Alias for h() - escape HTML entities
     *
     * @param mixed $value The value to escape
     * @return string Escaped string
     */
    function e($value): string
    {
        return h($value);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token from session
     *
     * @return string CSRF token
     */
    function csrf_token(): string
    {
        return $_SESSION['_csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate hidden CSRF input field
     *
     * @return string HTML hidden input
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . h(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value from session
     *
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed
     */
    function old(string $key, $default = '')
    {
        $old = $_SESSION['_old_input'] ?? [];
        return $old[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     *
     * @param string $key Config key
     * @param mixed $default Default value
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        static $config = null;

        if ($config === null) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);
            $configFile = $basePath . '/config/config.php';
            $config = file_exists($configFile) ? require $configFile : [];
        }

        return $config[$key] ?? $default;
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     *
     * @param string $path Path
     * @return string Full URL
     */
    function url(string $path = ''): string
    {
        $base = rtrim(config('app_url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     *
     * @param string $path Asset path
     * @return string Full asset URL
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die - debug helper
     *
     * @param mixed ...$vars Variables to dump
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        exit;
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     *
     * @param string $key Key
     * @param mixed $default Default value
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}
