<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/products" class="btn btn-secondary">&laquo; <?= $this->__('back_to_products') ?></a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $product ? $this->__('edit_product') : $this->__('create_new_product') ?></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="<?= $product ? "/catalog/products/{$product['id']}" : '/catalog/products' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="code"><?= $this->__('product_code') ?> *</label>
                    <input type="text" id="code" name="code" required
                           value="<?= $this->e($product['code'] ?? $this->old('code')) ?>"
                           placeholder="<?= $this->__('product_code_placeholder') ?>" style="text-transform: uppercase;">
                    <?php if ($this->hasError('code')): ?>
                    <span class="error"><?= $this->error('code') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <?php if (($categoryMode ?? 'table') === 'table'): ?>
                    <label for="category_id"><?= $this->__('category') ?> *</label>
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
                    <label for="category"><?= $this->__('category') ?> *</label>
                    <input type="text" id="category" name="category" required
                           value="<?= $this->e($product['category'] ?? $this->old('category')) ?>"
                           placeholder="<?= $this->__('category') ?>">
                    <?php if ($this->hasError('category')): ?>
                    <span class="error"><?= $this->error('category') ?></span>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="name"><?= $this->__('product_name') ?> *</label>
                    <input type="text" id="name" name="name" required
                           value="<?= $this->e($product['name'] ?? $this->old('name')) ?>"
                           placeholder="<?= $this->__('full_product_name') ?>">
                    <?php if ($this->hasError('name')): ?>
                    <span class="error"><?= $this->error('name') ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($collections)): ?>
                <div class="form-group">
                    <label for="collection_id"><?= $this->__('collection') ?></label>
                    <select id="collection_id" name="collection_id">
                        <option value=""><?= $this->__('no_collection') ?></option>
                        <?php foreach ($collections as $coll): ?>
                        <option value="<?= $this->e($coll['id']) ?>"
                                <?= (string)$this->old('collection_id', $product['collection_id'] ?? '') === (string)$coll['id'] ? 'selected' : '' ?>>
                            <?= $this->e($coll['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">
                        <a href="/admin/product-collections"><?= $this->__('manage_collections') ?></a>
                    </small>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description"><?= $this->__('description') ?></label>
                <textarea id="description" name="description" rows="4"
                          placeholder="<?= $this->__('product_description') ?>"><?= $this->e($product['description'] ?? $this->old('description')) ?></textarea>
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
                <label for="base_price"><?= $this->__('base_price') ?></label>
                <input type="number" id="base_price" name="base_price" step="0.01" min="0"
                       value="<?= $this->e($product['base_price'] ?? $this->old('base_price', '0.00')) ?>"
                       placeholder="0.00">
                <?php if ($this->hasError('base_price')): ?>
                <span class="error"><?= $this->error('base_price') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="website_url"><?= $this->__('website_url') ?></label>
                <input type="url" id="website_url" name="website_url"
                       value="<?= $this->e($product['website_url'] ?? $this->old('website_url')) ?>"
                       placeholder="https://shop.example.com/product/...">
                <small class="text-muted"><?= $this->__('website_url_hint') ?></small>
                <?php if ($this->hasError('website_url')): ?>
                <span class="error"><?= $this->error('website_url') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($product['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $product ? $this->__('update_product') : $this->__('create_product') ?></button>
                <a href="/catalog/products" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
</style>

<?php $this->endSection(); ?>
