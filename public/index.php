<?php
/**
 * Lumixa Manufacturing System (LMS)
 * Entry point
 */

define('LMS_START', microtime(true));
define('LMS_ROOT', dirname(__DIR__));

// Autoload
require_once LMS_ROOT . '/app/bootstrap.php';

// Run application
$app = new App\Core\Application();
$app->run();
