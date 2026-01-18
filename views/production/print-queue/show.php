<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/print-queue" class="btn btn-secondary">&laquo; Back to Queue</a>
    <?php if ($job['order_id']): ?>
    <a href="/production/orders/<?= $job['order_id'] ?>" class="btn btn-outline">View Order</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;"><?= h($job['job_number']) ?></h3>
        <?php
        $statusClass = [
            'queued' => 'secondary',
            'printing' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'muted'
        ][$job['status']] ?? 'secondary';
        ?>
        <span class="badge badge-<?= $statusClass ?>" style="font-size: 1em;"><?= ucfirst($job['status']) ?></span>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <h4>Job Details</h4>
                <table class="table-details">
                    <tr>
                        <th>Variant:</th>
                        <td>
                            <?php if ($job['variant_id']): ?>
                            <a href="/catalog/variants/<?= $job['variant_id'] ?>"><?= h($job['variant_sku']) ?></a>
                            - <?= h($job['variant_name']) ?>
                            <?php else: ?>
                            <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Order:</th>
                        <td>
                            <?php if ($job['order_id']): ?>
                            <a href="/production/orders/<?= $job['order_id'] ?>"><?= h($job['order_number']) ?></a>
                            <?php else: ?>
                            <span class="text-muted">Standalone job</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Quantity:</th>
                        <td><?= (int)$job['quantity'] ?></td>
                    </tr>
                    <tr>
                        <th>Material:</th>
                        <td><?= $job['material'] ? h($job['material']) : '-' ?></td>
                    </tr>
                    <tr>
                        <th>File:</th>
                        <td><?= $job['file_path'] ? h($job['file_path']) : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Priority:</th>
                        <td>
                            <?php if ($job['priority'] > 0): ?>
                            <span class="badge badge-warning"><?= $job['priority'] ?></span>
                            <?php else: ?>
                            Normal
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div>
                <h4>Printing Info</h4>
                <table class="table-details">
                    <tr>
                        <th>Printer:</th>
                        <td><?= $job['printer'] ? h($job['printer']) : '<span class="text-muted">Not assigned</span>' ?></td>
                    </tr>
                    <tr>
                        <th>Est. Time:</th>
                        <td><?= $job['estimated_time_minutes'] ? $job['estimated_time_minutes'] . ' min' : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Actual Time:</th>
                        <td><?= $job['actual_time_minutes'] ? $job['actual_time_minutes'] . ' min' : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Started:</th>
                        <td><?= $job['started_at'] ? date('d.m.Y H:i', strtotime($job['started_at'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Completed:</th>
                        <td><?= $job['completed_at'] ? date('d.m.Y H:i', strtotime($job['completed_at'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Created By:</th>
                        <td><?= h($job['created_by_name'] ?? 'Unknown') ?></td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td><?= date('d.m.Y H:i', strtotime($job['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if ($job['notes']): ?>
        <div style="margin-top: 20px;">
            <h4>Notes</h4>
            <div style="padding: 10px; background: #f5f5f5; border-radius: 4px; white-space: pre-wrap;"><?= h($job['notes']) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($this->can('production.print-queue.edit')): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h4 style="margin: 0;">Actions</h4>
    </div>
    <div class="card-body">
        <?php if ($job['status'] === 'queued'): ?>
        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/start" style="display: inline-block; margin-right: 10px;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div style="display: flex; gap: 10px; align-items: center;">
                <select name="printer" class="form-control" style="width: 200px;">
                    <option value="">Select Printer</option>
                    <?php foreach ($printers as $p): ?>
                    <option value="<?= h($p['code']) ?>" <?= $job['printer'] === $p['code'] ? 'selected' : '' ?>><?= h($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-success">Start Printing</button>
            </div>
        </form>

        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/cancel" style="display: inline-block;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="text" name="reason" placeholder="Cancellation reason" class="form-control" style="width: 200px; display: inline-block;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this print job?')">Cancel Job</button>
        </form>

        <?php elseif ($job['status'] === 'printing'): ?>
        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/complete" style="display: inline-block; margin-right: 10px;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="number" name="actual_time" placeholder="Actual time (min)" class="form-control" style="width: 150px;">
                <button type="submit" class="btn btn-success">Complete</button>
            </div>
        </form>

        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/cancel" style="display: inline-block;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="text" name="reason" placeholder="Reason" class="form-control" style="width: 150px; display: inline-block;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this print job?')">Cancel</button>
        </form>

        <?php else: ?>
        <p class="text-muted">No actions available for this job.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<style>
.table-details { width: 100%; }
.table-details th { width: 120px; padding: 8px; text-align: left; vertical-align: top; color: #666; }
.table-details td { padding: 8px; }
</style>

<?php $this->endSection(); ?>
