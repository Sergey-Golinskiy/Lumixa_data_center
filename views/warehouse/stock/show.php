<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/stock" class="btn btn-secondary">&laquo; Back to Stock</a>
    <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-outline">View Item</a>
</div>

<!-- Item Header -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <div class="item-header">
            <div>
                <h2 style="margin: 0;"><?= $this->e($item['sku']) ?></h2>
                <p style="margin: 5px 0 0; color: var(--text-muted);"><?= $this->e($item['name']) ?></p>
            </div>
            <div class="item-totals">
                <div class="total-box">
                    <div class="total-value"><?= number_format($totals['total_quantity'] ?? 0, 3) ?></div>
                    <div class="total-label">Total <?= $this->e($item['unit']) ?></div>
                </div>
                <div class="total-box">
                    <div class="total-value"><?= number_format($totals['total_reserved'] ?? 0, 3) ?></div>
                    <div class="total-label">Reserved</div>
                </div>
                <div class="total-box">
                    <div class="total-value"><?= number_format(($totals['total_quantity'] ?? 0) - ($totals['total_reserved'] ?? 0), 3) ?></div>
                    <div class="total-label">Available</div>
                </div>
                <div class="total-box">
                    <div class="total-value"><?= number_format($totals['total_value'] ?? 0, 2) ?></div>
                    <div class="total-label">Total Value</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock by Lot -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">Stock by Lot</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Lot</th>
                        <th>Status</th>
                        <th>Expiry</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Reserved</th>
                        <th class="text-right">Available</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stockByLot)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No stock</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($stockByLot as $stock): ?>
                    <?php $available = $stock['quantity'] - $stock['reserved_quantity']; ?>
                    <tr>
                        <td>
                            <?php if ($stock['lot_id']): ?>
                            <a href="/warehouse/lots/<?= $stock['lot_id'] ?>">
                                <?= $this->e($stock['lot_number']) ?>
                            </a>
                            <?php else: ?>
                            <em class="text-muted">No lot</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($stock['lot_status']): ?>
                            <?php
                            $statusClass = match($stock['lot_status']) {
                                'active' => 'success',
                                'quarantine' => 'warning',
                                'blocked' => 'danger',
                                'expired' => 'secondary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($stock['lot_status']) ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($stock['expiry_date']): ?>
                            <?= $this->date($stock['expiry_date'], 'Y-m-d') ?>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($stock['quantity'], 3) ?></td>
                        <td class="text-right">
                            <?php if ($stock['reserved_quantity'] > 0): ?>
                            <span class="text-warning"><?= number_format($stock['reserved_quantity'], 3) ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($available, 3) ?></td>
                        <td class="text-right"><?= number_format($stock['unit_cost'], 4) ?></td>
                        <td class="text-right"><?= number_format($stock['quantity'] * $stock['unit_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Movements -->
<div class="card">
    <div class="card-header">Recent Movements</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Lot</th>
                        <th>Direction</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No movements</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td><?= $this->datetime($movement['created_at']) ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $movement['document_id'] ?>">
                                <?= $this->e($movement['document_number']) ?>
                            </a>
                        </td>
                        <td><?= ucfirst($movement['document_type']) ?></td>
                        <td><?= $this->e($movement['lot_number'] ?? '-') ?></td>
                        <td>
                            <?php if ($movement['direction'] === 'in'): ?>
                            <span class="badge badge-success">IN</span>
                            <?php else: ?>
                            <span class="badge badge-danger">OUT</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($movement['direction'] === 'in'): ?>
                            <span class="text-success">+<?= number_format($movement['quantity'], 3) ?></span>
                            <?php else: ?>
                            <span class="text-danger">-<?= number_format($movement['quantity'], 3) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($movement['unit_cost'], 4) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.item-totals {
    display: flex;
    gap: 20px;
}
.total-box {
    text-align: center;
    padding: 10px 20px;
    background: var(--bg);
    border-radius: 8px;
}
.total-value {
    font-size: 20px;
    font-weight: bold;
}
.total-label {
    font-size: 12px;
    color: var(--text-muted);
}
.text-right { text-align: right; }
</style>

<?php $this->endSection(); ?>
