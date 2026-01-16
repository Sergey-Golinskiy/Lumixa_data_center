<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/lots" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('lots')]) ?></a>
    <?php if ($this->can('warehouse.lots.edit')): ?>
    <a href="/warehouse/lots/<?= $lot['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit_lot') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Lot Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('lot_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('lot_number') ?></span>
                <span class="detail-value"><strong><?= $this->e($lot['lot_number']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('items_list') ?></span>
                <span class="detail-value">
                    <a href="/warehouse/items/<?= $lot['item_id'] ?>">
                        <?= $this->e($lot['sku']) ?> - <?= $this->e($lot['item_name']) ?>
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($lot['status']) {
                        'active' => 'success',
                        'quarantine' => 'warning',
                        'blocked' => 'danger',
                        'expired' => 'secondary',
                        default => 'secondary'
                    };
                    $statusLabels = [
                        'active' => $this->__('active'),
                        'quarantine' => $this->__('quarantine'),
                        'blocked' => $this->__('blocked'),
                        'expired' => $this->__('expired')
                    ];
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= $statusLabels[$lot['status']] ?? $this->e($lot['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('manufacture_date') ?></span>
                <span class="detail-value"><?= $lot['manufacture_date'] ? $this->date($lot['manufacture_date'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('expiry_date') ?></span>
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
                        <span class="badge badge-danger"><?= $this->__('expired') ?> <?= $this->__('days_ago', ['count' => abs($daysLeft)]) ?></span>
                        <?php elseif ($daysLeft <= 30): ?>
                        <span class="badge badge-warning"><?= $this->__('days_left', ['count' => $daysLeft]) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('supplier_lot') ?></span>
                <span class="detail-value"><?= $this->e($lot['supplier_lot'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created') ?></span>
                <span class="detail-value"><?= $this->datetime($lot['created_at']) ?></span>
            </div>
            <?php if ($lot['notes']): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('notes') ?></span>
                <span class="detail-value"><?= nl2br($this->e($lot['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stock & Status -->
    <div class="card">
        <div class="card-header"><?= $this->__('stock_and_status') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('current_stock') ?></span>
                <span class="detail-value">
                    <strong style="font-size: 24px;">
                        <?= $stockBalance ? number_format($stockBalance['quantity'], 3) : '0.000' ?>
                    </strong>
                    <?= $this->e($lot['unit']) ?>
                </span>
            </div>
            <?php if ($stockBalance): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('reserved') ?></span>
                <span class="detail-value"><?= number_format($stockBalance['reserved_quantity'] ?? 0, 3) ?> <?= $this->e($lot['unit']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('available') ?></span>
                <span class="detail-value">
                    <?= number_format(($stockBalance['quantity'] ?? 0) - ($stockBalance['reserved_quantity'] ?? 0), 3) ?>
                    <?= $this->e($lot['unit']) ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if ($this->can('warehouse.lots.edit')): ?>
            <hr style="margin: 20px 0;">
            <h4><?= $this->__('change_status') ?></h4>
            <form method="POST" action="/warehouse/lots/<?= $lot['id'] ?>/status" style="margin-top: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="form-group">
                    <select name="status" required>
                        <option value="active" <?= $lot['status'] === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="quarantine" <?= $lot['status'] === 'quarantine' ? 'selected' : '' ?>><?= $this->__('quarantine') ?></option>
                        <option value="blocked" <?= $lot['status'] === 'blocked' ? 'selected' : '' ?>><?= $this->__('blocked') ?></option>
                        <option value="expired" <?= $lot['status'] === 'expired' ? 'selected' : '' ?>><?= $this->__('expired') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="reason" placeholder="<?= $this->__('status_change_reason') ?>">
                </div>
                <button type="submit" class="btn btn-warning btn-sm"><?= $this->__('update_status') ?></button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stock Movements -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('stock_movements') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('document') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('direction') ?></th>
                        <th><?= $this->__('quantity') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted"><?= $this->__('no_movements_found') ?></td>
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
                            <span class="badge badge-success"><?= $this->__('in') ?></span>
                            <?php else: ?>
                            <span class="badge badge-danger"><?= $this->__('out') ?></span>
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
