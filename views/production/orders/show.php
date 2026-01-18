<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/orders" class="btn btn-secondary">&laquo; Back to Orders</a>
    <?php if ($order['status'] === 'in_progress'): ?>
    <a href="/production/tasks?order_id=<?= $order['id'] ?>" class="btn btn-outline">View Tasks</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Order Information -->
    <div class="card">
        <div class="card-header">Order Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Order #</span>
                <span class="detail-value"><strong><?= $this->e($order['order_number']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Product</span>
                <span class="detail-value"><?= $this->e($order['product_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Variant</span>
                <span class="detail-value">
                    <a href="/catalog/variants/<?= $order['variant_id'] ?>"><?= $this->e($order['variant_sku']) ?></a>
                    - <?= $this->e($order['variant_name']) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Quantity</span>
                <span class="detail-value">
                    <strong><?= number_format($order['completed_quantity'], 0) ?></strong> / <?= number_format($order['quantity'], 0) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($order['status']) {
                        'draft' => 'secondary',
                        'planned' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Priority</span>
                <span class="detail-value"><?= ucfirst($order['priority']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">BOM</span>
                <span class="detail-value">
                    <?php if ($order['bom_id']): ?>
                    <a href="/catalog/bom/<?= $order['bom_id'] ?>">v<?= $this->e($order['bom_version']) ?></a>
                    <?php else: ?>
                    <span class="text-muted">None</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Routing</span>
                <span class="detail-value">
                    <?php if ($order['routing_id']): ?>
                    <a href="/catalog/routing/<?= $order['routing_id'] ?>">v<?= $this->e($order['routing_version']) ?></a>
                    <?php else: ?>
                    <span class="text-muted">None</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created By</span>
                <span class="detail-value"><?= $this->e($order['created_by_name'] ?? '-') ?></span>
            </div>
        </div>
    </div>

    <!-- Actions & Dates -->
    <div class="card">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <?php if ($order['status'] === 'draft' || $order['status'] === 'planned'): ?>
            <form method="POST" action="/production/orders/<?= $order['id'] ?>/start" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success">Start Production</button>
            </form>
            <?php elseif ($order['status'] === 'in_progress'): ?>
            <form method="POST" action="/production/orders/<?= $order['id'] ?>/complete" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label>Completed Quantity</label>
                    <input type="number" name="completed_quantity" value="<?= $order['quantity'] ?>" min="0" max="<?= $order['quantity'] ?>" step="1">
                </div>
                <button type="submit" class="btn btn-success" onclick="return confirm('Complete this order?')">Complete Order</button>
            </form>
            <?php endif; ?>

            <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
            <form method="POST" action="/production/orders/<?= $order['id'] ?>/cancel">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label>Cancel Reason</label>
                    <input type="text" name="reason" placeholder="Reason for cancellation">
                </div>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this order?')">Cancel Order</button>
            </form>
            <?php endif; ?>

            <hr>
            <div class="detail-row">
                <span class="detail-label">Planned Start</span>
                <span class="detail-value"><?= $order['planned_start'] ? $this->date($order['planned_start'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Planned End</span>
                <span class="detail-value"><?= $order['planned_end'] ? $this->date($order['planned_end'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Actual Start</span>
                <span class="detail-value"><?= $order['actual_start'] ? $this->datetime($order['actual_start']) : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Actual End</span>
                <span class="detail-value"><?= $order['actual_end'] ? $this->datetime($order['actual_end']) : '-' ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Tasks -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Production Tasks</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Op #</th>
                        <th>Operation</th>
                        <th>Work Center</th>
                        <th class="text-right">Progress</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No tasks</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                    <?php
                    $taskStatusClass = match($task['status']) {
                        'pending' => 'secondary',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'skipped' => 'info',
                        default => 'secondary'
                    };
                    ?>
                    <tr>
                        <td><?= $task['operation_number'] ?></td>
                        <td><?= $this->e($task['name']) ?></td>
                        <td><?= $this->e($task['work_center'] ?? '-') ?></td>
                        <td class="text-right">
                            <?= number_format($task['completed_quantity'], 0) ?> / <?= number_format($task['planned_quantity'], 0) ?>
                        </td>
                        <td><span class="badge badge-<?= $taskStatusClass ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span></td>
                        <td><?= $this->e($task['assigned_name'] ?? '-') ?></td>
                        <td>
                            <a href="/production/tasks/<?= $task['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Materials -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Material Consumption</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-right">Planned</th>
                        <th class="text-right">Actual</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No materials</td>
                    </tr>
                    <?php else: ?>
                    <?php $totalValue = 0; foreach ($materials as $mat): ?>
                    <?php $value = $mat['actual_quantity'] * $mat['unit_cost']; $totalValue += $value; ?>
                    <tr>
                        <td>
                            <a href="/warehouse/items/<?= $mat['item_id'] ?>"><?= $this->e($mat['sku']) ?></a>
                            <br><small class="text-muted"><?= $this->e($mat['item_name']) ?></small>
                        </td>
                        <td class="text-right"><?= number_format($mat['planned_quantity'], 4) ?> <?= $this->e($mat['unit']) ?></td>
                        <td class="text-right"><?= number_format($mat['actual_quantity'], 4) ?> <?= $this->e($mat['unit']) ?></td>
                        <td class="text-right"><?= number_format($mat['unit_cost'], 4) ?></td>
                        <td class="text-right"><?= number_format($value, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($materials)): ?>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total Material Cost:</strong></td>
                        <td class="text-right"><strong><?= number_format($totalValue, 2) ?></strong></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 120px; color: var(--text-muted); font-size: 13px; }
.detail-value { flex: 1; }
.text-right { text-align: right; }
</style>

<?php $this->endSection(); ?>
