<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/print-queue" class="btn btn-secondary">&laquo; <?= $this->__('back_to_queue') ?></a>
    <?php if ($job['order_id']): ?>
    <a href="/production/orders/<?= $job['order_id'] ?>" class="btn btn-outline"><?= $this->__('view_order') ?></a>
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
        $statusLabels = [
            'queued' => $this->__('queued'),
            'printing' => $this->__('printing'),
            'completed' => $this->__('completed'),
            'failed' => $this->__('failed'),
            'cancelled' => $this->__('cancelled')
        ];
        ?>
        <span class="badge badge-<?= $statusClass ?>" style="font-size: 1em;"><?= $statusLabels[$job['status']] ?? $this->e($job['status']) ?></span>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <h4><?= $this->__('job_details') ?></h4>
                <table class="table-details">
                    <tr>
                        <th><?= $this->__('variant') ?>:</th>
                        <td>
                            <?php if ($job['variant_id']): ?>
                            <a href="/catalog/variants/<?= $job['variant_id'] ?>"><?= h($job['variant_sku']) ?></a>
                            - <?= h($job['variant_name']) ?>
                            <?php else: ?>
                            <span class="text-muted"><?= $this->__('not_specified') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $this->__('order') ?>:</th>
                        <td>
                            <?php if ($job['order_id']): ?>
                            <a href="/production/orders/<?= $job['order_id'] ?>"><?= h($job['order_number']) ?></a>
                            <?php else: ?>
                            <span class="text-muted"><?= $this->__('standalone_job') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $this->__('quantity') ?>:</th>
                        <td><?= (int)$job['quantity'] ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('material') ?>:</th>
                        <td><?= $job['material'] ? h($job['material']) : '-' ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('file_path') ?>:</th>
                        <td><?= $job['file_path'] ? h($job['file_path']) : '-' ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('priority') ?>:</th>
                        <td>
                            <?php if ($job['priority'] > 0): ?>
                            <span class="badge badge-warning"><?= $job['priority'] ?></span>
                            <?php else: ?>
                            <?= $this->__('normal') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div>
                <h4><?= $this->__('printing_info') ?></h4>
                <table class="table-details">
                    <tr>
                        <th><?= $this->__('printer') ?>:</th>
                        <td><?= $job['printer'] ? h($job['printer']) : '<span class="text-muted">' . $this->__('not_assigned') . '</span>' ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('estimated_time') ?>:</th>
                        <td><?= $job['estimated_time_minutes'] ? $job['estimated_time_minutes'] . ' ' . $this->__('minutes_short') : '-' ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('actual_time') ?>:</th>
                        <td><?= $job['actual_time_minutes'] ? $job['actual_time_minutes'] . ' ' . $this->__('minutes_short') : '-' ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('started') ?>:</th>
                        <td><?= $job['started_at'] ? date('d.m.Y H:i', strtotime($job['started_at'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('completed') ?>:</th>
                        <td><?= $job['completed_at'] ? date('d.m.Y H:i', strtotime($job['completed_at'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('created_by') ?>:</th>
                        <td><?= h($job['created_by_name'] ?? $this->__('unknown')) ?></td>
                    </tr>
                    <tr>
                        <th><?= $this->__('created') ?>:</th>
                        <td><?= date('d.m.Y H:i', strtotime($job['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if ($job['notes']): ?>
        <div style="margin-top: 20px;">
            <h4><?= $this->__('notes') ?></h4>
            <div style="padding: 10px; background: #f5f5f5; border-radius: 4px; white-space: pre-wrap;"><?= h($job['notes']) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($this->can('production.print-queue.edit')): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h4 style="margin: 0;"><?= $this->__('actions') ?></h4>
    </div>
    <div class="card-body">
        <?php if ($job['status'] === 'queued'): ?>
        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/start" style="display: inline-block; margin-right: 10px;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div style="display: flex; gap: 10px; align-items: center;">
                <select name="printer" class="form-control" style="width: 200px;">
                    <option value=""><?= $this->__('select_printer') ?></option>
                    <?php foreach ($printers as $p): ?>
                    <?php $printerCode = $p['code'] ?? $p['name']; ?>
                    <option value="<?= h($printerCode) ?>" <?= $job['printer'] === $printerCode ? 'selected' : '' ?>><?= h($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-success"><?= $this->__('start_printing') ?></button>
            </div>
        </form>

        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/cancel" style="display: inline-block;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="text" name="reason" placeholder="<?= $this->__('cancellation_reason') ?>" class="form-control" style="width: 200px; display: inline-block;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('<?= $this->__('cancel_print_job_confirm') ?>')"><?= $this->__('cancel_job') ?></button>
        </form>

        <?php elseif ($job['status'] === 'printing'): ?>
        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/complete" style="display: inline-block; margin-right: 10px;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="number" name="actual_time" placeholder="<?= $this->__('actual_time_placeholder') ?>" class="form-control" style="width: 150px;">
                <button type="submit" class="btn btn-success"><?= $this->__('complete') ?></button>
            </div>
        </form>

        <form method="post" action="/production/print-queue/<?= $job['id'] ?>/cancel" style="display: inline-block;">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="text" name="reason" placeholder="<?= $this->__('reason') ?>" class="form-control" style="width: 150px; display: inline-block;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('<?= $this->__('cancel_print_job_confirm') ?>')"><?= $this->__('cancel') ?></button>
        </form>

        <?php else: ?>
        <p class="text-muted"><?= $this->__('no_actions_available') ?></p>
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
