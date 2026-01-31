<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('items_sku') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('warehouse.items.create')): ?>
        <a href="/warehouse/items/create" class="btn btn-primary">+ <?= $this->__('new_item') ?></a>
        <?php endif; ?>
    </div>
</div>

<!-- Live Filters -->
<div class="live-filters">
    <form method="GET" action="/warehouse/items">
        <div class="live-filters-row">
            <div class="live-filter-group filter-search">
                <label class="live-filter-label"><?= $this->__('search') ?></label>
                <div class="live-filter-search-wrapper <?= $search ? 'has-value' : '' ?>">
                    <span class="live-filter-search-icon">&#128269;</span>
                    <input type="text" name="search" class="live-filter-input"
                           placeholder="<?= $this->__('search_sku_name') ?>"
                           value="<?= $this->e($search) ?>">
                    <button type="button" class="live-filter-clear-search" title="<?= $this->__('clear') ?>">&times;</button>
                </div>
            </div>

            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('type') ?></label>
                <select name="type" class="live-filter-select">
                    <option value=""><?= $this->__('all_types') ?></option>
                    <?php foreach ($types as $value => $label): ?>
                    <option value="<?= $this->e($value) ?>" <?= $type === $value ? 'selected' : '' ?>>
                        <?= $this->e($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('status') ?></label>
                <select name="status" class="live-filter-select">
                    <option value=""><?= $this->__('all') ?></option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>><?= $this->__('inactive') ?></option>
                </select>
            </div>

            <div class="live-filter-group filter-actions">
                <button type="button" class="live-filter-clear-all" <?= (!$search && !$type && !($status ?? '')) ? 'disabled' : '' ?>>
                    <?= $this->__('clear_filters') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Items Table -->
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60px;"><?= $this->__('photo') ?></th>
                    <th><?= $this->__('sku') ?></th>
                    <th><?= $this->__('name') ?></th>
                    <th><?= $this->__('type') ?></th>
                    <th><?= $this->__('unit') ?></th>
                    <th class="text-right"><?= $this->__('on_hand') ?></th>
                    <th class="text-right"><?= $this->__('reserved') ?></th>
                    <th class="text-right"><?= $this->__('available') ?></th>
                    <th><?= $this->__('status') ?></th>
                    <th><?= $this->__('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="10" class="text-muted text-center" style="padding: 40px;">
                        <?= $this->__('no_items_found') ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['image_path'])): ?>
                            <img src="/<?= $this->e(ltrim($item['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($item['image_path'], '/')) ?>">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/warehouse/items/<?= $item['id'] ?>">
                                <strong><?= $this->e($item['sku']) ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($item['name']) ?></td>
                        <td>
                            <span class="badge badge-type-<?= $this->e($item['type']) ?>">
                                <?= $this->e($types[$item['type']] ?? $this->__('item_type_' . $item['type'])) ?>
                            </span>
                        </td>
                        <td><?= $this->__('unit_' . $item['unit']) ?></td>
                        <td class="text-right"><?= $this->number($item['total_on_hand'] ?? 0) ?></td>
                        <td class="text-right"><?= $this->number($item['total_reserved'] ?? 0) ?></td>
                        <td class="text-right">
                            <?php $available = ($item['total_on_hand'] ?? 0) - ($item['total_reserved'] ?? 0); ?>
                            <span class="<?= $available <= 0 ? 'text-danger' : ($available <= ($item['min_stock'] ?? 0) ? 'text-warning' : '') ?>">
                                <?= $this->number($available) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $item['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $item['is_active'] ? $this->__('active') : $this->__('inactive') ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                            <?php if ($this->can('warehouse.items.edit')): ?>
                            <a href="/warehouse/items/<?= $item['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($pagination['has_prev']): ?>
        <a href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>&status=<?= urlencode($status ?? '') ?>" class="btn btn-sm btn-secondary">&laquo; <?= $this->__('prev') ?></a>
        <?php endif; ?>

        <span class="pagination-info">
            <?= $this->__('page') ?> <?= $pagination['current_page'] ?> <?= $this->__('of') ?> <?= $pagination['total_pages'] ?>
            (<?= $pagination['total'] ?> <?= $this->__('items') ?>)
        </span>

        <?php if ($pagination['has_next']): ?>
        <a href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>&status=<?= urlencode($status ?? '') ?>" class="btn btn-sm btn-secondary"><?= $this->__('next') ?> &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.page-header h1 { margin: 0; }

.image-thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 15px;
    border-top: 1px solid var(--border);
}
.pagination-info {
    color: var(--text-muted);
}

/* Type badges */
.badge-type-material { background: #3b82f6; color: white; }
.badge-type-component { background: #8b5cf6; color: white; }
.badge-type-part { background: #06b6d4; color: white; }
.badge-type-consumable { background: #f59e0b; color: white; }
.badge-type-packaging { background: #10b981; color: white; }
.badge-type-fasteners { background: #6b7280; color: white; }

.actions {
    white-space: nowrap;
}
</style>

<?php $this->endSection(); ?>
