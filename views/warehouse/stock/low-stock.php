<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('low_stock_report') ?></h1>
    <div class="page-actions">
        <a href="/warehouse/stock" class="btn btn-secondary">&laquo; <?= $this->__('back_to_stock') ?></a>
    </div>
</div>

<?php if (!empty($items)): ?>
<div class="alert alert-warning" style="margin-bottom: 20px;">
    <strong><?= $this->__('attention') ?>!</strong> <?= $this->__('items_below_min_stock', ['count' => count($items)]) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('category') ?></th>
                        <th class="text-right"><?= $this->__('min_stock') ?></th>
                        <th class="text-right"><?= $this->__('current') ?></th>
                        <th class="text-right"><?= $this->__('reserved') ?></th>
                        <th class="text-right"><?= $this->__('available') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <?= $this->__('all_items_above_min_stock') ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <?php
                    $available = $item['current_stock'] - $item['reserved'];
                    $percentage = $item['min_stock'] > 0 ? ($item['current_stock'] / $item['min_stock']) * 100 : 0;
                    $statusClass = 'danger';
                    $statusText = $this->__('critical');
                    if ($percentage >= 75) {
                        $statusClass = 'warning';
                        $statusText = $this->__('low');
                    } elseif ($percentage >= 50) {
                        $statusClass = 'warning';
                        $statusText = $this->__('warning');
                    }
                    if ($item['current_stock'] <= 0) {
                        $statusClass = 'danger';
                        $statusText = $this->__('out_of_stock');
                    }
                    ?>
                    <tr>
                        <td>
                            <a href="/warehouse/items/<?= $item['id'] ?>">
                                <strong><?= $this->e($item['sku']) ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($item['name']) ?></td>
                        <td><?= $this->e($item['category'] ?? '-') ?></td>
                        <td class="text-right"><?= number_format($item['min_stock'], 0) ?> <?= $this->e($item['unit']) ?></td>
                        <td class="text-right">
                            <?php if ($item['current_stock'] <= 0): ?>
                            <span class="text-danger"><strong>0</strong></span>
                            <?php else: ?>
                            <?= number_format($item['current_stock'], 3) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($item['reserved'] > 0): ?>
                            <span class="text-warning"><?= number_format($item['reserved'], 3) ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($available <= 0): ?>
                            <span class="text-danger"><strong><?= number_format($available, 3) ?></strong></span>
                            <?php else: ?>
                            <?= number_format($available, 3) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $statusClass ?>"><?= $statusText ?></span>
                            <div class="progress-bar">
                                <div class="progress-fill progress-<?= $statusClass ?>" style="width: <?= min(100, $percentage) ?>%"></div>
                            </div>
                        </td>
                        <td>
                            <a href="/warehouse/stock/<?= $item['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view_stock') ?></a>
                            <a href="/warehouse/documents/create?type=receipt&item=<?= $item['id'] ?>" class="btn btn-sm btn-primary">+ <?= $this->__('receipt') ?></a>
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
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.page-header h1 { margin: 0; }
.alert {
    padding: 15px 20px;
    border-radius: 6px;
}
.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    color: #856404;
}
.text-right { text-align: right; }
.progress-bar {
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    margin-top: 5px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 2px;
}
.progress-danger { background: var(--danger); }
.progress-warning { background: var(--warning); }
</style>

<?php $this->endSection(); ?>
