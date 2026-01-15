<!DOCTYPE html>
<html lang="<?= $this->e($currentLocale ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $this->e($title ?? 'Dashboard') ?> - <?= $this->e($config['app_name'] ?? 'Lumixa LMS') ?></title>
    <link rel="stylesheet" href="<?= $this->asset('css/app.css') ?>">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="/" class="logo">
                    <span class="logo-icon">L</span>
                    <span class="logo-text">Lumixa LMS</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="/" class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/' ? 'active' : '' ?>">
                            <span class="nav-icon">&#9632;</span>
                            <span><?= $this->__('dashboard') ?></span>
                        </a>
                    </li>

                    <?php if ($this->can('warehouse.items.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title"><?= $this->__('nav_warehouse') ?></span>
                        <ul class="nav-submenu">
                            <li><a href="/warehouse/items" class="nav-link"><?= $this->__('nav_items') ?></a></li>
                            <li><a href="/warehouse/lots" class="nav-link"><?= $this->__('nav_lots') ?></a></li>
                            <li><a href="/warehouse/stock" class="nav-link"><?= $this->__('nav_stock') ?></a></li>
                            <li><a href="/warehouse/documents" class="nav-link"><?= $this->__('nav_documents') ?></a></li>
                            <li><a href="/warehouse/partners" class="nav-link"><?= $this->__('nav_partners') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('catalog.products.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title"><?= $this->__('nav_catalog') ?></span>
                        <ul class="nav-submenu">
                            <li><a href="/catalog/products" class="nav-link"><?= $this->__('nav_products') ?></a></li>
                            <li><a href="/catalog/variants" class="nav-link"><?= $this->__('nav_variants') ?></a></li>
                            <li><a href="/catalog/bom" class="nav-link"><?= $this->__('nav_bom') ?></a></li>
                            <li><a href="/catalog/routing" class="nav-link"><?= $this->__('nav_routing') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('production.orders.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title"><?= $this->__('nav_production') ?></span>
                        <ul class="nav-submenu">
                            <li><a href="/production/orders" class="nav-link"><?= $this->__('nav_orders') ?></a></li>
                            <li><a href="/production/tasks" class="nav-link"><?= $this->__('nav_tasks') ?></a></li>
                            <li><a href="/production/print-queue" class="nav-link"><?= $this->__('nav_print_queue') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('costing.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title"><?= $this->__('nav_costing') ?></span>
                        <ul class="nav-submenu">
                            <li><a href="/costing/plan" class="nav-link"><?= $this->__('nav_plan_cost') ?></a></li>
                            <li><a href="/costing/actual" class="nav-link"><?= $this->__('nav_actual_cost') ?></a></li>
                            <li><a href="/costing/compare" class="nav-link"><?= $this->__('nav_compare') ?></a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($this->can('admin.access')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title"><?= $this->__('nav_admin') ?></span>
                        <ul class="nav-submenu">
                            <li><a href="/admin/users" class="nav-link"><?= $this->__('nav_users') ?></a></li>
                            <li><a href="/admin/roles" class="nav-link"><?= $this->__('nav_roles') ?></a></li>
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
                    <span class="user-name"><?= $this->e($this->user()['name'] ?? 'User') ?></span>
                    <span class="user-role"><?= $this->e(implode(', ', $this->user()['roles'] ?? [])) ?></span>
                </div>
                <div class="user-actions">
                    <a href="/profile" class="btn-icon" title="<?= $this->__('profile') ?>">&#9881;</a>
                    <a href="/logout" class="btn-icon" title="<?= $this->__('logout') ?>">&#10140;</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1 class="page-title"><?= $this->e($title ?? 'Dashboard') ?></h1>
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
                            <span class="dropdown-arrow">&#9662;</span>
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

    <script src="<?= $this->asset('js/app.js') ?>"></script>
</body>
</html>
