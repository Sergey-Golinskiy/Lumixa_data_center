<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('production_orders') ?></h4>
            <div class="page-title-right">
                <?php if ($this->can('production.orders.create')): ?>
                <a href="/production/orders/create" class="btn btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> <?= $this->__('new_order') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line align-bottom me-1"></i> <?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/production/orders">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="<?= $this->__('search_order_sku') ?>"
                               value="<?= $this->e($search) ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>><?= $this->__('draft') ?></option>
                        <option value="planned" <?= $status === 'planned' ? 'selected' : '' ?>><?= $this->__('planned') ?></option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>><?= $this->__('in_progress') ?></option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>><?= $this->__('completed') ?></option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>><?= $this->__('cancelled') ?></option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-soft-primary">
                        <i class="ri-search-line align-bottom me-1"></i> <?= $this->__('search') ?>
                    </button>
                    <?php if ($search || $status): ?>
                    <a href="/production/orders" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line align-bottom me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= $this->__('production_orders') ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <div class="avatar-lg mx-auto mb-4">
                <div class="avatar-title bg-light text-secondary rounded-circle fs-24">
                    <i class="ri-file-list-3-line"></i>
                </div>
            </div>
            <h5 class="mb-2"><?= $this->__('no_orders_found') ?></h5>
            <p class="text-muted mb-0"><?= $this->__('try_adjusting_filters') ?></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('order_number') ?></th>
                        <th><?= $this->__('variant') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-center"><?= $this->__('progress') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('priority') ?></th>
                        <th><?= $this->__('planned') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php
                    $progress = $order['task_count'] > 0 ? ($order['completed_tasks'] / $order['task_count']) * 100 : 0;
                    $statusBadge = match($order['status']) {
                        'draft' => 'bg-secondary-subtle text-secondary',
                        'planned' => 'bg-info-subtle text-info',
                        'in_progress' => 'bg-warning-subtle text-warning',
                        'completed' => 'bg-success-subtle text-success',
                        'cancelled' => 'bg-danger-subtle text-danger',
                        default => 'bg-secondary-subtle text-secondary'
                    };
                    $priorityBadge = match($order['priority']) {
                        'urgent' => 'bg-danger-subtle text-danger',
                        'high' => 'bg-warning-subtle text-warning',
                        'normal' => 'bg-secondary-subtle text-secondary',
                        'low' => 'bg-info-subtle text-info',
                        default => 'bg-secondary-subtle text-secondary'
                    };
                    ?>
                    <tr>
                        <td>
                            <a href="/production/orders/<?= $order['id'] ?>" class="fw-semibold text-primary">
                                <?= $this->e($order['order_number']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="/catalog/variants/<?= $order['variant_id'] ?>"><?= $this->e($order['variant_sku']) ?></a>
                            <br><small class="text-muted"><?= $this->e($order['variant_name']) ?></small>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium"><?= number_format($order['completed_quantity'], 0) ?></span> / <?= number_format($order['quantity'], 0) ?>
                        </td>
                        <td class="text-center">
                            <div class="progress progress-sm mb-1" style="width: 80px; margin: 0 auto;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted"><?= $order['completed_tasks'] ?>/<?= $order['task_count'] ?> <?= $this->__('tasks') ?></small>
                        </td>
                        <td><span class="badge <?= $statusBadge ?>"><?= $this->__(strtolower(str_replace('_', '_', $order['status']))) ?></span></td>
                        <td><span class="badge <?= $priorityBadge ?>"><?= $this->__($order['priority']) ?></span></td>
                        <td>
                            <?php if ($order['planned_start']): ?>
                            <?= $this->date($order['planned_start'], 'Y-m-d') ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/production/orders/<?= $order['id'] ?>" class="btn btn-sm btn-soft-secondary">
                                <i class="ri-eye-line align-bottom"></i> <?= $this->__('view') ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
