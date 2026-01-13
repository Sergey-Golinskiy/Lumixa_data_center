<?php $this->section('content'); ?>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form method="POST" action="<?= $item ? "/warehouse/items/{$item['id']}/edit" : '/warehouse/items/create' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="sku">SKU *</label>
                    <input type="text" id="sku" name="sku"
                           value="<?= $this->e($this->old('sku', $item['sku'] ?? '')) ?>"
                           required maxlength="50" placeholder="e.g., PLA-BLK-001">
                    <?php if ($this->hasError('sku')): ?>
                    <span class="error"><?= $this->e($this->error('sku')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="type">Type *</label>
                    <select id="type" name="type" required>
                        <option value="">Select type...</option>
                        <?php foreach ($types as $value => $label): ?>
                        <option value="<?= $this->e($value) ?>"
                                <?= $this->old('type', $item['type'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= $this->e($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name"
                       value="<?= $this->e($this->old('name', $item['name'] ?? '')) ?>"
                       required maxlength="255" placeholder="Item name">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="unit">Unit *</label>
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
                    <label for="min_stock">Min Stock</label>
                    <input type="number" id="min_stock" name="min_stock" step="0.01"
                           value="<?= $this->e($this->old('min_stock', $item['min_stock'] ?? 0)) ?>">
                </div>

                <div class="form-group">
                    <label for="reorder_point">Reorder Point</label>
                    <input type="number" id="reorder_point" name="reorder_point" step="0.01"
                           value="<?= $this->e($this->old('reorder_point', $item['reorder_point'] ?? 0)) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?= $this->e($this->old('description', $item['description'] ?? '')) ?></textarea>
            </div>

            <hr>
            <h3>Attributes (for Materials)</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="attr_color">Color</label>
                    <input type="text" id="attr_color" name="attr_color"
                           value="<?= $this->e($this->old('attr_color', $attributes['color'] ?? '')) ?>"
                           placeholder="e.g., Black, White">
                </div>

                <div class="form-group">
                    <label for="attr_diameter">Diameter</label>
                    <input type="text" id="attr_diameter" name="attr_diameter"
                           value="<?= $this->e($this->old('attr_diameter', $attributes['diameter'] ?? '')) ?>"
                           placeholder="e.g., 1.75mm">
                </div>

                <div class="form-group">
                    <label for="attr_brand">Brand</label>
                    <input type="text" id="attr_brand" name="attr_brand"
                           value="<?= $this->e($this->old('attr_brand', $attributes['brand'] ?? '')) ?>"
                           placeholder="e.g., Polymaker">
                </div>
            </div>

            <?php if ($item): ?>
            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="is_active" value="1"
                           <?= $item['is_active'] ? 'checked' : '' ?>>
                    <span>Active</span>
                </label>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $item ? 'Save Changes' : 'Create Item' ?>
                </button>
                <a href="<?= $item ? "/warehouse/items/{$item['id']}" : '/warehouse/items' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
