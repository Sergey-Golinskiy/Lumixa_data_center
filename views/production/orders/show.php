<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('order_details') ?></h4>
            <div class="page-title-right d-flex gap-2">
                <a href="/production/orders" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('back_to_orders') ?>
                </a>
                <?php if ($order['status'] === 'in_progress'): ?>
                <a href="/production/tasks?order_id=<?= $order['id'] ?>" class="btn btn-soft-primary">
                    <i class="ri-task-line align-bottom me-1"></i> <?= $this->__('view_tasks') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Order Information -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-file-list-3-line align-bottom me-1"></i> <?= $this->__('order_information') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 150px;"><?= $this->__('order_number') ?></th>
                                <td class="text-muted fw-semibold"><?= $this->e($order['order_number']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('product') ?></th>
                                <td class="text-muted"><?= $this->e($order['product_name']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('variant') ?></th>
                                <td class="text-muted">
                                    <a href="/catalog/variants/<?= $order['variant_id'] ?>" class="text-primary"><?= $this->e($order['variant_sku']) ?></a>
                                    - <?= $this->e($order['variant_name']) ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('quantity') ?></th>
                                <td class="text-muted">
                                    <span class="fw-semibold"><?= number_format($order['completed_quantity'], 0) ?></span> / <?= number_format($order['quantity'], 0) ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('status') ?></th>
                                <td>
                                    <?php
                                    $statusBadge = match($order['status']) {
                                        'draft' => 'bg-secondary-subtle text-secondary',
                                        'planned' => 'bg-info-subtle text-info',
                                        'in_progress' => 'bg-warning-subtle text-warning',
                                        'completed' => 'bg-success-subtle text-success',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $order['status'])) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('priority') ?></th>
                                <td class="text-muted"><?= ucfirst($order['priority']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('bom') ?></th>
                                <td>
                                    <?php if ($order['bom_id']): ?>
                                    <a href="/catalog/bom/<?= $order['bom_id'] ?>" class="text-primary">v<?= $this->e($order['bom_version']) ?></a>
                                    <?php else: ?>
                                    <span class="text-muted"><?= $this->__('none') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('routing') ?></th>
                                <td>
                                    <?php if ($order['routing_id']): ?>
                                    <a href="/catalog/routing/<?= $order['routing_id'] ?>" class="text-primary">v<?= $this->e($order['routing_version']) ?></a>
                                    <?php else: ?>
                                    <span class="text-muted"><?= $this->__('none') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('created_by') ?></th>
                                <td class="text-muted"><?= $this->e($order['created_by_name'] ?? '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions & Dates -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-settings-3-line align-bottom me-1"></i> <?= $this->__('actions') ?></h5>
            </div>
            <div class="card-body">
                <?php if ($order['status'] === 'draft' || $order['status'] === 'planned'): ?>
                <form method="POST" action="/production/orders/<?= $order['id'] ?>/start" class="mb-3">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="ri-play-line align-bottom me-1"></i> <?= $this->__('start_production') ?>
                    </button>
                </form>
                <?php elseif ($order['status'] === 'in_progress'): ?>
                <form method="POST" action="/production/orders/<?= $order['id'] ?>/complete" class="mb-3">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('completed_quantity') ?></label>
                        <input type="number" name="completed_quantity" class="form-control" value="<?= $order['quantity'] ?>" min="0" max="<?= $order['quantity'] ?>" step="1">
                    </div>
                    <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('confirm_complete_order') ?>')">
                        <i class="ri-check-line align-bottom me-1"></i> <?= $this->__('complete_order') ?>
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                <form method="POST" action="/production/orders/<?= $order['id'] ?>/cancel">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('cancel_reason') ?></label>
                        <input type="text" name="reason" class="form-control" placeholder="<?= $this->__('reason_for_cancellation') ?>">
                    </div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('<?= $this->__('confirm_cancel_order') ?>')">
                        <i class="ri-close-line align-bottom me-1"></i> <?= $this->__('cancel_order') ?>
                    </button>
                </form>
                <?php endif; ?>

                <hr class="my-4">

                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 140px;"><?= $this->__('planned_start') ?></th>
                                <td class="text-muted"><?= $order['planned_start'] ? $this->date($order['planned_start'], 'Y-m-d') : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('planned_end') ?></th>
                                <td class="text-muted"><?= $order['planned_end'] ? $this->date($order['planned_end'], 'Y-m-d') : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('actual_start') ?></th>
                                <td class="text-muted"><?= $order['actual_start'] ? $this->datetime($order['actual_start']) : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('actual_end') ?></th>
                                <td class="text-muted"><?= $order['actual_end'] ? $this->datetime($order['actual_end']) : '-' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tasks -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-task-line align-bottom me-1"></i> <?= $this->__('production_tasks') ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($tasks)): ?>
        <div class="text-center py-5">
            <div class="avatar-lg mx-auto mb-4">
                <div class="avatar-title bg-light text-secondary rounded-circle fs-24">
                    <i class="ri-task-line"></i>
                </div>
            </div>
            <h5 class="mb-2"><?= $this->__('no_tasks') ?></h5>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('op_number') ?></th>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th class="text-end"><?= $this->__('progress') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('assigned_to') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <?php
                    $taskStatusBadge = match($task['status']) {
                        'pending' => 'bg-secondary-subtle text-secondary',
                        'in_progress' => 'bg-warning-subtle text-warning',
                        'completed' => 'bg-success-subtle text-success',
                        'skipped' => 'bg-info-subtle text-info',
                        default => 'bg-secondary-subtle text-secondary'
                    };
                    ?>
                    <tr>
                        <td><?= $task['operation_number'] ?></td>
                        <td><?= $this->e($task['name']) ?></td>
                        <td><?= $this->e($task['work_center'] ?? '-') ?></td>
                        <td class="text-end">
                            <?= number_format($task['completed_quantity'], 0) ?> / <?= number_format($task['planned_quantity'], 0) ?>
                        </td>
                        <td><span class="badge <?= $taskStatusBadge ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span></td>
                        <td><?= $this->e($task['assigned_name'] ?? '-') ?></td>
                        <td>
                            <a href="/production/tasks/<?= $task['id'] ?>" class="btn btn-sm btn-soft-secondary">
                                <i class="ri-eye-line align-bottom"></i> <?= $this->__('view') ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Materials -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-archive-line align-bottom me-1"></i> <?= $this->__('material_consumption') ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($materials)): ?>
        <div class="text-center py-5">
            <div class="avatar-lg mx-auto mb-4">
                <div class="avatar-title bg-light text-secondary rounded-circle fs-24">
                    <i class="ri-archive-line"></i>
                </div>
            </div>
            <h5 class="mb-2"><?= $this->__('no_materials') ?></h5>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('item') ?></th>
                        <th class="text-end"><?= $this->__('planned') ?></th>
                        <th class="text-end"><?= $this->__('actual') ?></th>
                        <th class="text-end"><?= $this->__('unit_cost') ?></th>
                        <th class="text-end"><?= $this->__('value') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalValue = 0; foreach ($materials as $mat): ?>
                    <?php $value = $mat['actual_quantity'] * $mat['unit_cost']; $totalValue += $value; ?>
                    <tr>
                        <td>
                            <a href="/warehouse/items/<?= $mat['item_id'] ?>" class="text-primary"><?= $this->e($mat['sku']) ?></a>
                            <br><small class="text-muted"><?= $this->e($mat['item_name']) ?></small>
                        </td>
                        <td class="text-end"><?= number_format($mat['planned_quantity'], 4) ?> <?= $this->__('unit_' . $mat['unit']) ?></td>
                        <td class="text-end"><?= number_format($mat['actual_quantity'], 4) ?> <?= $this->__('unit_' . $mat['unit']) ?></td>
                        <td class="text-end"><?= number_format($mat['unit_cost'], 4) ?></td>
                        <td class="text-end"><?= number_format($value, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-semibold"><?= $this->__('total_material_cost') ?>:</td>
                        <td class="text-end fw-semibold"><?= number_format($totalValue, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
