<!DOCTYPE html>
<html lang="<?= $this->e($currentLocale ?? 'en') ?>" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $this->e($title ?? 'Dashboard') ?> - <?= $this->e($config['app_name'] ?? 'Lumixa LMS') ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= $this->asset('velzon/images/favicon.ico') ?>">

    <!-- Velzon CSS -->
    <link href="<?= $this->asset('velzon/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= $this->asset('velzon/css/icons.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= $this->asset('velzon/css/app.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= $this->asset('velzon/css/custom.min.css') ?>" rel="stylesheet" type="text/css">

    <!-- Custom overrides -->
    <style>
        /* Lumixa custom overrides */
        .navbar-brand-box .logo-lg span { font-weight: 600; font-size: 18px; color: #fff; }
        .navbar-brand-box .logo-sm span { font-weight: 600; font-size: 14px; }
        .logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 8px; display: inline-flex;
            align-items: center; justify-content: center;
            color: #fff; font-size: 16px;
        }
        .menu-title { text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .badge-type-raw { background-color: rgba(41, 156, 219, 0.18); color: #299cdb; }
        .badge-type-consumable { background-color: rgba(247, 184, 75, 0.18); color: #f7b84b; }
        .badge-type-finished { background-color: rgba(69, 203, 133, 0.18); color: #45cb85; }
        .badge-type-part { background-color: rgba(80, 165, 241, 0.18); color: #50a5f1; }
        .page-title-box { padding-bottom: 20px; }
        .simplebar-content { padding-right: 0 !important; }
        /* Image preview */
        .image-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; cursor: pointer; }
        .image-preview-modal { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; z-index: 9999; }
        .image-preview-modal.open { display: flex; }
        .image-preview-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.7); }
        .image-preview-content { position: relative; background: #fff; padding: 16px; border-radius: 8px; max-width: 90vw; max-height: 90vh; z-index: 1; }
        .image-preview-content img { max-width: 85vw; max-height: 80vh; }
        .image-preview-close { position: absolute; top: -10px; right: -10px; width: 30px; height: 30px; border-radius: 50%; border: none; background: #f06548; color: #fff; cursor: pointer; font-size: 18px; }
        /* Live filters */
        .live-filters { background: var(--vz-card-bg); border: 1px solid var(--vz-border-color); border-radius: 0.25rem; padding: 16px; margin-bottom: 20px; }
        .live-filters-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .live-filter-group { flex: 1; min-width: 150px; }
        .live-filter-group.filter-search { min-width: 200px; flex: 2; }
        .live-filter-group.filter-actions { flex: 0 0 auto; min-width: auto; }
        .live-filter-label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--vz-secondary-color); margin-bottom: 6px; }
        .live-filter-search-wrapper { position: relative; }
        .live-filter-search-wrapper .form-control { padding-left: 38px; }
        .live-filter-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--vz-secondary-color); }
        .live-filter-clear-search { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; display: none; }
        .live-filter-search-wrapper.has-value .live-filter-clear-search { display: block; }
        @media (max-width: 768px) {
            .live-filters-row { flex-direction: column; }
            .live-filter-group { width: 100%; min-width: 100%; }
        }
    </style>
</head>
<body>
    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- ========== Header ========== -->
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="/" class="logo logo-dark">
                                <span class="logo-sm"><span class="logo-icon"><i class="ri-stack-line"></i></span></span>
                                <span class="logo-lg"><span class="logo-icon me-2"><i class="ri-stack-line"></i></span><span>Lumixa</span></span>
                            </a>
                            <a href="/" class="logo logo-light">
                                <span class="logo-sm"><span class="logo-icon"><i class="ri-stack-line"></i></span></span>
                                <span class="logo-lg"><span class="logo-icon me-2"><i class="ri-stack-line"></i></span><span>Lumixa</span></span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- Language Dropdown -->
                        <div class="dropdown ms-1 topbar-head-dropdown header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php
                                $flagMap = ['en' => 'us', 'uk' => 'russia', 'ru' => 'russia', 'de' => 'germany', 'pl' => 'spain'];
                                $flagFile = $flagMap[$currentLocale ?? 'en'] ?? 'us';
                                ?>
                                <img src="<?= $this->asset("velzon/images/flags/{$flagFile}.svg") ?>" alt="<?= strtoupper($currentLocale ?? 'en') ?>" height="20" class="rounded">
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <?php foreach ($localeNames ?? [] as $code => $name):
                                    $flag = $flagMap[$code] ?? 'us';
                                ?>
                                <a href="/lang/<?= $code ?>" class="dropdown-item notify-item language <?= ($currentLocale ?? 'en') === $code ? 'active' : '' ?>">
                                    <img src="<?= $this->asset("velzon/images/flags/{$flag}.svg") ?>" alt="<?= $name ?>" class="me-2 rounded" height="18">
                                    <span class="align-middle"><?= $this->e($name) ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <span class="avatar-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                        <?= strtoupper(substr($this->user()['name'] ?? 'U', 0, 1)) ?>
                                    </span>
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= $this->e($this->user()['name'] ?? 'User') ?></span>
                                        <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text"><?= $this->e(implode(', ', $this->user()['roles'] ?? [])) ?></span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header"><?= $this->__('welcome') ?>, <?= $this->e($this->user()['name'] ?? 'User') ?>!</h6>
                                <a class="dropdown-item" href="/profile"><i class="ri-user-line text-muted fs-16 align-middle me-1"></i> <span class="align-middle"><?= $this->__('profile') ?></span></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="/logout"><i class="ri-logout-box-r-line text-danger fs-16 align-middle me-1"></i> <span class="align-middle"><?= $this->__('logout') ?></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="/" class="logo logo-dark">
                    <span class="logo-sm"><span class="logo-icon"><i class="ri-stack-line"></i></span></span>
                    <span class="logo-lg"><span class="logo-icon me-2"><i class="ri-stack-line"></i></span><span>Lumixa</span></span>
                </a>
                <a href="/" class="logo logo-light">
                    <span class="logo-sm"><span class="logo-icon"><i class="ri-stack-line"></i></span></span>
                    <span class="logo-lg"><span class="logo-icon me-2"><i class="ri-stack-line"></i></span><span>Lumixa</span></span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu"></div>
                    <ul class="navbar-nav" id="navbar-nav">

                        <li class="menu-title"><span><?= $this->__('menu') ?></span></li>

                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link menu-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/' ? 'active' : '' ?>" href="/">
                                <i class="ri-dashboard-2-line"></i> <span><?= $this->__('dashboard') ?></span>
                            </a>
                        </li>

                        <?php if ($this->can('warehouse.items.view')): ?>
                        <!-- Warehouse -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarWarehouse" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarWarehouse">
                                <i class="ri-home-4-line"></i> <span><?= $this->__('nav_warehouse') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarWarehouse">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/warehouse/items" class="nav-link"><?= $this->__('nav_items') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/warehouse/stock" class="nav-link"><?= $this->__('nav_stock') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/warehouse/documents" class="nav-link"><?= $this->__('nav_documents') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/warehouse/partners" class="nav-link"><?= $this->__('nav_partners') ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($this->can('catalog.products.view')): ?>
                        <!-- Catalog -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarCatalog" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCatalog">
                                <i class="ri-shopping-bag-line"></i> <span><?= $this->__('nav_catalog') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarCatalog">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/catalog/products" class="nav-link"><?= $this->__('nav_products') ?></a>
                                    </li>
                                    <?php if ($this->can('catalog.details.view')): ?>
                                    <li class="nav-item">
                                        <a href="/catalog/details" class="nav-link"><?= $this->__('nav_details') ?></a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($this->can('production.orders.view')): ?>
                        <!-- Production -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarProduction" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarProduction">
                                <i class="ri-settings-3-line"></i> <span><?= $this->__('nav_production') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarProduction">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/production/orders" class="nav-link"><?= $this->__('nav_orders') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/production/tasks" class="nav-link"><?= $this->__('nav_tasks') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/production/print-queue" class="nav-link"><?= $this->__('nav_print_queue') ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($this->can('sales.orders.view')): ?>
                        <!-- Sales -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarSales" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSales">
                                <i class="ri-shopping-cart-line"></i> <span><?= $this->__('nav_sales') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarSales">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/sales/orders" class="nav-link"><?= $this->__('nav_sales_orders') ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($this->can('costing.view')): ?>
                        <!-- Costing -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarCosting" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCosting">
                                <i class="ri-money-dollar-circle-line"></i> <span><?= $this->__('nav_costing') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarCosting">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/costing/plan" class="nav-link"><?= $this->__('nav_plan_cost') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/costing/actual" class="nav-link"><?= $this->__('nav_actual_cost') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/costing/compare" class="nav-link"><?= $this->__('nav_compare') ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($this->can('admin.access')): ?>
                        <li class="menu-title"><span><?= $this->__('nav_admin') ?></span></li>

                        <!-- Admin -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarAdmin" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAdmin">
                                <i class="ri-shield-user-line"></i> <span><?= $this->__('system') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarAdmin">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/admin" class="nav-link"><?= $this->__('admin_dashboard') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/users" class="nav-link"><?= $this->__('nav_users') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/roles" class="nav-link"><?= $this->__('nav_roles') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/order-statuses" class="nav-link"><?= $this->__('nav_order_statuses') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/integrations" class="nav-link"><?= $this->__('nav_integrations') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/audit" class="nav-link"><?= $this->__('nav_audit') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/backups" class="nav-link"><?= $this->__('nav_backups') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/diagnostics" class="nav-link"><?= $this->__('nav_diagnostics') ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <?php if ($this->can('admin.product_categories.view') || $this->can('admin.product_collections.view')): ?>
                        <!-- Catalog Settings -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarCatalogSettings" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCatalogSettings">
                                <i class="ri-price-tag-3-line"></i> <span><?= $this->__('catalog_settings') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarCatalogSettings">
                                <ul class="nav nav-sm flex-column">
                                    <?php if ($this->can('admin.product_categories.view')): ?>
                                    <li class="nav-item">
                                        <a href="/admin/product-categories" class="nav-link"><?= $this->__('nav_product_categories') ?></a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($this->can('admin.product_collections.view')): ?>
                                    <li class="nav-item">
                                        <a href="/admin/product-collections" class="nav-link"><?= $this->__('nav_product_collections') ?></a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($this->can('admin.item_options.view')): ?>
                        <!-- Item Settings -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarItemSettings" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarItemSettings">
                                <i class="ri-list-settings-line"></i> <span><?= $this->__('item_settings') ?></span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarItemSettings">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="/admin/item-options/materials" class="nav-link"><?= $this->__('nav_materials') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/item-options/manufacturers" class="nav-link"><?= $this->__('nav_manufacturers') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/item-options/plastic-types" class="nav-link"><?= $this->__('nav_plastic_types') ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="/admin/item-options/filament-aliases" class="nav-link"><?= $this->__('nav_filament_aliases') ?></a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($this->can('admin.printers.view')): ?>
                        <!-- Equipment Settings -->
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/admin/printers">
                                <i class="ri-printer-line"></i> <span><?= $this->__('nav_printers') ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>

                    </ul>
                </div>
            </div>

            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->

        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <!-- Page Title -->
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0"><?= $this->e($title ?? 'Dashboard') ?></h4>
                        <?php if (isset($headerActions)): ?>
                        <div class="page-title-right">
                            <?= $headerActions ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Flash Messages -->
                    <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $type => $alertClass): ?>
                        <?php if ($message = $this->flash($type)): ?>
                        <div class="alert alert-<?= $alertClass ?> alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                            <i class="ri-<?= $type === 'success' ? 'check' : ($type === 'error' ? 'error-warning' : ($type === 'warning' ? 'alert' : 'information')) ?>-line label-icon"></i>
                            <?= $this->e($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Main Content -->
                    <?= $this->yield('content') ?>

                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= date('Y') ?> &copy; Lumixa LMS
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                <?php if (($config['app_debug'] ?? false) && $this->can('admin.access')): ?>
                                <span class="text-muted">Debug: <?= $this->e($this->user()['email'] ?? '-') ?> | <?= date('H:i:s') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Image Preview Modal -->
    <div class="image-preview-modal" id="image-preview-modal" aria-hidden="true">
        <div class="image-preview-backdrop" data-image-preview-close></div>
        <div class="image-preview-content">
            <button type="button" class="image-preview-close" data-image-preview-close>&times;</button>
            <img src="" alt="<?= $this->__('photo') ?>" id="image-preview-img">
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="<?= $this->asset('velzon/libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/libs/simplebar/simplebar.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/libs/node-waves/waves.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/libs/feather-icons/feather.min.js') ?>"></script>
    <script src="<?= $this->asset('velzon/js/plugins.js') ?>"></script>

    <!-- App js -->
    <script src="<?= $this->asset('velzon/js/app.js') ?>"></script>

    <!-- Custom Lumixa JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview functionality
        document.querySelectorAll('[data-image-preview]').forEach(function(img) {
            img.addEventListener('click', function() {
                var modal = document.getElementById('image-preview-modal');
                var previewImg = document.getElementById('image-preview-img');
                previewImg.src = this.getAttribute('data-image-preview');
                modal.classList.add('open');
            });
        });

        document.querySelectorAll('[data-image-preview-close]').forEach(function(el) {
            el.addEventListener('click', function() {
                document.getElementById('image-preview-modal').classList.remove('open');
            });
        });

        // Clear search button
        document.querySelectorAll('.live-filter-clear-search').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var wrapper = this.closest('.live-filter-search-wrapper');
                var input = wrapper.querySelector('input');
                input.value = '';
                wrapper.classList.remove('has-value');
                input.dispatchEvent(new Event('change'));
            });
        });

        // Clear all filters
        document.querySelectorAll('.live-filter-clear-all').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var form = this.closest('form');
                if (form) {
                    form.querySelectorAll('input, select').forEach(function(el) {
                        if (el.type !== 'hidden') {
                            el.value = '';
                        }
                    });
                    form.submit();
                }
            });
        });

        // Auto-submit filters on change
        document.querySelectorAll('.live-filter-select').forEach(function(select) {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });

        // Search input wrapper state
        document.querySelectorAll('.live-filter-search-wrapper input').forEach(function(input) {
            input.addEventListener('input', function() {
                this.closest('.live-filter-search-wrapper').classList.toggle('has-value', this.value.length > 0);
            });
        });

        // Highlight active menu items based on current URL
        var currentPath = window.location.pathname;
        document.querySelectorAll('.navbar-nav .nav-link').forEach(function(link) {
            var href = link.getAttribute('href');
            if (href && href !== '/' && currentPath.startsWith(href)) {
                link.classList.add('active');
                var collapse = link.closest('.collapse');
                if (collapse) {
                    collapse.classList.add('show');
                    var toggle = document.querySelector('[data-bs-toggle="collapse"][href="#' + collapse.id + '"], [data-bs-toggle="collapse"][data-bs-target="#' + collapse.id + '"]');
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'true');
                    }
                }
            }
        });
    });
    </script>
</body>
</html>
