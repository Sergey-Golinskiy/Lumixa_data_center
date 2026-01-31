<?php
/** @var array $item */
/** @var array $batches */
/** @var array $history */
/** @var float $availableQty */
/** @var string $status */
?>

<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <span class="text-muted"><?= $this->__('sku') ?>: <?= $this->e($item['sku']) ?></span> |
                <span class="text-muted"><?= $this->__('available') ?>:</span>
                <strong class="text-success"><?= number_format($availableQty, 2) ?> <?= $this->__('unit_' . $item['unit']) ?></strong>
            </div>
            <div class="d-flex gap-2">
                <a href="/warehouse/batches/create?item_id=<?= $item['id'] ?>" class="btn btn-success">
                    <i class="ri-add-line me-1"></i> <?= $this->__('create_batch') ?>
                </a>
                <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_item') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Costing Method Info -->
<div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
    <i class="ri-information-line me-2"></i>
    <strong><?= $this->__('costing_method') ?>:</strong>
    <span class="badge bg-primary-subtle text-primary"><?= $this->e($item['costing_method']) ?></span>
    <?php if ($item['allow_method_override']): ?>
        <span class="text-muted">(<?= $this->__('override_allowed_on_documents') ?>)</span>
    <?php else: ?>
        <span class="text-muted">(<?= $this->__('fixed_method') ?>)</span>
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="batches-tab" data-bs-toggle="tab" data-bs-target="#batches-content" type="button" role="tab">
            <i class="ri-stack-line me-1"></i> <?= $this->__('active_batches') ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-content" type="button" role="tab">
            <i class="ri-list-check me-1"></i> <?= $this->__('all_batches') ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" type="button" role="tab">
            <i class="ri-history-line me-1"></i> <?= $this->__('movement_history') ?>
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Active Batches Tab -->
    <div class="tab-pane fade show active" id="batches-content" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($batches)): ?>
                <div class="text-center py-5">
                    <i class="ri-stack-line fs-1 d-block mb-2 text-secondary"></i>
                    <span class="text-muted"><?= $this->__('no_active_batches') ?></span>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('batch_code') ?></th>
                                <th><?= $this->__('received_date') ?></th>
                                <th><?= $this->__('supplier') ?></th>
                                <th class="text-end"><?= $this->__('received_qty') ?></th>
                                <th class="text-end"><?= $this->__('available_qty') ?></th>
                                <th class="text-end"><?= $this->__('reserved') ?></th>
                                <th class="text-end"><?= $this->__('unit_cost') ?></th>
                                <th class="text-end"><?= $this->__('total_value') ?></th>
                                <th><?= $this->__('expiry_date') ?></th>
                                <th><?= $this->__('status') ?></th>
                                <th style="width: 80px;"><?= $this->__('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($batches as $batch): ?>
                                <?php
                                $qtyReserved = $batch['qty_reserved'] ?? 0;
                                $qtyUnreserved = $batch['qty_unreserved'] ?? $batch['qty_available'];
                                $totalValue = $batch['qty_available'] * $batch['unit_cost'];
                                $isExpiringSoon = false;
                                $isExpired = false;

                                if ($batch['expiry_date']) {
                                    $expiryDate = strtotime($batch['expiry_date']);
                                    $now = time();
                                    $warnDays = 30;
                                    $isExpired = $expiryDate < $now;
                                    $isExpiringSoon = !$isExpired && ($expiryDate - $now) < ($warnDays * 86400);
                                }
                                ?>
                                <tr class="<?= $isExpired ? 'table-danger' : ($isExpiringSoon ? 'table-warning' : '') ?>">
                                    <td>
                                        <a href="/warehouse/batches/<?= $batch['id'] ?>" class="fw-medium text-primary">
                                            <?= $this->e($batch['batch_code']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($batch['received_date'])) ?></td>
                                    <td><?= $this->e($batch['supplier_name'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format($batch['qty_received'], 2) ?></td>
                                    <td class="text-end">
                                        <strong class="text-success"><?= number_format($batch['qty_available'], 2) ?></strong>
                                    </td>
                                    <td class="text-end text-muted">
                                        <?= $qtyReserved > 0 ? number_format($qtyReserved, 2) : '-' ?>
                                    </td>
                                    <td class="text-end"><?= number_format($batch['unit_cost'], 4) ?></td>
                                    <td class="text-end fw-medium"><?= number_format($totalValue, 2) ?></td>
                                    <td>
                                        <?php if ($batch['expiry_date']): ?>
                                            <?php if ($isExpired): ?>
                                                <span class="badge bg-danger"><?= date('Y-m-d', strtotime($batch['expiry_date'])) ?> (<?= $this->__('expired') ?>)</span>
                                            <?php elseif ($isExpiringSoon): ?>
                                                <span class="badge bg-warning-subtle text-warning"><?= date('Y-m-d', strtotime($batch['expiry_date'])) ?> (<?= $this->__('soon') ?>)</span>
                                            <?php else: ?>
                                                <?= date('Y-m-d', strtotime($batch['expiry_date'])) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($batch['status']) {
                                            'active' => 'success',
                                            'depleted' => 'secondary',
                                            'expired' => 'danger',
                                            'quarantine' => 'warning',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= $this->e(ucfirst($batch['status'])) ?></span>
                                    </td>
                                    <td>
                                        <a href="/warehouse/batches/<?= $batch['id'] ?>" class="btn btn-sm btn-soft-primary" title="<?= $this->__('view') ?>">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <td colspan="4"><?= $this->__('total') ?>:</td>
                                <td class="text-end">
                                    <?= number_format(array_sum(array_column($batches, 'qty_available')), 2) ?>
                                </td>
                                <td class="text-end">
                                    <?= number_format(array_sum(array_column($batches, 'qty_reserved')), 2) ?>
                                </td>
                                <td></td>
                                <td class="text-end">
                                    <?php
                                    $totalValue = 0;
                                    foreach ($batches as $b) {
                                        $totalValue += $b['qty_available'] * $b['unit_cost'];
                                    }
                                    echo number_format($totalValue, 2);
                                    ?>
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- All Batches Tab -->
    <div class="tab-pane fade" id="all-content" role="tabpanel">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ri-stack-line fs-1 d-block mb-2 text-secondary"></i>
                <p class="text-muted"><?= $this->__('all_batches_including_depleted_expired') ?></p>
            </div>
        </div>
    </div>

    <!-- History Tab -->
    <div class="tab-pane fade" id="history-content" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($history)): ?>
                <div class="text-center py-5">
                    <i class="ri-history-line fs-1 d-block mb-2 text-secondary"></i>
                    <span class="text-muted"><?= $this->__('no_movement_history') ?></span>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('date_time') ?></th>
                                <th><?= $this->__('batch') ?></th>
                                <th><?= $this->__('type') ?></th>
                                <th><?= $this->__('document') ?></th>
                                <th class="text-end"><?= $this->__('quantity') ?></th>
                                <th class="text-end"><?= $this->__('unit_cost') ?></th>
                                <th class="text-end"><?= $this->__('balance_before') ?></th>
                                <th class="text-end"><?= $this->__('balance_after') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $move): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i', strtotime($move['created_at'])) ?></td>
                                <td><?= $this->e($move['batch_code']) ?></td>
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
                                        <span class="text-muted">(<?= $this->e($move['document_type']) ?>)</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= number_format($move['quantity'], 2) ?></td>
                                <td class="text-end"><?= number_format($move['unit_cost'], 4) ?></td>
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
</div>

<?php $this->endSection(); ?>
