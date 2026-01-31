<?php $this->section('content'); ?>

<!-- Welcome Card -->
<div class="row">
    <div class="col-12">
        <div class="card bg-primary bg-gradient">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="text-white mb-1"><?= $this->__('welcome_back', ['name' => $this->e($this->user()['name'] ?? '')]) ?></h4>
                        <p class="text-white-75 mb-0"><?= date('l, d F Y') ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($this->can('warehouse.documents.create')): ?>
                        <a href="/warehouse/documents/create/receipt" class="btn btn-light"><?= $this->__('new_receipt') ?></a>
                        <?php endif; ?>
                        <?php if ($this->can('production.orders.create')): ?>
                        <a href="/production/orders/create" class="btn btn-soft-light"><?= $this->__('new_order') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <?php if ($this->can('warehouse.items.view')): ?>
    <!-- Warehouse Stats -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('nav_warehouse') ?></p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="/warehouse/items" class="text-decoration-underline text-muted"><?= $this->__('nav_items') ?></a>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $this->e($stats['items_active'] ?? 0) ?></h4>
                        <span class="badge bg-info-subtle text-info"><?= $this->__('items') ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="ri-inbox-line text-primary"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2">
                    <span class="text-muted"><?= $this->__('stock_value') ?>:</span>
                    <span class="fw-medium"><?= $this->currency($stats['stock_value'] ?? 0) ?></span>
                    <?php if (($stats['low_stock_count'] ?? 0) > 0): ?>
                    <span class="badge bg-warning-subtle text-warning ms-2"><?= $stats['low_stock_count'] ?> <?= $this->__('low_stock') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($this->can('catalog.products.view')): ?>
    <!-- Catalog Stats -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('nav_catalog') ?></p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="/catalog/products" class="text-decoration-underline text-muted"><?= $this->__('nav_products') ?></a>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $this->e($stats['products_active'] ?? 0) ?></h4>
                        <span class="badge bg-success-subtle text-success"><?= $this->__('products') ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-shopping-bag-line text-success"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2">
                    <span class="text-muted"><?= $this->__('details') ?>:</span>
                    <span class="fw-medium"><?= $this->e($stats['details_total'] ?? 0) ?></span>
                    <span class="text-muted ms-2"><?= $this->__('variants') ?>:</span>
                    <span class="fw-medium"><?= $this->e($stats['variants_total'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($this->can('production.orders.view')): ?>
    <!-- Production Stats -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('nav_production') ?></p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="/production/orders" class="text-decoration-underline text-muted"><?= $this->__('nav_orders') ?></a>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $this->e($stats['orders_active'] ?? 0) ?></h4>
                        <span class="badge bg-warning-subtle text-warning"><?= $this->__('active_orders') ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="ri-settings-3-line text-warning"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2">
                    <span class="text-muted"><?= $this->__('tasks') ?>:</span>
                    <span class="fw-medium"><?= $this->e(($stats['tasks_pending'] ?? 0) + ($stats['tasks_in_progress'] ?? 0)) ?></span>
                    <span class="text-muted ms-2"><?= $this->__('completed_month') ?>:</span>
                    <span class="fw-medium"><?= $this->e($stats['orders_completed_month'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($this->can('production.print-queue.view')): ?>
    <!-- Print Queue Stats -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('print_queue') ?></p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="/production/print-queue" class="text-decoration-underline text-muted"><?= $this->__('view_all') ?></a>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $this->e($stats['print_jobs_queued'] ?? 0) ?></h4>
                        <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('queued') ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-secondary-subtle rounded fs-3">
                            <i class="ri-printer-line text-secondary"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2">
                    <?php if (($stats['print_jobs_printing'] ?? 0) > 0): ?>
                    <span class="badge bg-success"><?= $stats['print_jobs_printing'] ?> <?= $this->__('printing') ?></span>
                    <?php endif; ?>
                    <span class="text-muted"><?= $this->__('today') ?>:</span>
                    <span class="fw-medium"><?= $this->e($stats['print_jobs_completed_today'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-flashlight-line me-2"></i><?= $this->__('quick_actions') ?></h4>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <?php if ($this->can('warehouse.documents.create')): ?>
                    <a href="/warehouse/documents/create/receipt" class="btn btn-soft-primary text-start">
                        <i class="ri-add-line me-2"></i><?= $this->__('new_receipt') ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('production.print-queue.create')): ?>
                    <a href="/production/print-queue/create" class="btn btn-soft-secondary text-start">
                        <i class="ri-printer-line me-2"></i><?= $this->__('new_print_job') ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('production.orders.create')): ?>
                    <a href="/production/orders/create" class="btn btn-soft-warning text-start">
                        <i class="ri-file-list-3-line me-2"></i><?= $this->__('new_order') ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('catalog.products.create')): ?>
                    <a href="/catalog/products/create" class="btn btn-soft-success text-start">
                        <i class="ri-shopping-bag-line me-2"></i><?= $this->__('new_product') ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('warehouse.items.create')): ?>
                    <a href="/warehouse/items/create" class="btn btn-soft-info text-start">
                        <i class="ri-inbox-line me-2"></i><?= $this->__('new_item') ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($this->can('catalog.details.create')): ?>
                    <a href="/catalog/details/create" class="btn btn-soft-danger text-start">
                        <i class="ri-settings-line me-2"></i><?= $this->__('new_detail') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-shield-check-line me-2"></i><?= $this->__('system_status') ?></h4>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center p-2 bg-success-subtle rounded">
                        <i class="ri-checkbox-circle-line text-success fs-20 me-2"></i>
                        <span><?= $this->__('database_connected') ?></span>
                    </div>
                    <div class="d-flex align-items-center p-2 bg-success-subtle rounded">
                        <i class="ri-checkbox-circle-line text-success fs-20 me-2"></i>
                        <span><?= $this->__('system_operational') ?></span>
                    </div>
                    <?php if (($stats['printers_active'] ?? 0) > 0): ?>
                    <div class="d-flex align-items-center p-2 bg-success-subtle rounded">
                        <i class="ri-checkbox-circle-line text-success fs-20 me-2"></i>
                        <span><?= $this->__('printers_online', ['count' => $stats['printers_active']]) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($this->can('admin.diagnostics.view')): ?>
                <div class="mt-3">
                    <a href="/admin/diagnostics" class="btn btn-sm btn-soft-primary"><?= $this->__('view_diagnostics') ?></a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <!-- Low Stock Alert -->
        <?php if (!empty($lowStockItems)): ?>
        <div class="card border-warning">
            <div class="card-header align-items-center d-flex bg-warning-subtle">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-alert-line me-2 text-warning"></i><?= $this->__('low_stock_alert') ?></h4>
                <a href="/warehouse/stock/low-stock" class="btn btn-sm btn-soft-warning"><?= $this->__('view_all') ?></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('sku') ?></th>
                                <th><?= $this->__('name') ?></th>
                                <th class="text-end"><?= $this->__('current') ?></th>
                                <th class="text-end"><?= $this->__('min_stock') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><code class="text-primary"><?= $this->e($item['sku']) ?></code></td>
                                <td><?= $this->e($item['name']) ?></td>
                                <td class="text-end"><span class="badge bg-danger"><?= $this->number($item['current_stock'], 0) ?></span></td>
                                <td class="text-end"><?= $this->number($item['min_stock'], 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Orders -->
        <?php if (!empty($recentOrders)): ?>
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-file-list-3-line me-2"></i><?= $this->__('recent_orders') ?></h4>
                <a href="/production/orders" class="btn btn-sm btn-soft-primary"><?= $this->__('view_all') ?></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('order') ?></th>
                                <th><?= $this->__('variant') ?></th>
                                <th class="text-end"><?= $this->__('quantity') ?></th>
                                <th><?= $this->__('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a href="/production/orders/<?= $order['id'] ?>" class="fw-medium"><?= $this->e($order['order_number'] ?? $order['id']) ?></a></td>
                                <td><?= $this->e($order['variant_sku'] ?? '-') ?></td>
                                <td class="text-end"><?= $this->number($order['quantity'] ?? 0, 0) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($order['status'] ?? '') {
                                        'draft' => 'secondary',
                                        'in_progress' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= $this->__($order['status'] ?? 'unknown') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Documents -->
        <?php if (!empty($recentDocuments)): ?>
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-file-text-line me-2"></i><?= $this->__('recent_documents') ?></h4>
                <a href="/warehouse/documents" class="btn btn-sm btn-soft-primary"><?= $this->__('view_all') ?></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
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
                                <td><a href="/warehouse/documents/<?= $doc['id'] ?>" class="fw-medium"><?= $this->e($doc['document_number'] ?? $doc['id']) ?></a></td>
                                <td><?= $this->__($doc['type'] ?? 'unknown') ?></td>
                                <td><?= $this->e($doc['partner_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($doc['status'] === 'posted'): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('posted') ?></span>
                                    <?php elseif ($doc['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger-subtle text-danger"><?= $this->__('cancelled') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning"><?= $this->__('draft') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Active Print Jobs -->
        <?php if (!empty($activePrintJobs)): ?>
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-printer-line me-2"></i><?= $this->__('active_print_jobs') ?></h4>
                <a href="/production/print-queue" class="btn btn-sm btn-soft-primary"><?= $this->__('view_all') ?></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
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
                                <td><a href="/production/print-queue/<?= $job['id'] ?>" class="fw-medium"><?= $this->e($job['job_number'] ?? $job['id']) ?></a></td>
                                <td><?= $this->e($job['variant_sku'] ?? '-') ?></td>
                                <td><?= $this->e($job['printer_name'] ?? '-') ?></td>
                                <td>
                                    <?php if ($job['status'] === 'printing'): ?>
                                    <span class="badge bg-success"><?= $this->__('printing') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-warning"><?= $this->__('queued') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Products -->
        <?php if (!empty($recentProducts)): ?>
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-shopping-bag-line me-2"></i><?= $this->__('recent_products') ?></h4>
                <a href="/catalog/products" class="btn btn-sm btn-soft-primary"><?= $this->__('view_all') ?></a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-nowrap mb-0">
                        <tbody>
                            <?php foreach ($recentProducts as $product): ?>
                            <tr>
                                <td style="width: 60px;">
                                    <?php if (!empty($product['image_path'])): ?>
                                    <div class="avatar-sm bg-light rounded p-1">
                                        <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="" class="img-fluid d-block rounded">
                                    </div>
                                    <?php else: ?>
                                    <div class="avatar-sm bg-light rounded p-1 d-flex align-items-center justify-content-center">
                                        <i class="ri-shopping-bag-line text-muted fs-20"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <h5 class="fs-14 my-1">
                                        <a href="/catalog/products/<?= $product['id'] ?>" class="text-reset"><?= $this->e($product['code']) ?></a>
                                    </h5>
                                    <span class="text-muted"><?= $this->e($product['name']) ?></span>
                                </td>
                                <td>
                                    <span class="text-muted"><?= $this->e($product['category_name'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <?php if ($product['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
