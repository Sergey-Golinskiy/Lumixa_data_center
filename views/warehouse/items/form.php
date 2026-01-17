<?php $this->section('content'); ?>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="<?= $item ? "/warehouse/items/{$item['id']}" : '/warehouse/items' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="sku"><?= $this->__('sku') ?> *</label>
                    <input type="text" id="sku" name="sku"
                           value="<?= $this->e($this->old('sku', $item['sku'] ?? '')) ?>"
                           required maxlength="50" placeholder="<?= $this->__('placeholder_sku') ?>">
                    <?php if ($this->hasError('sku')): ?>
                    <span class="error"><?= $this->e($this->error('sku')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="type"><?= $this->__('type') ?> *</label>
                    <select id="type" name="type" required>
                        <option value=""><?= $this->__('select_type') ?></option>
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
            <h3><?= $this->__('attributes_materials') ?></h3>

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

<?php $this->endSection(); ?>
