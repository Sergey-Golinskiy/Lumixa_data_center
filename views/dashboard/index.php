<?php $this->section('content'); ?>

<div class="main-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <h2><?= $this->__('welcome_back', ['name' => $this->e($this->user()['name'] ?? '')]) ?></h2>
            <p class="welcome-date"><?= date('l, d F Y') ?></p>
        </div>
        <div class="welcome-actions">
            <?php if ($this->can('warehouse.documents.create')): ?>
            <a href="/warehouse/documents/create/receipt" class="btn btn-primary"><?= $this->__('new_receipt') ?></a>
            <?php endif; ?>
            <?php if ($this->can('production.orders.create')): ?>
            <a href="/production/orders/create" class="btn btn-secondary"><?= $this->__('new_order') ?></a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Stats Overview -->
    <div class="dashboard-stats-overview">
        <!-- Warehouse Stats -->
        <?php if ($this->can('warehouse.items.view')): ?>
        <div class="dashboard-stat-group">
            <div class="stat-group-header">
                <span class="stat-group-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">&#128230;</span>
                <span class="stat-group-title"><?= $this->__('nav_warehouse') ?></span>
            </div>
            <div class="stat-group-stats">
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['items_active'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('items') ?></span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->currency($stats['stock_value'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('stock_value') ?></span>
                </div>
                <div class="mini-stat <?= ($stats['low_stock_count'] ?? 0) > 0 ? 'stat-warning' : '' ?>">
                    <span class="mini-stat-value"><?= $this->e($stats['low_stock_count'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('low_stock') ?></span>
                </div>
            </div>
            <div class="stat-group-actions">
                <a href="/warehouse/items" class="btn btn-sm btn-outline"><?= $this->__('nav_items') ?></a>
                <a href="/warehouse/stock" class="btn btn-sm btn-outline"><?= $this->__('nav_stock') ?></a>
                <a href="/warehouse/documents" class="btn btn-sm btn-outline"><?= $this->__('nav_documents') ?></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Catalog Stats -->
        <?php if ($this->can('catalog.products.view')): ?>
        <div class="dashboard-stat-group">
            <div class="stat-group-header">
                <span class="stat-group-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">&#128736;</span>
                <span class="stat-group-title"><?= $this->__('nav_catalog') ?></span>
            </div>
            <div class="stat-group-stats">
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['products_active'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('products') ?></span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['details_total'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('details') ?></span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['variants_total'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('variants') ?></span>
                </div>
            </div>
            <div class="stat-group-actions">
                <a href="/catalog/products" class="btn btn-sm btn-outline"><?= $this->__('nav_products') ?></a>
                <a href="/catalog/details" class="btn btn-sm btn-outline"><?= $this->__('nav_details') ?></a>
                <a href="/catalog/variants" class="btn btn-sm btn-outline"><?= $this->__('nav_variants') ?></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Production Stats -->
        <?php if ($this->can('production.orders.view')): ?>
        <div class="dashboard-stat-group">
            <div class="stat-group-header">
                <span class="stat-group-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">&#128196;</span>
                <span class="stat-group-title"><?= $this->__('nav_production') ?></span>
            </div>
            <div class="stat-group-stats">
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['orders_active'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('active_orders') ?></span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e(($stats['tasks_pending'] ?? 0) + ($stats['tasks_in_progress'] ?? 0)) ?></span>
                    <span class="mini-stat-label"><?= $this->__('tasks') ?></span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['orders_completed_month'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('completed_month') ?></span>
                </div>
            </div>
            <div class="stat-group-actions">
                <a href="/production/orders" class="btn btn-sm btn-outline"><?= $this->__('nav_orders') ?></a>
                <a href="/production/tasks" class="btn btn-sm btn-outline"><?= $this->__('nav_tasks') ?></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Print Queue Stats -->
        <?php if ($this->can('production.print-queue.view')): ?>
        <div class="dashboard-stat-group">
            <div class="stat-group-header">
                <span class="stat-group-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">&#128424;</span>
                <span class="stat-group-title"><?= $this->__('print_queue') ?></span>
            </div>
            <div class="stat-group-stats">
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['print_jobs_queued'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('queued') ?></span>
                </div>
                <div class="mini-stat <?= ($stats['print_jobs_printing'] ?? 0) > 0 ? 'stat-active' : '' ?>">
                    <span class="mini-stat-value"><?= $this->e($stats['print_jobs_printing'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('printing') ?></span>
                </div>
                <div class="mini-stat">
                    <span class="mini-stat-value"><?= $this->e($stats['print_jobs_completed_today'] ?? 0) ?></span>
                    <span class="mini-stat-label"><?= $this->__('today') ?></span>
                </div>
            </div>
            <div class="stat-group-actions">
                <a href="/production/print-queue" class="btn btn-sm btn-outline"><?= $this->__('view_all') ?></a>
                <?php if ($this->can('production.print-queue.create')): ?>
                <a href="/production/print-queue/create" class="btn btn-sm btn-primary"><?= $this->__('new_print_job') ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Dashboard Content -->
    <div class="dashboard-main-content">
        <!-- Left Column -->
        <div class="dashboard-column">
            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="card-header-icon">&#9889;</span>
                    <h3><?= $this->__('quick_actions') ?></h3>
                </div>
                <div class="dashboard-quick-actions">
                    <?php if ($this->can('warehouse.documents.create')): ?>
                    <a href="/warehouse/documents/create/receipt" class="dashboard-quick-action">
                        <span class="qa-icon" style="background: #3b82f6;">+</span>
                        <span class="qa-text">
                            <strong><?= $this->__('new_receipt') ?></strong>
                            <small><?= $this->__('warehouse_receipt_desc') ?></small>
                        </span>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('production.print-queue.create')): ?>
                    <a href="/production/print-queue/create" class="dashboard-quick-action">
                        <span class="qa-icon" style="background: #8b5cf6;">&#128424;</span>
                        <span class="qa-text">
                            <strong><?= $this->__('new_print_job') ?></strong>
                            <small><?= $this->__('print_job_desc') ?></small>
                        </span>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('production.orders.create')): ?>
                    <a href="/production/orders/create" class="dashboard-quick-action">
                        <span class="qa-icon" style="background: #f59e0b;">&#128196;</span>
                        <span class="qa-text">
                            <strong><?= $this->__('new_order') ?></strong>
                            <small><?= $this->__('production_order_desc') ?></small>
                        </span>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('catalog.products.create')): ?>
                    <a href="/catalog/products/create" class="dashboard-quick-action">
                        <span class="qa-icon" style="background: #22c55e;">&#128736;</span>
                        <span class="qa-text">
                            <strong><?= $this->__('new_product') ?></strong>
                            <small><?= $this->__('product_desc') ?></small>
                        </span>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('warehouse.items.create')): ?>
                    <a href="/warehouse/items/create" class="dashboard-quick-action">
                        <span class="qa-icon" style="background: #06b6d4;">&#128230;</span>
                        <span class="qa-text">
                            <strong><?= $this->__('new_item') ?></strong>
                            <small><?= $this->__('item_desc') ?></small>
                        </span>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('catalog.details.create')): ?>
                    <a href="/catalog/details/create" class="dashboard-quick-action">
                        <span class="qa-icon" style="background: #ec4899;">&#9881;</span>
                        <span class="qa-text">
                            <strong><?= $this->__('new_detail') ?></strong>
                            <small><?= $this->__('detail_desc') ?></small>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <?php if (!empty($lowStockItems)): ?>
            <div class="dashboard-card dashboard-card-warning">
                <div class="dashboard-card-header">
                    <span class="card-header-icon">&#9888;</span>
                    <h3><?= $this->__('low_stock_alert') ?></h3>
                    <a href="/warehouse/stock/low-stock" class="btn btn-sm btn-outline"><?= $this->__('view_all') ?></a>
                </div>
                <div class="dashboard-card-body">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th><?= $this->__('sku') ?></th>
                                <th><?= $this->__('name') ?></th>
                                <th><?= $this->__('current') ?></th>
                                <th><?= $this->__('min_stock') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><code><?= $this->e($item['sku']) ?></code></td>
                                <td><?= $this->e($item['name']) ?></td>
                                <td>
                                    <span class="badge badge-danger"><?= $this->number($item['current_stock'], 0) ?></span>
                                </td>
                                <td><?= $this->number($item['min_stock'], 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Active Print Jobs -->
            <?php if (!empty($activePrintJobs)): ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="card-header-icon">&#128424;</span>
                    <h3><?= $this->__('active_print_jobs') ?></h3>
                    <a href="/production/print-queue" class="btn btn-sm btn-outline"><?= $this->__('view_all') ?></a>
                </div>
                <div class="dashboard-card-body">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= $this->__('variant') ?></th>
                                <th><?= $this->__('printer') ?></th>
                                <th><?= $this->__('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activePrintJobs as $job): ?>
                            <tr>
                                <td><a href="/production/print-queue/<?= $job['id'] ?>"><?= $this->e($job['job_number'] ?? $job['id']) ?></a></td>
                                <td><?= $this->e($job['variant_sku'] ?? '-') ?></td>
                                <td><?= $this->e($job['printer_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($job['status'] === 'printing'): ?>
                                    <span class="badge badge-success"><?= $this->__('printing') ?></span>
                                    <?php else: ?>
                                    <span class="badge badge-warning"><?= $this->__('queued') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="dashboard-column">
            <!-- Recent Products -->
            <?php if (!empty($recentProducts)): ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="card-header-icon">&#128736;</span>
                    <h3><?= $this->__('recent_products') ?></h3>
                    <a href="/catalog/products" class="btn btn-sm btn-outline"><?= $this->__('view_all') ?></a>
                </div>
                <div class="dashboard-card-body">
                    <div class="product-list">
                        <?php foreach ($recentProducts as $product): ?>
                        <a href="/catalog/products/<?= $product['id'] ?>" class="product-list-item">
                            <?php if (!empty($product['image_path'])): ?>
                            <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="" class="product-thumb">
                            <?php else: ?>
                            <div class="product-thumb product-thumb-placeholder">&#128736;</div>
                            <?php endif; ?>
                            <div class="product-info">
                                <strong><?= $this->e($product['code']) ?></strong>
                                <span><?= $this->e($product['name']) ?></span>
                                <small><?= $this->e($product['category_name'] ?? '-') ?></small>
                            </div>
                            <?php if ($product['is_active']): ?>
                            <span class="badge badge-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Production Orders -->
            <?php if (!empty($recentOrders)): ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="card-header-icon">&#128196;</span>
                    <h3><?= $this->__('recent_orders') ?></h3>
                    <a href="/production/orders" class="btn btn-sm btn-outline"><?= $this->__('view_all') ?></a>
                </div>
                <div class="dashboard-card-body">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th><?= $this->__('order') ?></th>
                                <th><?= $this->__('variant') ?></th>
                                <th><?= $this->__('quantity') ?></th>
                                <th><?= $this->__('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a href="/production/orders/<?= $order['id'] ?>"><?= $this->e($order['order_number'] ?? $order['id']) ?></a></td>
                                <td><?= $this->e($order['variant_sku'] ?? '-') ?></td>
                                <td><?= $this->number($order['quantity'] ?? 0, 0) ?></td>
                                <td>
                                    <?php
                                    $statusBadge = match($order['status'] ?? '') {
                                        'draft' => 'secondary',
                                        'in_progress' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $statusBadge ?>"><?= $this->__($order['status'] ?? 'unknown') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Documents -->
            <?php if (!empty($recentDocuments)): ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="card-header-icon">&#128203;</span>
                    <h3><?= $this->__('recent_documents') ?></h3>
                    <a href="/warehouse/documents" class="btn btn-sm btn-outline"><?= $this->__('view_all') ?></a>
                </div>
                <div class="dashboard-card-body">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th><?= $this->__('document') ?></th>
                                <th><?= $this->__('type') ?></th>
                                <th><?= $this->__('partner') ?></th>
                                <th><?= $this->__('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDocuments as $doc): ?>
                            <tr>
                                <td><a href="/warehouse/documents/<?= $doc['id'] ?>"><?= $this->e($doc['document_number'] ?? $doc['id']) ?></a></td>
                                <td><?= $this->__($doc['type'] ?? 'unknown') ?></td>
                                <td><?= $this->e($doc['partner_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($doc['status'] === 'posted'): ?>
                                    <span class="badge badge-success"><?= $this->__('posted') ?></span>
                                    <?php elseif ($doc['status'] === 'cancelled'): ?>
                                    <span class="badge badge-danger"><?= $this->__('cancelled') ?></span>
                                    <?php else: ?>
                                    <span class="badge badge-warning"><?= $this->__('draft') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- System Status -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="card-header-icon">&#9881;</span>
                    <h3><?= $this->__('system_status') ?></h3>
                </div>
                <div class="dashboard-card-body">
                    <div class="system-status-list">
                        <div class="status-item status-ok">
                            <span class="status-icon">&#10003;</span>
                            <span class="status-text"><?= $this->__('database_connected') ?></span>
                        </div>
                        <div class="status-item status-ok">
                            <span class="status-icon">&#10003;</span>
                            <span class="status-text"><?= $this->__('system_operational') ?></span>
                        </div>
                        <?php if (($stats['printers_active'] ?? 0) > 0): ?>
                        <div class="status-item status-ok">
                            <span class="status-icon">&#10003;</span>
                            <span class="status-text"><?= $this->__('printers_online', ['count' => $stats['printers_active']]) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($this->can('admin.diagnostics.view')): ?>
                    <div style="margin-top: 15px;">
                        <a href="/admin/diagnostics" class="btn btn-sm btn-outline"><?= $this->__('view_diagnostics') ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Main Dashboard Styles */
.main-dashboard {
    max-width: 1600px;
}

/* Welcome Banner */
.welcome-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 25px 30px;
    border-radius: var(--radius);
    margin-bottom: 25px;
}

.welcome-content h2 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 5px;
}

.welcome-date {
    opacity: 0.8;
    font-size: 14px;
}

.welcome-actions {
    display: flex;
    gap: 10px;
}

/* Stats Overview */
.dashboard-stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.dashboard-stat-group {
    background: var(--bg-card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 20px;
}

.stat-group-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.stat-group-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.stat-group-title {
    font-weight: 600;
    font-size: 16px;
    color: var(--text);
}

.stat-group-stats {
    display: flex;
    gap: 25px;
    margin-bottom: 15px;
}

.mini-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.mini-stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
    line-height: 1.2;
}

.mini-stat-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    text-align: center;
}

.stat-warning .mini-stat-value {
    color: var(--warning);
}

.stat-active .mini-stat-value {
    color: var(--success);
}

.stat-group-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Main Content */
.dashboard-main-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.dashboard-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Dashboard Cards */
.dashboard-card {
    background: var(--bg-card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.dashboard-card-warning {
    border-left: 4px solid var(--warning);
}

.dashboard-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
    background: #f8fafc;
}

.card-header-icon {
    font-size: 18px;
}

.dashboard-card-header h3 {
    flex: 1;
    font-size: 15px;
    font-weight: 600;
    margin: 0;
}

.dashboard-card-body {
    padding: 15px 20px;
}

/* Quick Actions */
.dashboard-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 15px 20px;
}

.dashboard-quick-action {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 15px;
    background: var(--bg);
    border-radius: var(--radius);
    color: var(--text);
    transition: all 0.2s;
}

.dashboard-quick-action:hover {
    background: var(--primary);
    color: white;
    text-decoration: none;
    transform: translateX(5px);
}

.dashboard-quick-action .qa-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    flex-shrink: 0;
}

.dashboard-quick-action .qa-text {
    display: flex;
    flex-direction: column;
}

.dashboard-quick-action .qa-text strong {
    font-size: 14px;
}

.dashboard-quick-action .qa-text small {
    font-size: 12px;
    opacity: 0.7;
}

/* Dashboard Table */
.dashboard-table {
    width: 100%;
    font-size: 13px;
}

.dashboard-table th,
.dashboard-table td {
    padding: 10px 12px;
}

.dashboard-table th {
    background: transparent;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    color: var(--text-muted);
}

/* Product List */
.product-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.product-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: var(--bg);
    border-radius: var(--radius);
    color: var(--text);
    transition: all 0.2s;
}

.product-list-item:hover {
    background: var(--border);
    text-decoration: none;
}

.product-thumb {
    width: 45px;
    height: 45px;
    border-radius: var(--radius);
    object-fit: cover;
    flex-shrink: 0;
}

.product-thumb-placeholder {
    background: var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--text-muted);
}

.product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-info strong {
    font-size: 13px;
}

.product-info span {
    font-size: 12px;
    color: var(--text-muted);
}

.product-info small {
    font-size: 11px;
    color: var(--text-muted);
}

/* System Status */
.system-status-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: var(--radius);
}

.status-ok {
    background: #f0fdf4;
    color: #166534;
}

.status-icon {
    font-weight: bold;
}

.status-text {
    font-size: 13px;
}

/* Button Outline */
.btn-outline {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
}

.btn-outline:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

/* Responsive */
@media (max-width: 1200px) {
    .dashboard-main-content {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .welcome-banner {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .welcome-actions {
        width: 100%;
    }

    .welcome-actions .btn {
        flex: 1;
    }

    .dashboard-stats-overview {
        grid-template-columns: 1fr;
    }

    .stat-group-stats {
        justify-content: space-around;
    }

    .dashboard-quick-action .qa-text small {
        display: none;
    }
}
</style>

<?php $this->endSection(); ?>
