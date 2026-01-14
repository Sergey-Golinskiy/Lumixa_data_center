<?php $this->section('content'); ?>

<div class="page-header">
    <h1>Production Orders</h1>
    <div class="page-actions">
        <?php if ($this->can('production.orders.create')): ?>
        <a href="/production/orders/create" class="btn btn-primary">+ New Order</a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search order # or SKU..."
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="planned" <?= $status === 'planned' ? 'selected' : '' ?>>Planned</option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="/production/orders" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Variant</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-center">Progress</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Planned</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No orders found</td>
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
                            <small><?= $order['completed_tasks'] ?>/<?= $order['task_count'] ?> tasks</small>
                        </td>
                        <td><span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span></td>
                        <td><span class="badge badge-<?= $priorityClass ?>"><?= ucfirst($order['priority']) ?></span></td>
                        <td>
                            <?php if ($order['planned_start']): ?>
                            <?= $this->date($order['planned_start'], 'Y-m-d') ?>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/production/orders/<?= $order['id'] ?>" class="btn btn-sm btn-secondary">View</a>
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
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; Prev</a>
            <?php endif; ?>
            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.filter-form { margin: 0; }
.filter-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.filter-group { flex: 1; min-width: 150px; }
.filter-group input, .filter-group select { width: 100%; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.progress-bar { height: 8px; background: var(--border); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: var(--primary); border-radius: 4px; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
</style>

<?php $this->endSection(); ?>
