<?php $this->section('content'); ?>

<div class="page-header">
    <h1>Lots Management</h1>
    <div class="page-actions">
        <?php if ($this->can('warehouse.lots.create')): ?>
        <a href="/warehouse/lots/create" class="btn btn-primary">+ New Lot</a>
        <?php endif; ?>
        <a href="/warehouse/lots/expiring" class="btn btn-warning">Expiring Lots</a>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search lot #, item..."
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="item_id">
                        <option value="">All Items</option>
                        <?php foreach ($items as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= $itemId == $item['id'] ? 'selected' : '' ?>>
                            <?= $this->e($item['sku']) ?> - <?= $this->e($item['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="quarantine" <?= $status === 'quarantine' ? 'selected' : '' ?>>Quarantine</option>
                        <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                        <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="/warehouse/lots" class="btn btn-outline">Clear</a>
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
                        <th>Lot #</th>
                        <th>Item</th>
                        <th>Status</th>
                        <th>Manufacture Date</th>
                        <th>Expiry Date</th>
                        <th>Supplier Lot</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lots)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No lots found</td>
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
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($lot['status']) ?></span>
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
                                    <br><small>(Expired)</small>
                                    <?php elseif ($daysLeft <= 30): ?>
                                    <br><small>(<?= $daysLeft ?> days left)</small>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= $this->e($lot['supplier_lot'] ?? '-') ?></td>
                        <td>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                            <?php if ($this->can('warehouse.lots.edit')): ?>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
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
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; Previous</a>
            <?php endif; ?>

            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> total)</span>

            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline">Next &raquo;</a>
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
