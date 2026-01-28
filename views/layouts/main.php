<!DOCTYPE html>
<html lang="<?= $this->e($currentLocale ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $this->e($title ?? 'Dashboard') ?> - <?= $this->e($config['app_name'] ?? 'Lumixa LMS') ?></title>
    <link rel="stylesheet" href="<?= $this->asset('css/app.css') ?>?v=<?= time() ?>">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="/" class="logo">
                    <div class="logo-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <span class="logo-text">Lumixa</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="/" class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/' ? 'active' : '' ?>">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                                    <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                                    <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                                    <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                                </svg>
                            </span>
                            <span><?= $this->__('dashboard') ?></span>
                        </a>
                    </li>

                    <?php if ($this->can('warehouse.items.view')): ?>
                    <!-- Warehouse -->
                    <li class="nav-item nav-group">
                        <button class="nav-group-toggle" type="button" data-section="warehouse">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                            </span>
                            <span class="nav-group-title"><?= $this->__('nav_warehouse') ?></span>
                            <span class="nav-group-arrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </button>
                        <ul class="nav-submenu" data-submenu="warehouse">
                            <li><a href="/warehouse/items" class="nav-link"><?= $this->__('nav_items') ?></a></li>
                            <li><a href="/warehouse/stock" class="nav-link"><?= $this->__('nav_stock') ?></a></li>
                            <li><a href="/warehouse/documents" class="nav-link"><?= $this->__('nav_documents') ?></a></li>
                            <li><a href="/warehouse/partners" class="nav-link"><?= $this->__('nav_partners') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('catalog.products.view')): ?>
                    <!-- Catalog -->
                    <li class="nav-item nav-group">
                        <button class="nav-group-toggle" type="button" data-section="catalog">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                            </span>
                            <span class="nav-group-title"><?= $this->__('nav_catalog') ?></span>
                            <span class="nav-group-arrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </button>
                        <ul class="nav-submenu" data-submenu="catalog">
                            <li><a href="/catalog/products" class="nav-link"><?= $this->__('nav_products') ?></a></li>
                            <?php if ($this->can('catalog.details.view')): ?>
                            <li><a href="/catalog/details" class="nav-link"><?= $this->__('nav_details') ?></a></li>
                            <?php endif; ?>
                            <li><a href="/catalog/bom" class="nav-link"><?= $this->__('nav_bom') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('production.orders.view')): ?>
                    <!-- Production -->
                    <li class="nav-item nav-group">
                        <button class="nav-group-toggle" type="button" data-section="production">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                            </span>
                            <span class="nav-group-title"><?= $this->__('nav_production') ?></span>
                            <span class="nav-group-arrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </button>
                        <ul class="nav-submenu" data-submenu="production">
                            <li><a href="/production/orders" class="nav-link"><?= $this->__('nav_orders') ?></a></li>
                            <li><a href="/production/tasks" class="nav-link"><?= $this->__('nav_tasks') ?></a></li>
                            <li><a href="/production/print-queue" class="nav-link"><?= $this->__('nav_print_queue') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('costing.view')): ?>
                    <!-- Costing -->
                    <li class="nav-item nav-group">
                        <button class="nav-group-toggle" type="button" data-section="costing">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </span>
                            <span class="nav-group-title"><?= $this->__('nav_costing') ?></span>
                            <span class="nav-group-arrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </button>
                        <ul class="nav-submenu" data-submenu="costing">
                            <li><a href="/costing/plan" class="nav-link"><?= $this->__('nav_plan_cost') ?></a></li>
                            <li><a href="/costing/actual" class="nav-link"><?= $this->__('nav_actual_cost') ?></a></li>
                            <li><a href="/costing/compare" class="nav-link"><?= $this->__('nav_compare') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('admin.access')): ?>
                    <!-- Admin -->
                    <li class="nav-item nav-group">
                        <button class="nav-group-toggle" type="button" data-section="admin">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                            </span>
                            <span class="nav-group-title"><?= $this->__('nav_admin') ?></span>
                            <span class="nav-group-arrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </button>
                        <ul class="nav-submenu" data-submenu="admin">
                            <li><a href="/admin" class="nav-link"><?= $this->__('admin_dashboard') ?></a></li>

                            <li class="nav-submenu-header"><?= $this->__('user_management') ?></li>
                            <li><a href="/admin/users" class="nav-link"><?= $this->__('nav_users') ?></a></li>
                            <li><a href="/admin/roles" class="nav-link"><?= $this->__('nav_roles') ?></a></li>

                            <?php if ($this->can('admin.product_categories.view') || $this->can('admin.product_collections.view')): ?>
                            <li class="nav-submenu-header"><?= $this->__('catalog_settings') ?></li>
                            <?php if ($this->can('admin.product_categories.view')): ?>
                            <li><a href="/admin/product-categories" class="nav-link"><?= $this->__('nav_product_categories') ?></a></li>
                            <?php endif; ?>
                            <?php if ($this->can('admin.product_collections.view')): ?>
                            <li><a href="/admin/product-collections" class="nav-link"><?= $this->__('nav_product_collections') ?></a></li>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($this->can('admin.item_options.view')): ?>
                            <li class="nav-submenu-header"><?= $this->__('item_settings') ?></li>
                            <li><a href="/admin/item-options/materials" class="nav-link"><?= $this->__('nav_materials') ?></a></li>
                            <li><a href="/admin/item-options/manufacturers" class="nav-link"><?= $this->__('nav_manufacturers') ?></a></li>
                            <li><a href="/admin/item-options/plastic-types" class="nav-link"><?= $this->__('nav_plastic_types') ?></a></li>
                            <li><a href="/admin/item-options/filament-aliases" class="nav-link"><?= $this->__('nav_filament_aliases') ?></a></li>
                            <?php endif; ?>

                            <?php if ($this->can('admin.printers.view')): ?>
                            <li class="nav-submenu-header"><?= $this->__('equipment_settings') ?></li>
                            <li><a href="/admin/printers" class="nav-link"><?= $this->__('nav_printers') ?></a></li>
                            <?php endif; ?>

                            <li class="nav-submenu-header"><?= $this->__('system') ?></li>
                            <li><a href="/admin/audit" class="nav-link"><?= $this->__('nav_audit') ?></a></li>
                            <li><a href="/admin/backups" class="nav-link"><?= $this->__('nav_backups') ?></a></li>
                            <li><a href="/admin/diagnostics" class="nav-link"><?= $this->__('nav_diagnostics') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($this->user()['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?= $this->e($this->user()['name'] ?? 'User') ?></span>
                        <span class="user-role"><?= $this->e(implode(', ', $this->user()['roles'] ?? [])) ?></span>
                    </div>
                </div>
                <div class="user-actions">
                    <a href="/profile" class="btn-icon" title="<?= $this->__('profile') ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                    </a>
                    <a href="/logout" class="btn-icon btn-logout" title="<?= $this->__('logout') ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </a>
                </div>
            </div>
        </aside>
        <div class="sidebar-backdrop" data-sidebar-backdrop></div>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="content-header-left">
                    <button class="sidebar-toggle" type="button" aria-label="<?= $this->__('toggle_navigation') ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="page-title"><?= $this->e($title ?? 'Dashboard') ?></h1>
                </div>
                <div class="header-right">
                    <?php if (isset($headerActions)): ?>
                    <div class="header-actions">
                        <?= $headerActions ?>
                    </div>
                    <?php endif; ?>
                    <div class="language-dropdown">
                        <button class="language-dropdown-toggle" type="button">
                            <span class="lang-flag"><?= strtoupper($currentLocale ?? 'en') ?></span>
                            <span class="lang-name"><?= $this->e($localeNames[$currentLocale] ?? 'English') ?></span>
                            <span class="dropdown-arrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </span>
                        </button>
                        <div class="language-dropdown-menu">
                            <?php foreach ($localeNames as $code => $name): ?>
                            <a href="/lang/<?= $code ?>" class="language-option <?= $currentLocale === $code ? 'active' : '' ?>">
                                <span class="lang-flag"><?= strtoupper($code) ?></span>
                                <span class="lang-name"><?= $this->e($name) ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php foreach (['success', 'error', 'warning', 'info'] as $type): ?>
                <?php if ($message = $this->flash($type)): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= $this->e($message) ?>
                    <button type="button" class="alert-close">&times;</button>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="content-body">
                <?= $this->yield('content') ?>
            </div>
        </main>
    </div>

    <!-- Debug Panel (only in dev + admin) -->
    <?php if (($config['app_debug'] ?? false) && $this->can('admin.access')): ?>
    <div class="debug-panel">
        <div class="debug-toggle">Debug</div>
        <div class="debug-content">
            <div><strong>Request ID:</strong> <?= $this->e($requestId ?? '-') ?></div>
            <div><strong>User:</strong> <?= $this->e($this->user()['email'] ?? '-') ?></div>
            <div><strong>Roles:</strong> <?= $this->e(implode(', ', $this->user()['roles'] ?? [])) ?></div>
            <div><strong>URI:</strong> <?= $this->e($_SERVER['REQUEST_URI'] ?? '-') ?></div>
            <div><strong>Time:</strong> <?= date('H:i:s') ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="image-preview-modal" id="image-preview-modal" aria-hidden="true">
        <div class="image-preview-backdrop" data-image-preview-close></div>
        <div class="image-preview-content">
            <button type="button" class="image-preview-close" data-image-preview-close>&times;</button>
            <img src="" alt="<?= $this->__('photo') ?>" id="image-preview-img">
        </div>
    </div>

    <script src="<?= $this->asset('js/app.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
