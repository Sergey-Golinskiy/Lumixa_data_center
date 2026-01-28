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
                <!-- Multi-material section -->
                <div class="form-group">
                    <label><?= $this->__('materials') ?> <small class="text-muted">(<?= $this->__('multi_color_printing') ?>)</small></label>
                    <div id="materialsContainer">
                        <?php
                        $existingMaterials = $detailMaterials ?? [];
                        // Fall back to legacy single material if no multi-material data
                        if (empty($existingMaterials) && !empty($detail['material_item_id'])) {
                            $existingMaterials = [[
                                'material_item_id' => $detail['material_item_id'],
                                'material_qty_grams' => $detail['material_qty_grams'] ?? 0
                            ]];
                        }
                        if (empty($existingMaterials)) {
                            $existingMaterials = [['material_item_id' => '', 'material_qty_grams' => 0]];
                        }
                        foreach ($existingMaterials as $mi => $dm):
                        ?>
                        <div class="material-row" data-index="<?= $mi ?>">
                            <div class="material-row-fields">
                                <select name="materials[<?= $mi ?>][item_id]" class="material-select">
                                    <option value=""><?= $this->__('select_material') ?></option>
                                    <?php foreach ($materials as $material): ?>
                                    <?php
                                    $materialInfo = [];
                                    if (!empty($material['manufacturer'])) $materialInfo[] = $material['manufacturer'];
                                    if (!empty($material['plastic_type'])) $materialInfo[] = $material['plastic_type'];
                                    if (!empty($material['color'])) $materialInfo[] = $material['color'];
                                    if (!empty($material['filament_alias'])) $materialInfo[] = $material['filament_alias'];
                                    $line1 = $material['sku'] . ' - ' . $material['name'];
                                    $line2 = !empty($materialInfo) ? '    ' . implode(' | ', $materialInfo) : '';
                                    $optionText = $line2 ? $line1 . "\n" . $line2 : $line1;
                                    ?>
                                    <option value="<?= $material['id'] ?>"
                                            <?= (string)($dm['material_item_id'] ?? '') === (string)$material['id'] ? 'selected' : '' ?>>
<?= $this->e($optionText) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="material-qty-group">
                                    <input type="number" name="materials[<?= $mi ?>][qty_grams]" step="0.01" min="0"
                                           value="<?= $this->e($dm['material_qty_grams'] ?? 0) ?>"
                                           placeholder="<?= $this->__('grams_short') ?>" class="material-qty-input">
                                    <span class="input-suffix"><?= $this->__('grams_short') ?></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger remove-material-btn" title="<?= $this->__('delete') ?>">&times;</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="addMaterialBtn" class="btn btn-sm btn-outline" style="margin-top: 8px;">
                        + <?= $this->__('add_material') ?>
                    </button>
                    <?php if ($this->hasError('material_item_id')): ?>
                    <span class="error"><?= $this->e($this->error('material_item_id')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
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

    // Multi-material management
    const container = document.getElementById('materialsContainer');
    const addBtn = document.getElementById('addMaterialBtn');
    let materialIndex = container.querySelectorAll('.material-row').length;

    // Build options HTML from first select (template)
    const firstSelect = container.querySelector('.material-select');
    const optionsHtml = firstSelect ? firstSelect.innerHTML : '<option value="">-</option>';

    addBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'material-row';
        row.dataset.index = materialIndex;
        row.innerHTML = `
            <div class="material-row-fields">
                <select name="materials[${materialIndex}][item_id]" class="material-select">
                    ${optionsHtml}
                </select>
                <div class="material-qty-group">
                    <input type="number" name="materials[${materialIndex}][qty_grams]" step="0.01" min="0"
                           value="0" placeholder="<?= $this->__('grams_short') ?>" class="material-qty-input">
                    <span class="input-suffix"><?= $this->__('grams_short') ?></span>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-material-btn" title="<?= $this->__('delete') ?>">&times;</button>
            </div>
        `;
        container.appendChild(row);
        materialIndex++;
        updateRemoveButtons();
    });

    container.addEventListener('click', (e) => {
        if (e.target.closest('.remove-material-btn')) {
            const row = e.target.closest('.material-row');
            if (container.querySelectorAll('.material-row').length > 1) {
                row.remove();
            }
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.material-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-material-btn');
            if (btn) btn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
        });
    }

    updateRemoveButtons();
});
</script>

<style>
.detail-printing-fields.is-hidden { display: none; }

.material-select {
    font-size: 13px;
    max-width: 100%;
    font-family: monospace;
    line-height: 1.6;
}

.material-select option {
    padding: 8px 4px;
    line-height: 1.6;
    white-space: pre-wrap;
    font-family: monospace;
}

#materialsContainer {
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 8px;
    padding: 12px;
    background: var(--bg-secondary, #f8f9fa);
}

.material-row {
    margin-bottom: 8px;
}

.material-row:last-child {
    margin-bottom: 0;
}

.material-row-fields {
    display: flex;
    gap: 10px;
    align-items: center;
}

.material-row-fields .material-select {
    flex: 1;
    min-width: 0;
}

.material-qty-group {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

.material-qty-input {
    width: 100px;
    text-align: right;
}

.input-suffix {
    font-size: 0.85rem;
    color: var(--text-muted, #6b7280);
    white-space: nowrap;
}

.remove-material-btn {
    flex-shrink: 0;
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    line-height: 1;
}
</style>

<?php $this->endSection(); ?>
