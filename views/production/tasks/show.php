<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('task_details') ?></h4>
            <div class="page-title-right d-flex gap-2">
                <a href="/production/tasks" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('back_to', ['name' => $this->__('tasks')]) ?>
                </a>
                <a href="/production/orders/<?= $task['order_id'] ?>" class="btn btn-soft-primary">
                    <i class="ri-file-list-3-line align-bottom me-1"></i> <?= $this->__('view_order') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Task Information -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-task-line align-bottom me-1"></i> <?= $this->__('task_information') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 140px;"><?= $this->__('order') ?></th>
                                <td>
                                    <a href="/production/orders/<?= $task['order_id'] ?>" class="text-primary"><?= $this->e($task['order_number']) ?></a>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('variant') ?></th>
                                <td class="text-muted"><?= $this->e($task['variant_sku']) ?> - <?= $this->e($task['variant_name']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('operation') ?></th>
                                <td>
                                    <span class="badge bg-light text-dark me-1">#<?= $task['operation_number'] ?></span>
                                    <span class="fw-medium"><?= $this->e($task['name']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('work_center') ?></th>
                                <td class="text-muted"><?= $this->e($task['work_center'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('status') ?></th>
                                <td>
                                    <?php
                                    $statusBadge = match($task['status']) {
                                        'pending' => 'bg-secondary-subtle text-secondary',
                                        'in_progress' => 'bg-warning-subtle text-warning',
                                        'completed' => 'bg-success-subtle text-success',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                    $statusLabels = [
                                        'pending' => $this->__('pending'),
                                        'in_progress' => $this->__('in_progress'),
                                        'completed' => $this->__('completed')
                                    ];
                                    ?>
                                    <span class="badge <?= $statusBadge ?>"><?= $statusLabels[$task['status']] ?? $this->e($task['status']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('quantity') ?></th>
                                <td>
                                    <span class="fw-semibold"><?= number_format($task['completed_quantity'], 0) ?></span> / <?= number_format($task['planned_quantity'], 0) ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('assigned_to') ?></th>
                                <td class="text-muted"><?= $this->e($task['assigned_name'] ?? $this->__('unassigned')) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('time_estimate') ?></th>
                                <td class="text-muted">
                                    <?= $this->__('setup_time') ?>: <?= $task['setup_time_minutes'] ?> <?= $this->__('minutes_short') ?>,
                                    <?= $this->__('run_time') ?>: <?= $task['run_time_minutes'] ?> <?= $this->__('minutes_short') ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-settings-3-line align-bottom me-1"></i> <?= $this->__('actions') ?></h5>
            </div>
            <div class="card-body">
                <?php if ($task['status'] === 'pending'): ?>
                <form method="POST" action="/production/tasks/<?= $task['id'] ?>/start">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('assign_to') ?></label>
                        <select name="assigned_to" class="form-select">
                            <option value=""><?= $this->__('self') ?></option>
                            <?php foreach ($workers as $worker): ?>
                            <option value="<?= $worker['id'] ?>"><?= $this->e($worker['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-play-line align-bottom me-1"></i> <?= $this->__('start_task') ?>
                    </button>
                </form>
                <?php elseif ($task['status'] === 'in_progress'): ?>
                <form method="POST" action="/production/tasks/<?= $task['id'] ?>/complete">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('completed_quantity') ?></label>
                        <input type="number" name="completed_quantity" class="form-control" value="<?= $task['planned_quantity'] ?>" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('notes') ?></label>
                        <textarea name="notes" class="form-control" rows="2"><?= $this->e($task['notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-check-line align-bottom me-1"></i> <?= $this->__('complete_task') ?>
                    </button>
                </form>
                <?php else: ?>
                <div class="text-center py-4">
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title bg-success-subtle text-success rounded-circle fs-20">
                            <i class="ri-check-line"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0"><?= $this->__('task_completed_at', ['time' => $this->datetime($task['actual_end'])]) ?></p>
                </div>
                <?php endif; ?>

                <hr class="my-4">

                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 100px;"><?= $this->__('started') ?></th>
                                <td class="text-muted"><?= $task['actual_start'] ? $this->datetime($task['actual_start']) : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('ended') ?></th>
                                <td class="text-muted"><?= $task['actual_end'] ? $this->datetime($task['actual_end']) : '-' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
