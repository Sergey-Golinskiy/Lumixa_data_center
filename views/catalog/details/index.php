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

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('photo') ?></th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('detail_type') ?></th>
                        <th><?= $this->__('material') ?></th>
                        <th><?= $this->__('material_qty_grams') ?></th>
                        <th><?= $this->__('print_time_minutes') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($details)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted"><?= $this->__('no_details_found') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($details as $detail): ?>
                    <tr>
                        <td>
                            <?php if (!empty($detail['image_path'])): ?>
                            <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/catalog/details/<?= $detail['id'] ?>">
                                <strong><?= $this->e($detail['sku']) ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($detail['name']) ?></td>
                        <td>
                            <?= $detail['detail_type'] === 'printed'
                                ? $this->__('detail_type_printed')
                                : $this->__('detail_type_purchased') ?>
                        </td>
                        <td>
                            <?php if ($detail['material_item_id']): ?>
                            <?= $this->e($detail['material_sku'] ?? '') ?> <?= $detail['material_name'] ? ' - ' . $this->e($detail['material_name']) : '' ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $detail['material_qty_grams'] ? $this->e($detail['material_qty_grams']) : '-' ?></td>
                        <td><?= $detail['print_time_minutes'] ? $this->e($detail['print_time_minutes']) : '-' ?></td>
                        <td>
                            <?php if (!empty($detail['is_active'])): ?>
                            <span class="badge badge-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/catalog/details/<?= $detail['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                            <?php if ($this->can('catalog.details.edit')): ?>
                            <a href="/catalog/details/<?= $detail['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

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
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.filter-form { margin: 0; }
.filter-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.filter-group { flex: 1; min-width: 160px; }
.filter-group input, .filter-group select { width: 100%; }
.text-center { text-align: center; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
</style>

<?php $this->endSection(); ?>
