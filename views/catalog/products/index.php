<?php $this->section('content'); ?>

<?php if (!empty($selectedCollection)): ?>
<!-- Collection Banner -->
<div class="card bg-primary text-white mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="ri-book-2-line fs-1"></i>
                <div>
                    <h4 class="mb-1"><?= $this->e($selectedCollection['name']) ?></h4>
                    <p class="mb-0 opacity-75"><?= $this->__('collection_products_count', ['count' => $total]) ?></p>
                </div>
            </div>
            <a href="/catalog/products" class="btn btn-light"><?= $this->__('show_all_products') ?></a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= $this->__('products') ?></h4>
            <?php if ($this->can('catalog.products.create')): ?>
            <a href="/catalog/products/create" class="btn btn-success">
                <i class="ri-add-line me-1"></i><?= $this->__('new_product') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Live Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/catalog/products">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="<?= $this->__('search_code_name') ?>"
                               value="<?= $this->e($search) ?>">
                        <?php if ($search): ?>
                        <button type="button" class="btn btn-outline-secondary live-filter-clear-search">
                            <i class="ri-close-line"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (($categoryMode ?? 'table') !== 'none'): ?>
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('category') ?></label>
                    <select name="category" class="form-select">
                        <option value=""><?= $this->__('all_categories') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $this->e($cat['id']) ?>" <?= (string)$category === (string)$cat['id'] ? 'selected' : '' ?>>
                            <?= $this->e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-md-2">
                    <label class="form-label"><?= $this->__('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>><?= $this->__('inactive') ?></option>
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-line me-1"></i><?= $this->__('filter') ?>
                        </button>
                        <?php if ($search || $category || $status): ?>
                        <a href="/catalog/products" class="btn btn-soft-secondary">
                            <i class="ri-refresh-line me-1"></i><?= $this->__('clear_filters') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Products Grid -->
<?php if (empty($products)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ri-inbox-line fs-1 text-muted"></i>
        <h5 class="mt-3 text-muted"><?= $this->__('no_products_found') ?></h5>
        <?php if ($this->can('catalog.products.create')): ?>
        <a href="/catalog/products/create" class="btn btn-primary mt-3">
            <i class="ri-add-line me-1"></i><?= $this->__('new_product') ?>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="row row-cols-1 row-cols-xl-2 g-3">
    <?php foreach ($products as $product): ?>
    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-shrink-0" style="width: 140px; height: 140px;">
                        <?php if (!empty($product['image_path'])): ?>
                        <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>"
                             alt="<?= $this->e($product['name']) ?>"
                             class="img-fluid rounded object-fit-cover w-100 h-100"
                             data-image-preview="/<?= $this->e(ltrim($product['image_path'], '/')) ?>"
                             style="cursor: pointer;">
                        <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center w-100 h-100">
                            <span class="text-muted small"><?= $this->__('no_photo') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <a href="/catalog/products/<?= $product['id'] ?>" class="fw-semibold text-primary fs-5 text-decoration-none">
                                <?= $this->e($product['code']) ?>
                            </a>
                            <?php if ($product['is_active']): ?>
                            <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted mb-2"><?= $this->e($product['name']) ?></p>
                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('category') ?>:</span>
                                <span class="fw-medium"><?= $this->e($product['category_name'] ?? '-') ?></span>
                            </div>
                            <?php if (!empty($product['collection_name'])): ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('collection') ?>:</span>
                                <a href="/catalog/products?collection_id=<?= $this->e($product['collection_id']) ?>" class="badge bg-primary-subtle text-primary text-decoration-none">
                                    <?= $this->e($product['collection_name']) ?>
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('base_price') ?>:</span>
                                <span class="fw-semibold text-success"><?= number_format($product['base_price'], 2) ?></span>
                            </div>
                            <?php if (isset($product['production_cost']) && $product['production_cost'] > 0): ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('production_cost') ?>:</span>
                                <span class="fw-medium"><?= number_format($product['production_cost'], 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($product['variant_count'] > 0): ?>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><?= $this->__('variants') ?>:</span>
                                <span class="badge bg-info-subtle text-info"><?= $product['variant_count'] ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="/catalog/products/<?= $product['id'] ?>" class="btn btn-sm btn-soft-secondary">
                        <i class="ri-eye-line me-1"></i><?= $this->__('view') ?>
                    </a>
                    <?php if ($this->can('catalog.products.edit')): ?>
                    <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-soft-primary">
                        <i class="ri-pencil-line me-1"></i><?= $this->__('edit') ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($this->can('catalog.products.create')): ?>
                    <a href="/catalog/products/<?= $product['id'] ?>/copy" target="_blank" class="btn btn-sm btn-soft-info" title="<?= $this->__('copy') ?>">
                        <i class="ri-file-copy-line"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($product['website_url'])): ?>
                    <a href="<?= $this->e($product['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-soft-success" title="<?= $this->__('open_in_store') ?>">
                        <i class="ri-external-link-line me-1"></i><?= $this->__('store') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="card mt-3">
    <div class="card-body">
        <nav>
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                        <i class="ri-arrow-left-s-line"></i> <?= $this->__('prev') ?>
                    </a>
                </li>
                <li class="page-item disabled">
                    <span class="page-link"><?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?> (<?= $total ?> <?= $this->__('products') ?>)</span>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                        <?= $this->__('next') ?> <i class="ri-arrow-right-s-line"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php $this->endSection(); ?>
