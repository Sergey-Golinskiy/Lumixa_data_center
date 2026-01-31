<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex gap-2">
    <a href="/warehouse/stock" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_stock') ?>
    </a>
</div>

<!-- Summary Cards -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_inventory_value') ?></p>
                        <h4 class="mb-0"><?= number_format($grandTotal, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                            <i class="ri-stack-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('items_with_stock') ?></p>
                        <h4 class="mb-0"><?= count($valuation) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                            <i class="ri-folder-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('categories') ?></p>
                        <h4 class="mb-0"><?= count($byCategory) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Breakdown -->
<?php if (!empty($byCategory)): ?>
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-pie-chart-line me-2"></i><?= $this->__('value_by_category') ?></h5>
    </div>
    <div class="card-body">
        <?php foreach ($byCategory as $cat => $data): ?>
        <?php $pct = $grandTotal > 0 ? ($data['value'] / $grandTotal) * 100 : 0; ?>
        <div class="d-flex align-items-center mb-3">
            <div style="width: 150px;" class="fw-medium"><?= $this->e($cat) ?></div>
            <div class="flex-grow-1 mx-3">
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%">
                        <?= number_format($pct, 1) ?>%
                    </div>
                </div>
            </div>
            <div style="width: 180px;" class="text-end">
                <span class="fw-medium"><?= number_format($data['value'], 2) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label"><?= $this->__('category') ?></label>
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value=""><?= $this->__('all_categories') ?></option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $this->e($cat['type']) ?>" <?= $category === $cat['type'] ? 'selected' : '' ?>>
                        <?= $this->e(ucfirst($cat['type'])) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Valuation Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-file-list-3-line me-2"></i><?= $this->__('inventory_valuation') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('category') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('avg_cost') ?></th>
                        <th class="text-end"><?= $this->__('total_value') ?></th>
                        <th class="text-end"><?= $this->__('percent_of_total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($valuation)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
                            <span class="text-muted"><?= $this->__('no_inventory') ?></span>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($valuation as $item): ?>
                    <?php $pct = $grandTotal > 0 ? ($item['total_value'] / $grandTotal) * 100 : 0; ?>
                    <tr>
                        <td>
                            <a href="/warehouse/stock/<?= $item['id'] ?>" class="fw-medium text-primary">
                                <?= $this->e($item['sku']) ?>
                            </a>
                        </td>
                        <td><?= $this->e($item['name']) ?></td>
                        <td>
                            <span class="badge badge-type-<?= $this->e($item['type'] ?? 'material') ?>">
                                <?= $this->__('item_type_' . ($item['type'] ?? 'material')) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?= number_format($item['quantity'], 3) ?>
                            <small class="text-muted"><?= $this->__('unit_' . ($item['unit'] ?? 'pcs')) ?></small>
                        </td>
                        <td class="text-end"><?= number_format($item['avg_cost'], 4) ?></td>
                        <td class="text-end fw-semibold"><?= number_format($item['total_value'], 2) ?></td>
                        <td class="text-end">
                            <?= number_format($pct, 1) ?>%
                            <div class="progress mt-1" style="height: 3px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $pct * 2) ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-semibold"><?= $this->__('grand_total') ?>:</td>
                        <td class="text-end fw-semibold"><?= number_format($grandTotal, 2) ?></td>
                        <td class="text-end fw-semibold">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
