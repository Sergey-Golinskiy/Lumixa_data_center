<?php $this->section('content'); ?>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="<?= $item ? "/warehouse/items/{$item['id']}" : '/warehouse/items' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="type"><?= $this->__('type') ?> *</label>
                    <select id="type" name="type" required <?= $item ? 'disabled' : '' ?>>
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
                    <div id="type-description" style="margin-top: 0.5rem; padding: 0.75rem; background: #f8f9fa; border-radius: 4px; display: none;">
                        <small class="text-muted" style="line-height: 1.5;"></small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sku"><?= $this->__('sku') ?> *</label>
                    <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                        <div style="flex: 1;">
                            <input type="text" id="sku" name="sku"
                                   value="<?= $this->e($this->old('sku', $item['sku'] ?? '')) ?>"
                                   required maxlength="50" placeholder="<?= $this->__('placeholder_sku') ?>"
                                   <?= $item ? 'readonly' : '' ?>>
                            <div id="sku-feedback" style="margin-top: 0.25rem; font-size: 0.875rem;"></div>
                        </div>
                        <?php if (!$item): ?>
                        <button type="button" id="generate-sku-btn" class="btn btn-secondary" style="white-space: nowrap;" disabled>
                            <i class="fas fa-sync"></i> <?= $this->__('generate') ?>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($this->hasError('sku')): ?>
                    <span class="error"><?= $this->e($this->error('sku')) ?></span>
                    <?php endif; ?>
                    <small class="text-muted" id="sku-hint"></small>
                </div>
            </div>

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> *</label>
                <input type="text" id="name" name="name"
                       value="<?= $this->e($this->old('name', $item['name'] ?? '')) ?>"
                       required maxlength="255" placeholder="<?= $this->__('placeholder_name') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="unit"><?= $this->__('unit') ?> *</label>
                    <select id="unit" name="unit" required>
                        <?php foreach ($units as $value => $label): ?>
                        <option value="<?= $this->e($value) ?>"
                                <?= $this->old('unit', $item['unit'] ?? 'pcs') === $value ? 'selected' : '' ?>>
                            <?= $this->e($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="min_stock"><?= $this->__('min_stock') ?></label>
                    <input type="number" id="min_stock" name="min_stock" step="0.01"
                           value="<?= $this->e($this->old('min_stock', $item['min_stock'] ?? 0)) ?>">
                </div>

                <div class="form-group">
                    <label for="reorder_point"><?= $this->__('reorder_point') ?></label>
                    <input type="number" id="reorder_point" name="reorder_point" step="0.01"
                           value="<?= $this->e($this->old('reorder_point', $item['reorder_point'] ?? 0)) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="costing_method"><?= $this->__('costing_method') ?> *</label>
                    <select id="costing_method" name="costing_method" required>
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
                        <option value="<?= $this->e($value) ?>"
                                <?= $selectedMethod === $value ? 'selected' : '' ?>>
                            <?= $this->e($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted"><?= $this->__('costing_method_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="allow_method_override">
                        <input type="checkbox" id="allow_method_override" name="allow_method_override" value="1"
                               <?= $this->old('allow_method_override', $item['allow_method_override'] ?? 1) ? 'checked' : '' ?>>
                        <?= $this->__('allow_method_override') ?>
                    </label>
                    <small class="text-muted"><?= $this->__('allow_method_override_help') ?></small>
                </div>
            </div>

            <div class="form-group">
                <label for="description"><?= $this->__('description') ?></label>
                <textarea id="description" name="description" rows="3"><?= $this->e($this->old('description', $item['description'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label for="image"><?= $this->__('photo') ?></label>
                <?php if (!empty($item['image_path'])): ?>
                <div class="form-image-preview">
                    <img src="/<?= $this->e(ltrim($item['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($item['image_path'], '/')) ?>">
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small class="text-muted"><?= $this->__('upload_photo') ?></small>
            </div>

            <hr>
            <div id="material-attributes" class="item-attributes-section">
                <h3><?= $this->__('attributes_materials') ?></h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="attr_material"><?= $this->__('material') ?></label>
                        <select id="attr_material" name="attr_material">
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

                    <div class="form-group">
                        <label for="attr_manufacturer"><?= $this->__('manufacturer') ?></label>
                        <select id="attr_manufacturer" name="attr_manufacturer">
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
                    <div class="form-row">
                        <div class="form-group">
                            <label for="attr_plastic_type"><?= $this->__('plastic_type') ?></label>
                            <select id="attr_plastic_type" name="attr_plastic_type">
                                <option value=""><?= $this->__('select_plastic_type') ?></option>
                                <?php foreach (($plasticTypeOptions ?? []) as $option): ?>
                                <option value="<?= $this->e($option['name']) ?>"
                                        <?= $this->old('attr_plastic_type', $attributes['plastic_type'] ?? '') === $option['name'] ? 'selected' : '' ?>>
                                    <?= $this->e($option['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="attr_filament_color"><?= $this->__('filament_color') ?></label>
                            <input type="text" id="attr_filament_color" name="attr_filament_color"
                                   value="<?= $this->e($this->old('attr_filament_color', $attributes['filament_color'] ?? '')) ?>"
                                   placeholder="<?= $this->__('placeholder_color') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="attr_filament_diameter"><?= $this->__('filament_diameter') ?></label>
                            <select id="attr_filament_diameter" name="attr_filament_diameter">
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

                        <div class="form-group">
                            <label for="attr_filament_alias"><?= $this->__('filament_alias') ?></label>
                            <select id="attr_filament_alias" name="attr_filament_alias">
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

            <div id="non-material-attributes" class="item-attributes-section">
                <h3><?= $this->__('attributes') ?></h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="attr_color"><?= $this->__('color') ?></label>
                        <input type="text" id="attr_color" name="attr_color"
                               value="<?= $this->e($this->old('attr_color', $attributes['color'] ?? '')) ?>"
                               placeholder="<?= $this->__('placeholder_color') ?>">
                    </div>

                    <div class="form-group">
                        <label for="attr_diameter"><?= $this->__('diameter') ?></label>
                        <input type="text" id="attr_diameter" name="attr_diameter"
                               value="<?= $this->e($this->old('attr_diameter', $attributes['diameter'] ?? '')) ?>"
                               placeholder="<?= $this->__('placeholder_diameter') ?>">
                    </div>

                    <div class="form-group">
                        <label for="attr_brand"><?= $this->__('brand') ?></label>
                        <input type="text" id="attr_brand" name="attr_brand"
                               value="<?= $this->e($this->old('attr_brand', $attributes['brand'] ?? '')) ?>"
                               placeholder="<?= $this->__('placeholder_brand') ?>">
                    </div>
                </div>
            </div>

            <?php if ($item): ?>
            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="is_active" value="1"
                           <?= $item['is_active'] ? 'checked' : '' ?>>
                    <span><?= $this->__('active') ?></span>
                </label>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $item ? $this->__('save_changes') : $this->__('create_item') ?>
                </button>
                <a href="<?= $item ? "/warehouse/items/{$item['id']}" : '/warehouse/items' ?>" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
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

    // Types that require manual SKU input (none - parts moved to catalog)
    const manualSkuTypes = [];

    // Types that auto-generate SKU
    const autoSkuTypes = ['material', 'component', 'consumable', 'packaging', 'hardware'];

    // Type descriptions
    const typeDescriptions = {
        'material': '<?= $this->__('item_type_material_desc') ?>',
        'component': '<?= $this->__('item_type_component_desc') ?>',
        'consumable': '<?= $this->__('item_type_consumable_desc') ?>',
        'packaging': '<?= $this->__('item_type_packaging_desc') ?>',
        'hardware': '<?= $this->__('item_type_hardware_desc') ?>'
    };

    let skuCheckTimeout = null;

    const updateFilamentSection = () => {
        if (!materialSelect || !filamentSection) {
            return;
        }
        const selectedOption = materialSelect.options[materialSelect.selectedIndex];
        const isFilament = selectedOption?.dataset?.filament === '1';
        filamentSection.classList.toggle('is-hidden', !isFilament);
    };

    const updateTypeSections = () => {
        const isMaterial = typeSelect?.value === 'material';
        materialSection?.classList.toggle('is-hidden', !isMaterial);
        nonMaterialSection?.classList.toggle('is-hidden', isMaterial);
        updateFilamentSection();
    };

    const setSkuFeedback = (message, type) => {
        if (!skuFeedback) return;
        skuFeedback.textContent = message;
        skuFeedback.className = '';
        if (type === 'success') {
            skuFeedback.style.color = '#10B981';
        } else if (type === 'error') {
            skuFeedback.style.color = '#EF4444';
        } else if (type === 'info') {
            skuFeedback.style.color = '#3B82F6';
        } else {
            skuFeedback.style.color = '';
        }
    };

    const checkSkuUniqueness = async (sku) => {
        if (!sku || sku.length < 3) {
            setSkuFeedback('', '');
            return;
        }

        setSkuFeedback('<?= $this->__('checking') ?>...', 'info');

        try {
            const url = `/warehouse/api/items/check-sku?sku=${encodeURIComponent(sku)}${itemId ? '&exclude_id=' + itemId : ''}`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.unique) {
                setSkuFeedback('✓ <?= $this->__('sku_available') ?>', 'success');
            } else {
                setSkuFeedback('✗ <?= $this->__('sku_exists') ?>', 'error');
            }
        } catch (error) {
            console.error('SKU check error:', error);
            setSkuFeedback('<?= $this->__('check_failed') ?>', 'error');
        }
    };

    const generateSku = async () => {
        const type = typeSelect?.value;
        if (!type) return;

        if (manualSkuTypes.includes(type)) {
            return;
        }

        if (generateBtn) {
            generateBtn.disabled = true;
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= $this->__('generating') ?>...';
        }

        try {
            const response = await fetch(`/warehouse/api/items/generate-sku?type=${encodeURIComponent(type)}`);
            const data = await response.json();

            if (data.sku) {
                skuInput.value = data.sku;
                setSkuFeedback('✓ <?= $this->__('sku_generated') ?>', 'success');
            } else {
                setSkuFeedback('✗ <?= $this->__('generation_failed') ?>', 'error');
            }
        } catch (error) {
            console.error('SKU generation error:', error);
            setSkuFeedback('✗ <?= $this->__('generation_failed') ?>', 'error');
        } finally {
            if (generateBtn) {
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fas fa-sync"></i> <?= $this->__('generate') ?>';
            }
        }
    };

    const updateTypeDescription = () => {
        const type = typeSelect?.value;

        if (!type || !typeDescription) {
            if (typeDescription) typeDescription.style.display = 'none';
            return;
        }

        const description = typeDescriptions[type];
        if (description) {
            typeDescription.querySelector('small').textContent = description;
            typeDescription.style.display = 'block';
        } else {
            typeDescription.style.display = 'none';
        }
    };

    const updateSkuField = () => {
        const type = typeSelect?.value;

        if (!type) {
            if (skuHint) skuHint.textContent = '';
            if (skuInput && !isEditMode) {
                skuInput.readOnly = true;
                skuInput.placeholder = '<?= $this->__('select_type_first') ?>';
            }
            if (generateBtn) generateBtn.disabled = true;
            return;
        }

        if (manualSkuTypes.includes(type)) {
            // Manual SKU input (for parts/details)
            if (skuHint) skuHint.textContent = '<?= $this->__('sku_manual_hint') ?>';
            if (skuInput && !isEditMode) {
                skuInput.readOnly = false;
                skuInput.placeholder = '<?= $this->__('enter_sku_manually') ?>';
                skuInput.value = '';
            }
            if (generateBtn) generateBtn.style.display = 'none';
            setSkuFeedback('', '');
        } else if (autoSkuTypes.includes(type)) {
            // Auto-generate SKU
            if (skuHint) {
                const formats = {
                    'material': 'LX-MAT-xxxxx',
                    'component': 'LX-CMP-xxxxx',
                    'consumable': 'LX-CSM-xxxxx',
                    'packaging': 'LX-PKG-xxxxx',
                    'hardware': 'LX-HRD-xxxxx'
                };
                const format = formats[type] || 'LX-xxxxx';
                skuHint.textContent = `<?= $this->__('sku_auto_format') ?>: ${format}`;
            }
            if (skuInput && !isEditMode) {
                skuInput.readOnly = true;
                skuInput.placeholder = '<?= $this->__('will_be_generated') ?>';
            }
            if (generateBtn) {
                generateBtn.style.display = 'block';
                generateBtn.disabled = false;
            }

            // Auto-generate on type select if field is empty
            if (!isEditMode && skuInput && !skuInput.value) {
                generateSku();
            }
        }
    };

    // Event Listeners
    if (typeSelect && !isEditMode) {
        typeSelect.addEventListener('change', () => {
            updateTypeDescription();
            updateTypeSections();
            updateSkuField();
        });
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', generateSku);
    }

    if (skuInput && !isEditMode) {
        skuInput.addEventListener('input', () => {
            const type = typeSelect?.value;
            // Only check uniqueness for manually entered SKUs
            if (type && manualSkuTypes.includes(type)) {
                clearTimeout(skuCheckTimeout);
                skuCheckTimeout = setTimeout(() => {
                    checkSkuUniqueness(skuInput.value);
                }, 500);
            }
        });
    }

    materialSelect?.addEventListener('change', updateFilamentSection);

    // Initialize
    updateTypeDescription();
    updateTypeSections();
    if (!isEditMode) {
        updateSkuField();
    }
});
</script>

<style>
.item-attributes-section.is-hidden { display: none; }
</style>

<?php $this->endSection(); ?>
