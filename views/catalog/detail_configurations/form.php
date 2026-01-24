<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/details/<?= $detail['id'] ?>/configurations" class="btn btn-secondary">
        &laquo; <?= $this->__('back') ?>
    </a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <?= $configuration ? $this->__('edit_configuration') : $this->__('create_configuration') ?>
    </div>
    <div class="card-body">
        <div class="detail-info" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <strong><?= $this->__('base_detail') ?>:</strong> <?= $this->e($detail['name']) ?> (<?= $this->e($detail['sku']) ?>)
        </div>

        <form method="POST" enctype="multipart/form-data"
              action="<?= $configuration ? "/catalog/details/{$detail['id']}/configurations/{$configuration['id']}" : "/catalog/details/{$detail['id']}/configurations" ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="sku"><?= $this->__('configuration_sku') ?> *</label>
                    <input type="text" id="sku" name="sku" required
                           value="<?= $this->e($configuration['sku'] ?? $this->old('sku')) ?>"
                           placeholder="<?= $this->e($detail['sku']) ?>-01" style="text-transform: uppercase;">
                    <?php if ($this->hasError('sku')): ?>
                    <span class="error"><?= $this->error('sku') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="material_id"><?= $this->__('material') ?></label>
                    <select id="material_id" name="material_id">
                        <option value=""><?= $this->__('select_material') ?></option>
                        <?php foreach ($materials as $material): ?>
                        <option value="<?= $material['id'] ?>"
                                <?= ($configuration['material_id'] ?? $this->old('material_id')) == $material['id'] ? 'selected' : '' ?>>
                            <?= $this->e($material['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($this->hasError('material_id')): ?>
                    <span class="error"><?= $this->error('material_id') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="name"><?= $this->__('configuration_name') ?> *</label>
                <input type="text" id="name" name="name" required
                       value="<?= $this->e($configuration['name'] ?? $this->old('name')) ?>"
                       placeholder="<?= $this->__('full_product_name') ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="material_color"><?= $this->__('material_color') ?></label>
                <input type="text" id="material_color" name="material_color"
                       value="<?= $this->e($configuration['material_color'] ?? $this->old('material_color')) ?>"
                       placeholder="<?= $this->__('placeholder_color') ?>">
                <?php if ($this->hasError('material_color')): ?>
                <span class="error"><?= $this->error('material_color') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image"><?= $this->__('photo') ?></label>
                <?php if (!empty($configuration['image_path'])): ?>
                <div class="form-image-preview">
                    <img src="/<?= $this->e(ltrim($configuration['image_path'], '/')) ?>"
                         alt="<?= $this->__('photo') ?>"
                         class="image-thumb"
                         data-image-preview="/<?= $this->e(ltrim($configuration['image_path'], '/')) ?>">
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small class="text-muted"><?= $this->__('upload_photo') ?></small>
            </div>

            <div class="form-group">
                <label for="notes"><?= $this->__('notes') ?></label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="<?= $this->__('additional_notes') ?>"><?= $this->e($configuration['notes'] ?? $this->old('notes')) ?></textarea>
                <?php if ($this->hasError('notes')): ?>
                <span class="error"><?= $this->error('notes') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($configuration['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $configuration ? $this->__('update_configuration') : $this->__('create_configuration') ?>
                </button>
                <a href="/catalog/details/<?= $detail['id'] ?>/configurations" class="btn btn-secondary">
                    <?= $this->__('cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.form-image-preview {
    margin-bottom: 10px;
}

.image-thumb {
    max-width: 200px;
    max-height: 200px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}
</style>

<?php $this->endSection(); ?>
