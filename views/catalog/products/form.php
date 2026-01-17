<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/products" class="btn btn-secondary">&laquo; Back to Products</a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $product ? 'Edit Product' : 'Create New Product' ?></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="<?= $product ? "/catalog/products/{$product['id']}" : '/catalog/products' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="code">Product Code *</label>
                    <input type="text" id="code" name="code" required
                           value="<?= $this->e($product['code'] ?? $this->old('code')) ?>"
                           placeholder="e.g., PROD-001" style="text-transform: uppercase;">
                    <?php if ($this->hasError('code')): ?>
                    <span class="error"><?= $this->error('code') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <?php if (($categoryMode ?? 'table') === 'table'): ?>
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value=""><?= $this->__('select_category') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $this->e($cat['id']) ?>"
                                <?= (string)$this->old('category_id', $product['category_id'] ?? '') === (string)$cat['id'] ? 'selected' : '' ?>>
                            <?= $this->e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($categories)): ?>
                    <small class="text-muted">
                        <?= $this->__('no_categories_hint') ?>
                        <a href="/admin/product-categories"><?= $this->__('manage_categories') ?></a>
                    </small>
                    <?php endif; ?>
                    <?php if ($this->hasError('category_id')): ?>
                    <span class="error"><?= $this->error('category_id') ?></span>
                    <?php endif; ?>
                    <?php else: ?>
                    <label for="category">Category *</label>
                    <input type="text" id="category" name="category" required
                           value="<?= $this->e($product['category'] ?? $this->old('category')) ?>"
                           placeholder="<?= $this->__('category') ?>">
                    <?php if ($this->hasError('category')): ?>
                    <span class="error"><?= $this->error('category') ?></span>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required
                       value="<?= $this->e($product['name'] ?? $this->old('name')) ?>"
                       placeholder="Full product name">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"
                          placeholder="Product description"><?= $this->e($product['description'] ?? $this->old('description')) ?></textarea>
                <?php if ($this->hasError('description')): ?>
                <span class="error"><?= $this->error('description') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image"><?= $this->__('photo') ?></label>
                <?php if (!empty($product['image_path'])): ?>
                <div class="form-image-preview">
                    <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($product['image_path'], '/')) ?>">
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
                <small class="text-muted"><?= $this->__('upload_photo') ?></small>
            </div>

            <div class="form-group">
                <label for="base_price">Base Price</label>
                <input type="number" id="base_price" name="base_price" step="0.01" min="0"
                       value="<?= $this->e($product['base_price'] ?? $this->old('base_price', '0.00')) ?>"
                       placeholder="0.00">
                <?php if ($this->hasError('base_price')): ?>
                <span class="error"><?= $this->error('base_price') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($product['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $product ? 'Update Product' : 'Create Product' ?></button>
                <a href="/catalog/products" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
</style>

<?php $this->endSection(); ?>
