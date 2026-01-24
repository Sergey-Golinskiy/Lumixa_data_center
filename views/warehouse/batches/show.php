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

<div class="page-header">
    <div>
        <h1><?= h($title) ?></h1>
        <p class="text-muted"><?= h($batch['item_name']) ?> (<?= h($batch['sku']) ?>)</p>
    </div>
    <div>
        <a href="/warehouse/batches?item_id=<?= $batch['item_id'] ?>" class="btn btn-secondary">
            <?= __('Back to Batches') ?>
        </a>
        <a href="/warehouse/items/<?= $batch['item_id'] ?>" class="btn btn-secondary">
            <?= __('View Item') ?>
        </a>
    </div>
</div>

<!-- Batch Info Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted"><?= __('Status') ?></h6>
                <h3 class="mb-0">
                    <span class="badge badge-<?= $statusColor ?>"><?= h(ucfirst($batch['status'])) ?></span>
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted"><?= __('Received Quantity') ?></h6>
                <h3 class="mb-0"><?= number_format($batch['qty_received'], 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted"><?= __('Available Quantity') ?></h6>
                <h3 class="mb-0 text-success"><?= number_format($batch['qty_available'], 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted"><?= __('Unit Cost') ?></h6>
                <h3 class="mb-0">$<?= number_format($batch['unit_cost'], 4) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Usage Progress -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title"><?= __('Batch Usage') ?></h5>
        <div class="progress" style="height: 30px;">
            <div class="progress-bar bg-success"
                 role="progressbar"
                 style="width: <?= $usagePercent ?>%"
                 aria-valuenow="<?= $usagePercent ?>"
                 aria-valuemin="0"
                 aria-valuemax="100">
                <?= round($usagePercent, 1) ?>% <?= __('used') ?>
            </div>
        </div>
        <p class="mt-2 text-muted">
            <?= __('Used') ?>: <?= number_format($qtyUsed, 2) ?> /
            <?= __('Received') ?>: <?= number_format($batch['qty_received'], 2) ?>
        </p>
    </div>
</div>

<!-- Batch Details -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><?= __('Batch Details') ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th width="40%"><?= __('Batch Code') ?>:</th>
                        <td><strong><?= h($batch['batch_code']) ?></strong></td>
                    </tr>
                    <tr>
                        <th><?= __('Received Date') ?>:</th>
                        <td><?= date('Y-m-d', strtotime($batch['received_date'])) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Supplier') ?>:</th>
                        <td>
                            <?php if ($batch['supplier_id']): ?>
                                <a href="/warehouse/partners/<?= $batch['supplier_id'] ?>">
                                    <?= h($batch['supplier_name']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= __('Source Type') ?>:</th>
                        <td><?= h(ucfirst($batch['source_type'])) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th width="40%"><?= __('Total Value') ?>:</th>
                        <td><strong>$<?= number_format($batch['qty_available'] * $batch['unit_cost'], 2) ?></strong></td>
                    </tr>
                    <tr>
                        <th><?= __('Expiry Date') ?>:</th>
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
                                    <span class="badge badge-danger"><?= __('Expired') ?></span>
                                <?php elseif ($daysUntilExpiry <= 30): ?>
                                    <span class="badge badge-warning"><?= __('Expires in') ?> <?= $daysUntilExpiry ?> <?= __('days') ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= __('Costing Method') ?>:</th>
                        <td><span class="badge badge-info"><?= h($batch['default_costing_method']) ?></span></td>
                    </tr>
                    <tr>
                        <th><?= __('Created At') ?>:</th>
                        <td><?= date('Y-m-d H:i', strtotime($batch['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if ($batch['notes']): ?>
            <div class="mt-3">
                <strong><?= __('Notes') ?>:</strong>
                <p><?= nl2br(h($batch['notes'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Status Change -->
        <?php if ($batch['status'] === 'active'): ?>
            <div class="mt-3">
                <form method="POST" action="/warehouse/batches/<?= $batch['id'] ?>/status" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" name="status" value="quarantine" class="btn btn-sm btn-warning"
                            onclick="return confirm('<?= __('Mark this batch as quarantined?') ?>')">
                        <?= __('Set to Quarantine') ?>
                    </button>
                </form>
                <form method="POST" action="/warehouse/batches/<?= $batch['id'] ?>/status" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" name="status" value="expired" class="btn btn-sm btn-danger"
                            onclick="return confirm('<?= __('Mark this batch as expired?') ?>')">
                        <?= __('Mark as Expired') ?>
                    </button>
                </form>
            </div>
        <?php elseif ($batch['status'] === 'quarantine'): ?>
            <div class="mt-3">
                <form method="POST" action="/warehouse/batches/<?= $batch['id'] ?>/status" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" name="status" value="active" class="btn btn-sm btn-success"
                            onclick="return confirm('<?= __('Return this batch to active status?') ?>')">
                        <?= __('Return to Active') ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link active" data-tab="movements"><?= __('Movements') ?> (<?= count($movements) ?>)</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-tab="allocations"><?= __('Issue Allocations') ?> (<?= count($allocations) ?>)</a>
    </li>
</ul>

<!-- Movements Tab -->
<div class="tab-content" data-tab-content="movements">
    <?php if (empty($movements)): ?>
        <div class="alert alert-info">
            <?= __('No movements recorded for this batch yet.') ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('Date/Time') ?></th>
                        <th><?= __('Type') ?></th>
                        <th><?= __('Document') ?></th>
                        <th class="text-right"><?= __('Quantity') ?></th>
                        <th class="text-right"><?= __('Unit Cost') ?></th>
                        <th class="text-right"><?= __('Balance Before') ?></th>
                        <th class="text-right"><?= __('Balance After') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $move): ?>
                        <tr>
                            <td><?= date('Y-m-d H:i', strtotime($move['created_at'])) ?></td>
                            <td>
                                <?php
                                $typeClass = ['in' => 'success', 'out' => 'danger', 'adjust' => 'warning'][$move['movement_type']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $typeClass ?>"><?= h(strtoupper($move['movement_type'])) ?></span>
                            </td>
                            <td>
                                <?php if ($move['document_number']): ?>
                                    <a href="/warehouse/documents/<?= $move['document_id'] ?>">
                                        <?= h($move['document_number']) ?>
                                    </a>
                                    <br><small class="text-muted"><?= h($move['document_type']) ?> - <?= date('Y-m-d', strtotime($move['document_date'])) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= number_format($move['quantity'], 2) ?></td>
                            <td class="text-right">$<?= number_format($move['unit_cost'], 4) ?></td>
                            <td class="text-right text-muted"><?= number_format($move['balance_before'], 2) ?></td>
                            <td class="text-right">
                                <strong><?= number_format($move['balance_after'], 2) ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Allocations Tab -->
<div class="tab-content" data-tab-content="allocations" style="display:none;">
    <?php if (empty($allocations)): ?>
        <div class="alert alert-info">
            <?= __('No issue allocations recorded for this batch yet.') ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('Date') ?></th>
                        <th><?= __('Document') ?></th>
                        <th><?= __('Method') ?></th>
                        <th class="text-right"><?= __('Quantity') ?></th>
                        <th class="text-right"><?= __('Unit Cost') ?></th>
                        <th class="text-right"><?= __('Total Cost') ?></th>
                        <th><?= __('Allocated By') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allocations as $alloc): ?>
                        <tr>
                            <td><?= date('Y-m-d', strtotime($alloc['document_date'])) ?></td>
                            <td>
                                <a href="/warehouse/documents/<?= $alloc['document_id'] ?>">
                                    <?= h($alloc['document_number']) ?>
                                </a>
                                <br><small class="text-muted"><?= h($alloc['document_type']) ?></small>
                            </td>
                            <td><span class="badge badge-info"><?= h($alloc['allocation_method']) ?></span></td>
                            <td class="text-right"><?= number_format($alloc['quantity'], 2) ?></td>
                            <td class="text-right">$<?= number_format($alloc['unit_cost'], 4) ?></td>
                            <td class="text-right"><strong>$<?= number_format($alloc['total_cost'], 2) ?></strong></td>
                            <td><?= h($alloc['allocated_by_name'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold">
                        <td colspan="3"><?= __('Total') ?>:</td>
                        <td class="text-right"><?= number_format(array_sum(array_column($allocations, 'quantity')), 2) ?></td>
                        <td></td>
                        <td class="text-right">$<?= number_format(array_sum(array_column($allocations, 'total_cost')), 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('[data-tab]');
    const contents = document.querySelectorAll('[data-tab-content]');

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');

            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.style.display = 'none');

            this.classList.add('active');
            const targetContent = document.querySelector(`[data-tab-content="${targetTab}"]`);
            if (targetContent) {
                targetContent.style.display = 'block';
            }
        });
    });
});
</script>
