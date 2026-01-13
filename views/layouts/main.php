<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <title><?= e($title ?? 'LMS') ?> - <?= e(config('app.name', 'Lumixa Manufacturing System')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="<?= url('dashboard') ?>" class="sidebar-logo">
                    <span class="logo-text">LMS</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?= url('dashboard') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false || $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">
                            <span class="nav-icon">&#9632;</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>

                    <?php if (can('warehouse.items.view') || can('warehouse.stock.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title">Warehouse</span>
                        <ul class="nav-sublist">
                            <?php if (can('warehouse.items.view')): ?>
                            <li><a href="<?= url('warehouse/items') ?>" class="nav-link">Items (SKU)</a></li>
                            <?php endif; ?>
                            <?php if (can('warehouse.lots.view')): ?>
                            <li><a href="<?= url('warehouse/lots') ?>" class="nav-link">Lots/Batches</a></li>
                            <?php endif; ?>
                            <?php if (can('warehouse.stock.view')): ?>
                            <li><a href="<?= url('warehouse/stock') ?>" class="nav-link">Stock</a></li>
                            <?php endif; ?>
                            <?php if (can('warehouse.documents.view')): ?>
                            <li><a href="<?= url('warehouse/documents') ?>" class="nav-link">Documents</a></li>
                            <?php endif; ?>
                            <?php if (can('warehouse.partners.view')): ?>
                            <li><a href="<?= url('warehouse/partners') ?>" class="nav-link">Partners</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (can('catalog.products.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title">Catalog</span>
                        <ul class="nav-sublist">
                            <li><a href="<?= url('catalog/products') ?>" class="nav-link">Products</a></li>
                            <li><a href="<?= url('catalog/variants') ?>" class="nav-link">Variants</a></li>
                            <?php if (can('catalog.bom.view')): ?>
                            <li><a href="<?= url('catalog/bom') ?>" class="nav-link">BOM</a></li>
                            <?php endif; ?>
                            <?php if (can('catalog.routing.view')): ?>
                            <li><a href="<?= url('catalog/routing') ?>" class="nav-link">Routing</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (can('production.orders.view') || can('production.tasks.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title">Production</span>
                        <ul class="nav-sublist">
                            <?php if (can('production.orders.view')): ?>
                            <li><a href="<?= url('production/orders') ?>" class="nav-link">Orders</a></li>
                            <?php endif; ?>
                            <?php if (can('production.tasks.view')): ?>
                            <li><a href="<?= url('production/tasks') ?>" class="nav-link">Tasks</a></li>
                            <?php endif; ?>
                            <?php if (can('production.print_queue.view')): ?>
                            <li><a href="<?= url('production/print-queue') ?>" class="nav-link">Print Queue</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (can('costing.view')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title">Costing</span>
                        <ul class="nav-sublist">
                            <li><a href="<?= url('costing/planned') ?>" class="nav-link">Planned Cost</a></li>
                            <li><a href="<?= url('costing/actual') ?>" class="nav-link">Actual Cost</a></li>
                            <li><a href="<?= url('costing/compare') ?>" class="nav-link">Compare</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (can('admin.access')): ?>
                    <li class="nav-item nav-group">
                        <span class="nav-group-title">Admin</span>
                        <ul class="nav-sublist">
                            <li><a href="<?= url('admin/users') ?>" class="nav-link">Users</a></li>
                            <li><a href="<?= url('admin/roles') ?>" class="nav-link">Roles</a></li>
                            <li><a href="<?= url('admin/custom-fields') ?>" class="nav-link">Custom Fields</a></li>
                            <li><a href="<?= url('admin/workflows') ?>" class="nav-link">Workflows</a></li>
                            <li><a href="<?= url('admin/audit') ?>" class="nav-link">Audit Log</a></li>
                            <li><a href="<?= url('admin/backups') ?>" class="nav-link">Backups</a></li>
                            <li><a href="<?= url('admin/settings') ?>" class="nav-link">Settings</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h1 class="page-title"><?= e($title ?? 'Dashboard') ?></h1>
                </div>

                <div class="header-right">
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle" id="userDropdownToggle">
                            <span class="user-name"><?= e(auth()['full_name'] ?? auth()['username']) ?></span>
                            <span class="user-arrow">&#9662;</span>
                        </button>
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <a href="<?= url('profile') ?>" class="dropdown-item">Profile</a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= url('logout') ?>" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <?php include LMS_ROOT . '/views/partials/flash.php'; ?>

                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
