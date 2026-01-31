<?php $this->section('content'); ?>

<div class="row">
    <!-- Statistics Overview -->
    <div class="col-12">
        <div class="row">
            <!-- User Management Stats -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('user_management') ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-3">
                            <div class="d-flex gap-4">
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['users'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('users') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1 text-success"><?= $this->e($stats['active_users'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('active') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['roles'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('roles') ?></span>
                                </div>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                    <i class="ri-user-line text-primary"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <a href="/admin/users" class="btn btn-soft-primary btn-sm me-1"><?= $this->__('nav_users') ?></a>
                            <a href="/admin/roles" class="btn btn-soft-secondary btn-sm"><?= $this->__('nav_roles') ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catalog Stats -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('catalog_settings') ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-3">
                            <div class="d-flex gap-4">
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['products'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('products') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['categories'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('categories') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['collections'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('collections') ?></span>
                                </div>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                    <i class="ri-shopping-bag-line text-success"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <a href="/admin/product-categories" class="btn btn-soft-success btn-sm me-1"><?= $this->__('categories') ?></a>
                            <a href="/admin/product-collections" class="btn btn-soft-secondary btn-sm"><?= $this->__('collections') ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Stats -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('system') ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-3">
                            <div class="d-flex gap-4">
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['backups'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('backups') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['audit_today'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('today') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['audit_entries'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('week') ?></span>
                                </div>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-secondary-subtle rounded fs-3">
                                    <i class="ri-settings-3-line text-secondary"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <a href="/admin/backups" class="btn btn-soft-secondary btn-sm me-1"><?= $this->__('backups') ?></a>
                            <a href="/admin/audit" class="btn btn-soft-secondary btn-sm me-1"><?= $this->__('audit') ?></a>
                            <a href="/admin/diagnostics" class="btn btn-soft-secondary btn-sm"><?= $this->__('diagnostics') ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Stats -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('equipment_settings') ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-3">
                            <div class="d-flex gap-4">
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['printers'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('printers') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['items'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('items') ?></span>
                                </div>
                                <div class="text-center">
                                    <h4 class="fs-22 fw-semibold mb-1"><?= $this->e($stats['partners'] ?? 0) ?></h4>
                                    <span class="text-muted fs-12"><?= $this->__('partners') ?></span>
                                </div>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                    <i class="ri-printer-line text-info"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <a href="/admin/printers" class="btn btn-soft-info btn-sm me-1"><?= $this->__('printers') ?></a>
                            <a href="/admin/item-options/materials" class="btn btn-soft-secondary btn-sm"><?= $this->__('materials') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Menu Cards -->
    <div class="col-12">
        <div class="row">
            <!-- User Management -->
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-primary-subtle rounded">
                                    <i class="ri-user-settings-line text-primary fs-20"></i>
                                </span>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('user_management') ?></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?= $this->__('manage_users_roles_desc') ?></p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <a href="/admin/users" class="d-flex align-items-center text-body">
                                    <i class="ri-user-line me-2 text-muted"></i>
                                    <?= $this->__('nav_users') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/roles" class="d-flex align-items-center text-body">
                                    <i class="ri-shield-user-line me-2 text-muted"></i>
                                    <?= $this->__('nav_roles') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Catalog Settings -->
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-success-subtle rounded">
                                    <i class="ri-shopping-bag-line text-success fs-20"></i>
                                </span>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('catalog_settings') ?></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?= $this->__('catalog_settings_desc') ?></p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <a href="/admin/product-categories" class="d-flex align-items-center text-body">
                                    <i class="ri-folder-line me-2 text-muted"></i>
                                    <?= $this->__('nav_product_categories') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/product-collections" class="d-flex align-items-center text-body">
                                    <i class="ri-stack-line me-2 text-muted"></i>
                                    <?= $this->__('nav_product_collections') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Orders Settings -->
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-warning-subtle rounded">
                                    <i class="ri-shopping-cart-line text-warning fs-20"></i>
                                </span>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('orders_settings') ?></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?= $this->__('orders_settings_desc') ?></p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <a href="/admin/order-statuses" class="d-flex align-items-center text-body">
                                    <i class="ri-list-check-2 me-2 text-muted"></i>
                                    <?= $this->__('nav_order_statuses') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Item Settings -->
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-warning-subtle rounded">
                                    <i class="ri-tools-line text-warning fs-20"></i>
                                </span>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('item_settings') ?></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?= $this->__('item_settings_desc') ?></p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <a href="/admin/item-options/materials" class="d-flex align-items-center text-body">
                                    <i class="ri-hammer-line me-2 text-muted"></i>
                                    <?= $this->__('nav_materials') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/item-options/manufacturers" class="d-flex align-items-center text-body">
                                    <i class="ri-building-line me-2 text-muted"></i>
                                    <?= $this->__('nav_manufacturers') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/item-options/plastic-types" class="d-flex align-items-center text-body">
                                    <i class="ri-attachment-line me-2 text-muted"></i>
                                    <?= $this->__('nav_plastic_types') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/item-options/filament-aliases" class="d-flex align-items-center text-body">
                                    <i class="ri-palette-line me-2 text-muted"></i>
                                    <?= $this->__('nav_filament_aliases') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Equipment -->
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-info-subtle rounded">
                                    <i class="ri-printer-line text-info fs-20"></i>
                                </span>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('equipment_settings') ?></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?= $this->__('equipment_settings_desc') ?></p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <a href="/admin/printers" class="d-flex align-items-center text-body">
                                    <i class="ri-printer-line me-2 text-muted"></i>
                                    <?= $this->__('nav_printers') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- System -->
            <div class="col-xl-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0 me-3">
                                <span class="avatar-title bg-secondary-subtle rounded">
                                    <i class="ri-settings-3-line text-secondary fs-20"></i>
                                </span>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('system') ?></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?= $this->__('system_settings_desc') ?></p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                <a href="/admin/integrations" class="d-flex align-items-center text-body">
                                    <i class="ri-link me-2 text-muted"></i>
                                    <?= $this->__('nav_integrations') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/audit" class="d-flex align-items-center text-body">
                                    <i class="ri-file-list-3-line me-2 text-muted"></i>
                                    <?= $this->__('nav_audit') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/backups" class="d-flex align-items-center text-body">
                                    <i class="ri-hard-drive-2-line me-2 text-muted"></i>
                                    <?= $this->__('nav_backups') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                            <li class="list-group-item px-0">
                                <a href="/admin/diagnostics" class="d-flex align-items-center text-body">
                                    <i class="ri-stethoscope-line me-2 text-muted"></i>
                                    <?= $this->__('nav_diagnostics') ?>
                                    <i class="ri-arrow-right-s-line ms-auto text-muted"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0 me-3">
                        <span class="avatar-title bg-danger-subtle rounded">
                            <i class="ri-flashlight-line text-danger fs-20"></i>
                        </span>
                    </div>
                    <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('quick_actions') ?></h5>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <a href="/admin/users/create" class="btn btn-soft-primary">
                        <i class="ri-user-add-line me-1"></i>
                        <?= $this->__('create_user') ?>
                    </a>
                    <a href="/admin/backups" class="btn btn-soft-success">
                        <i class="ri-hard-drive-2-line me-1"></i>
                        <?= $this->__('create_backup') ?>
                    </a>
                    <a href="/admin/diagnostics" class="btn btn-soft-info">
                        <i class="ri-stethoscope-line me-1"></i>
                        <?= $this->__('diagnostics') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <?php if (!empty($recentAudit)): ?>
    <div class="col-xl-8 col-md-6">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    <i class="ri-history-line me-1"></i>
                    <?= $this->__('recent_activity') ?>
                </h5>
                <a href="/admin/audit" class="btn btn-soft-primary btn-sm"><?= $this->__('view_all') ?></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('action') ?></th>
                                <th><?= $this->__('user') ?></th>
                                <th><?= $this->__('entity') ?></th>
                                <th><?= $this->__('time') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAudit as $entry): ?>
                            <tr>
                                <td>
                                    <?php
                                    $action = $entry['action'] ?? '';
                                    $badgeClass = match($action) {
                                        'create' => 'bg-success-subtle text-success',
                                        'update' => 'bg-warning-subtle text-warning',
                                        'delete' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $this->e($action) ?>
                                    </span>
                                </td>
                                <td><?= $this->e($entry['user_name'] ?? '-') ?></td>
                                <td><?= $this->e($entry['entity_type'] ?? '-') ?></td>
                                <td>
                                    <span class="text-muted"><?= $this->e(date('d.m.Y H:i', strtotime($entry['created_at']))) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
