<?php $this->section('content'); ?>

<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128230;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['items'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('items_sku') ?></span>
            </div>
            <?php if ($this->can('warehouse.items.view')): ?>
            <a href="/warehouse/items" class="stat-link"><?= $this->__('view_all') ?></a>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128736;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['products'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('products') ?></span>
            </div>
            <?php if ($this->can('catalog.products.view')): ?>
            <a href="/catalog/products" class="stat-link"><?= $this->__('view_all') ?></a>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128196;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['production_orders'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('active_orders') ?></span>
            </div>
            <?php if ($this->can('production.orders.view')): ?>
            <a href="/production/orders" class="stat-link"><?= $this->__('view_all') ?></a>
            <?php endif; ?>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128424;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['print_jobs_pending'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('pending_print_jobs') ?></span>
            </div>
            <?php if ($this->can('production.print-queue.view')): ?>
            <a href="/production/print-queue" class="stat-link"><?= $this->__('view_all') ?></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-sections">
        <div class="dashboard-section">
            <h2><?= $this->__('quick_actions') ?></h2>
            <div class="quick-actions">
                <?php if ($this->can('warehouse.documents.create')): ?>
                <a href="/warehouse/documents/create/receipt" class="quick-action">
                    <span class="qa-icon">+</span>
                    <span><?= $this->__('new_receipt') ?></span>
                </a>
                <?php endif; ?>

                <?php if ($this->can('production.print-queue.create')): ?>
                <a href="/production/print-queue/create" class="quick-action">
                    <span class="qa-icon">&#128424;</span>
                    <span><?= $this->__('new_print_job') ?></span>
                </a>
                <?php endif; ?>

                <?php if ($this->can('production.orders.create')): ?>
                <a href="/production/orders/create" class="quick-action">
                    <span class="qa-icon">&#128196;</span>
                    <span><?= $this->__('new_order') ?></span>
                </a>
                <?php endif; ?>

                <?php if ($this->can('warehouse.items.create')): ?>
                <a href="/warehouse/items/create" class="quick-action">
                    <span class="qa-icon">&#128230;</span>
                    <span><?= $this->__('new_item') ?></span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-section">
            <h2><?= $this->__('system_status') ?></h2>
            <div class="system-status">
                <div class="status-item status-ok">
                    <span class="status-icon">&#10003;</span>
                    <span><?= $this->__('database_connected') ?></span>
                </div>
                <div class="status-item status-ok">
                    <span class="status-icon">&#10003;</span>
                    <span><?= $this->__('system_operational') ?></span>
                </div>
                <?php if ($this->can('admin.diagnostics.view')): ?>
                <a href="/admin/diagnostics" class="btn btn-sm"><?= $this->__('view_diagnostics') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
