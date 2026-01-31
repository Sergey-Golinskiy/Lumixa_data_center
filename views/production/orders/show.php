<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/orders" class="btn btn-secondary">&laquo; <?= $this->__('back_to_orders') ?></a>
    <?php if ($order['status'] === 'in_progress'): ?>
    <a href="/production/tasks?order_id=<?= $order['id'] ?>" class="btn btn-outline"><?= $this->__('view_tasks') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Order Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('order_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('order_number') ?></span>
                <span class="detail-value"><strong><?= $this->e($order['order_number']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('product') ?></span>
                <span class="detail-value"><?= $this->e($order['product_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('variant') ?></span>
                <span class="detail-value">
                    <a href="/catalog/variants/<?= $order['variant_id'] ?>"><?= $this->e($order['variant_sku']) ?></a>
                    - <?= $this->e($order['variant_name']) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('quantity') ?></span>
                <span class="detail-value">
                    <strong><?= number_format($order['completed_quantity'], 0) ?></strong> / <?= number_format($order['quantity'], 0) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
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
                <span class="detail-label"><?= $this->__('priority') ?></span>
                <span class="detail-value"><?= ucfirst($order['priority']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('bom') ?></span>
                <span class="detail-value">
                    <?php if ($order['bom_id']): ?>
                    <a href="/catalog/bom/<?= $order['bom_id'] ?>">v<?= $this->e($order['bom_version']) ?></a>
                    <?php else: ?>
                    <span class="text-muted"><?= $this->__('none') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('routing') ?></span>
                <span class="detail-value">
                    <?php if ($order['routing_id']): ?>
                    <a href="/catalog/routing/<?= $order['routing_id'] ?>">v<?= $this->e($order['routing_version']) ?></a>
                    <?php else: ?>
                    <span class="text-muted"><?= $this->__('none') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created_by') ?></span>
                <span class="detail-value"><?= $this->e($order['created_by_name'] ?? '-') ?></span>
            </div>
        </div>
    </div>

    <!-- Actions & Dates -->
    <div class="card">
        <div class="card-header"><?= $this->__('actions') ?></div>
        <div class="card-body">
            <?php if ($order['status'] === 'draft' || $order['status'] === 'planned'): ?>
            <form method="POST" action="/production/orders/<?= $order['id'] ?>/start" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success"><?= $this->__('start_production') ?></button>
            </form>
            <?php elseif ($order['status'] === 'in_progress'): ?>
            <form method="POST" action="/production/orders/<?= $order['id'] ?>/complete" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label><?= $this->__('completed_quantity') ?></label>
                    <input type="number" name="completed_quantity" value="<?= $order['quantity'] ?>" min="0" max="<?= $order['quantity'] ?>" step="1">
                </div>
                <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('confirm_complete_order') ?>')"><?= $this->__('complete_order') ?></button>
            </form>
            <?php endif; ?>

            <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
            <form method="POST" action="/production/orders/<?= $order['id'] ?>/cancel">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label><?= $this->__('cancel_reason') ?></label>
                    <input type="text" name="reason" placeholder="<?= $this->__('reason_for_cancellation') ?>">
                </div>
                <button type="submit" class="btn btn-danger" onclick="return confirm('<?= $this->__('confirm_cancel_order') ?>')"><?= $this->__('cancel_order') ?></button>
            </form>
            <?php endif; ?>

            <hr>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('planned_start') ?></span>
                <span class="detail-value"><?= $order['planned_start'] ? $this->date($order['planned_start'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('planned_end') ?></span>
                <span class="detail-value"><?= $order['planned_end'] ? $this->date($order['planned_end'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('actual_start') ?></span>
                <span class="detail-value"><?= $order['actual_start'] ? $this->datetime($order['actual_start']) : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('actual_end') ?></span>
                <span class="detail-value"><?= $order['actual_end'] ? $this->datetime($order['actual_end']) : '-' ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Tasks -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('production_tasks') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('op_number') ?></th>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th class="text-right"><?= $this->__('progress') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('assigned_to') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted"><?= $this->__('no_tasks') ?></td>
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
                            <a href="/production/tasks/<?= $task['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
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
    <div class="card-header"><?= $this->__('material_consumption') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('item') ?></th>
                        <th class="text-right"><?= $this->__('planned') ?></th>
                        <th class="text-right"><?= $this->__('actual') ?></th>
                        <th class="text-right"><?= $this->__('unit_cost') ?></th>
                        <th class="text-right"><?= $this->__('value') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materials)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted"><?= $this->__('no_materials') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php $totalValue = 0; foreach ($materials as $mat): ?>
                    <?php $value = $mat['actual_quantity'] * $mat['unit_cost']; $totalValue += $value; ?>
                    <tr>
                        <td>
                            <a href="/warehouse/items/<?= $mat['item_id'] ?>"><?= $this->e($mat['sku']) ?></a>
                            <br><small class="text-muted"><?= $this->e($mat['item_name']) ?></small>
                        </td>
                        <td class="text-right"><?= number_format($mat['planned_quantity'], 4) ?> <?= $this->__('unit_' . $mat['unit']) ?></td>
                        <td class="text-right"><?= number_format($mat['actual_quantity'], 4) ?> <?= $this->__('unit_' . $mat['unit']) ?></td>
                        <td class="text-right"><?= number_format($mat['unit_cost'], 4) ?></td>
                        <td class="text-right"><?= number_format($value, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($materials)): ?>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong><?= $this->__('total_material_cost') ?>:</strong></td>
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
