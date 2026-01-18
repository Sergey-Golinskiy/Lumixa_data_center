<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production" class="btn btn-secondary">&laquo; Production</a>
    <?php if ($this->can('production.print-queue.create')): ?>
    <a href="/production/print-queue/create" class="btn btn-primary">+ Add Print Job</a>
    <?php endif; ?>
</div>

<div class="filters" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
    <form method="get" action="/production/print-queue" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <select name="status" class="form-control" style="width: 150px;">
            <option value="">All Statuses</option>
            <option value="queued" <?= $status === 'queued' ? 'selected' : '' ?>>Queued</option>
            <option value="printing" <?= $status === 'printing' ? 'selected' : '' ?>>Printing</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <select name="printer" class="form-control" style="width: 150px;">
            <option value="">All Printers</option>
            <?php foreach ($printers as $p): ?>
            <option value="<?= h($p['printer']) ?>" <?= $printer === $p['printer'] ? 'selected' : '' ?>><?= h($p['printer']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if ($status || $printer): ?>
        <a href="/production/print-queue" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($jobs)): ?>
<div class="empty-state" style="text-align: center; padding: 40px; color: #666;">
    <p>No print jobs found.</p>
</div>
<?php else: ?>

<table class="table">
    <thead>
        <tr>
            <th>Job #</th>
            <th>Variant</th>
            <th>Order</th>
            <th>Printer</th>
            <th>Qty</th>
            <th>Est. Time</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($jobs as $job): ?>
        <tr>
            <td>
                <a href="/production/print-queue/<?= $job['id'] ?>"><?= h($job['job_number']) ?></a>
            </td>
            <td>
                <?php if ($job['variant_id']): ?>
                <a href="/catalog/variants/<?= $job['variant_id'] ?>"><?= h($job['variant_sku']) ?></a>
                <small style="display: block; color: #666;"><?= h($job['variant_name']) ?></small>
                <?php else: ?>
                <span class="text-muted">-</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($job['order_id']): ?>
                <a href="/production/orders/<?= $job['order_id'] ?>"><?= h($job['order_number']) ?></a>
                <?php else: ?>
                <span class="text-muted">-</span>
                <?php endif; ?>
            </td>
            <td><?= $job['printer'] ? h($job['printer']) : '-' ?></td>
            <td><?= (int)$job['quantity'] ?></td>
            <td><?= $job['estimated_time_minutes'] ? $job['estimated_time_minutes'] . ' min' : '-' ?></td>
            <td>
                <?php if ($job['priority'] > 0): ?>
                <span class="badge badge-warning"><?= $job['priority'] ?></span>
                <?php else: ?>
                <span class="text-muted">Normal</span>
                <?php endif; ?>
            </td>
            <td>
                <?php
                $statusClass = [
                    'queued' => 'secondary',
                    'printing' => 'warning',
                    'completed' => 'success',
                    'failed' => 'danger',
                    'cancelled' => 'muted'
                ][$job['status']] ?? 'secondary';
                ?>
                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($job['status']) ?></span>
            </td>
            <td><?= date('d.m.Y H:i', strtotime($job['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div class="pagination" style="margin-top: 20px; display: flex; gap: 5px; justify-content: center;">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&printer=<?= urlencode($printer) ?>" class="btn btn-outline">&laquo;</a>
    <?php endif; ?>

    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
    <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&printer=<?= urlencode($printer) ?>"
       class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&printer=<?= urlencode($printer) ?>" class="btn btn-outline">&raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<?php $this->endSection(); ?>
