<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex gap-2">
    <a href="/warehouse/stock" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_stock') ?>
    </a>
</div>

<?php if (!empty($items)): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="ri-alert-line me-2"></i>
    <strong><?= $this->__('attention') ?>!</strong> <?= $this->__('items_below_min_stock', ['count' => count($items)]) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-alert-line me-2"></i><?= $this->__('low_stock_report') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('category') ?></th>
                        <th class="text-end"><?= $this->__('min_stock') ?></th>
                        <th class="text-end"><?= $this->__('current') ?></th>
                        <th class="text-end"><?= $this->__('reserved') ?></th>
                        <th class="text-end"><?= $this->__('available') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th style="width: 180px;"><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="ri-checkbox-circle-line fs-1 d-block mb-2 text-success"></i>
                            <span class="text-muted"><?= $this->__('all_items_above_min_stock') ?></span>
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
                            <a href="/warehouse/items/<?= $item['id'] ?>" class="fw-medium text-primary">
                                <?= $this->e($item['sku']) ?>
                            </a>
                        </td>
                        <td><?= $this->e($item['name']) ?></td>
                        <td><?= $this->e($item['category'] ?? '-') ?></td>
                        <td class="text-end"><?= number_format($item['min_stock'], 0) ?> <?= $this->__('unit_' . $item['unit']) ?></td>
                        <td class="text-end">
                            <?php if ($item['current_stock'] <= 0): ?>
                            <span class="text-danger fw-semibold">0</span>
                            <?php else: ?>
                            <?= number_format($item['current_stock'], 3) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($item['reserved'] > 0): ?>
                            <span class="text-warning"><?= number_format($item['reserved'], 3) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($available <= 0): ?>
                            <span class="badge bg-danger"><?= number_format($available, 3) ?></span>
                            <?php else: ?>
                            <?= number_format($available, 3) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= $statusText ?></span>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-<?= $statusClass ?>" role="progressbar" style="width: <?= min(100, $percentage) ?>%"></div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/warehouse/stock/<?= $item['id'] ?>" class="btn btn-sm btn-soft-primary" title="<?= $this->__('view_stock') ?>">
                                    <i class="ri-eye-line"></i>
                                </a>
                                <a href="/warehouse/documents/create?type=receipt&item=<?= $item['id'] ?>" class="btn btn-sm btn-success" title="<?= $this->__('receipt') ?>">
                                    <i class="ri-add-line"></i> <?= $this->__('receipt') ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
