<?php $this->section('content'); ?>

<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128230;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['items'] ?? 0) ?></span>
                <span class="stat-label">Items (SKU)</span>
            </div>
            <?php if ($this->can('warehouse.items.view')): ?>
            <a href="/warehouse/items" class="stat-link">View all</a>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128736;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['products'] ?? 0) ?></span>
                <span class="stat-label">Products</span>
            </div>
            <?php if ($this->can('catalog.products.view')): ?>
            <a href="/catalog/products" class="stat-link">View all</a>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128196;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['production_orders'] ?? 0) ?></span>
                <span class="stat-label">Active Orders</span>
            </div>
            <?php if ($this->can('production.orders.view')): ?>
            <a href="/production/orders" class="stat-link">View all</a>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128424;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['print_jobs_pending'] ?? 0) ?></span>
                <span class="stat-label">Pending Print Jobs</span>
            </div>
            <?php if ($this->can('production.print-queue.view')): ?>
            <a href="/production/print-queue" class="stat-link">View all</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-sections">
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <?php if ($this->can('warehouse.documents.create')): ?>
                <a href="/warehouse/documents/create/receipt" class="quick-action">
                    <span class="qa-icon">+</span>
                    <span>New Receipt</span>
                </a>
                <?php endif; ?>

                <?php if ($this->can('production.print-queue.create')): ?>
                <a href="/production/print-queue/create" class="quick-action">
                    <span class="qa-icon">&#128424;</span>
                    <span>New Print Job</span>
                </a>
                <?php endif; ?>

                <?php if ($this->can('production.orders.create')): ?>
                <a href="/production/orders/create" class="quick-action">
                    <span class="qa-icon">&#128196;</span>
                    <span>New Order</span>
                </a>
                <?php endif; ?>

                <?php if ($this->can('warehouse.items.create')): ?>
                <a href="/warehouse/items/create" class="quick-action">
                    <span class="qa-icon">&#128230;</span>
                    <span>New Item</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>System Status</h2>
            <div class="system-status">
                <div class="status-item status-ok">
                    <span class="status-icon">&#10003;</span>
                    <span>Database Connected</span>
                </div>
                <div class="status-item status-ok">
                    <span class="status-icon">&#10003;</span>
                    <span>System Operational</span>
                </div>
                <?php if ($this->can('admin.diagnostics.view')): ?>
                <a href="/admin/diagnostics" class="btn btn-sm">View Diagnostics</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
