<?php
/** @var array $item */
/** @var array $batches */
/** @var array $history */
/** @var float $availableQty */
/** @var string $status */
?>

<div class="page-header">
    <div>
        <h1><?= h($title) ?></h1>
        <p class="text-muted">SKU: <?= h($item['sku']) ?> | <?= __('Available') ?>: <strong><?= number_format($availableQty, 2) ?> <?= h($item['unit']) ?></strong></p>
    </div>
    <div>
        <a href="/warehouse/batches/create?item_id=<?= $item['id'] ?>" class="btn btn-primary">
            <?= __('Create Batch') ?>
        </a>
        <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-secondary">
            <?= __('Back to Item') ?>
        </a>
    </div>
</div>

<!-- Costing Method Info -->
<div class="alert alert-info mb-4">
    <strong><?= __('Costing Method') ?>:</strong>
    <span class="badge badge-primary"><?= h($item['costing_method']) ?></span>
    <?php if ($item['allow_method_override']): ?>
        <span class="text-muted">(<?= __('Override allowed on documents') ?>)</span>
    <?php else: ?>
        <span class="text-muted">(<?= __('Fixed method') ?>)</span>
    <?php endif; ?>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" data-tab="batches"><?= __('Active Batches') ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-tab="all"><?= __('All Batches') ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-tab="history"><?= __('Movement History') ?></a>
    </li>
</ul>

<!-- Active Batches Tab -->
<div class="tab-content" data-tab-content="batches">
    <?php if (empty($batches)): ?>
        <div class="alert alert-warning">
            <?= __('No active batches found for this item.') ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('Batch Code') ?></th>
                        <th><?= __('Received Date') ?></th>
                        <th><?= __('Supplier') ?></th>
                        <th class="text-right"><?= __('Received Qty') ?></th>
                        <th class="text-right"><?= __('Available Qty') ?></th>
                        <th class="text-right"><?= __('Reserved') ?></th>
                        <th class="text-right"><?= __('Unit Cost') ?></th>
                        <th class="text-right"><?= __('Total Value') ?></th>
                        <th><?= __('Expiry Date') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Actions') ?></th>
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
                            $warnDays = 30; // TODO: Get from settings
                            $isExpired = $expiryDate < $now;
                            $isExpiringSoon = !$isExpired && ($expiryDate - $now) < ($warnDays * 86400);
                        }
                        ?>
                        <tr class="<?= $isExpired ? 'table-danger' : ($isExpiringSoon ? 'table-warning' : '') ?>">
                            <td>
                                <a href="/warehouse/batches/<?= $batch['id'] ?>"><?= h($batch['batch_code']) ?></a>
                            </td>
                            <td><?= date('Y-m-d', strtotime($batch['received_date'])) ?></td>
                            <td><?= h($batch['supplier_name'] ?? '-') ?></td>
                            <td class="text-right"><?= number_format($batch['qty_received'], 2) ?></td>
                            <td class="text-right">
                                <strong><?= number_format($batch['qty_available'], 2) ?></strong>
                            </td>
                            <td class="text-right text-muted">
                                <?= $qtyReserved > 0 ? number_format($qtyReserved, 2) : '-' ?>
                            </td>
                            <td class="text-right"><?= number_format($batch['unit_cost'], 4) ?></td>
                            <td class="text-right"><?= number_format($totalValue, 2) ?></td>
                            <td>
                                <?php if ($batch['expiry_date']): ?>
                                    <?php if ($isExpired): ?>
                                        <span class="badge badge-danger"><?= date('Y-m-d', strtotime($batch['expiry_date'])) ?> (<?= __('Expired') ?>)</span>
                                    <?php elseif ($isExpiringSoon): ?>
                                        <span class="badge badge-warning"><?= date('Y-m-d', strtotime($batch['expiry_date'])) ?> (<?= __('Soon') ?>)</span>
                                    <?php else: ?>
                                        <?= date('Y-m-d', strtotime($batch['expiry_date'])) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'active' => 'success',
                                    'depleted' => 'secondary',
                                    'expired' => 'danger',
                                    'quarantine' => 'warning'
                                ][$batch['status']] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $statusClass ?>"><?= h(ucfirst($batch['status'])) ?></span>
                            </td>
                            <td>
                                <a href="/warehouse/batches/<?= $batch['id'] ?>" class="btn btn-sm btn-info">
                                    <?= __('View') ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold">
                        <td colspan="4"><?= __('Total') ?>:</td>
                        <td class="text-right">
                            <?= number_format(array_sum(array_column($batches, 'qty_available')), 2) ?>
                        </td>
                        <td class="text-right">
                            <?= number_format(array_sum(array_column($batches, 'qty_reserved')), 2) ?>
                        </td>
                        <td></td>
                        <td class="text-right">
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

<!-- All Batches Tab -->
<div class="tab-content" data-tab-content="all" style="display:none;">
    <p class="text-muted"><?= __('This will show all batches including depleted and expired ones.') ?></p>
    <!-- TODO: Add filter and show all batches -->
</div>

<!-- History Tab -->
<div class="tab-content" data-tab-content="history" style="display:none;">
    <?php if (empty($history)): ?>
        <div class="alert alert-info">
            <?= __('No movement history found.') ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th><?= __('Date/Time') ?></th>
                        <th><?= __('Batch') ?></th>
                        <th><?= __('Type') ?></th>
                        <th><?= __('Document') ?></th>
                        <th class="text-right"><?= __('Quantity') ?></th>
                        <th class="text-right"><?= __('Unit Cost') ?></th>
                        <th class="text-right"><?= __('Balance Before') ?></th>
                        <th class="text-right"><?= __('Balance After') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $move): ?>
                        <tr>
                            <td><?= date('Y-m-d H:i', strtotime($move['created_at'])) ?></td>
                            <td><?= h($move['batch_code']) ?></td>
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
                                    <span class="text-muted">(<?= h($move['document_type']) ?>)</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= number_format($move['quantity'], 2) ?></td>
                            <td class="text-right"><?= number_format($move['unit_cost'], 4) ?></td>
                            <td class="text-right text-muted"><?= number_format($move['balance_before'], 2) ?></td>
                            <td class="text-right"><?= number_format($move['balance_after'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
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
