<?php
/**
 * LMS Bootstrap
 * Initializes autoloading and core dependencies
 */

// Error reporting (will be controlled by config later)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', LMS_ROOT . '/storage/logs/php_errors.log');

// Session settings
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Strict');

// Timezone
date_default_timezone_set('UTC');

// Autoloader
spl_autoload_register(function ($class) {
    // App namespace
    if (strpos($class, 'App\\') === 0) {
        $path = LMS_ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    return false;
});

// Helper functions
require_once LMS_ROOT . '/app/Helpers/functions.php';

// Load configuration if exists
$configFile = LMS_ROOT . '/config/app.php';
if (file_exists($configFile)) {
    $GLOBALS['lms_config'] = require $configFile;
} else {
    $GLOBALS['lms_config'] = [];
}
