<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('production_orders') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('production.orders.create')): ?>
        <a href="/production/orders/create" class="btn btn-primary">+ <?= $this->__('new_order') ?></a>
        <?php endif; ?>
    </div>
</div>

<!-- Live Filters -->
<div class="live-filters">
    <form method="GET" action="/production/orders">
        <div class="live-filters-row">
            <div class="live-filter-group filter-search">
                <label class="live-filter-label"><?= $this->__('search') ?></label>
                <div class="live-filter-search-wrapper <?= $search ? 'has-value' : '' ?>">
                    <span class="live-filter-search-icon">&#128269;</span>
                    <input type="text" name="search" class="live-filter-input"
                           placeholder="<?= $this->__('search_order_sku') ?>"
                           value="<?= $this->e($search) ?>">
                    <button type="button" class="live-filter-clear-search" title="<?= $this->__('clear') ?>">&times;</button>
                </div>
            </div>

            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('status') ?></label>
                <select name="status" class="live-filter-select">
                    <option value=""><?= $this->__('all_statuses') ?></option>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>><?= $this->__('draft') ?></option>
                    <option value="planned" <?= $status === 'planned' ? 'selected' : '' ?>><?= $this->__('planned') ?></option>
                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>><?= $this->__('in_progress') ?></option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>><?= $this->__('completed') ?></option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>><?= $this->__('cancelled') ?></option>
                </select>
            </div>

            <div class="live-filter-group filter-actions">
                <button type="button" class="live-filter-clear-all" <?= (!$search && !$status) ? 'disabled' : '' ?>>
                    <?= $this->__('clear_filters') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('order_number') ?></th>
                        <th><?= $this->__('variant') ?></th>
                        <th class="text-right"><?= $this->__('quantity') ?></th>
                        <th class="text-center"><?= $this->__('progress') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('priority') ?></th>
                        <th><?= $this->__('planned') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted"><?= $this->__('no_orders_found') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <?php
                    $progress = $order['task_count'] > 0 ? ($order['completed_tasks'] / $order['task_count']) * 100 : 0;
                    $statusClass = match($order['status']) {
                        'draft' => 'secondary',
                        'planned' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    };
                    $priorityClass = match($order['priority']) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'normal' => 'secondary',
                        'low' => 'info',
                        default => 'secondary'
                    };
                    ?>
                    <tr>
                        <td>
                            <a href="/production/orders/<?= $order['id'] ?>">
                                <strong><?= $this->e($order['order_number']) ?></strong>
                            </a>
                        </td>
                        <td>
                            <a href="/catalog/variants/<?= $order['variant_id'] ?>"><?= $this->e($order['variant_sku']) ?></a>
                            <br><small class="text-muted"><?= $this->e($order['variant_name']) ?></small>
                        </td>
                        <td class="text-right">
                            <?= number_format($order['completed_quantity'], 0) ?> / <?= number_format($order['quantity'], 0) ?>
                        </td>
                        <td class="text-center">
                            <div class="progress-bar" style="width: 80px; margin: 0 auto;">
                                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                            </div>
                            <small><?= $order['completed_tasks'] ?>/<?= $order['task_count'] ?> <?= $this->__('tasks') ?></small>
                        </td>
                        <td><span class="badge badge-<?= $statusClass ?>"><?= $this->__(strtolower(str_replace('_', '_', $order['status']))) ?></span></td>
                        <td><span class="badge badge-<?= $priorityClass ?>"><?= $this->__($order['priority']) ?></span></td>
                        <td>
                            <?php if ($order['planned_start']): ?>
                            <?= $this->date($order['planned_start'], 'Y-m-d') ?>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/production/orders/<?= $order['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; <?= $this->__('prev') ?></a>
            <?php endif; ?>
            <span class="pagination-info"><?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline"><?= $this->__('next') ?> &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.progress-bar { height: 8px; background: var(--border); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: var(--primary); border-radius: 4px; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
</style>

<?php $this->endSection(); ?>
