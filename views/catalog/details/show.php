<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->e($detail['name']) ?></h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.details.edit')): ?>
        <a href="/catalog/details/<?= $detail['id'] ?>/edit" class="btn btn-primary"><?= $this->__('edit') ?></a>
        <?php endif; ?>
        <a href="/catalog/details" class="btn btn-secondary"><?= $this->__('back_to_list') ?></a>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <div class="details-grid" style="display:grid;grid-template-columns: 120px 1fr;gap: 20px;align-items:start;">
            <div>
                <?php if (!empty($detail['image_path'])): ?>
                <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" style="width: 100px; height: 100px;" data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
                <?php else: ?>
                <span class="text-muted">-</span>
                <?php endif; ?>
            </div>
            <div>
                <h3><?= $this->e($detail['sku']) ?></h3>
                <p><?= $this->e($detail['name']) ?></p>
                <p>
                    <strong><?= $this->__('detail_type') ?>:</strong>
                    <?= $detail['detail_type'] === 'printed'
                        ? $this->__('detail_type_printed')
                        : $this->__('detail_type_purchased') ?>
                </p>
                <p>
                    <strong><?= $this->__('status') ?>:</strong>
                    <?= !empty($detail['is_active']) ? $this->__('active') : $this->__('inactive') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($detail['detail_type'] === 'printed'): ?>
        <div class="detail-row">
            <strong><?= $this->__('material') ?>:</strong>
            <?php if (!empty($detail['material_item_id'])): ?>
            <?= $this->e($detail['material_sku'] ?? '') ?> <?= !empty($detail['material_name']) ? ' - ' . $this->e($detail['material_name']) : '' ?>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('printer') ?>:</strong>
            <?php if (!empty($detail['printer_id'])): ?>
            <?= $this->e($detail['printer_name'] ?? '') ?><?= !empty($detail['printer_model']) ? ' - ' . $this->e($detail['printer_model']) : '' ?>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('material_qty_grams') ?>:</strong>
            <?= $detail['material_qty_grams'] ? $this->e($detail['material_qty_grams']) : '-' ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('print_time_minutes') ?>:</strong>
            <?= $detail['print_time_minutes'] ? $this->e($detail['print_time_minutes']) : '-' ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('print_parameters') ?>:</strong>
            <?= $detail['print_parameters'] ? $this->e($detail['print_parameters']) : '-' ?>
        </div>
        <?php endif; ?>
        <div class="detail-row">
            <strong><?= $this->__('model_file') ?>:</strong>
            <?php if (!empty($detail['model_path'])): ?>
            <a href="/<?= $this->e(ltrim($detail['model_path'], '/')) ?>" target="_blank" rel="noopener">
                <?= $this->__('download_model') ?>
            </a>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('detail_routing') ?></div>
    <div class="card-body">
        <?php if (!empty($activeRouting)): ?>
        <div class="routing-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <strong><?= $this->e($activeRouting['version']) ?></strong>
                <?php if ($activeRouting['name']): ?>
                <span class="text-muted">- <?= $this->e($activeRouting['name']) ?></span>
                <?php endif; ?>
            </div>
            <a href="/catalog/detail-routing/<?= $activeRouting['id'] ?>" class="btn btn-sm btn-outline"><?= $this->__('view_details') ?></a>
        </div>
        <div class="table-container" style="margin-top: 12px;">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th><?= $this->__('setup_time_minutes') ?></th>
                        <th><?= $this->__('run_time_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor_cost') ?></th>
                        <th class="text-right"><?= $this->__('overhead_cost') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routingOperations as $op): ?>
                    <tr>
                        <td><?= $this->e($op['operation_number']) ?> - <?= $this->e($op['name']) ?></td>
                        <td><?= $this->e($op['work_center'] ?? '-') ?></td>
                        <td><?= $this->e($op['setup_time_minutes']) ?></td>
                        <td><?= $this->e($op['run_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'] ?? 0, 2) ?></td>
                        <td class="text-right"><?= number_format($op['overhead_cost'] ?? 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted"><?= $this->__('no_active_routing') ?></p>
        <?php if ($this->can('catalog.detail_routing.create')): ?>
        <a href="/catalog/detail-routing/create?detail_id=<?= $detail['id'] ?>" class="btn btn-primary btn-sm">+ <?= $this->__('create_detail_routing') ?></a>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.detail-row { padding: 10px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
</style>

<?php $this->endSection(); ?>
