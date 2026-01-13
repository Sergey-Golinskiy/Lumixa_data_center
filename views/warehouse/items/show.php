<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/items" class="btn btn-secondary">&laquo; Back to Items</a>
    <?php if ($this->can('warehouse.items.edit')): ?>
    <a href="/warehouse/items/<?= $item['id'] ?>/edit" class="btn btn-primary">Edit Item</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- General Info -->
    <div class="card">
        <div class="card-header">General Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">SKU</span>
                <span class="detail-value"><strong><?= $this->e($item['sku']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= $this->e($item['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value"><?= $this->e(ucfirst($item['type'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Unit</span>
                <span class="detail-value"><?= $this->e($item['unit']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <span class="badge badge-<?= $item['is_active'] ? 'success' : 'secondary' ?>">
                        <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </span>
            </div>
            <?php if ($item['description']): ?>
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value"><?= nl2br($this->e($item['description'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stock Info -->
    <div class="card">
        <div class="card-header">Stock Levels</div>
        <div class="card-body">
            <?php
            $totalOnHand = 0;
            $totalReserved = 0;
            foreach ($stock as $s) {
                $totalOnHand += $s['on_hand'];
                $totalReserved += $s['reserved'];
            }
            $totalAvailable = $totalOnHand - $totalReserved;
            ?>
            <div class="stock-summary">
                <div class="stock-item">
                    <span class="stock-value"><?= $this->number($totalOnHand) ?></span>
                    <span class="stock-label">On Hand</span>
                </div>
                <div class="stock-item">
                    <span class="stock-value"><?= $this->number($totalReserved) ?></span>
                    <span class="stock-label">Reserved</span>
                </div>
                <div class="stock-item">
                    <span class="stock-value <?= $totalAvailable < 0 ? 'text-danger' : '' ?>">
                        <?= $this->number($totalAvailable) ?>
                    </span>
                    <span class="stock-label">Available</span>
                </div>
            </div>

            <?php if (count($stock) > 1): ?>
            <h4 style="margin-top: 20px;">By Lot</h4>
            <table>
                <thead>
                    <tr>
                        <th>Lot</th>
                        <th>Color</th>
                        <th>On Hand</th>
                        <th>Reserved</th>
                        <th>Avg Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stock as $s): ?>
                    <tr>
                        <td><?= $this->e($s['lot_number'] ?? 'No Lot') ?></td>
                        <td><?= $this->e($s['color'] ?? '-') ?></td>
                        <td><?= $this->number($s['on_hand']) ?></td>
                        <td><?= $this->number($s['reserved']) ?></td>
                        <td><?= $this->currency($s['avg_cost']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Attributes -->
    <?php if (!empty($attributes)): ?>
    <div class="card">
        <div class="card-header">Attributes</div>
        <div class="card-body">
            <?php foreach ($attributes as $name => $value): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->e(ucfirst($name)) ?></span>
                <span class="detail-value"><?= $this->e($value) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Settings -->
    <div class="card">
        <div class="card-header">Settings</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Min Stock</span>
                <span class="detail-value"><?= $this->number($item['min_stock']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Reorder Point</span>
                <span class="detail-value"><?= $this->number($item['reorder_point']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?= $this->date($item['created_at']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Updated</span>
                <span class="detail-value"><?= $this->date($item['updated_at']) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Movement History -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Recent Movements</div>
    <div class="card-body">
        <?php if (empty($history)): ?>
        <p class="text-muted">No movements yet</p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Lot</th>
                        <th>Quantity</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= $this->date($h['created_at']) ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $h['document_id'] ?>">
                                <?= $this->e($h['document_number']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-<?= $h['movement_type'] === 'in' ? 'success' : 'warning' ?>">
                                <?= $this->e(strtoupper($h['movement_type'])) ?>
                            </span>
                        </td>
                        <td><?= $this->e($h['lot_number'] ?? '-') ?></td>
                        <td>
                            <?= $h['movement_type'] === 'in' ? '+' : '-' ?><?= $this->number($h['quantity']) ?>
                        </td>
                        <td><?= $this->number($h['balance_after']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}
.detail-row {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    flex: 0 0 120px;
    color: var(--text-muted);
    font-size: 13px;
}
.detail-value {
    flex: 1;
}
.stock-summary {
    display: flex;
    gap: 30px;
}
.stock-item {
    text-align: center;
}
.stock-value {
    display: block;
    font-size: 28px;
    font-weight: 700;
}
.stock-label {
    font-size: 13px;
    color: var(--text-muted);
}
</style>

<?php $this->endSection(); ?>
