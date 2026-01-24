<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('details') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.details.create')): ?>
        <a href="/catalog/details/create" class="btn btn-primary">+ <?= $this->__('new_detail') ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="<?= $this->__('search_sku_name') ?>"
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="detail_type">
                        <option value=""><?= $this->__('all_detail_types') ?></option>
                        <option value="purchased" <?= $detailType === 'purchased' ? 'selected' : '' ?>><?= $this->__('detail_type_purchased') ?></option>
                        <option value="printed" <?= $detailType === 'printed' ? 'selected' : '' ?>><?= $this->__('detail_type_printed') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/catalog/details" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($details)): ?>
<div class="card">
    <div class="card-body">
        <p class="text-center text-muted"><?= $this->__('no_details_found') ?></p>
    </div>
</div>
<?php else: ?>
<div class="details-grid">
    <?php foreach ($details as $detail): ?>
    <div class="detail-tile <?= empty($detail['is_active']) ? 'detail-tile-inactive' : '' ?>">
        <div class="detail-tile-image">
            <?php if (!empty($detail['image_path'])): ?>
            <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>"
                 alt="<?= $this->e($detail['name']) ?>"
                 data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
            <?php else: ?>
            <div class="no-image">
                <span><?= $this->__('no_photo') ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="detail-tile-content">
            <div class="detail-tile-header">
                <div class="detail-sku"><?= $this->e($detail['sku']) ?></div>
                <div class="detail-status">
                    <?php if (!empty($detail['is_active'])): ?>
                    <span class="badge badge-success"><?= $this->__('active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="detail-name"><?= $this->e($detail['name']) ?></div>

            <div class="detail-info">
                <div class="detail-info-row">
                    <span class="info-label"><?= $this->__('detail_type') ?>:</span>
                    <span class="info-value">
                        <?= $detail['detail_type'] === 'printed'
                            ? $this->__('detail_type_printed')
                            : $this->__('detail_type_purchased') ?>
                    </span>
                </div>

                <?php if ($detail['detail_type'] === 'printed'): ?>
                <div class="detail-info-row">
                    <span class="info-label"><?= $this->__('material') ?>:</span>
                    <span class="info-value">
                        <?php if ($detail['material_item_id']): ?>
                        <?= $this->e($detail['material_sku'] ?? '') ?>
                        <?php
                        $materialDetails = [];
                        if (!empty($detail['material_color'])) $materialDetails[] = $detail['material_color'];
                        if (!empty($detail['material_plastic_type'])) $materialDetails[] = $detail['material_plastic_type'];
                        if (!empty($materialDetails)):
                        ?>
                        <small class="text-muted">(<?= implode(', ', $materialDetails) ?>)</small>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="detail-info-row">
                    <span class="info-label"><?= $this->__('material_qty_grams') ?>:</span>
                    <span class="info-value"><?= $detail['material_qty_grams'] ? $this->e($detail['material_qty_grams']) . ' g' : '-' ?></span>
                </div>

                <div class="detail-info-row">
                    <span class="info-label"><?= $this->__('print_time_minutes') ?>:</span>
                    <span class="info-value"><?= $detail['print_time_minutes'] ? $this->e($detail['print_time_minutes']) . ' min' : '-' ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-tile-actions">
                <a href="/catalog/details/<?= $detail['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                <?php if ($this->can('catalog.details.edit')): ?>
                <a href="/catalog/details/<?= $detail['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; <?= $this->__('prev') ?></a>
    <?php endif; ?>
    <span class="pagination-info"><?= $this->__('page_of', ['current' => $page, 'total' => $totalPages]) ?></span>
    <?php if ($page < $totalPages): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline"><?= $this->__('next') ?> &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.filter-form { margin: 0; }
.filter-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.filter-group { flex: 1; min-width: 160px; }
.filter-group input, .filter-group select { width: 100%; }
.text-center { text-align: center; }

/* Details Grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 1200px) {
    .details-grid {
        grid-template-columns: 1fr;
    }
}

/* Detail Tile */
.detail-tile {
    display: flex;
    background: var(--bg-card, #fff);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.2s, transform 0.2s;
}

.detail-tile:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.detail-tile-inactive {
    opacity: 0.7;
    background: var(--bg-secondary, #f8f9fa);
}

/* Tile Image */
.detail-tile-image {
    width: 180px;
    min-width: 180px;
    height: 180px;
    background: var(--bg-secondary, #f5f5f5);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.detail-tile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s;
}

.detail-tile-image img:hover {
    transform: scale(1.05);
}

.detail-tile-image .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 0.85rem;
    text-align: center;
    padding: 10px;
}

/* Tile Content */
.detail-tile-content {
    flex: 1;
    padding: 15px;
    display: flex;
    flex-direction: column;
}

.detail-tile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 5px;
}

.detail-sku {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary, #007bff);
    font-family: monospace;
}

.detail-name {
    font-size: 1rem;
    color: var(--text);
    margin-bottom: 10px;
    line-height: 1.3;
}

/* Detail Info */
.detail-info {
    flex: 1;
    margin-bottom: 12px;
}

.detail-info-row {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    font-size: 0.9rem;
    border-bottom: 1px dashed var(--border);
}

.detail-info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: var(--text-muted);
}

.info-value {
    font-weight: 500;
    text-align: right;
}

/* Tile Actions */
.detail-tile-actions {
    display: flex;
    gap: 8px;
    padding-top: 10px;
    border-top: 1px solid var(--border);
}

.detail-tile-actions .btn {
    flex: 1;
    text-align: center;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
    padding: 20px 0;
}

/* Responsive */
@media (max-width: 768px) {
    .detail-tile {
        flex-direction: column;
    }

    .detail-tile-image {
        width: 100%;
        height: 200px;
    }
}
</style>

<?php $this->endSection(); ?>
