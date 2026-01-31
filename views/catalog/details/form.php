<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="ri-shape-2-line me-2"></i>
                <?= $detail ? $this->__('edit_detail') : $this->__('create_detail') ?>
            </h4>
            <a href="<?= $detail ? "/catalog/details/{$detail['id']}" : '/catalog/details' ?>" class="btn btn-soft-secondary">
                <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back') ?>
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="<?= $detail ? "/catalog/details/{$detail['id']}" : '/catalog/details' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="sku" class="form-label"><?= $this->__('sku') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $this->hasError('sku') ? 'is-invalid' : '' ?>" id="sku" name="sku"
                                   value="<?= $this->e($this->old('sku', $detail['sku'] ?? '')) ?>"
                                   required maxlength="50" placeholder="<?= $this->__('placeholder_sku') ?>">
                            <?php if ($this->hasError('sku')): ?>
                            <div class="invalid-feedback"><?= $this->e($this->error('sku')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="detail_type" class="form-label"><?= $this->__('detail_type') ?> <span class="text-danger">*</span></label>
                            <select class="form-select <?= $this->hasError('detail_type') ? 'is-invalid' : '' ?>" id="detail_type" name="detail_type" required>
                                <option value=""><?= $this->__('select_type') ?></option>
                                <option value="purchased" <?= $this->old('detail_type', $detail['detail_type'] ?? '') === 'purchased' ? 'selected' : '' ?>>
                                    <?= $this->__('detail_type_purchased') ?>
                                </option>
                                <option value="printed" <?= $this->old('detail_type', $detail['detail_type'] ?? '') === 'printed' ? 'selected' : '' ?>>
                                    <?= $this->__('detail_type_printed') ?>
                                </option>
                            </select>
                            <?php if ($this->hasError('detail_type')): ?>
                            <div class="invalid-feedback"><?= $this->e($this->error('detail_type')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $this->hasError('name') ? 'is-invalid' : '' ?>" id="name" name="name"
                                   value="<?= $this->e($this->old('name', $detail['name'] ?? '')) ?>"
                                   required maxlength="255" placeholder="<?= $this->__('placeholder_name') ?>">
                            <?php if ($this->hasError('name')): ?>
                            <div class="invalid-feedback"><?= $this->e($this->error('name')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Printing Fields (shown only for printed type) -->
                    <div class="detail-printing-fields mt-4">
                        <div class="card bg-light border-0">
                            <div class="card-header bg-light">
                                <i class="ri-palette-line me-2"></i>
                                <?= $this->__('materials') ?>
                                <small class="text-muted ms-2">(<?= $this->__('multi_color_printing') ?>)</small>
                            </div>
                            <div class="card-body">
                                <div id="materialsContainer">
                                    <?php
                                    $existingMaterials = $detailMaterials ?? [];
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
                                    <div class="material-row mb-2" data-index="<?= $mi ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col">
                                                <select name="materials[<?= $mi ?>][item_id]" class="form-select material-select">
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
                                            </div>
                                            <div class="col-auto">
                                                <div class="input-group" style="width: 150px;">
                                                    <input type="number" name="materials[<?= $mi ?>][qty_grams]" step="0.01" min="0"
                                                           class="form-control text-end material-qty-input"
                                                           value="<?= $this->e($dm['material_qty_grams'] ?? 0) ?>"
                                                           placeholder="<?= $this->__('grams_short') ?>">
                                                    <span class="input-group-text"><?= $this->__('grams_short') ?></span>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-soft-danger btn-sm remove-material-btn" title="<?= $this->__('delete') ?>">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" id="addMaterialBtn" class="btn btn-soft-primary btn-sm mt-2">
                                    <i class="ri-add-line me-1"></i> <?= $this->__('add_material') ?>
                                </button>
                                <?php if ($this->hasError('material_item_id')): ?>
                                <div class="text-danger mt-2 fs-12"><?= $this->e($this->error('material_item_id')) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="printer_id" class="form-label"><?= $this->__('printer') ?></label>
                                <select class="form-select <?= $this->hasError('printer_id') ? 'is-invalid' : '' ?>" id="printer_id" name="printer_id">
                                    <option value=""><?= $this->__('select_printer') ?></option>
                                    <?php foreach ($printers as $printer): ?>
                                    <option value="<?= $printer['id'] ?>" <?= (string)$this->old('printer_id', $detail['printer_id'] ?? '') === (string)$printer['id'] ? 'selected' : '' ?>>
                                        <?= $this->e($printer['name']) ?><?= $printer['model'] ? ' - ' . $this->e($printer['model']) : '' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($this->hasError('printer_id')): ?>
                                <div class="invalid-feedback"><?= $this->e($this->error('printer_id')) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="print_time_minutes" class="form-label"><?= $this->__('print_time_minutes') ?></label>
                                <div class="input-group">
                                    <input type="number" class="form-control <?= $this->hasError('print_time_minutes') ? 'is-invalid' : '' ?>"
                                           id="print_time_minutes" name="print_time_minutes" step="1" min="0"
                                           value="<?= $this->e($this->old('print_time_minutes', $detail['print_time_minutes'] ?? 0)) ?>">
                                    <span class="input-group-text"><?= $this->__('minutes_short') ?></span>
                                </div>
                                <?php if ($this->hasError('print_time_minutes')): ?>
                                <div class="invalid-feedback"><?= $this->e($this->error('print_time_minutes')) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label for="print_parameters" class="form-label"><?= $this->__('print_parameters') ?></label>
                                <textarea class="form-control" id="print_parameters" name="print_parameters" rows="3"><?= $this->e($this->old('print_parameters', $detail['print_parameters'] ?? '')) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3 mt-4">
                        <label for="image" class="form-label"><?= $this->__('photo') ?></label>
                        <?php if (!empty($detail['image_path'])): ?>
                        <div class="mb-2">
                            <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>"
                                 class="img-thumbnail" style="max-height: 120px;" data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text"><?= $this->__('upload_photo') ?></div>
                    </div>

                    <!-- Model File Upload -->
                    <div class="mb-3">
                        <label for="model_file" class="form-label"><?= $this->__('model_file') ?></label>
                        <?php if (!empty($detail['model_path'])): ?>
                        <div class="mb-2">
                            <a href="/<?= $this->e(ltrim($detail['model_path'], '/')) ?>" target="_blank" rel="noopener" class="btn btn-soft-info btn-sm">
                                <i class="ri-download-2-line me-1"></i> <?= $this->__('download_model') ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="model_file" name="model_file" accept=".stl,.step,.stp">
                        <div class="form-text"><?= $this->__('upload_model_file') ?></div>
                    </div>

                    <!-- Active Status -->
                    <?php if ($detail): ?>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= !empty($detail['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Form Actions -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $detail ? $this->__('save_changes') : $this->__('create_detail') ?>
                        </button>
                        <a href="<?= $detail ? "/catalog/details/{$detail['id']}" : '/catalog/details' ?>" class="btn btn-soft-secondary">
                            <?= $this->__('cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('detail_type');
    const printingFields = document.querySelector('.detail-printing-fields');

    const updatePrintingFields = () => {
        const isPrinted = typeSelect?.value === 'printed';
        printingFields?.classList.toggle('d-none', !isPrinted);
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
        row.className = 'material-row mb-2';
        row.dataset.index = materialIndex;
        row.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col">
                    <select name="materials[${materialIndex}][item_id]" class="form-select material-select">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="col-auto">
                    <div class="input-group" style="width: 150px;">
                        <input type="number" name="materials[${materialIndex}][qty_grams]" step="0.01" min="0"
                               class="form-control text-end material-qty-input" value="0"
                               placeholder="<?= $this->__('grams_short') ?>">
                        <span class="input-group-text"><?= $this->__('grams_short') ?></span>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-soft-danger btn-sm remove-material-btn" title="<?= $this->__('delete') ?>">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
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

<?php $this->endSection(); ?>
