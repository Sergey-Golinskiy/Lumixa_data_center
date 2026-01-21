<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/stock" class="btn btn-secondary">&laquo; <?= $this->__('back_to_stock') ?></a>
    <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-outline"><?= $this->__('view_item') ?></a>
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
                    <div class="total-label"><?= $this->__('total') ?> <?= $this->e($item['unit']) ?></div>
                </div>
                <div class="total-box">
                    <div class="total-value"><?= number_format($totals['total_reserved'] ?? 0, 3) ?></div>
                    <div class="total-label"><?= $this->__('reserved') ?></div>
                </div>
                <div class="total-box">
                    <div class="total-value"><?= number_format(($totals['total_quantity'] ?? 0) - ($totals['total_reserved'] ?? 0), 3) ?></div>
                    <div class="total-label"><?= $this->__('available') ?></div>
                </div>
                <div class="total-box">
                    <div class="total-value"><?= number_format($totals['total_value'] ?? 0, 2) ?></div>
                    <div class="total-label"><?= $this->__('total_value') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Movements -->
<div class="card">
    <div class="card-header"><?= $this->__('recent_movements') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('document') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('direction') ?></th>
                        <th class="text-right"><?= $this->__('quantity') ?></th>
                        <th class="text-right"><?= $this->__('unit_cost') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted"><?= $this->__('no_movements') ?></td>
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
                            <?php if ($movement['movement_type'] === 'in'): ?>
                            <span class="badge badge-success"><?= $this->__('in') ?></span>
                            <?php else: ?>
                            <span class="badge badge-danger"><?= $this->__('out') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($movement['movement_type'] === 'in'): ?>
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
