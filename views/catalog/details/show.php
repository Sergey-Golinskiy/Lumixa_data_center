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
        <div class="detail-row">
            <strong><?= $this->__('material') ?>:</strong>
            <?php if (!empty($detail['material_item_id'])): ?>
            <?= $this->e($detail['material_sku'] ?? '') ?> <?= !empty($detail['material_name']) ? ' - ' . $this->e($detail['material_name']) : '' ?>
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

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.detail-row { padding: 10px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
</style>

<?php $this->endSection(); ?>
