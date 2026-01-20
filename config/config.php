<?php
/**
 * Lumixa Manufacturing System - Configuration
 *
 * Copy this file to config.php and modify values for your environment.
 * NEVER commit real passwords to version control!
 */

return [
    // === Application ===
    'app_env' => 'dev',  // 'prod' or 'dev'
    'app_debug' => true, // Set to false in production!
    'app_url' => 'http://localhost',
    'app_name' => 'Lumixa LMS',
    'app_version' => '1.0.0',

    // === Database ===
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_name' => 'lms_db',
    'db_user' => 'root',
    'db_pass' => '',
    'db_charset' => 'utf8mb4',

    // === Session ===
    'session_lifetime' => 86400, // 24 hours
    'session_name' => 'lms_session',

    // === Security ===
    'csrf_enabled' => true,
    'login_max_attempts' => 5,
    'login_lockout_time' => 900, // 15 minutes

    // === Files ===
    'upload_max_size' => 10485760, // 10MB
    'upload_allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],

    // === Paths ===
    'base_path' => dirname(__DIR__),
    'storage_path' => dirname(__DIR__) . '/storage',
    'upload_path' => dirname(__DIR__) . '/public/uploads',

    // === Logging ===
    'log_level' => 'DEBUG', // DEBUG, INFO, WARNING, ERROR, CRITICAL
    'log_file' => dirname(__DIR__) . '/storage/logs/app.log',

    // === Backup ===
    'backup_path' => dirname(__DIR__) . '/storage/backups',
    'backup_max_count' => 10,

    // === Timezone ===
    'timezone' => 'Europe/Kiev',

    // === Business ===
    'currency' => 'UAH',
    'currency_symbol' => '₴',
    'overhead_percent' => 15.0,
    'costing_method' => 'AVG',

    // === Pagination ===
    'items_per_page' => 25,
    'max_items_per_page' => 100,
];
