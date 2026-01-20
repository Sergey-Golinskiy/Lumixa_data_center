<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/tasks" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('tasks')]) ?></a>
    <a href="/production/orders/<?= $task['order_id'] ?>" class="btn btn-outline"><?= $this->__('view_order') ?></a>
</div>

<div class="detail-grid">
    <div class="card">
        <div class="card-header"><?= $this->__('task_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('order') ?></span>
                <span class="detail-value"><a href="/production/orders/<?= $task['order_id'] ?>"><?= $this->e($task['order_number']) ?></a></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('variant') ?></span>
                <span class="detail-value"><?= $this->e($task['variant_sku']) ?> - <?= $this->e($task['variant_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('operation') ?></span>
                <span class="detail-value">#<?= $task['operation_number'] ?> - <strong><?= $this->e($task['name']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('work_center') ?></span>
                <span class="detail-value"><?= $this->e($task['work_center'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($task['status']) {
                        'pending' => 'secondary',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'secondary'
                    };
                    $statusLabels = [
                        'pending' => $this->__('pending'),
                        'in_progress' => $this->__('in_progress'),
                        'completed' => $this->__('completed')
                    ];
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= $statusLabels[$task['status']] ?? $this->e($task['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('quantity') ?></span>
                <span class="detail-value"><?= number_format($task['completed_quantity'], 0) ?> / <?= number_format($task['planned_quantity'], 0) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('assigned_to') ?></span>
                <span class="detail-value"><?= $this->e($task['assigned_name'] ?? $this->__('unassigned')) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('time_estimate') ?></span>
                <span class="detail-value">
                    <?= $this->__('setup_time') ?>: <?= $task['setup_time_minutes'] ?> <?= $this->__('minutes_short') ?>,
                    <?= $this->__('run_time') ?>: <?= $task['run_time_minutes'] ?> <?= $this->__('minutes_short') ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><?= $this->__('actions') ?></div>
        <div class="card-body">
            <?php if ($task['status'] === 'pending'): ?>
            <form method="POST" action="/production/tasks/<?= $task['id'] ?>/start">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label><?= $this->__('assign_to') ?></label>
                    <select name="assigned_to">
                        <option value=""><?= $this->__('self') ?></option>
                        <?php foreach ($workers as $worker): ?>
                        <option value="<?= $worker['id'] ?>"><?= $this->e($worker['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success"><?= $this->__('start_task') ?></button>
            </form>
            <?php elseif ($task['status'] === 'in_progress'): ?>
            <form method="POST" action="/production/tasks/<?= $task['id'] ?>/complete">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label><?= $this->__('completed_quantity') ?></label>
                    <input type="number" name="completed_quantity" value="<?= $task['planned_quantity'] ?>" min="0">
                </div>
                <div class="form-group">
                    <label><?= $this->__('notes') ?></label>
                    <textarea name="notes" rows="2"><?= $this->e($task['notes'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-success"><?= $this->__('complete_task') ?></button>
            </form>
            <?php else: ?>
            <p class="text-muted"><?= $this->__('task_completed_at', ['time' => $this->datetime($task['actual_end'])]) ?></p>
            <?php endif; ?>

            <hr>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('started') ?></span>
                <span class="detail-value"><?= $task['actual_start'] ? $this->datetime($task['actual_start']) : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('ended') ?></span>
                <span class="detail-value"><?= $task['actual_end'] ? $this->datetime($task['actual_end']) : '-' ?></span>
            </div>
        </div>
    </div>
</div>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 120px; color: var(--text-muted); font-size: 13px; }
.detail-value { flex: 1; }
</style>

<?php $this->endSection(); ?>
