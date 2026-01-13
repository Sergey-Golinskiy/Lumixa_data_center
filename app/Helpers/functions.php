<?php
/**
 * LMS Helper Functions
 */

/**
 * Get configuration value
 */
function config(string $key, $default = null) {
    $keys = explode('.', $key);
    $value = $GLOBALS['lms_config'] ?? [];

    foreach ($keys as $k) {
        if (!is_array($value) || !array_key_exists($k, $value)) {
            return $default;
        }
        $value = $value[$k];
    }

    return $value;
}

/**
 * Get environment variable or config
 */
function env(string $key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    // Handle boolean strings
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }

    return $value;
}

/**
 * Generate URL path
 */
function url(string $path = ''): string {
    $base = rtrim(config('app.url', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Asset URL
 */
function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirect to URL
 */
function redirect(string $url, int $code = 302): void {
    header('Location: ' . $url, true, $code);
    exit;
}

/**
 * Escape HTML
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get old input value (for form repopulation)
 */
function old(string $key, $default = '') {
    return $_SESSION['_old_input'][$key] ?? $default;
}

/**
 * Get flash message
 */
function flash(string $key, $default = null) {
    $value = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

/**
 * Set flash message
 */
function setFlash(string $key, $value): void {
    $_SESSION['_flash'][$key] = $value;
}

/**
 * Check if user is authenticated
 */
function auth(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user has permission
 */
function can(string $permission): bool {
    $user = auth();
    if (!$user) {
        return false;
    }

    // Admin has all permissions
    if (in_array('admin', $user['roles'] ?? [])) {
        return true;
    }

    return in_array($permission, $user['permissions'] ?? []);
}

/**
 * Get current user ID
 */
function userId(): ?int {
    return auth()['id'] ?? null;
}

/**
 * CSRF token generation
 */
function csrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * CSRF field HTML
 */
function csrfField(): string {
    return '<input type="hidden" name="_csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Validate CSRF token
 */
function validateCsrf(): bool {
    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['_csrf_token'] ?? '';

    if (empty($token) || empty($sessionToken)) {
        return false;
    }

    return hash_equals($sessionToken, $token);
}

/**
 * Format date
 */
function formatDate(?string $date, string $format = 'Y-m-d'): string {
    if (!$date) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime(?string $datetime, string $format = 'Y-m-d H:i:s'): string {
    if (!$datetime) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Format number
 */
function formatNumber($number, int $decimals = 2): string {
    return number_format((float)$number, $decimals, '.', ' ');
}

/**
 * Format currency
 */
function formatCurrency($amount, string $currency = 'UAH'): string {
    return formatNumber($amount) . ' ' . $currency;
}

/**
 * Generate UUID v4
 */
function uuid(): string {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Log message
 */
function logMessage(string $level, string $message, array $context = []): void {
    $logFile = LMS_ROOT . '/storage/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = $context ? ' ' . json_encode($context) : '';
    $line = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

/**
 * Log info
 */
function logInfo(string $message, array $context = []): void {
    logMessage('INFO', $message, $context);
}

/**
 * Log error
 */
function logError(string $message, array $context = []): void {
    logMessage('ERROR', $message, $context);
}

/**
 * Log warning
 */
function logWarning(string $message, array $context = []): void {
    logMessage('WARNING', $message, $context);
}

/**
 * Debug dump
 */
function dd(...$vars): void {
    echo '<pre>';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    exit;
}

/**
 * Check if application is installed
 */
function isInstalled(): bool {
    return file_exists(LMS_ROOT . '/config/app.php') &&
           file_exists(LMS_ROOT . '/storage/.installed');
}

/**
 * Sanitize filename
 */
function sanitizeFilename(string $filename): string {
    $filename = preg_replace('/[^\w\-\.]/', '_', $filename);
    return preg_replace('/_+/', '_', $filename);
}

/**
 * Get file extension
 */
function getFileExtension(string $filename): string {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Human readable file size
 */
function humanFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * JSON response
 */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Abort with error
 */
function abort(int $code = 404, string $message = ''): void {
    http_response_code($code);
    $messages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];
    $message = $message ?: ($messages[$code] ?? 'Error');

    if (isAjax()) {
        jsonResponse(['error' => $message], $code);
    }

    include LMS_ROOT . '/views/errors/' . $code . '.php';
    exit;
}

/**
 * Check if request is AJAX
 */
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get request method
 */
function requestMethod(): string {
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/**
 * Check request method
 */
function isMethod(string $method): bool {
    return requestMethod() === strtoupper($method);
}

/**
 * Get input value
 */
function input(string $key, $default = null) {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Get all input
 */
function allInput(): array {
    return array_merge($_GET, $_POST);
}

/**
 * Validate required fields
 */
function validateRequired(array $fields, array $data): array {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[$field] = "{$label} is required";
        }
    }
    return $errors;
}
