<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('products') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.products.create')): ?>
        <a href="/catalog/products/create" class="btn btn-primary">+ <?= $this->__('new_product') ?></a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="<?= $this->__('search_code_name') ?>"
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <?php if (($categoryMode ?? 'table') !== 'none'): ?>
                    <select name="category">
                        <option value=""><?= $this->__('all_categories') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $this->e($cat['id']) ?>" <?= (string)$category === (string)$cat['id'] ? 'selected' : '' ?>>
                            <?= $this->e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>><?= $this->__('inactive') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/catalog/products" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<!-- Products Grid -->
<?php if (empty($products)): ?>
<div class="card">
    <div class="card-body text-center text-muted" style="padding: 40px;">
        <?= $this->__('no_products_found') ?>
    </div>
</div>
<?php else: ?>
<div class="products-grid">
    <?php foreach ($products as $product): ?>
    <div class="product-tile">
        <div class="product-tile-image">
            <?php if (!empty($product['image_path'])): ?>
            <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>"
                 alt="<?= $this->e($product['name']) ?>"
                 data-image-preview="/<?= $this->e(ltrim($product['image_path'], '/')) ?>">
            <?php else: ?>
            <div class="no-image">
                <span><?= $this->__('no_photo') ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="product-tile-content">
            <div class="product-tile-header">
                <a href="/catalog/products/<?= $product['id'] ?>" class="product-code">
                    <?= $this->e($product['code']) ?>
                </a>
                <?php if ($product['is_active']): ?>
                <span class="badge badge-success"><?= $this->__('active') ?></span>
                <?php else: ?>
                <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                <?php endif; ?>
            </div>
            <div class="product-tile-name"><?= $this->e($product['name']) ?></div>
            <div class="product-tile-info">
                <div class="info-row">
                    <span class="info-label"><?= $this->__('category') ?>:</span>
                    <span class="info-value"><?= $this->e($product['category_name'] ?? '-') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= $this->__('base_price') ?>:</span>
                    <span class="info-value price"><?= number_format($product['base_price'], 2) ?></span>
                </div>
                <?php if (isset($product['production_cost']) && $product['production_cost'] > 0): ?>
                <div class="info-row">
                    <span class="info-label"><?= $this->__('production_cost') ?>:</span>
                    <span class="info-value"><?= number_format($product['production_cost'], 2) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($product['variant_count'] > 0): ?>
                <div class="info-row">
                    <span class="info-label"><?= $this->__('variants') ?>:</span>
                    <span class="info-value"><span class="badge badge-info"><?= $product['variant_count'] ?></span></span>
                </div>
                <?php endif; ?>
            </div>
            <div class="product-tile-actions">
                <a href="/catalog/products/<?= $product['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                <?php if ($this->can('catalog.products.edit')): ?>
                <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                <?php endif; ?>
                <?php if (!empty($product['website_url'])): ?>
                <a href="<?= $this->e($product['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary" title="<?= $this->__('open_in_store') ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <?= $this->__('store') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="pagination-wrapper">
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; <?= $this->__('prev') ?></a>
        <?php endif; ?>
        <span class="pagination-info"><?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?> (<?= $total ?> <?= $this->__('products') ?>)</span>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline"><?= $this->__('next') ?> &raquo;</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.page-header h1 {
    margin: 0;
}
.filter-form {
    margin: 0;
}
.filter-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.filter-group {
    flex: 1;
    min-width: 150px;
}
.filter-group input,
.filter-group select {
    width: 100%;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 1200px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
}

.product-tile {
    display: flex;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border, #ddd);
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.2s, transform 0.2s;
}

.product-tile:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.product-tile-image {
    width: 180px;
    min-width: 180px;
    height: 180px;
    background: var(--bg-secondary, #f5f5f5);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-tile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
}

.product-tile-image .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted, #999);
    font-size: 14px;
    text-align: center;
    padding: 10px;
}

.product-tile-content {
    flex: 1;
    padding: 15px;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.product-tile-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.product-code {
    font-weight: 600;
    font-size: 16px;
    color: var(--primary, #007bff);
    text-decoration: none;
}

.product-code:hover {
    text-decoration: underline;
}

.product-tile-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--text, #333);
    margin-bottom: 10px;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-tile-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-row {
    display: flex;
    font-size: 13px;
    line-height: 1.4;
}

.info-label {
    color: var(--text-muted, #666);
    min-width: 100px;
}

.info-value {
    color: var(--text, #333);
    font-weight: 500;
}

.info-value.price {
    color: var(--success, #28a745);
    font-weight: 600;
}

.product-tile-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--border, #eee);
}

.pagination-wrapper {
    margin-top: 20px;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border, #ddd);
    border-radius: 8px;
}

.pagination-info {
    color: var(--text-muted, #666);
    font-size: 14px;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: var(--text-muted, #666);
}
</style>

<?php $this->endSection(); ?>
