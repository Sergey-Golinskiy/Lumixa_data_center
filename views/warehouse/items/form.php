<?php $this->section('content'); ?>

<!-- Back button -->
<div class="mb-3">
    <a href="<?= $item ? "/warehouse/items/{$item['id']}" : '/warehouse/items' ?>" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back') ?>
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $item ? $this->__('edit_item') : $this->__('new_item') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="<?= $item ? "/warehouse/items/{$item['id']}" : '/warehouse/items' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label"><?= $this->__('type') ?> <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select" required <?= $item ? 'disabled' : '' ?>>
                                <option value=""><?= $this->__('select_type') ?></option>
                                <?php foreach ($types as $value => $label): ?>
                                <option value="<?= $this->e($value) ?>"
                                        <?= $this->old('type', $item['type'] ?? '') === $value ? 'selected' : '' ?>>
                                    <?= $this->e($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($item): ?>
                            <input type="hidden" name="type" value="<?= $this->e($item['type']) ?>">
                            <?php endif; ?>
                            <div id="type-description" class="form-text bg-light p-2 rounded mt-2" style="display: none;">
                                <small class="text-muted"></small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label"><?= $this->__('sku') ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="sku" name="sku" class="form-control <?= $this->hasError('sku') ? 'is-invalid' : '' ?>"
                                       value="<?= $this->e($this->old('sku', $item['sku'] ?? '')) ?>"
                                       required maxlength="50" placeholder="<?= $this->__('placeholder_sku') ?>"
                                       <?= $item ? 'readonly' : '' ?>>
                                <?php if (!$item): ?>
                                <button type="button" id="generate-sku-btn" class="btn btn-soft-primary" disabled>
                                    <i class="ri-refresh-line"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($this->hasError('sku')): ?>
                                <div class="invalid-feedback"><?= $this->e($this->error('sku')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div id="sku-feedback" class="form-text"></div>
                            <small class="text-muted" id="sku-hint"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?= $this->e($this->old('name', $item['name'] ?? '')) ?>"
                               required maxlength="255" placeholder="<?= $this->__('placeholder_name') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="unit" class="form-label"><?= $this->__('unit') ?> <span class="text-danger">*</span></label>
                            <select id="unit" name="unit" class="form-select" required>
                                <?php foreach ($units as $value => $label): ?>
                                <option value="<?= $this->e($value) ?>"
                                        <?= $this->old('unit', $item['unit'] ?? 'pcs') === $value ? 'selected' : '' ?>>
                                    <?= $this->e($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="min_stock" class="form-label"><?= $this->__('min_stock') ?></label>
                            <input type="number" id="min_stock" name="min_stock" class="form-control" step="0.01"
                                   value="<?= $this->e($this->old('min_stock', $item['min_stock'] ?? 0)) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="reorder_point" class="form-label"><?= $this->__('reorder_point') ?></label>
                            <input type="number" id="reorder_point" name="reorder_point" class="form-control" step="0.01"
                                   value="<?= $this->e($this->old('reorder_point', $item['reorder_point'] ?? 0)) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="costing_method" class="form-label"><?= $this->__('costing_method') ?> <span class="text-danger">*</span></label>
                            <select id="costing_method" name="costing_method" class="form-select" required>
                                <?php
                                $costingMethods = [
                                    'FIFO' => $this->__('costing_method_fifo'),
                                    'LIFO' => $this->__('costing_method_lifo'),
                                    'WEIGHTED_AVG' => $this->__('costing_method_weighted_avg'),
                                    'MANUAL' => $this->__('costing_method_manual')
                                ];
                                $selectedMethod = $this->old('costing_method', $item['costing_method'] ?? 'FIFO');
                                ?>
                                <?php foreach ($costingMethods as $value => $label): ?>
                                <option value="<?= $this->e($value) ?>" <?= $selectedMethod === $value ? 'selected' : '' ?>>
                                    <?= $this->e($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted"><?= $this->__('costing_method_help') ?></small>
                        </div>

                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" id="allow_method_override" name="allow_method_override" class="form-check-input" value="1"
                                       <?= $this->old('allow_method_override', $item['allow_method_override'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="allow_method_override"><?= $this->__('allow_method_override') ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label"><?= $this->__('description') ?></label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?= $this->e($this->old('description', $item['description'] ?? '')) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label"><?= $this->__('photo') ?></label>
                        <?php if (!empty($item['image_path'])): ?>
                        <div class="mb-2">
                            <div class="avatar-lg bg-light rounded p-1">
                                <img src="/<?= $this->e(ltrim($item['image_path'], '/')) ?>" alt="" class="img-fluid rounded" data-image-preview="/<?= $this->e(ltrim($item['image_path'], '/')) ?>">
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <small class="text-muted"><?= $this->__('upload_photo') ?></small>
                    </div>

                    <hr class="my-4">

                    <!-- Material Attributes Section -->
                    <div id="material-attributes" class="item-attributes-section">
                        <h5 class="mb-3"><i class="ri-flask-line me-2"></i><?= $this->__('attributes_materials') ?></h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="attr_material" class="form-label"><?= $this->__('material') ?></label>
                                <select id="attr_material" name="attr_material" class="form-select">
                                    <option value=""><?= $this->__('select_material') ?></option>
                                    <?php foreach (($materialOptions ?? []) as $option): ?>
                                    <option value="<?= $this->e($option['name']) ?>"
                                            data-filament="<?= $option['is_filament'] ? '1' : '0' ?>"
                                            <?= $this->old('attr_material', $attributes['material'] ?? '') === $option['name'] ? 'selected' : '' ?>>
                                        <?= $this->e($option['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="attr_manufacturer" class="form-label"><?= $this->__('manufacturer') ?></label>
                                <select id="attr_manufacturer" name="attr_manufacturer" class="form-select">
                                    <option value=""><?= $this->__('select_manufacturer') ?></option>
                                    <?php foreach (($manufacturerOptions ?? []) as $option): ?>
                                    <option value="<?= $this->e($option['name']) ?>"
                                            <?= $this->old('attr_manufacturer', $attributes['manufacturer'] ?? '') === $option['name'] ? 'selected' : '' ?>>
                                        <?= $this->e($option['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="filament-attributes" class="item-attributes-section">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="attr_plastic_type" class="form-label"><?= $this->__('plastic_type') ?></label>
                                    <select id="attr_plastic_type" name="attr_plastic_type" class="form-select">
                                        <option value=""><?= $this->__('select_plastic_type') ?></option>
                                        <?php foreach (($plasticTypeOptions ?? []) as $option): ?>
                                        <option value="<?= $this->e($option['name']) ?>"
                                                <?= $this->old('attr_plastic_type', $attributes['plastic_type'] ?? '') === $option['name'] ? 'selected' : '' ?>>
                                            <?= $this->e($option['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="attr_filament_color" class="form-label"><?= $this->__('filament_color') ?></label>
                                    <input type="text" id="attr_filament_color" name="attr_filament_color" class="form-control"
                                           value="<?= $this->e($this->old('attr_filament_color', $attributes['filament_color'] ?? '')) ?>"
                                           placeholder="<?= $this->__('placeholder_color') ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="attr_filament_diameter" class="form-label"><?= $this->__('filament_diameter') ?></label>
                                    <select id="attr_filament_diameter" name="attr_filament_diameter" class="form-select">
                                        <option value=""><?= $this->__('select_filament_diameter') ?></option>
                                        <?php
                                        $diameters = ['1.75 mm', '2.85 mm', '3.00 mm'];
                                        $selectedDiameter = $this->old('attr_filament_diameter', $attributes['filament_diameter'] ?? '');
                                        ?>
                                        <?php foreach ($diameters as $diameter): ?>
                                        <option value="<?= $this->e($diameter) ?>" <?= $selectedDiameter === $diameter ? 'selected' : '' ?>>
                                            <?= $this->e($diameter) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="attr_filament_alias" class="form-label"><?= $this->__('filament_alias') ?></label>
                                    <select id="attr_filament_alias" name="attr_filament_alias" class="form-select">
                                        <option value=""><?= $this->__('select_filament_alias') ?></option>
                                        <?php foreach (($filamentAliasOptions ?? []) as $option): ?>
                                        <option value="<?= $this->e($option['name']) ?>"
                                                <?= $this->old('attr_filament_alias', $attributes['filament_alias'] ?? '') === $option['name'] ? 'selected' : '' ?>>
                                            <?= $this->e($option['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Material Attributes Section -->
                    <div id="non-material-attributes" class="item-attributes-section">
                        <h5 class="mb-3"><i class="ri-settings-4-line me-2"></i><?= $this->__('attributes') ?></h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="attr_color" class="form-label"><?= $this->__('color') ?></label>
                                <input type="text" id="attr_color" name="attr_color" class="form-control"
                                       value="<?= $this->e($this->old('attr_color', $attributes['color'] ?? '')) ?>"
                                       placeholder="<?= $this->__('placeholder_color') ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="attr_diameter" class="form-label"><?= $this->__('diameter') ?></label>
                                <input type="text" id="attr_diameter" name="attr_diameter" class="form-control"
                                       value="<?= $this->e($this->old('attr_diameter', $attributes['diameter'] ?? '')) ?>"
                                       placeholder="<?= $this->__('placeholder_diameter') ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="attr_brand" class="form-label"><?= $this->__('brand') ?></label>
                                <input type="text" id="attr_brand" name="attr_brand" class="form-control"
                                       value="<?= $this->e($this->old('attr_brand', $attributes['brand'] ?? '')) ?>"
                                       placeholder="<?= $this->__('placeholder_brand') ?>">
                            </div>
                        </div>
                    </div>

                    <?php if ($item): ?>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1"
                                   <?= $item['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $item ? $this->__('save_changes') : $this->__('create_item') ?>
                        </button>
                        <a href="<?= $item ? "/warehouse/items/{$item['id']}" : '/warehouse/items' ?>" class="btn btn-soft-secondary">
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
    const typeSelect = document.getElementById('type');
    const skuInput = document.getElementById('sku');
    const skuFeedback = document.getElementById('sku-feedback');
    const skuHint = document.getElementById('sku-hint');
    const typeDescription = document.getElementById('type-description');
    const generateBtn = document.getElementById('generate-sku-btn');
    const materialSection = document.getElementById('material-attributes');
    const nonMaterialSection = document.getElementById('non-material-attributes');
    const materialSelect = document.getElementById('attr_material');
    const filamentSection = document.getElementById('filament-attributes');

    const isEditMode = <?= $item ? 'true' : 'false' ?>;
    const itemId = <?= $item ? $item['id'] : 'null' ?>;

    const manualSkuTypes = [];
    const autoSkuTypes = ['material', 'component', 'consumable', 'packaging', 'fasteners'];

    const typeDescriptions = {
        'material': '<?= $this->__('item_type_material_desc') ?>',
        'component': '<?= $this->__('item_type_component_desc') ?>',
        'consumable': '<?= $this->__('item_type_consumable_desc') ?>',
        'packaging': '<?= $this->__('item_type_packaging_desc') ?>',
        'fasteners': '<?= $this->__('item_type_fasteners_desc') ?>'
    };

    let skuCheckTimeout = null;

    const updateFilamentSection = () => {
        if (!materialSelect || !filamentSection) return;
        const selectedOption = materialSelect.options[materialSelect.selectedIndex];
        const isFilament = selectedOption?.dataset?.filament === '1';
        filamentSection.classList.toggle('d-none', !isFilament);
    };

    const updateTypeSections = () => {
        const isMaterial = typeSelect?.value === 'material';
        materialSection?.classList.toggle('d-none', !isMaterial);
        nonMaterialSection?.classList.toggle('d-none', isMaterial);
        updateFilamentSection();
    };

    const setSkuFeedback = (message, type) => {
        if (!skuFeedback) return;
        skuFeedback.textContent = message;
        skuFeedback.className = 'form-text';
        if (type === 'success') skuFeedback.classList.add('text-success');
        else if (type === 'error') skuFeedback.classList.add('text-danger');
        else if (type === 'info') skuFeedback.classList.add('text-info');
    };

    const checkSkuUniqueness = async (sku) => {
        if (!sku || sku.length < 3) { setSkuFeedback('', ''); return; }
        setSkuFeedback('<?= $this->__('checking') ?>...', 'info');
        try {
            const url = `/warehouse/api/items/check-sku?sku=${encodeURIComponent(sku)}${itemId ? '&exclude_id=' + itemId : ''}`;
            const response = await fetch(url);
            const data = await response.json();
            if (data.unique) setSkuFeedback('<?= $this->__('sku_available') ?>', 'success');
            else setSkuFeedback('<?= $this->__('sku_exists') ?>', 'error');
        } catch (error) { setSkuFeedback('<?= $this->__('check_failed') ?>', 'error'); }
    };

    const generateSku = async () => {
        const type = typeSelect?.value;
        if (!type || manualSkuTypes.includes(type)) return;
        if (generateBtn) { generateBtn.disabled = true; }
        try {
            const response = await fetch(`/warehouse/api/items/generate-sku?type=${encodeURIComponent(type)}`);
            const data = await response.json();
            if (data.sku) { skuInput.value = data.sku; setSkuFeedback('<?= $this->__('sku_generated') ?>', 'success'); }
            else { setSkuFeedback('<?= $this->__('generation_failed') ?>', 'error'); }
        } catch (error) { setSkuFeedback('<?= $this->__('generation_failed') ?>', 'error'); }
        finally { if (generateBtn) generateBtn.disabled = false; }
    };

    const updateTypeDescription = () => {
        const type = typeSelect?.value;
        if (!type || !typeDescription) { if (typeDescription) typeDescription.style.display = 'none'; return; }
        const description = typeDescriptions[type];
        if (description) { typeDescription.querySelector('small').textContent = description; typeDescription.style.display = 'block'; }
        else { typeDescription.style.display = 'none'; }
    };

    const updateSkuField = () => {
        const type = typeSelect?.value;
        if (!type) {
            if (skuHint) skuHint.textContent = '';
            if (skuInput && !isEditMode) { skuInput.readOnly = true; skuInput.placeholder = '<?= $this->__('select_type_first') ?>'; }
            if (generateBtn) generateBtn.disabled = true;
            return;
        }
        if (autoSkuTypes.includes(type)) {
            const formats = { 'material': 'LX-MAT-xxxxx', 'component': 'LX-CMP-xxxxx', 'consumable': 'LX-CSM-xxxxx', 'packaging': 'LX-PKG-xxxxx', 'fasteners': 'LX-FST-xxxxx' };
            if (skuHint) skuHint.textContent = `<?= $this->__('sku_auto_format') ?>: ${formats[type] || 'LX-xxxxx'}`;
            if (skuInput && !isEditMode) { skuInput.readOnly = true; skuInput.placeholder = '<?= $this->__('will_be_generated') ?>'; }
            if (generateBtn) { generateBtn.style.display = ''; generateBtn.disabled = false; }
            if (!isEditMode && skuInput) generateSku();
        }
    };

    if (typeSelect && !isEditMode) {
        typeSelect.addEventListener('change', () => { updateTypeDescription(); updateTypeSections(); updateSkuField(); });
    }
    if (generateBtn) generateBtn.addEventListener('click', generateSku);
    if (skuInput && !isEditMode) {
        skuInput.addEventListener('input', () => {
            const type = typeSelect?.value;
            if (type && manualSkuTypes.includes(type)) {
                clearTimeout(skuCheckTimeout);
                skuCheckTimeout = setTimeout(() => checkSkuUniqueness(skuInput.value), 500);
            }
        });
    }
    materialSelect?.addEventListener('change', updateFilamentSection);

    updateTypeDescription();
    updateTypeSections();
    if (!isEditMode) updateSkuField();
});
</script>

<style>
.item-attributes-section.d-none { display: none !important; }
</style>

<?php $this->endSection(); ?>
