<?php
/** @var array $batch */
/** @var array $movements */
/** @var array $allocations */

$qtyUsed = $batch['qty_received'] - $batch['qty_available'];
$usagePercent = $batch['qty_received'] > 0 ? ($qtyUsed / $batch['qty_received']) * 100 : 0;

$statusColors = [
    'active' => 'success',
    'depleted' => 'secondary',
    'expired' => 'danger',
    'quarantine' => 'warning'
];
$statusColor = $statusColors[$batch['status']] ?? 'secondary';
?>

<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex gap-2">
    <a href="/warehouse/batches?item_id=<?= $batch['item_id'] ?>" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_batches') ?>
    </a>
    <a href="/warehouse/items/<?= $batch['item_id'] ?>" class="btn btn-soft-primary">
        <i class="ri-eye-line me-1"></i> <?= $this->__('view_item') ?>
    </a>
</div>

<!-- Batch Info Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> rounded-circle fs-3">
                            <i class="ri-checkbox-circle-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('status') ?></p>
                        <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?>"><?= $this->e(ucfirst($batch['status'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                            <i class="ri-box-3-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('received_quantity') ?></p>
                        <h4 class="mb-0"><?= number_format($batch['qty_received'], 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                            <i class="ri-stack-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('available_quantity') ?></p>
                        <h4 class="mb-0 text-success"><?= number_format($batch['qty_available'], 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('unit_cost') ?></p>
                        <h4 class="mb-0">$<?= number_format($batch['unit_cost'], 4) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Usage Progress -->
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title mb-3"><?= $this->__('batch_usage') ?></h5>
        <div class="progress" style="height: 30px;">
            <div class="progress-bar bg-<?= $usagePercent >= 90 ? 'danger' : ($usagePercent >= 70 ? 'warning' : 'success') ?>"
                 role="progressbar"
                 style="width: <?= $usagePercent ?>%">
                <?= round($usagePercent, 1) ?>% <?= $this->__('used') ?>
            </div>
        </div>
        <div class="d-flex justify-content-between mt-2 small text-muted">
            <span><?= $this->__('used') ?>: <?= number_format($qtyUsed, 2) ?></span>
            <span><?= $this->__('received') ?>: <?= number_format($batch['qty_received'], 2) ?></span>
        </div>
    </div>
</div>

<!-- Batch Details -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-file-info-line me-2"></i><?= $this->__('batch_details') ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 40%;"><?= $this->__('batch_code') ?>:</th>
                                <td class="fw-semibold text-primary"><?= $this->e($batch['batch_code']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('received_date') ?>:</th>
                                <td><?= date('Y-m-d', strtotime($batch['received_date'])) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('supplier') ?>:</th>
                                <td>
                                    <?php if ($batch['supplier_id']): ?>
                                        <a href="/warehouse/partners/<?= $batch['supplier_id'] ?>" class="text-primary">
                                            <?= $this->e($batch['supplier_name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('source_type') ?>:</th>
                                <td><?= $this->e(ucfirst($batch['source_type'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 40%;"><?= $this->__('total_value') ?>:</th>
                                <td class="fw-semibold">$<?= number_format($batch['qty_available'] * $batch['unit_cost'], 2) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('expiry_date') ?>:</th>
                                <td>
                                    <?php if ($batch['expiry_date']): ?>
                                        <?php
                                        $expiryTs = strtotime($batch['expiry_date']);
                                        $now = time();
                                        $isExpired = $expiryTs < $now;
                                        $daysUntilExpiry = ceil(($expiryTs - $now) / 86400);
                                        ?>
                                        <?= date('Y-m-d', $expiryTs) ?>
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-danger"><?= $this->__('expired') ?></span>
                                        <?php elseif ($daysUntilExpiry <= 30): ?>
                                            <span class="badge bg-warning-subtle text-warning"><?= $this->__('expires_in') ?> <?= $daysUntilExpiry ?> <?= $this->__('days') ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('costing_method') ?>:</th>
                                <td><span class="badge bg-info-subtle text-info"><?= $this->e($batch['default_costing_method']) ?></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('created_at') ?>:</th>
                                <td><?= date('Y-m-d H:i', strtotime($batch['created_at'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($batch['notes']): ?>
        <div class="mt-3 pt-3 border-top">
            <strong><?= $this->__('notes') ?>:</strong>
            <p class="mb-0 text-muted"><?= nl2br($this->e($batch['notes'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Status Change -->
        <?php if ($batch['status'] === 'active'): ?>
        <div class="mt-3 pt-3 border-top d-flex gap-2">
            <form method="POST" action="/warehouse/batches/<?= $batch['id'] ?>/status" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" name="status" value="quarantine" class="btn btn-soft-warning"
                        onclick="return confirm('<?= $this->__('confirm_quarantine_batch') ?>')">
                    <i class="ri-alert-line me-1"></i> <?= $this->__('set_to_quarantine') ?>
                </button>
            </form>
            <form method="POST" action="/warehouse/batches/<?= $batch['id'] ?>/status" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" name="status" value="expired" class="btn btn-soft-danger"
                        onclick="return confirm('<?= $this->__('confirm_mark_expired') ?>')">
                    <i class="ri-time-line me-1"></i> <?= $this->__('mark_as_expired') ?>
                </button>
            </form>
        </div>
        <?php elseif ($batch['status'] === 'quarantine'): ?>
        <div class="mt-3 pt-3 border-top">
            <form method="POST" action="/warehouse/batches/<?= $batch['id'] ?>/status" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" name="status" value="active" class="btn btn-success"
                        onclick="return confirm('<?= $this->__('confirm_return_to_active') ?>')">
                    <i class="ri-check-line me-1"></i> <?= $this->__('return_to_active') ?>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="movements-tab" data-bs-toggle="tab" data-bs-target="#movements-content" type="button" role="tab">
            <i class="ri-history-line me-1"></i> <?= $this->__('movements') ?> (<?= count($movements) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="allocations-tab" data-bs-toggle="tab" data-bs-target="#allocations-content" type="button" role="tab">
            <i class="ri-flow-chart me-1"></i> <?= $this->__('issue_allocations') ?> (<?= count($allocations) ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Movements Tab -->
    <div class="tab-pane fade show active" id="movements-content" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($movements)): ?>
                <div class="text-center py-5">
                    <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
                    <span class="text-muted"><?= $this->__('no_movements_recorded') ?></span>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('date_time') ?></th>
                                <th><?= $this->__('type') ?></th>
                                <th><?= $this->__('document') ?></th>
                                <th class="text-end"><?= $this->__('quantity') ?></th>
                                <th class="text-end"><?= $this->__('unit_cost') ?></th>
                                <th class="text-end"><?= $this->__('balance_before') ?></th>
                                <th class="text-end"><?= $this->__('balance_after') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movements as $move): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i', strtotime($move['created_at'])) ?></td>
                                <td>
                                    <?php
                                    $typeClass = match($move['movement_type']) {
                                        'in' => 'success',
                                        'out' => 'danger',
                                        'adjust' => 'warning',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $typeClass ?>-subtle text-<?= $typeClass ?>"><?= $this->e(strtoupper($move['movement_type'])) ?></span>
                                </td>
                                <td>
                                    <?php if ($move['document_number']): ?>
                                        <a href="/warehouse/documents/<?= $move['document_id'] ?>" class="text-primary">
                                            <?= $this->e($move['document_number']) ?>
                                        </a>
                                        <br><small class="text-muted"><?= $this->e($move['document_type']) ?> - <?= date('Y-m-d', strtotime($move['document_date'])) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= number_format($move['quantity'], 2) ?></td>
                                <td class="text-end">$<?= number_format($move['unit_cost'], 4) ?></td>
                                <td class="text-end text-muted"><?= number_format($move['balance_before'], 2) ?></td>
                                <td class="text-end fw-medium"><?= number_format($move['balance_after'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Allocations Tab -->
    <div class="tab-pane fade" id="allocations-content" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($allocations)): ?>
                <div class="text-center py-5">
                    <i class="ri-flow-chart fs-1 d-block mb-2 text-secondary"></i>
                    <span class="text-muted"><?= $this->__('no_allocations_recorded') ?></span>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('date') ?></th>
                                <th><?= $this->__('document') ?></th>
                                <th><?= $this->__('method') ?></th>
                                <th class="text-end"><?= $this->__('quantity') ?></th>
                                <th class="text-end"><?= $this->__('unit_cost') ?></th>
                                <th class="text-end"><?= $this->__('total_cost') ?></th>
                                <th><?= $this->__('allocated_by') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allocations as $alloc): ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($alloc['document_date'])) ?></td>
                                <td>
                                    <a href="/warehouse/documents/<?= $alloc['document_id'] ?>" class="text-primary">
                                        <?= $this->e($alloc['document_number']) ?>
                                    </a>
                                    <br><small class="text-muted"><?= $this->e($alloc['document_type']) ?></small>
                                </td>
                                <td><span class="badge bg-info-subtle text-info"><?= $this->e($alloc['allocation_method']) ?></span></td>
                                <td class="text-end"><?= number_format($alloc['quantity'], 2) ?></td>
                                <td class="text-end">$<?= number_format($alloc['unit_cost'], 4) ?></td>
                                <td class="text-end fw-medium">$<?= number_format($alloc['total_cost'], 2) ?></td>
                                <td><?= $this->e($alloc['allocated_by_name'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <td colspan="3"><?= $this->__('total') ?>:</td>
                                <td class="text-end"><?= number_format(array_sum(array_column($allocations, 'quantity')), 2) ?></td>
                                <td></td>
                                <td class="text-end">$<?= number_format(array_sum(array_column($allocations, 'total_cost')), 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
