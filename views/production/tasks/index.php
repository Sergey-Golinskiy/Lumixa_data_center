<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('production_tasks') ?></h4>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line align-bottom me-1"></i> <?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/production/tasks">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>><?= $this->__('pending') ?></option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>><?= $this->__('in_progress') ?></option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>><?= $this->__('completed') ?></option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('assignment') ?></label>
                    <select name="assigned_to" class="form-select">
                        <option value=""><?= $this->__('all_assignments') ?></option>
                        <option value="me" <?= $assignedTo === 'me' ? 'selected' : '' ?>><?= $this->__('assigned_to_me') ?></option>
                        <option value="unassigned" <?= $assignedTo === 'unassigned' ? 'selected' : '' ?>><?= $this->__('unassigned') ?></option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-soft-primary">
                        <i class="ri-search-line align-bottom me-1"></i> <?= $this->__('filter') ?>
                    </button>
                    <?php if ($status || $assignedTo): ?>
                    <a href="/production/tasks" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line align-bottom me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tasks Table -->
<div class="card">
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
            <h5 class="mb-2"><?= $this->__('no_results') ?></h5>
            <p class="text-muted mb-0"><?= $this->__('try_adjusting_filters') ?></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('order') ?></th>
                        <th><?= $this->__('operation_number') ?></th>
                        <th><?= $this->__('task') ?></th>
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
                    <tr>
                        <td>
                            <a href="/production/orders/<?= $task['order_id'] ?>" class="text-primary fw-medium"><?= $this->e($task['order_number']) ?></a>
                            <br><small class="text-muted"><?= $this->e($task['variant_sku']) ?></small>
                        </td>
                        <td><?= $task['operation_number'] ?></td>
                        <td><?= $this->e($task['name']) ?></td>
                        <td><?= $this->e($task['work_center'] ?? '-') ?></td>
                        <td class="text-end">
                            <span class="fw-medium"><?= number_format($task['completed_quantity'], 0) ?></span> / <?= number_format($task['planned_quantity'], 0) ?>
                        </td>
                        <td><span class="badge <?= $statusBadge ?>"><?= $statusLabels[$task['status']] ?? $this->e($task['status']) ?></span></td>
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

<?php $this->endSection(); ?>
