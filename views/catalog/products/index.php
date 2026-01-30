<?php $this->section('content'); ?>

<?php if (!empty($selectedCollection)): ?>
<!-- Collection Banner -->
<div class="collection-banner">
    <div class="collection-banner-content">
        <div class="collection-icon">&#128218;</div>
        <div class="collection-info">
            <h2><?= $this->e($selectedCollection['name']) ?></h2>
            <p><?= $this->__('collection_products_count', ['count' => $total]) ?></p>
        </div>
    </div>
    <a href="/catalog/products" class="btn btn-outline"><?= $this->__('show_all_products') ?></a>
</div>
<?php else: ?>
<div class="page-header">
    <h1><?= $this->__('products') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.products.create')): ?>
        <a href="/catalog/products/create" class="btn btn-primary">+ <?= $this->__('new_product') ?></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Live Filters -->
<div class="live-filters">
    <form method="GET" action="/catalog/products">
        <div class="live-filters-row">
            <div class="live-filter-group filter-search">
                <label class="live-filter-label"><?= $this->__('search') ?></label>
                <div class="live-filter-search-wrapper <?= $search ? 'has-value' : '' ?>">
                    <span class="live-filter-search-icon">&#128269;</span>
                    <input type="text" name="search" class="live-filter-input"
                           placeholder="<?= $this->__('search_code_name') ?>"
                           value="<?= $this->e($search) ?>">
                    <button type="button" class="live-filter-clear-search" title="<?= $this->__('clear') ?>">&times;</button>
                </div>
            </div>

            <?php if (($categoryMode ?? 'table') !== 'none'): ?>
            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('category') ?></label>
                <select name="category" class="live-filter-select">
                    <option value=""><?= $this->__('all_categories') ?></option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $this->e($cat['id']) ?>" <?= (string)$category === (string)$cat['id'] ? 'selected' : '' ?>>
                        <?= $this->e($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('status') ?></label>
                <select name="status" class="live-filter-select">
                    <option value=""><?= $this->__('all_statuses') ?></option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>><?= $this->__('inactive') ?></option>
                </select>
            </div>

            <div class="live-filter-group filter-actions">
                <button type="button" class="live-filter-clear-all" <?= (!$search && !$category && !$status) ? 'disabled' : '' ?>>
                    <?= $this->__('clear_filters') ?>
                </button>
            </div>
        </div>
    </form>
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
                <?php if (!empty($product['collection_name'])): ?>
                <div class="info-row">
                    <span class="info-label"><?= $this->__('collection') ?>:</span>
                    <span class="info-value">
                        <a href="/catalog/products?collection_id=<?= $this->e($product['collection_id']) ?>" class="collection-tag">
                            <?= $this->e($product['collection_name']) ?>
                        </a>
                    </span>
                </div>
                <?php endif; ?>
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
                <?php if ($this->can('catalog.products.create')): ?>
                <a href="/catalog/products/<?= $product['id'] ?>/copy" target="_blank" class="btn btn-sm btn-outline" title="<?= $this->__('copy') ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </a>
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
/* Collection Banner */
.collection-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}
.collection-banner-content {
    display: flex;
    align-items: center;
    gap: 15px;
}
.collection-icon {
    font-size: 32px;
}
.collection-info h2 {
    margin: 0 0 4px 0;
    font-size: 20px;
    font-weight: 600;
}
.collection-info p {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}
.collection-banner .btn-outline {
    background: rgba(255,255,255,0.2);
    border-color: white;
    color: white;
}
.collection-banner .btn-outline:hover {
    background: white;
    color: #6366f1;
}

/* Collection Tag in Product Tile */
.collection-tag {
    display: inline-block;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}
.collection-tag:hover {
    transform: scale(1.05);
    text-decoration: none;
    color: white;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.page-header h1 {
    margin: 0;
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
