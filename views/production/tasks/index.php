<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('production_tasks') ?></h1>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <select name="status">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>><?= $this->__('pending') ?></option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>><?= $this->__('in_progress') ?></option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>><?= $this->__('completed') ?></option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="assigned_to">
                        <option value=""><?= $this->__('all_assignments') ?></option>
                        <option value="me" <?= $assignedTo === 'me' ? 'selected' : '' ?>><?= $this->__('assigned_to_me') ?></option>
                        <option value="unassigned" <?= $assignedTo === 'unassigned' ? 'selected' : '' ?>><?= $this->__('unassigned') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/production/tasks" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<!-- Tasks Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('order') ?></th>
                        <th><?= $this->__('operation_number') ?></th>
                        <th><?= $this->__('task') ?></th>
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
                        <td colspan="8" class="text-center text-muted"><?= $this->__('no_results') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
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
                    <tr>
                        <td>
                            <a href="/production/orders/<?= $task['order_id'] ?>"><?= $this->e($task['order_number']) ?></a>
                            <br><small class="text-muted"><?= $this->e($task['variant_sku']) ?></small>
                        </td>
                        <td><?= $task['operation_number'] ?></td>
                        <td><?= $this->e($task['name']) ?></td>
                        <td><?= $this->e($task['work_center'] ?? '-') ?></td>
                        <td class="text-right"><?= number_format($task['completed_quantity'], 0) ?> / <?= number_format($task['planned_quantity'], 0) ?></td>
                        <td><span class="badge badge-<?= $statusClass ?>"><?= $statusLabels[$task['status']] ?? $this->e($task['status']) ?></span></td>
                        <td><?= $this->e($task['assigned_name'] ?? '-') ?></td>
                        <td><a href="/production/tasks/<?= $task['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.filter-form { margin: 0; }
.filter-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.filter-group { flex: 1; min-width: 150px; }
.filter-group select { width: 100%; }
.text-right { text-align: right; }
</style>

<?php $this->endSection(); ?>
