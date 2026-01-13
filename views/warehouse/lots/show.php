<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/lots" class="btn btn-secondary">&laquo; Back to Lots</a>
    <?php if ($this->can('warehouse.lots.edit')): ?>
    <a href="/warehouse/lots/<?= $lot['id'] ?>/edit" class="btn btn-outline">Edit Lot</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Lot Information -->
    <div class="card">
        <div class="card-header">Lot Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Lot Number</span>
                <span class="detail-value"><strong><?= $this->e($lot['lot_number']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Item</span>
                <span class="detail-value">
                    <a href="/warehouse/items/<?= $lot['item_id'] ?>">
                        <?= $this->e($lot['sku']) ?> - <?= $this->e($lot['item_name']) ?>
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($lot['status']) {
                        'active' => 'success',
                        'quarantine' => 'warning',
                        'blocked' => 'danger',
                        'expired' => 'secondary',
                        default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($lot['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Manufacture Date</span>
                <span class="detail-value"><?= $lot['manufacture_date'] ? $this->date($lot['manufacture_date'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Expiry Date</span>
                <span class="detail-value">
                    <?php if ($lot['expiry_date']): ?>
                        <?php
                        $expiry = new DateTime($lot['expiry_date']);
                        $today = new DateTime();
                        $diff = $today->diff($expiry);
                        $daysLeft = $diff->invert ? -$diff->days : $diff->days;
                        ?>
                        <?= $this->date($lot['expiry_date'], 'Y-m-d') ?>
                        <?php if ($daysLeft < 0): ?>
                        <span class="badge badge-danger">Expired <?= abs($daysLeft) ?> days ago</span>
                        <?php elseif ($daysLeft <= 30): ?>
                        <span class="badge badge-warning"><?= $daysLeft ?> days left</span>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Supplier Lot</span>
                <span class="detail-value"><?= $this->e($lot['supplier_lot'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?= $this->datetime($lot['created_at']) ?></span>
            </div>
            <?php if ($lot['notes']): ?>
            <div class="detail-row">
                <span class="detail-label">Notes</span>
                <span class="detail-value"><?= nl2br($this->e($lot['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stock & Status -->
    <div class="card">
        <div class="card-header">Stock & Status</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Current Stock</span>
                <span class="detail-value">
                    <strong style="font-size: 24px;">
                        <?= $stockBalance ? number_format($stockBalance['quantity'], 3) : '0.000' ?>
                    </strong>
                    <?= $this->e($lot['unit']) ?>
                </span>
            </div>
            <?php if ($stockBalance): ?>
            <div class="detail-row">
                <span class="detail-label">Reserved</span>
                <span class="detail-value"><?= number_format($stockBalance['reserved_quantity'] ?? 0, 3) ?> <?= $this->e($lot['unit']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Available</span>
                <span class="detail-value">
                    <?= number_format(($stockBalance['quantity'] ?? 0) - ($stockBalance['reserved_quantity'] ?? 0), 3) ?>
                    <?= $this->e($lot['unit']) ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if ($this->can('warehouse.lots.edit')): ?>
            <hr style="margin: 20px 0;">
            <h4>Change Status</h4>
            <form method="POST" action="/warehouse/lots/<?= $lot['id'] ?>/status" style="margin-top: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <select name="status" required>
                        <option value="active" <?= $lot['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="quarantine" <?= $lot['status'] === 'quarantine' ? 'selected' : '' ?>>Quarantine</option>
                        <option value="blocked" <?= $lot['status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                        <option value="expired" <?= $lot['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="reason" placeholder="Reason for status change">
                </div>
                <button type="submit" class="btn btn-warning btn-sm">Update Status</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stock Movements -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Stock Movements</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No movements found</td>
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
                        <td>
                            <?php if ($movement['direction'] === 'in'): ?>
                            <span class="badge badge-success">IN</span>
                            <?php else: ?>
                            <span class="badge badge-danger">OUT</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($movement['direction'] === 'in'): ?>
                            <span class="text-success">+<?= number_format($movement['quantity'], 3) ?></span>
                            <?php else: ?>
                            <span class="text-danger">-<?= number_format($movement['quantity'], 3) ?></span>
                            <?php endif; ?>
                            <?= $this->e($lot['unit']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}
.detail-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    flex: 0 0 140px;
    color: var(--text-muted);
    font-size: 13px;
}
.detail-value {
    flex: 1;
}
</style>

<?php $this->endSection(); ?>
