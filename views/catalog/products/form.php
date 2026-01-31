<?php $this->section('content'); ?>

<div class="row mb-3">
    <div class="col-12">
        <a href="/catalog/products" class="btn btn-soft-secondary">
            <i class="ri-arrow-left-line me-1"></i><?= $this->__('back_to_products') ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-box-3-line me-2"></i>
                    <?= $product ? $this->__('edit_product') : $this->__('create_new_product') ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="<?= $product ? "/catalog/products/{$product['id']}" : '/catalog/products' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="code" class="form-label"><?= $this->__('product_code') ?> <span class="text-danger">*</span></label>
                            <input type="text" id="code" name="code" class="form-control <?= $this->hasError('code') ? 'is-invalid' : '' ?>" required
                                   value="<?= $this->e($product['code'] ?? $this->old('code')) ?>"
                                   placeholder="<?= $this->__('product_code_placeholder') ?>" style="text-transform: uppercase;">
                            <?php if ($this->hasError('code')): ?>
                            <div class="invalid-feedback"><?= $this->error('code') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <?php if (($categoryMode ?? 'table') === 'table'): ?>
                            <label for="category_id" class="form-label"><?= $this->__('category') ?> <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-select <?= $this->hasError('category_id') ? 'is-invalid' : '' ?>" required>
                                <option value=""><?= $this->__('select_category') ?></option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $this->e($cat['id']) ?>"
                                        <?= (string)$this->old('category_id', $product['category_id'] ?? '') === (string)$cat['id'] ? 'selected' : '' ?>>
                                    <?= $this->e($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($categories)): ?>
                            <div class="form-text">
                                <?= $this->__('no_categories_hint') ?>
                                <a href="/admin/product-categories"><?= $this->__('manage_categories') ?></a>
                            </div>
                            <?php endif; ?>
                            <?php if ($this->hasError('category_id')): ?>
                            <div class="invalid-feedback"><?= $this->error('category_id') ?></div>
                            <?php endif; ?>
                            <?php else: ?>
                            <label for="category" class="form-label"><?= $this->__('category') ?> <span class="text-danger">*</span></label>
                            <input type="text" id="category" name="category" class="form-control <?= $this->hasError('category') ? 'is-invalid' : '' ?>" required
                                   value="<?= $this->e($product['category'] ?? $this->old('category')) ?>"
                                   placeholder="<?= $this->__('category') ?>">
                            <?php if ($this->hasError('category')): ?>
                            <div class="invalid-feedback"><?= $this->error('category') ?></div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label"><?= $this->__('product_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control <?= $this->hasError('name') ? 'is-invalid' : '' ?>" required
                                   value="<?= $this->e($product['name'] ?? $this->old('name')) ?>"
                                   placeholder="<?= $this->__('full_product_name') ?>">
                            <?php if ($this->hasError('name')): ?>
                            <div class="invalid-feedback"><?= $this->error('name') ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($collections)): ?>
                        <div class="col-md-6">
                            <label for="collection_id" class="form-label"><?= $this->__('collection') ?></label>
                            <select id="collection_id" name="collection_id" class="form-select">
                                <option value=""><?= $this->__('no_collection') ?></option>
                                <?php foreach ($collections as $coll): ?>
                                <option value="<?= $this->e($coll['id']) ?>"
                                        <?= (string)$this->old('collection_id', $product['collection_id'] ?? '') === (string)$coll['id'] ? 'selected' : '' ?>>
                                    <?= $this->e($coll['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <a href="/admin/product-collections"><?= $this->__('manage_collections') ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label"><?= $this->__('description') ?></label>
                        <textarea id="description" name="description" class="form-control <?= $this->hasError('description') ? 'is-invalid' : '' ?>" rows="4"
                                  placeholder="<?= $this->__('product_description') ?>"><?= $this->e($product['description'] ?? $this->old('description')) ?></textarea>
                        <?php if ($this->hasError('description')): ?>
                        <div class="invalid-feedback"><?= $this->error('description') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label"><?= $this->__('photo') ?></label>
                        <?php if (!empty($product['image_path'])): ?>
                        <div class="mb-2">
                            <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>"
                                 class="img-thumbnail" style="max-width: 150px; cursor: pointer;"
                                 data-image-preview="/<?= $this->e(ltrim($product['image_path'], '/')) ?>">
                        </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <div class="form-text"><?= $this->__('upload_photo') ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="base_price" class="form-label"><?= $this->__('base_price') ?></label>
                            <div class="input-group">
                                <input type="number" id="base_price" name="base_price" class="form-control <?= $this->hasError('base_price') ? 'is-invalid' : '' ?>" step="0.01" min="0"
                                       value="<?= $this->e($product['base_price'] ?? $this->old('base_price', '0.00')) ?>"
                                       placeholder="0.00">
                                <span class="input-group-text"><i class="ri-money-dollar-circle-line"></i></span>
                                <?php if ($this->hasError('base_price')): ?>
                                <div class="invalid-feedback"><?= $this->error('base_price') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="website_url" class="form-label"><?= $this->__('website_url') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-link"></i></span>
                            <input type="url" id="website_url" name="website_url" class="form-control <?= $this->hasError('website_url') ? 'is-invalid' : '' ?>"
                                   value="<?= $this->e($product['website_url'] ?? $this->old('website_url')) ?>"
                                   placeholder="https://shop.example.com/product/...">
                            <?php if ($this->hasError('website_url')): ?>
                            <div class="invalid-feedback"><?= $this->error('website_url') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text"><?= $this->__('website_url_hint') ?></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1"
                                   <?= ($product['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $product ? $this->__('update_product') : $this->__('create_product') ?>
                        </button>
                        <a href="/catalog/products" class="btn btn-soft-secondary">
                            <i class="ri-close-line me-1"></i><?= $this->__('cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
