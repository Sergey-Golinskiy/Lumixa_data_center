<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('print_job_details') ?></h4>
            <div class="page-title-right d-flex gap-2">
                <a href="/production/print-queue" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('back_to_queue') ?>
                </a>
                <?php if ($job['order_id']): ?>
                <a href="/production/orders/<?= $job['order_id'] ?>" class="btn btn-soft-primary">
                    <i class="ri-file-list-3-line align-bottom me-1"></i> <?= $this->__('view_order') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">
            <i class="ri-printer-line align-bottom me-1"></i> <?= h($job['job_number']) ?>
        </h5>
        <?php
        $statusBadge = match($job['status']) {
            'queued' => 'bg-secondary-subtle text-secondary',
            'printing' => 'bg-warning-subtle text-warning',
            'completed' => 'bg-success-subtle text-success',
            'failed' => 'bg-danger-subtle text-danger',
            'cancelled' => 'bg-dark-subtle text-dark',
            default => 'bg-secondary-subtle text-secondary'
        };
        $statusLabels = [
            'queued' => $this->__('queued'),
            'printing' => $this->__('printing'),
            'completed' => $this->__('completed'),
            'failed' => $this->__('failed'),
            'cancelled' => $this->__('cancelled')
        ];
        ?>
        <span class="badge <?= $statusBadge ?> fs-12"><?= $statusLabels[$job['status']] ?? $this->e($job['status']) ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="text-muted text-uppercase fw-semibold mb-3"><?= $this->__('job_details') ?></h6>
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 140px;"><?= $this->__('variant') ?>:</th>
                                <td>
                                    <?php if ($job['variant_id']): ?>
                                    <a href="/catalog/variants/<?= $job['variant_id'] ?>" class="text-primary"><?= h($job['variant_sku']) ?></a>
                                    - <?= h($job['variant_name']) ?>
                                    <?php else: ?>
                                    <span class="text-muted"><?= $this->__('not_specified') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('order') ?>:</th>
                                <td>
                                    <?php if ($job['order_id']): ?>
                                    <a href="/production/orders/<?= $job['order_id'] ?>" class="text-primary"><?= h($job['order_number']) ?></a>
                                    <?php else: ?>
                                    <span class="text-muted"><?= $this->__('standalone_job') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('quantity') ?>:</th>
                                <td class="fw-medium"><?= (int)$job['quantity'] ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('material') ?>:</th>
                                <td><?= $job['material'] ? h($job['material']) : '<span class="text-muted">-</span>' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('file_path') ?>:</th>
                                <td><?= $job['file_path'] ? h($job['file_path']) : '<span class="text-muted">-</span>' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('priority') ?>:</th>
                                <td>
                                    <?php if ($job['priority'] > 0): ?>
                                    <span class="badge bg-warning-subtle text-warning"><?= $job['priority'] ?></span>
                                    <?php else: ?>
                                    <?= $this->__('normal') ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <h6 class="text-muted text-uppercase fw-semibold mb-3"><?= $this->__('printing_info') ?></h6>
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 140px;"><?= $this->__('printer') ?>:</th>
                                <td><?= $job['printer'] ? h($job['printer']) : '<span class="text-muted">' . $this->__('not_assigned') . '</span>' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('estimated_time') ?>:</th>
                                <td><?= $job['estimated_time_minutes'] ? $job['estimated_time_minutes'] . ' ' . $this->__('minutes_short') : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('actual_time') ?>:</th>
                                <td><?= $job['actual_time_minutes'] ? $job['actual_time_minutes'] . ' ' . $this->__('minutes_short') : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('started') ?>:</th>
                                <td><?= $job['started_at'] ? date('d.m.Y H:i', strtotime($job['started_at'])) : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('completed') ?>:</th>
                                <td><?= $job['completed_at'] ? date('d.m.Y H:i', strtotime($job['completed_at'])) : '-' ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('created_by') ?>:</th>
                                <td><?= h($job['created_by_name'] ?? $this->__('unknown')) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('created') ?>:</th>
                                <td><?= date('d.m.Y H:i', strtotime($job['created_at'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($job['notes']): ?>
        <div class="mt-4">
            <h6 class="text-muted text-uppercase fw-semibold mb-3"><?= $this->__('notes') ?></h6>
            <div class="bg-light p-3 rounded" style="white-space: pre-wrap;"><?= h($job['notes']) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($this->can('production.print-queue.edit')): ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-settings-3-line align-bottom me-1"></i> <?= $this->__('actions') ?></h5>
    </div>
    <div class="card-body">
        <?php if ($job['status'] === 'queued'): ?>
        <div class="row g-3">
            <div class="col-lg-6">
                <form method="post" action="/production/print-queue/<?= $job['id'] ?>/start">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <div class="d-flex gap-2 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label"><?= $this->__('select_printer') ?></label>
                            <select name="printer" class="form-select">
                                <option value=""><?= $this->__('select_printer') ?></option>
                                <?php foreach ($printers as $p): ?>
                                <?php $printerCode = $p['code'] ?? $p['name']; ?>
                                <option value="<?= h($printerCode) ?>" <?= $job['printer'] === $printerCode ? 'selected' : '' ?>><?= h($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-play-line align-bottom me-1"></i> <?= $this->__('start_printing') ?>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <form method="post" action="/production/print-queue/<?= $job['id'] ?>/cancel">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <div class="d-flex gap-2 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label"><?= $this->__('cancellation_reason') ?></label>
                            <input type="text" name="reason" placeholder="<?= $this->__('cancellation_reason') ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('<?= $this->__('cancel_print_job_confirm') ?>')">
                            <i class="ri-close-line align-bottom me-1"></i> <?= $this->__('cancel_job') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($job['status'] === 'printing'): ?>
        <div class="row g-3">
            <div class="col-lg-6">
                <form method="post" action="/production/print-queue/<?= $job['id'] ?>/complete">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <div class="d-flex gap-2 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label"><?= $this->__('actual_time_placeholder') ?></label>
                            <input type="number" name="actual_time" placeholder="<?= $this->__('actual_time_placeholder') ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-check-line align-bottom me-1"></i> <?= $this->__('complete') ?>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <form method="post" action="/production/print-queue/<?= $job['id'] ?>/cancel">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <div class="d-flex gap-2 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label"><?= $this->__('reason') ?></label>
                            <input type="text" name="reason" placeholder="<?= $this->__('reason') ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('<?= $this->__('cancel_print_job_confirm') ?>')">
                            <i class="ri-close-line align-bottom me-1"></i> <?= $this->__('cancel') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <div class="text-center py-4">
            <div class="avatar-md mx-auto mb-3">
                <div class="avatar-title bg-light text-secondary rounded-circle fs-20">
                    <i class="ri-information-line"></i>
                </div>
            </div>
            <p class="text-muted mb-0"><?= $this->__('no_actions_available') ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php $this->endSection(); ?>
