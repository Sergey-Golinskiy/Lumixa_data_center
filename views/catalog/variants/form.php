<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/variants" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('variants')]) ?></a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $variant ? $this->__('edit_variant') : $this->__('create_new_variant') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $variant ? "/catalog/variants/{$variant['id']}" : '/catalog/variants' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="product_id"><?= $this->__('product') ?> *</label>
                <?php if ($variant): ?>
                <input type="text" value="<?= $this->e($variant['product_name']) ?>" disabled>
                <input type="hidden" name="product_id" value="<?= $variant['product_id'] ?>">
                <?php else: ?>
                <select id="product_id" name="product_id" required>
                    <option value=""><?= $this->__('select_product') ?></option>
                    <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>" <?= $preselectedProductId == $product['id'] ? 'selected' : '' ?>>
                        <?= $this->e($product['code']) ?> - <?= $this->e($product['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <?php if ($this->hasError('product_id')): ?>
                <span class="error"><?= $this->error('product_id') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sku"><?= $this->__('sku') ?> *</label>
                    <input type="text" id="sku" name="sku" required
                           value="<?= $this->e($variant['sku'] ?? $this->old('sku')) ?>"
                           placeholder="<?= $this->__('sku_placeholder') ?>" style="text-transform: uppercase;">
                    <?php if ($this->hasError('sku')): ?>
                    <span class="error"><?= $this->error('sku') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="base_price"><?= $this->__('base_price') ?></label>
                    <input type="number" id="base_price" name="base_price" step="0.01" min="0"
                           value="<?= $this->e($variant['base_price'] ?? $this->old('base_price', '0.00')) ?>">
                    <?php if ($this->hasError('base_price')): ?>
                    <span class="error"><?= $this->error('base_price') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="name"><?= $this->__('variant_name') ?> *</label>
                <input type="text" id="name" name="name" required
                       value="<?= $this->e($variant['name'] ?? $this->old('name')) ?>"
                       placeholder="<?= $this->__('variant_name_placeholder') ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <!-- Attributes -->
            <div class="form-group">
                <label><?= $this->__('attributes') ?></label>
                <div id="attributes-container">
                    <?php
                    $attrs = [];
                    if ($variant && $variant['attributes']) {
                        $attrs = json_decode($variant['attributes'], true) ?? [];
                    }
                    if (empty($attrs)) {
                        $attrs = ['' => ''];
                    }
                    $i = 0;
                    foreach ($attrs as $key => $value): ?>
                    <div class="attribute-row">
                        <input type="text" name="attr_key[]" placeholder="<?= $this->__('attribute_key_placeholder') ?>" value="<?= $this->e($key) ?>">
                        <input type="text" name="attr_value[]" placeholder="<?= $this->__('attribute_value_placeholder') ?>" value="<?= $this->e($value) ?>">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeAttribute(this)">&times;</button>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline" onclick="addAttribute()">+ <?= $this->__('add_attribute') ?></button>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($variant['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $variant ? $this->__('update_variant') : $this->__('create_variant') ?></button>
                <a href="/catalog/variants" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.attribute-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}
.attribute-row input { flex: 1; }
.attribute-row button { flex-shrink: 0; }
</style>

<script>
function addAttribute() {
    const container = document.getElementById('attributes-container');
    const row = document.createElement('div');
    row.className = 'attribute-row';
    row.innerHTML = `
        <input type="text" name="attr_key[]" placeholder="<?= $this->__('attribute_key_placeholder') ?>">
        <input type="text" name="attr_value[]" placeholder="<?= $this->__('attribute_value_placeholder') ?>">
        <button type="button" class="btn btn-sm btn-danger" onclick="removeAttribute(this)">&times;</button>
    `;
    container.appendChild(row);
}

function removeAttribute(btn) {
    const container = document.getElementById('attributes-container');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    } else {
        // Clear values instead of removing last row
        const inputs = btn.parentElement.querySelectorAll('input');
        inputs.forEach(input => input.value = '');
    }
}
</script>

<?php $this->endSection(); ?>
