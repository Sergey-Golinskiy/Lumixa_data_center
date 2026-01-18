<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/tasks" class="btn btn-secondary">&laquo; Back to Tasks</a>
    <a href="/production/orders/<?= $task['order_id'] ?>" class="btn btn-outline">View Order</a>
</div>

<div class="detail-grid">
    <div class="card">
        <div class="card-header">Task Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Order</span>
                <span class="detail-value"><a href="/production/orders/<?= $task['order_id'] ?>"><?= $this->e($task['order_number']) ?></a></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Variant</span>
                <span class="detail-value"><?= $this->e($task['variant_sku']) ?> - <?= $this->e($task['variant_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Operation</span>
                <span class="detail-value">#<?= $task['operation_number'] ?> - <strong><?= $this->e($task['name']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Work Center</span>
                <span class="detail-value"><?= $this->e($task['work_center'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($task['status']) {
                        'pending' => 'secondary',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Quantity</span>
                <span class="detail-value"><?= number_format($task['completed_quantity'], 0) ?> / <?= number_format($task['planned_quantity'], 0) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Assigned To</span>
                <span class="detail-value"><?= $this->e($task['assigned_name'] ?? 'Unassigned') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time Estimate</span>
                <span class="detail-value">Setup: <?= $task['setup_time_minutes'] ?> min, Run: <?= $task['run_time_minutes'] ?> min</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <?php if ($task['status'] === 'pending'): ?>
            <form method="POST" action="/production/tasks/<?= $task['id'] ?>/start">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label>Assign To</label>
                    <select name="assigned_to">
                        <option value="">Self</option>
                        <?php foreach ($workers as $worker): ?>
                        <option value="<?= $worker['id'] ?>"><?= $this->e($worker['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Start Task</button>
            </form>
            <?php elseif ($task['status'] === 'in_progress'): ?>
            <form method="POST" action="/production/tasks/<?= $task['id'] ?>/complete">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <label>Completed Quantity</label>
                    <input type="number" name="completed_quantity" value="<?= $task['planned_quantity'] ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="2"><?= $this->e($task['notes'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-success">Complete Task</button>
            </form>
            <?php else: ?>
            <p class="text-muted">Task completed at <?= $this->datetime($task['actual_end']) ?></p>
            <?php endif; ?>

            <hr>
            <div class="detail-row">
                <span class="detail-label">Started</span>
                <span class="detail-value"><?= $task['actual_start'] ? $this->datetime($task['actual_start']) : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Ended</span>
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
