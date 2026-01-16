<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('lots_management') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('warehouse.lots.create')): ?>
        <a href="/warehouse/lots/create" class="btn btn-primary">+ <?= $this->__('new_lot') ?></a>
        <?php endif; ?>
        <a href="/warehouse/lots/expiring" class="btn btn-warning"><?= $this->__('expiring_lots') ?></a>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="<?= $this->__('search_lot_item') ?>"
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="item_id">
                        <option value=""><?= $this->__('all_items') ?></option>
                        <?php foreach ($items as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= $itemId == $item['id'] ? 'selected' : '' ?>>
                            <?= $this->e($item['sku']) ?> - <?= $this->e($item['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="quarantine" <?= $status === 'quarantine' ? 'selected' : '' ?>><?= $this->__('quarantine') ?></option>
                        <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>><?= $this->__('blocked') ?></option>
                        <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>><?= $this->__('expired') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/warehouse/lots" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('lot_number') ?></th>
                        <th><?= $this->__('items_list') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('manufacture_date') ?></th>
                        <th><?= $this->__('expiry_date') ?></th>
                        <th><?= $this->__('supplier_lot') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lots)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted"><?= $this->__('no_lots_found') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($lots as $lot): ?>
                    <tr>
                        <td>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>">
                                <strong><?= $this->e($lot['lot_number']) ?></strong>
                            </a>
                        </td>
                        <td>
                            <a href="/warehouse/items/<?= $lot['item_id'] ?>">
                                <?= $this->e($lot['sku']) ?>
                            </a>
                            <br>
                            <small class="text-muted"><?= $this->e($lot['item_name']) ?></small>
                        </td>
                        <td>
                            <?php
                            $statusClass = match($lot['status']) {
                                'active' => 'success',
                                'quarantine' => 'warning',
                                'blocked' => 'danger',
                                'expired' => 'secondary',
                                default => 'secondary'
                            };
                            ?>
                            <?php
                            $statusLabels = [
                                'active' => $this->__('active'),
                                'quarantine' => $this->__('quarantine'),
                                'blocked' => $this->__('blocked'),
                                'expired' => $this->__('expired')
                            ];
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= $statusLabels[$lot['status']] ?? $this->e($lot['status']) ?></span>
                        </td>
                        <td><?= $lot['manufacture_date'] ? $this->date($lot['manufacture_date'], 'Y-m-d') : '-' ?></td>
                        <td>
                            <?php if ($lot['expiry_date']): ?>
                                <?php
                                $expiry = new DateTime($lot['expiry_date']);
                                $today = new DateTime();
                                $diff = $today->diff($expiry);
                                $daysLeft = $diff->invert ? -$diff->days : $diff->days;
                                $expiryClass = '';
                                if ($daysLeft < 0) $expiryClass = 'text-danger';
                                elseif ($daysLeft <= 30) $expiryClass = 'text-warning';
                                ?>
                                <span class="<?= $expiryClass ?>">
                                    <?= $this->date($lot['expiry_date'], 'Y-m-d') ?>
                                    <?php if ($daysLeft < 0): ?>
                                    <br><small>(<?= $this->__('expired') ?>)</small>
                                    <?php elseif ($daysLeft <= 30): ?>
                                    <br><small>(<?= $this->__('days_left', ['count' => $daysLeft]) ?>)</small>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= $this->e($lot['supplier_lot'] ?? '-') ?></td>
                        <td>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                            <?php if ($this->can('warehouse.lots.edit')): ?>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; <?= $this->__('previous') ?></a>
            <?php endif; ?>

            <span class="pagination-info">
                <?= $this->__('page_of', ['current' => $page, 'total' => $totalPages]) ?>
                (<?= $this->e($total) ?> <?= $this->__('total') ?>)
            </span>

            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline"><?= $this->__('next') ?> &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

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
.page-actions {
    display: flex;
    gap: 10px;
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
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}
.pagination-info {
    color: var(--text-muted);
}
</style>

<?php $this->endSection(); ?>
