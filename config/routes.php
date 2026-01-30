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

        // Product Collections
        $router->get('/product-collections', 'Admin\\ProductCollectionsController@index', 'admin.product-collections');
        $router->get('/product-collections/create', 'Admin\\ProductCollectionsController@create', 'admin.product-collections.create');
        $router->post('/product-collections', 'Admin\\ProductCollectionsController@store');
        $router->get('/product-collections/{id}/edit', 'Admin\\ProductCollectionsController@edit', 'admin.product-collections.edit');
        $router->post('/product-collections/{id}', 'Admin\\ProductCollectionsController@update');
        $router->post('/product-collections/{id}/delete', 'Admin\\ProductCollectionsController@delete');

        // Item Options
        $router->get('/item-options/{group}', 'Admin\\ItemOptionsController@index', 'admin.item-options');
        $router->get('/item-options/{group}/create', 'Admin\\ItemOptionsController@create', 'admin.item-options.create');
        $router->post('/item-options/{group}', 'Admin\\ItemOptionsController@store');
        $router->get('/item-options/{group}/{id}/edit', 'Admin\\ItemOptionsController@edit', 'admin.item-options.edit');
        $router->post('/item-options/{group}/{id}', 'Admin\\ItemOptionsController@update');
        $router->post('/item-options/{group}/{id}/delete', 'Admin\\ItemOptionsController@delete');

        // Printers
        $router->get('/printers', 'Admin\\PrintersController@index', 'admin.printers');
        $router->get('/printers/create', 'Admin\\PrintersController@create', 'admin.printers.create');
        $router->post('/printers', 'Admin\\PrintersController@store');
        $router->get('/printers/{id}/edit', 'Admin\\PrintersController@edit', 'admin.printers.edit');
        $router->post('/printers/{id}', 'Admin\\PrintersController@update');
        $router->post('/printers/{id}/delete', 'Admin\\PrintersController@delete');

        // Integrations (WooCommerce, etc.)
        $router->get('/integrations', 'Admin\\IntegrationsController@index', 'admin.integrations');
        $router->post('/integrations/woocommerce', 'Admin\\IntegrationsController@updateWooCommerce');
        $router->post('/integrations/woocommerce/test', 'Admin\\IntegrationsController@testWooCommerce');
        $router->post('/integrations/woocommerce/sync', 'Admin\\IntegrationsController@syncWooCommerce');

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

        // Items API endpoints
        $router->get('/api/items/generate-sku', 'Warehouse\\ItemsController@generateSku', 'api.items.generate-sku');
        $router->get('/api/items/check-sku', 'Warehouse\\ItemsController@checkSkuUniqueness', 'api.items.check-sku');

        // Batches
        $router->get('/batches', 'Warehouse\\BatchesController@index', 'warehouse.batches');
        $router->get('/batches/create', 'Warehouse\\BatchesController@create', 'warehouse.batches.create');
        $router->post('/batches', 'Warehouse\\BatchesController@store');
        $router->get('/batches/{id}', 'Warehouse\\BatchesController@show', 'warehouse.batches.show');
        $router->post('/batches/{id}/status', 'Warehouse\\BatchesController@updateStatus');
        $router->get('/batches/api/available', 'Warehouse\\BatchesController@getAvailableForAllocation');
        $router->post('/batches/api/preview', 'Warehouse\\BatchesController@previewAllocation');

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
        $router->get('/products/{id}/copy', 'Catalog\\ProductsController@copy', 'catalog.products.create');
        $router->post('/products/{id}', 'Catalog\\ProductsController@update');

        // Product Composition
        $router->post('/products/{id}/components', 'Catalog\\ProductsController@addComponent');
        $router->post('/products/{id}/components/{componentId}', 'Catalog\\ProductsController@updateComponent');
        $router->post('/products/{id}/components/{componentId}/remove', 'Catalog\\ProductsController@removeComponent');
        $router->get('/api/products/details', 'Catalog\\ProductsController@apiGetDetails');
        $router->get('/api/products/items', 'Catalog\\ProductsController@apiGetItems');

        // Product Packaging
        $router->post('/products/{id}/packaging', 'Catalog\\ProductsController@addPackaging');
        $router->post('/products/{id}/packaging/{packagingId}', 'Catalog\\ProductsController@updatePackaging');
        $router->post('/products/{id}/packaging/{packagingId}/remove', 'Catalog\\ProductsController@removePackaging');
        $router->get('/api/products/packaging-items', 'Catalog\\ProductsController@apiGetPackagingItems');

        // Product Operations (Routing)
        $router->post('/products/{id}/operations', 'Catalog\\ProductsController@addOperation');
        $router->post('/products/{id}/operations/{operationId}', 'Catalog\\ProductsController@updateOperation');
        $router->post('/products/{id}/operations/{operationId}/remove', 'Catalog\\ProductsController@removeOperation');
        $router->post('/products/{id}/operations/{operationId}/move-up', 'Catalog\\ProductsController@moveOperationUp');
        $router->post('/products/{id}/operations/{operationId}/move-down', 'Catalog\\ProductsController@moveOperationDown');
        $router->get('/api/products/{id}/components', 'Catalog\\ProductsController@apiGetProductComponents');

        // Details
        $router->get('/details', 'Catalog\\DetailsController@index', 'catalog.details');
        $router->get('/details/create', 'Catalog\\DetailsController@create', 'catalog.details.create');
        $router->post('/details', 'Catalog\\DetailsController@store');
        $router->get('/details/{id}', 'Catalog\\DetailsController@show', 'catalog.details.show');
        $router->get('/details/{id}/edit', 'Catalog\\DetailsController@edit', 'catalog.details.edit');
        $router->get('/details/{id}/copy', 'Catalog\\DetailsController@copy', 'catalog.details.create');
        $router->post('/details/{id}', 'Catalog\\DetailsController@update');

        // Detail Operations (Routing)
        $router->post('/details/{id}/operations', 'Catalog\\DetailsController@addOperation');
        $router->post('/details/{id}/operations/{operationId}', 'Catalog\\DetailsController@updateOperation');
        $router->post('/details/{id}/operations/{operationId}/remove', 'Catalog\\DetailsController@removeOperation');
        $router->post('/details/{id}/operations/{operationId}/move-up', 'Catalog\\DetailsController@moveOperationUp');
        $router->post('/details/{id}/operations/{operationId}/move-down', 'Catalog\\DetailsController@moveOperationDown');

        // Detail Routing
        $router->get('/detail-routing', 'Catalog\\DetailRoutingController@index', 'catalog.detail-routing');
        $router->get('/detail-routing/create', 'Catalog\\DetailRoutingController@create', 'catalog.detail-routing.create');
        $router->post('/detail-routing', 'Catalog\\DetailRoutingController@store');
        $router->get('/detail-routing/{id}', 'Catalog\\DetailRoutingController@show', 'catalog.detail-routing.show');
        $router->get('/detail-routing/{id}/edit', 'Catalog\\DetailRoutingController@edit', 'catalog.detail-routing.edit');
        $router->post('/detail-routing/{id}', 'Catalog\\DetailRoutingController@update');
        $router->post('/detail-routing/{id}/activate', 'Catalog\\DetailRoutingController@activate');
        $router->post('/detail-routing/{id}/archive', 'Catalog\\DetailRoutingController@archive');

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
    // Sales Routes
    // ========================================

    $router->group('/sales', function (Router $router) {
        // Sales Orders (customer orders from all sources)
        $router->get('/orders', 'Sales\\OrdersController@index', 'sales.orders');
        $router->get('/orders/create', 'Sales\\OrdersController@create', 'sales.orders.create');
        $router->post('/orders', 'Sales\\OrdersController@store');
        $router->get('/orders/{id}', 'Sales\\OrdersController@show', 'sales.orders.show');
        $router->get('/orders/{id}/edit', 'Sales\\OrdersController@edit', 'sales.orders.edit');
        $router->post('/orders/{id}', 'Sales\\OrdersController@update');
        $router->post('/orders/{id}/status', 'Sales\\OrdersController@updateStatus');
        $router->post('/orders/{id}/delete', 'Sales\\OrdersController@delete');

    }, ['AuthMiddleware', 'CSRFMiddleware']);

    // ========================================
    // Costing Routes
    // ========================================

    $router->group('/costing', function (Router $router) {
        $router->get('', 'Costing\\CostingController@index', 'costing');
        $router->get('/plan', 'Costing\\CostingController@plan', 'costing.plan');
        $router->get('/actual', 'Costing\\CostingController@actual', 'costing.actual');
        $router->get('/compare', 'Costing\\CostingController@compare', 'costing.compare');

    }, ['AuthMiddleware', 'CSRFMiddleware']);

};
