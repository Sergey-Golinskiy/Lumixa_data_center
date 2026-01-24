<?php $this->section('content'); ?>

<div class="page-actions">
    <form method="GET" class="search-form">
        <input type="text" name="search" value="<?= $this->e($search) ?>" placeholder="<?= $this->__('search_sku_name') ?>">
        <select name="type">
            <option value=""><?= $this->__('all_types') ?></option>
            <?php foreach ($types as $value => $label): ?>
            <option value="<?= $this->e($value) ?>" <?= $type === $value ? 'selected' : '' ?>><?= $this->e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary"><?= $this->__('search') ?></button>
    </form>

    <?php if ($this->can('warehouse.items.create')): ?>
    <a href="/warehouse/items/create" class="btn btn-primary">+ <?= $this->__('new_item') ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?= $this->__('photo') ?></th>
                    <th><?= $this->__('sku') ?></th>
                    <th><?= $this->__('name') ?></th>
                    <th><?= $this->__('type') ?></th>
                    <th><?= $this->__('unit') ?></th>
                    <th><?= $this->__('on_hand') ?></th>
                    <th><?= $this->__('reserved') ?></th>
                    <th><?= $this->__('available') ?></th>
                    <th><?= $this->__('status') ?></th>
                    <th><?= $this->__('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="10" class="text-muted" style="text-align: center; padding: 40px;">
                        <?= $this->__('no_items_found') ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr data-href="/warehouse/items/<?= $item['id'] ?>">
                        <td>
                            <?php if (!empty($item['image_path'])): ?>
                            <img src="/<?= $this->e(ltrim($item['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($item['image_path'], '/')) ?>">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= $this->e($item['sku']) ?></strong></td>
                        <td><?= $this->e($item['name']) ?></td>
                        <td><?= $this->e($types[$item['type']] ?? $item['type']) ?></td>
                        <td><?= $this->e($item['unit']) ?></td>
                        <td><?= $this->number($item['total_on_hand'] ?? 0) ?></td>
                        <td><?= $this->number($item['total_reserved'] ?? 0) ?></td>
                        <td><?= $this->number(($item['total_on_hand'] ?? 0) - ($item['total_reserved'] ?? 0)) ?></td>
                        <td>
                            <span class="badge badge-<?= $item['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $item['is_active'] ? $this->__('active') : $this->__('inactive') ?>
                            </span>
                        </td>
                        <td>
                            <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                            <?php if ($this->can('warehouse.items.edit')): ?>
                            <a href="/warehouse/items/<?= $item['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= $this->__('edit') ?></a>
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
        <a href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>" class="btn btn-sm">&laquo; <?= $this->__('prev') ?></a>
        <?php endif; ?>

        <span class="pagination-info">
            <?= $this->__('page') ?> <?= $pagination['current_page'] ?> <?= $this->__('of') ?> <?= $pagination['total_pages'] ?>
            (<?= $pagination['total'] ?> <?= $this->__('items') ?>)
        </span>

        <?php if ($pagination['has_next']): ?>
        <a href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>" class="btn btn-sm"><?= $this->__('next') ?> &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.page-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 20px;
}
.search-form {
    display: flex;
    gap: 10px;
    flex: 1;
}
.search-form input,
.search-form select {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
}
.search-form input {
    flex: 1;
    max-width: 300px;
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
</style>

<?php $this->endSection(); ?>
