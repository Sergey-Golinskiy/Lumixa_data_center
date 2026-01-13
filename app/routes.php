<?php
/**
 * LMS Routes
 */

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AdminMiddleware;

// Register middleware
$router->registerMiddleware('auth', AuthMiddleware::class);
$router->registerMiddleware('guest', GuestMiddleware::class);
$router->registerMiddleware('csrf', CsrfMiddleware::class);
$router->registerMiddleware('admin', AdminMiddleware::class);

// Setup routes (no auth required)
$router->get('/setup', 'SetupController@index');
$router->post('/setup', 'SetupController@store', ['csrf']);
$router->post('/setup/test-db', 'SetupController@testDatabase', ['csrf']);
$router->post('/setup/migrate', 'SetupController@migrate', ['csrf']);

// Auth routes
$router->group(['middleware' => 'guest'], function($router) {
    $router->get('/login', 'AuthController@loginForm');
    $router->post('/login', 'AuthController@login', ['csrf']);
});

$router->get('/logout', 'AuthController@logout');

// Protected routes
$router->group(['middleware' => 'auth'], function($router) {
    // Dashboard
    $router->get('/', 'DashboardController@index');
    $router->get('/dashboard', 'DashboardController@index');

    // Profile
    $router->get('/profile', 'ProfileController@index');
    $router->post('/profile', 'ProfileController@update', ['csrf']);
    $router->post('/profile/password', 'ProfileController@changePassword', ['csrf']);

    // Warehouse module
    $router->group(['prefix' => 'warehouse'], function($router) {
        // Items (SKU)
        $router->get('/items', 'Warehouse\ItemController@index');
        $router->get('/items/create', 'Warehouse\ItemController@create');
        $router->post('/items', 'Warehouse\ItemController@store', ['csrf']);
        $router->get('/items/{id}', 'Warehouse\ItemController@show');
        $router->get('/items/{id}/edit', 'Warehouse\ItemController@edit');
        $router->post('/items/{id}', 'Warehouse\ItemController@update', ['csrf']);
        $router->post('/items/{id}/delete', 'Warehouse\ItemController@delete', ['csrf']);

        // Lots
        $router->get('/lots', 'Warehouse\LotController@index');
        $router->get('/lots/create', 'Warehouse\LotController@create');
        $router->post('/lots', 'Warehouse\LotController@store', ['csrf']);
        $router->get('/lots/{id}', 'Warehouse\LotController@show');
        $router->get('/lots/{id}/edit', 'Warehouse\LotController@edit');
        $router->post('/lots/{id}', 'Warehouse\LotController@update', ['csrf']);

        // Stock
        $router->get('/stock', 'Warehouse\StockController@index');
        $router->get('/stock/item/{id}', 'Warehouse\StockController@byItem');

        // Partners
        $router->get('/partners', 'Warehouse\PartnerController@index');
        $router->get('/partners/create', 'Warehouse\PartnerController@create');
        $router->post('/partners', 'Warehouse\PartnerController@store', ['csrf']);
        $router->get('/partners/{id}', 'Warehouse\PartnerController@show');
        $router->get('/partners/{id}/edit', 'Warehouse\PartnerController@edit');
        $router->post('/partners/{id}', 'Warehouse\PartnerController@update', ['csrf']);

        // Documents
        $router->get('/documents', 'Warehouse\DocumentController@index');
        $router->get('/documents/create', 'Warehouse\DocumentController@create');
        $router->post('/documents', 'Warehouse\DocumentController@store', ['csrf']);
        $router->get('/documents/{id}', 'Warehouse\DocumentController@show');
        $router->get('/documents/{id}/edit', 'Warehouse\DocumentController@edit');
        $router->post('/documents/{id}', 'Warehouse\DocumentController@update', ['csrf']);
        $router->post('/documents/{id}/post', 'Warehouse\DocumentController@post', ['csrf']);
        $router->post('/documents/{id}/cancel', 'Warehouse\DocumentController@cancel', ['csrf']);
    });

    // Catalog module
    $router->group(['prefix' => 'catalog'], function($router) {
        // Products
        $router->get('/products', 'Catalog\ProductController@index');
        $router->get('/products/create', 'Catalog\ProductController@create');
        $router->post('/products', 'Catalog\ProductController@store', ['csrf']);
        $router->get('/products/{id}', 'Catalog\ProductController@show');
        $router->get('/products/{id}/edit', 'Catalog\ProductController@edit');
        $router->post('/products/{id}', 'Catalog\ProductController@update', ['csrf']);

        // Variants
        $router->get('/variants', 'Catalog\VariantController@index');
        $router->get('/variants/create', 'Catalog\VariantController@create');
        $router->post('/variants', 'Catalog\VariantController@store', ['csrf']);
        $router->get('/variants/{id}', 'Catalog\VariantController@show');
        $router->get('/variants/{id}/edit', 'Catalog\VariantController@edit');
        $router->post('/variants/{id}', 'Catalog\VariantController@update', ['csrf']);

        // BOM
        $router->get('/bom', 'Catalog\BomController@index');
        $router->get('/bom/create', 'Catalog\BomController@create');
        $router->post('/bom', 'Catalog\BomController@store', ['csrf']);
        $router->get('/bom/{id}', 'Catalog\BomController@show');
        $router->get('/bom/{id}/edit', 'Catalog\BomController@edit');
        $router->post('/bom/{id}', 'Catalog\BomController@update', ['csrf']);
        $router->post('/bom/{id}/activate', 'Catalog\BomController@activate', ['csrf']);
        $router->post('/bom/{id}/archive', 'Catalog\BomController@archive', ['csrf']);
        $router->get('/bom/{id}/copy', 'Catalog\BomController@copy');
        $router->post('/bom/{id}/copy', 'Catalog\BomController@storeCopy', ['csrf']);

        // Routing
        $router->get('/routing', 'Catalog\RoutingController@index');
        $router->get('/routing/create', 'Catalog\RoutingController@create');
        $router->post('/routing', 'Catalog\RoutingController@store', ['csrf']);
        $router->get('/routing/{id}', 'Catalog\RoutingController@show');
        $router->get('/routing/{id}/edit', 'Catalog\RoutingController@edit');
        $router->post('/routing/{id}', 'Catalog\RoutingController@update', ['csrf']);
        $router->post('/routing/{id}/activate', 'Catalog\RoutingController@activate', ['csrf']);
        $router->post('/routing/{id}/archive', 'Catalog\RoutingController@archive', ['csrf']);
    });

    // Production module
    $router->group(['prefix' => 'production'], function($router) {
        // Production Orders
        $router->get('/orders', 'Production\OrderController@index');
        $router->get('/orders/create', 'Production\OrderController@create');
        $router->post('/orders', 'Production\OrderController@store', ['csrf']);
        $router->get('/orders/{id}', 'Production\OrderController@show');
        $router->get('/orders/{id}/edit', 'Production\OrderController@edit');
        $router->post('/orders/{id}', 'Production\OrderController@update', ['csrf']);
        $router->post('/orders/{id}/start', 'Production\OrderController@start', ['csrf']);
        $router->post('/orders/{id}/complete', 'Production\OrderController@complete', ['csrf']);
        $router->post('/orders/{id}/cancel', 'Production\OrderController@cancel', ['csrf']);

        // Tasks
        $router->get('/tasks', 'Production\TaskController@index');
        $router->get('/tasks/my', 'Production\TaskController@myTasks');
        $router->get('/tasks/create', 'Production\TaskController@create');
        $router->post('/tasks', 'Production\TaskController@store', ['csrf']);
        $router->get('/tasks/{id}', 'Production\TaskController@show');
        $router->get('/tasks/{id}/edit', 'Production\TaskController@edit');
        $router->post('/tasks/{id}', 'Production\TaskController@update', ['csrf']);
        $router->post('/tasks/{id}/assign', 'Production\TaskController@assign', ['csrf']);
        $router->post('/tasks/{id}/start', 'Production\TaskController@start', ['csrf']);
        $router->post('/tasks/{id}/complete', 'Production\TaskController@complete', ['csrf']);

        // Print Queue
        $router->get('/print-queue', 'Production\PrintQueueController@index');
        $router->get('/print-queue/create', 'Production\PrintQueueController@create');
        $router->post('/print-queue', 'Production\PrintQueueController@store', ['csrf']);
        $router->get('/print-queue/{id}', 'Production\PrintQueueController@show');
        $router->get('/print-queue/{id}/edit', 'Production\PrintQueueController@edit');
        $router->post('/print-queue/{id}', 'Production\PrintQueueController@update', ['csrf']);
        $router->post('/print-queue/{id}/start', 'Production\PrintQueueController@start', ['csrf']);
        $router->post('/print-queue/{id}/complete', 'Production\PrintQueueController@complete', ['csrf']);
        $router->post('/print-queue/{id}/cancel', 'Production\PrintQueueController@cancel', ['csrf']);
    });

    // Costing module
    $router->group(['prefix' => 'costing'], function($router) {
        $router->get('/', 'CostingController@index');
        $router->get('/planned', 'CostingController@planned');
        $router->get('/actual', 'CostingController@actual');
        $router->get('/compare', 'CostingController@compare');
        $router->get('/variant/{id}', 'CostingController@variant');
        $router->get('/order/{id}', 'CostingController@order');
    });

    // Admin routes
    $router->group(['prefix' => 'admin', 'middleware' => 'admin'], function($router) {
        $router->get('/', 'Admin\DashboardController@index');

        // Users
        $router->get('/users', 'Admin\UserController@index');
        $router->get('/users/create', 'Admin\UserController@create');
        $router->post('/users', 'Admin\UserController@store', ['csrf']);
        $router->get('/users/{id}', 'Admin\UserController@show');
        $router->get('/users/{id}/edit', 'Admin\UserController@edit');
        $router->post('/users/{id}', 'Admin\UserController@update', ['csrf']);
        $router->post('/users/{id}/toggle', 'Admin\UserController@toggle', ['csrf']);
        $router->post('/users/{id}/reset-password', 'Admin\UserController@resetPassword', ['csrf']);

        // Roles
        $router->get('/roles', 'Admin\RoleController@index');
        $router->get('/roles/create', 'Admin\RoleController@create');
        $router->post('/roles', 'Admin\RoleController@store', ['csrf']);
        $router->get('/roles/{id}', 'Admin\RoleController@show');
        $router->get('/roles/{id}/edit', 'Admin\RoleController@edit');
        $router->post('/roles/{id}', 'Admin\RoleController@update', ['csrf']);

        // Permissions
        $router->get('/permissions', 'Admin\PermissionController@index');

        // Custom Fields
        $router->get('/custom-fields', 'Admin\CustomFieldController@index');
        $router->get('/custom-fields/create', 'Admin\CustomFieldController@create');
        $router->post('/custom-fields', 'Admin\CustomFieldController@store', ['csrf']);
        $router->get('/custom-fields/{id}/edit', 'Admin\CustomFieldController@edit');
        $router->post('/custom-fields/{id}', 'Admin\CustomFieldController@update', ['csrf']);
        $router->post('/custom-fields/{id}/delete', 'Admin\CustomFieldController@delete', ['csrf']);

        // UI Templates
        $router->get('/templates', 'Admin\TemplateController@index');
        $router->get('/templates/create', 'Admin\TemplateController@create');
        $router->post('/templates', 'Admin\TemplateController@store', ['csrf']);
        $router->get('/templates/{id}/edit', 'Admin\TemplateController@edit');
        $router->post('/templates/{id}', 'Admin\TemplateController@update', ['csrf']);

        // Workflows
        $router->get('/workflows', 'Admin\WorkflowController@index');
        $router->get('/workflows/create', 'Admin\WorkflowController@create');
        $router->post('/workflows', 'Admin\WorkflowController@store', ['csrf']);
        $router->get('/workflows/{id}/edit', 'Admin\WorkflowController@edit');
        $router->post('/workflows/{id}', 'Admin\WorkflowController@update', ['csrf']);

        // Audit Log
        $router->get('/audit', 'Admin\AuditController@index');
        $router->get('/audit/{id}', 'Admin\AuditController@show');

        // Backups
        $router->get('/backups', 'Admin\BackupController@index');
        $router->get('/backups/create', 'Admin\BackupController@create');
        $router->post('/backups', 'Admin\BackupController@store', ['csrf']);
        $router->get('/backups/{id}', 'Admin\BackupController@show');
        $router->get('/backups/{id}/download', 'Admin\BackupController@download');
        $router->post('/backups/{id}/restore', 'Admin\BackupController@restore', ['csrf']);
        $router->post('/backups/{id}/delete', 'Admin\BackupController@delete', ['csrf']);

        // Settings
        $router->get('/settings', 'Admin\SettingsController@index');
        $router->post('/settings', 'Admin\SettingsController@update', ['csrf']);
    });
});

// API routes (optional, for AJAX)
$router->group(['prefix' => 'api', 'middleware' => 'auth'], function($router) {
    // Stock check
    $router->get('/stock/check', 'Api\StockController@check');
    $router->get('/stock/available', 'Api\StockController@available');

    // Item search
    $router->get('/items/search', 'Api\ItemController@search');

    // Variant cost
    $router->get('/variants/{id}/cost', 'Api\VariantController@cost');
});
