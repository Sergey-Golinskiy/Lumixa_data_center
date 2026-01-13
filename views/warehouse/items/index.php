<?php $this->section('content'); ?>

<div class="page-actions">
    <form method="GET" class="search-form">
        <input type="text" name="search" value="<?= $this->e($search) ?>" placeholder="Search SKU or name...">
        <select name="type">
            <option value="">All Types</option>
            <?php foreach ($types as $value => $label): ?>
            <option value="<?= $this->e($value) ?>" <?= $type === $value ? 'selected' : '' ?>><?= $this->e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>

    <?php if ($this->can('warehouse.items.create')): ?>
    <a href="/warehouse/items/create" class="btn btn-primary">+ New Item</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Unit</th>
                    <th>On Hand</th>
                    <th>Reserved</th>
                    <th>Available</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="9" class="text-muted" style="text-align: center; padding: 40px;">
                        No items found
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr data-href="/warehouse/items/<?= $item['id'] ?>">
                        <td><strong><?= $this->e($item['sku']) ?></strong></td>
                        <td><?= $this->e($item['name']) ?></td>
                        <td><?= $this->e($types[$item['type']] ?? $item['type']) ?></td>
                        <td><?= $this->e($item['unit']) ?></td>
                        <td><?= $this->number($item['total_on_hand'] ?? 0) ?></td>
                        <td><?= $this->number($item['total_reserved'] ?? 0) ?></td>
                        <td><?= $this->number(($item['total_on_hand'] ?? 0) - ($item['total_reserved'] ?? 0)) ?></td>
                        <td>
                            <span class="badge badge-<?= $item['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                            <?php if ($this->can('warehouse.items.edit')): ?>
                            <a href="/warehouse/items/<?= $item['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
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
        <a href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>" class="btn btn-sm">&laquo; Prev</a>
        <?php endif; ?>

        <span class="pagination-info">
            Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
            (<?= $pagination['total'] ?> items)
        </span>

        <?php if ($pagination['has_next']): ?>
        <a href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>" class="btn btn-sm">Next &raquo;</a>
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
