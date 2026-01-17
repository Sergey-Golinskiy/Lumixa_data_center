<?php
/**
 * Application Routes
 */

use App\Core\Router;

return function (Router $router) {

    // ========================================
    // Public Routes
    // ========================================

    // Setup Wizard
    $router->get('/setup', 'SetupController@index', 'setup');
    $router->post('/setup', 'SetupController@install');
    $router->get('/setup/check', 'SetupController@check');

    // Health Endpoints
    $router->get('/health', 'HealthController@index', 'health');
    $router->get('/health/details', 'HealthController@details', 'health.details');

    // Authentication
    $router->get('/login', 'AuthController@showLogin', 'login');
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout', 'logout');
    $router->post('/logout', 'AuthController@logout');

    // Language Switch
    $router->get('/lang/{locale}', 'LanguageController@switch', 'lang.switch');

    // ========================================
    // Protected Routes (require auth)
    // ========================================

    $router->group('', function (Router $router) {
        // Dashboard
        $router->get('/', 'DashboardController@index', 'dashboard');

        // Password Change
        $router->get('/change-password', 'AuthController@showChangePassword', 'password.change');
        $router->post('/change-password', 'AuthController@changePassword');

        // Profile
        $router->get('/profile', 'AuthController@profile', 'profile');
        $router->post('/profile', 'AuthController@updateProfile');

    }, ['AuthMiddleware', 'CSRFMiddleware']);

    // ========================================
    // Admin Routes
    // ========================================

    $router->group('/admin', function (Router $router) {
        // Admin Dashboard
        $router->get('', 'Admin\\AdminController@index', 'admin');

        // Diagnostics
        $router->get('/diagnostics', 'Admin\\DiagnosticsController@index', 'admin.diagnostics');
        $router->post('/diagnostics/run-tests', 'Admin\\DiagnosticsController@runTests');
        $router->post('/diagnostics/run-migrations', 'Admin\\DiagnosticsController@runMigrations');
        $router->get('/diagnostics/logs', 'Admin\\DiagnosticsController@logs');
        $router->get('/diagnostics/logs/download', 'Admin\\DiagnosticsController@downloadLogs');

        // Users
        $router->get('/users', 'Admin\\UsersController@index', 'admin.users');
        $router->get('/users/create', 'Admin\\UsersController@create', 'admin.users.create');
        $router->post('/users/create', 'Admin\\UsersController@store');
        $router->get('/users/{id}', 'Admin\\UsersController@show', 'admin.users.show');
        $router->get('/users/{id}/edit', 'Admin\\UsersController@edit', 'admin.users.edit');
        $router->post('/users/{id}/edit', 'Admin\\UsersController@update');
        $router->post('/users/{id}/delete', 'Admin\\UsersController@delete');
        $router->post('/users/{id}/toggle-status', 'Admin\\UsersController@toggleStatus');

        // Roles
        $router->get('/roles', 'Admin\\RolesController@index', 'admin.roles');
        $router->get('/roles/create', 'Admin\\RolesController@create', 'admin.roles.create');
        $router->post('/roles/create', 'Admin\\RolesController@store');
        $router->get('/roles/{id}/edit', 'Admin\\RolesController@edit', 'admin.roles.edit');
        $router->post('/roles/{id}/edit', 'Admin\\RolesController@update');

        // Audit Log
        $router->get('/audit', 'Admin\\AuditController@index', 'admin.audit');
        $router->get('/audit/{id}', 'Admin\\AuditController@show', 'admin.audit.show');

        // Backups
        $router->get('/backups', 'Admin\\BackupController@index', 'admin.backups');
        $router->post('/backups/create', 'Admin\\BackupController@create');
        $router->get('/backups/{id}/download', 'Admin\\BackupController@download');
        $router->post('/backups/{id}/restore', 'Admin\\BackupController@restore');
        $router->post('/backups/{id}/delete', 'Admin\\BackupController@delete');

        // Product Categories
        $router->get('/product-categories', 'Admin\\ProductCategoriesController@index', 'admin.product-categories');
        $router->get('/product-categories/create', 'Admin\\ProductCategoriesController@create', 'admin.product-categories.create');
        $router->post('/product-categories', 'Admin\\ProductCategoriesController@store');
        $router->get('/product-categories/{id}/edit', 'Admin\\ProductCategoriesController@edit', 'admin.product-categories.edit');
        $router->post('/product-categories/{id}', 'Admin\\ProductCategoriesController@update');
        $router->post('/product-categories/{id}/delete', 'Admin\\ProductCategoriesController@delete');

    }, ['AdminMiddleware', 'CSRFMiddleware']);

    // ========================================
    // Warehouse Routes
    // ========================================

    $router->group('/warehouse', function (Router $router) {
        // Items (SKU)
        $router->get('/items', 'Warehouse\\ItemsController@index', 'warehouse.items');
        $router->get('/items/create', 'Warehouse\\ItemsController@create', 'warehouse.items.create');
        $router->post('/items', 'Warehouse\\ItemsController@store');
        $router->get('/items/{id}', 'Warehouse\\ItemsController@show', 'warehouse.items.show');
        $router->get('/items/{id}/edit', 'Warehouse\\ItemsController@edit', 'warehouse.items.edit');
        $router->post('/items/{id}', 'Warehouse\\ItemsController@update');

        // Lots
        // Stock
        $router->get('/stock', 'Warehouse\\StockController@index', 'warehouse.stock');
        $router->get('/stock/movements', 'Warehouse\\StockController@movements', 'warehouse.stock.movements');
        $router->get('/stock/low-stock', 'Warehouse\\StockController@lowStock', 'warehouse.stock.low');
        $router->get('/stock/valuation', 'Warehouse\\StockController@valuation', 'warehouse.stock.valuation');
        $router->get('/stock/{id}', 'Warehouse\\StockController@show', 'warehouse.stock.show');

        // Documents
        $router->get('/documents', 'Warehouse\\DocumentsController@index', 'warehouse.documents');
        $router->get('/documents/create/{type}', 'Warehouse\\DocumentsController@create', 'warehouse.documents.create.type');
        $router->get('/documents/create', 'Warehouse\\DocumentsController@create', 'warehouse.documents.create');
        $router->post('/documents', 'Warehouse\\DocumentsController@store');
        $router->get('/documents/{id}', 'Warehouse\\DocumentsController@show', 'warehouse.documents.show');
        $router->get('/documents/{id}/edit', 'Warehouse\\DocumentsController@edit', 'warehouse.documents.edit');
        $router->post('/documents/{id}', 'Warehouse\\DocumentsController@update');
        $router->post('/documents/{id}/post', 'Warehouse\\DocumentsController@postDocument');
        $router->post('/documents/{id}/cancel', 'Warehouse\\DocumentsController@cancel');

        // Partners
        $router->get('/partners', 'Warehouse\\PartnersController@index', 'warehouse.partners');
        $router->get('/partners/create', 'Warehouse\\PartnersController@create', 'warehouse.partners.create');
        $router->post('/partners', 'Warehouse\\PartnersController@store');
        $router->get('/partners/{id}', 'Warehouse\\PartnersController@show', 'warehouse.partners.show');
        $router->get('/partners/{id}/edit', 'Warehouse\\PartnersController@edit', 'warehouse.partners.edit');
        $router->post('/partners/{id}', 'Warehouse\\PartnersController@update');
        $router->post('/partners/{id}/delete', 'Warehouse\\PartnersController@delete');

    }, ['AuthMiddleware', 'CSRFMiddleware']);

    // ========================================
    // Catalog Routes
    // ========================================

    $router->group('/catalog', function (Router $router) {
        // Products
        $router->get('/products', 'Catalog\\ProductsController@index', 'catalog.products');
        $router->get('/products/create', 'Catalog\\ProductsController@create', 'catalog.products.create');
        $router->post('/products', 'Catalog\\ProductsController@store');
        $router->get('/products/{id}', 'Catalog\\ProductsController@show', 'catalog.products.show');
        $router->get('/products/{id}/edit', 'Catalog\\ProductsController@edit', 'catalog.products.edit');
        $router->post('/products/{id}', 'Catalog\\ProductsController@update');

        // Details
        $router->get('/details', 'Catalog\\DetailsController@index', 'catalog.details');
        $router->get('/details/create', 'Catalog\\DetailsController@create', 'catalog.details.create');
        $router->post('/details', 'Catalog\\DetailsController@store');
        $router->get('/details/{id}', 'Catalog\\DetailsController@show', 'catalog.details.show');
        $router->get('/details/{id}/edit', 'Catalog\\DetailsController@edit', 'catalog.details.edit');
        $router->post('/details/{id}', 'Catalog\\DetailsController@update');

        // Variants
        $router->get('/variants', 'Catalog\\VariantsController@index', 'catalog.variants');
        $router->get('/variants/create', 'Catalog\\VariantsController@create', 'catalog.variants.create');
        $router->post('/variants', 'Catalog\\VariantsController@store');
        $router->get('/variants/{id}', 'Catalog\\VariantsController@show', 'catalog.variants.show');
        $router->get('/variants/{id}/edit', 'Catalog\\VariantsController@edit', 'catalog.variants.edit');
        $router->post('/variants/{id}', 'Catalog\\VariantsController@update');

        // BOM
        $router->get('/bom', 'Catalog\\BOMController@index', 'catalog.bom');
        $router->get('/bom/create', 'Catalog\\BOMController@create', 'catalog.bom.create');
        $router->post('/bom', 'Catalog\\BOMController@store');
        $router->get('/bom/{id}', 'Catalog\\BOMController@show', 'catalog.bom.show');
        $router->get('/bom/{id}/edit', 'Catalog\\BOMController@edit', 'catalog.bom.edit');
        $router->post('/bom/{id}', 'Catalog\\BOMController@update');
        $router->post('/bom/{id}/activate', 'Catalog\\BOMController@activate');
        $router->post('/bom/{id}/archive', 'Catalog\\BOMController@archive');

        // Routing
        $router->get('/routing', 'Catalog\\RoutingController@index', 'catalog.routing');
        $router->get('/routing/create', 'Catalog\\RoutingController@create', 'catalog.routing.create');
        $router->post('/routing', 'Catalog\\RoutingController@store');
        $router->get('/routing/{id}', 'Catalog\\RoutingController@show', 'catalog.routing.show');
        $router->get('/routing/{id}/edit', 'Catalog\\RoutingController@edit', 'catalog.routing.edit');
        $router->post('/routing/{id}', 'Catalog\\RoutingController@update');
        $router->post('/routing/{id}/activate', 'Catalog\\RoutingController@activate');
        $router->post('/routing/{id}/archive', 'Catalog\\RoutingController@archive');

    }, ['AuthMiddleware', 'CSRFMiddleware']);

    // ========================================
    // Production Routes
    // ========================================

    $router->group('/production', function (Router $router) {
        // Production Orders
        $router->get('/orders', 'Production\\OrdersController@index', 'production.orders');
        $router->get('/orders/create', 'Production\\OrdersController@create', 'production.orders.create');
        $router->post('/orders/create', 'Production\\OrdersController@store');
        $router->get('/orders/{id}', 'Production\\OrdersController@show', 'production.orders.show');
        $router->get('/orders/{id}/edit', 'Production\\OrdersController@edit', 'production.orders.edit');
        $router->post('/orders/{id}/edit', 'Production\\OrdersController@update');
        $router->post('/orders/{id}/start', 'Production\\OrdersController@start');
        $router->post('/orders/{id}/complete', 'Production\\OrdersController@complete');
        $router->post('/orders/{id}/cancel', 'Production\\OrdersController@cancel');

        // Tasks
        $router->get('/tasks', 'Production\\TasksController@index', 'production.tasks');
        $router->get('/tasks/{id}', 'Production\\TasksController@show', 'production.tasks.show');
        $router->post('/tasks/{id}/start', 'Production\\TasksController@start');
        $router->post('/tasks/{id}/complete', 'Production\\TasksController@complete');

        // Print Queue
        $router->get('/print-queue', 'Production\\PrintQueueController@index', 'production.print-queue');
        $router->get('/print-queue/create', 'Production\\PrintQueueController@create', 'production.print-queue.create');
        $router->post('/print-queue/create', 'Production\\PrintQueueController@store');
        $router->get('/print-queue/{id}', 'Production\\PrintQueueController@show', 'production.print-queue.show');
        $router->post('/print-queue/{id}/start', 'Production\\PrintQueueController@start');
        $router->post('/print-queue/{id}/complete', 'Production\\PrintQueueController@complete');
        $router->post('/print-queue/{id}/cancel', 'Production\\PrintQueueController@cancel');

    }, ['AuthMiddleware', 'CSRFMiddleware']);

    // ========================================
    // Costing Routes
    // ========================================

    $router->group('/costing', function (Router $router) {
        $router->get('', 'Costing\\CostingController@index', 'costing');
        $router->get('/plan', 'Costing\\CostingController@plan', 'costing.plan');
        $router->get('/actual', 'Costing\\CostingController@actual', 'costing.actual');
        $router->get('/compare', 'Costing\\CostingController@compare', 'costing.compare');
        $router->get('/variant/{id}', 'Costing\\CostingController@variant', 'costing.variant');

    }, ['AuthMiddleware', 'CSRFMiddleware']);

};
