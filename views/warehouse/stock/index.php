<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('stock_balances') ?></h1>
    <div class="page-actions">
        <a href="/warehouse/stock/movements" class="btn btn-secondary"><?= $this->__('movements') ?></a>
        <a href="/warehouse/stock/low-stock" class="btn btn-warning"><?= $this->__('low_stock') ?></a>
        <a href="/warehouse/stock/valuation" class="btn btn-outline"><?= $this->__('valuation') ?></a>
    </div>
</div>

<!-- Summary Cards -->
<?php if ($summary): ?>
<div class="summary-cards" style="margin-bottom: 20px;">
    <div class="summary-card">
        <div class="summary-value"><?= number_format($summary['item_count'] ?? 0) ?></div>
        <div class="summary-label"><?= $this->__('items_in_stock') ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= number_format($summary['total_quantity'] ?? 0, 0) ?></div>
        <div class="summary-label"><?= $this->__('total_units') ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= number_format($summary['total_value'] ?? 0, 2) ?></div>
        <div class="summary-label"><?= $this->__('total_value') ?></div>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="<?= $this->__('search_sku_name') ?>"
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="category">
                        <option value=""><?= $this->__('all_categories') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $this->e($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                            <?= $this->e($cat['category']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_empty" <?= $showEmpty ? 'checked' : '' ?>>
                        <?= $this->__('show_zero_stock') ?>
                    </label>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/warehouse/stock" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('category') ?></th>
                        <th class="text-right"><?= $this->__('quantity') ?></th>
                        <th class="text-right"><?= $this->__('reserved') ?></th>
                        <th class="text-right"><?= $this->__('available') ?></th>
                        <th class="text-right"><?= $this->__('value') ?></th>
                        <th><?= $this->__('lots') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stocks)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted"><?= $this->__('no_stock_found') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($stocks as $stock): ?>
                    <?php
                    $available = $stock['total_quantity'] - $stock['total_reserved'];
                    ?>
                    <tr>
                        <td>
                            <a href="/warehouse/stock/<?= $stock['id'] ?>">
                                <strong><?= $this->e($stock['sku']) ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($stock['name']) ?></td>
                        <td><?= $this->e($stock['category'] ?? '-') ?></td>
                        <td class="text-right">
                            <?= number_format($stock['total_quantity'], 3) ?>
                            <small class="text-muted"><?= $this->e($stock['unit']) ?></small>
                        </td>
                        <td class="text-right">
                            <?php if ($stock['total_reserved'] > 0): ?>
                            <span class="text-warning"><?= number_format($stock['total_reserved'], 3) ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($available < 0): ?>
                            <span class="text-danger"><?= number_format($available, 3) ?></span>
                            <?php else: ?>
                            <?= number_format($available, 3) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($stock['total_value'], 2) ?></td>
                        <td>
                            <?php if ($stock['track_lots']): ?>
                            <span class="badge badge-info"><?= $stock['lot_count'] ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/warehouse/stock/<?= $stock['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('details') ?></a>
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
            <span class="pagination-info"><?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?> (<?= $total ?> <?= $this->__('items') ?>)</span>
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
.page-header h1 { margin: 0; }
.page-actions { display: flex; gap: 10px; }
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}
.summary-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}
.summary-value {
    font-size: 28px;
    font-weight: bold;
    color: var(--primary);
}
.summary-label {
    color: var(--text-muted);
    font-size: 13px;
    margin-top: 5px;
}
.filter-form { margin: 0; }
.filter-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.filter-group { flex: 1; min-width: 150px; }
.filter-group input, .filter-group select { width: 100%; }
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.text-right { text-align: right; }
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}
</style>

<?php $this->endSection(); ?>
