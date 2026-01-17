<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/items" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
    <?php if ($this->can('warehouse.items.edit')): ?>
    <a href="/warehouse/items/<?= $item['id'] ?>/edit" class="btn btn-primary"><?= $this->__('edit_item') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- General Info -->
    <div class="card">
        <div class="card-header"><?= $this->__('details') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('sku') ?></span>
                <span class="detail-value"><strong><?= $this->e($item['sku'] ?? '') ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('photo') ?></span>
                <span class="detail-value">
                    <?php if (!empty($item['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($item['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($item['image_path'], '/')) ?>">
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($item['name'] ?? '') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('type') ?></span>
                <span class="detail-value"><?= $this->e(ucfirst($item['type'] ?? '')) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('unit') ?></span>
                <span class="detail-value"><?= $this->e($item['unit'] ?? '') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <span class="badge badge-<?= ($item['is_active'] ?? false) ? 'success' : 'secondary' ?>">
                        <?= ($item['is_active'] ?? false) ? $this->__('active') : $this->__('inactive') ?>
                    </span>
                </span>
            </div>
            <?php if ($item['description'] ?? false): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('description') ?></span>
                <span class="detail-value"><?= nl2br($this->e($item['description'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stock Info -->
    <div class="card">
        <div class="card-header"><?= $this->__('stock') ?></div>
        <div class="card-body">
            <?php
            $totalOnHand = 0;
            $totalReserved = 0;
            foreach ($stock ?? [] as $s) {
                $totalOnHand += $s['on_hand'] ?? 0;
                $totalReserved += $s['reserved'] ?? 0;
            }
            $totalAvailable = $totalOnHand - $totalReserved;
            ?>
            <div class="stock-summary">
                <div class="stock-item">
                    <span class="stock-value"><?= $this->number($totalOnHand) ?></span>
                    <span class="stock-label"><?= $this->__('on_hand') ?></span>
                </div>
                <div class="stock-item">
                    <span class="stock-value"><?= $this->number($totalReserved) ?></span>
                    <span class="stock-label"><?= $this->__('reserved') ?></span>
                </div>
                <div class="stock-item">
                    <span class="stock-value <?= $totalAvailable < 0 ? 'text-danger' : '' ?>">
                        <?= $this->number($totalAvailable) ?>
                    </span>
                    <span class="stock-label"><?= $this->__('available') ?></span>
                </div>
            </div>

        </div>
    </div>

    <!-- Attributes -->
    <?php if (!empty($attributes)): ?>
    <div class="card">
        <div class="card-header"><?= $this->__('attributes_materials') ?></div>
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
        <div class="card-header"><?= $this->__('settings') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('min_stock') ?></span>
                <span class="detail-value"><?= $this->number($item['min_stock'] ?? 0) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('reorder_point') ?></span>
                <span class="detail-value"><?= $this->number($item['reorder_point'] ?? 0) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created_at') ?></span>
                <span class="detail-value"><?= $this->date($item['created_at'] ?? '') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('updated_at') ?></span>
                <span class="detail-value"><?= $this->date($item['updated_at'] ?? '') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Movement History -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('stock_movements') ?></div>
    <div class="card-body">
        <?php if (empty($history)): ?>
        <p class="text-muted"><?= $this->__('no_results') ?></p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('document') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('quantity') ?></th>
                        <th><?= $this->__('total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= $this->date($h['created_at'] ?? '') ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $h['document_id'] ?? '' ?>">
                                <?= $this->e($h['document_number'] ?? '') ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-<?= ($h['movement_type'] ?? '') === 'in' ? 'success' : 'warning' ?>">
                                <?= $this->e(strtoupper($h['movement_type'] ?? '')) ?>
                            </span>
                        </td>
                        <td>
                            <?= ($h['movement_type'] ?? '') === 'in' ? '+' : '-' ?><?= $this->number($h['quantity'] ?? 0) ?>
                        </td>
                        <td><?= $this->number($h['balance_after'] ?? 0) ?></td>
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
