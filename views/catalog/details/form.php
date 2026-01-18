<?php $this->section('content'); ?>

<div class="card" style="max-width: 900px;">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="<?= $detail ? "/catalog/details/{$detail['id']}" : '/catalog/details' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="sku"><?= $this->__('sku') ?> *</label>
                    <input type="text" id="sku" name="sku"
                           value="<?= $this->e($this->old('sku', $detail['sku'] ?? '')) ?>"
                           required maxlength="50" placeholder="<?= $this->__('placeholder_sku') ?>">
                    <?php if ($this->hasError('sku')): ?>
                    <span class="error"><?= $this->e($this->error('sku')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="detail_type"><?= $this->__('detail_type') ?> *</label>
                    <select id="detail_type" name="detail_type" required>
                        <option value=""><?= $this->__('select_type') ?></option>
                        <option value="purchased" <?= $this->old('detail_type', $detail['detail_type'] ?? '') === 'purchased' ? 'selected' : '' ?>>
                            <?= $this->__('detail_type_purchased') ?>
                        </option>
                        <option value="printed" <?= $this->old('detail_type', $detail['detail_type'] ?? '') === 'printed' ? 'selected' : '' ?>>
                            <?= $this->__('detail_type_printed') ?>
                        </option>
                    </select>
                    <?php if ($this->hasError('detail_type')): ?>
                    <span class="error"><?= $this->e($this->error('detail_type')) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> *</label>
                <input type="text" id="name" name="name"
                       value="<?= $this->e($this->old('name', $detail['name'] ?? '')) ?>"
                       required maxlength="255" placeholder="<?= $this->__('placeholder_name') ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->e($this->error('name')) ?></span>
                <?php endif; ?>
            </div>

            <div class="detail-printing-fields">
                <div class="form-row">
                    <div class="form-group">
                        <label for="material_item_id"><?= $this->__('material') ?></label>
                        <select id="material_item_id" name="material_item_id">
                            <option value=""><?= $this->__('select_material') ?></option>
                            <?php foreach ($materials as $material): ?>
                            <option value="<?= $material['id'] ?>" <?= (string)$this->old('material_item_id', $detail['material_item_id'] ?? '') === (string)$material['id'] ? 'selected' : '' ?>>
                                <?= $this->e($material['sku']) ?> - <?= $this->e($material['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($this->hasError('material_item_id')): ?>
                        <span class="error"><?= $this->e($this->error('material_item_id')) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="printer_id"><?= $this->__('printer') ?></label>
                        <select id="printer_id" name="printer_id">
                            <option value=""><?= $this->__('select_printer') ?></option>
                            <?php foreach ($printers as $printer): ?>
                            <option value="<?= $printer['id'] ?>" <?= (string)$this->old('printer_id', $detail['printer_id'] ?? '') === (string)$printer['id'] ? 'selected' : '' ?>>
                                <?= $this->e($printer['name']) ?><?= $printer['model'] ? ' - ' . $this->e($printer['model']) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($this->hasError('printer_id')): ?>
                        <span class="error"><?= $this->e($this->error('printer_id')) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="material_qty_grams"><?= $this->__('material_qty_grams') ?></label>
                        <input type="number" id="material_qty_grams" name="material_qty_grams" step="0.01"
                               value="<?= $this->e($this->old('material_qty_grams', $detail['material_qty_grams'] ?? 0)) ?>">
                        <?php if ($this->hasError('material_qty_grams')): ?>
                        <span class="error"><?= $this->e($this->error('material_qty_grams')) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="print_time_minutes"><?= $this->__('print_time_minutes') ?></label>
                        <input type="number" id="print_time_minutes" name="print_time_minutes" step="1" min="0"
                               value="<?= $this->e($this->old('print_time_minutes', $detail['print_time_minutes'] ?? 0)) ?>">
                        <?php if ($this->hasError('print_time_minutes')): ?>
                        <span class="error"><?= $this->e($this->error('print_time_minutes')) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="print_parameters"><?= $this->__('print_parameters') ?></label>
                    <textarea id="print_parameters" name="print_parameters" rows="3"><?= $this->e($this->old('print_parameters', $detail['print_parameters'] ?? '')) ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="image"><?= $this->__('photo') ?></label>
                <?php if (!empty($detail['image_path'])): ?>
                <div class="form-image-preview">
                    <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small class="text-muted"><?= $this->__('upload_photo') ?></small>
            </div>

            <div class="form-group">
                <label for="model_file"><?= $this->__('model_file') ?></label>
                <?php if (!empty($detail['model_path'])): ?>
                <div class="form-image-preview">
                    <a href="/<?= $this->e(ltrim($detail['model_path'], '/')) ?>" target="_blank" rel="noopener">
                        <?= $this->__('download_model') ?>
                    </a>
                </div>
                <?php endif; ?>
                <input type="file" id="model_file" name="model_file" accept=".stl,.step,.stp">
                <small class="text-muted"><?= $this->__('upload_model_file') ?></small>
            </div>

            <?php if ($detail): ?>
            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?= !empty($detail['is_active']) ? 'checked' : '' ?>>
                    <span><?= $this->__('active') ?></span>
                </label>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $detail ? $this->__('save_changes') : $this->__('create_detail') ?>
                </button>
                <a href="<?= $detail ? "/catalog/details/{$detail['id']}" : '/catalog/details' ?>" class="btn btn-secondary">
                    <?= $this->__('cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('detail_type');
    const printingFields = document.querySelector('.detail-printing-fields');

    const updatePrintingFields = () => {
        const isPrinted = typeSelect?.value === 'printed';
        printingFields?.classList.toggle('is-hidden', !isPrinted);
    };

    typeSelect?.addEventListener('change', updatePrintingFields);
    updatePrintingFields();
});
</script>

<style>
.detail-printing-fields.is-hidden { display: none; }
</style>

<?php $this->endSection(); ?>
