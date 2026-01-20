<?php $this->section('content'); ?>

<div class="page-header">
    <h1>Stock Movements</h1>
    <div class="page-actions">
        <a href="/warehouse/stock" class="btn btn-secondary">&laquo; Back to Stock</a>
    </div>
</div>

<!-- Summary -->
<div class="summary-cards" style="margin-bottom: 20px;">
    <div class="summary-card summary-in">
        <div class="summary-value">+<?= number_format($inTotal['qty'] ?? 0, 0) ?></div>
        <div class="summary-label">Total IN</div>
        <div class="summary-sub"><?= number_format($inTotal['value'] ?? 0, 2) ?></div>
    </div>
    <div class="summary-card summary-out">
        <div class="summary-value">-<?= number_format($outTotal['qty'] ?? 0, 0) ?></div>
        <div class="summary-label">Total OUT</div>
        <div class="summary-sub"><?= number_format($outTotal['value'] ?? 0, 2) ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= number_format(($inTotal['qty'] ?? 0) - ($outTotal['qty'] ?? 0), 0) ?></div>
        <div class="summary-label">Net Change</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="date_from" value="<?= $this->e($dateFrom) ?>">
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="date_to" value="<?= $this->e($dateTo) ?>">
                </div>
                <div class="filter-group">
                    <label>Item</label>
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
                    <label>Direction</label>
                    <select name="direction">
                        <option value="">All</option>
                        <option value="in" <?= $direction === 'in' ? 'selected' : '' ?>>IN</option>
                        <option value="out" <?= $direction === 'out' ? 'selected' : '' ?>>OUT</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="/warehouse/stock/movements" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Movements Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Direction</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No movements found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td><?= $this->datetime($movement['created_at']) ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $movement['document_id'] ?>">
                                <?= $this->e($movement['document_number']) ?>
                            </a>
                        </td>
                        <td><?= ucfirst($movement['document_type']) ?></td>
                        <td>
                            <a href="/warehouse/stock/<?= $movement['item_id'] ?>">
                                <?= $this->e($movement['sku']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($movement['movement_type'] === 'in'): ?>
                            <span class="badge badge-success">IN</span>
                            <?php else: ?>
                            <span class="badge badge-danger">OUT</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($movement['movement_type'] === 'in'): ?>
                            <span class="text-success">+<?= number_format($movement['quantity'], 3) ?></span>
                            <?php else: ?>
                            <span class="text-danger">-<?= number_format($movement['quantity'], 3) ?></span>
                            <?php endif; ?>
                            <small class="text-muted"><?= $this->e($movement['unit']) ?></small>
                        </td>
                        <td class="text-right"><?= number_format($movement['unit_cost'], 4) ?></td>
                        <td class="text-right"><?= number_format($movement['quantity'] * $movement['unit_cost'], 2) ?></td>
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
            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> movements)</span>
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
.page-header h1 { margin: 0; }
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
}
.summary-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}
.summary-in { border-color: var(--success); }
.summary-out { border-color: var(--danger); }
.summary-value { font-size: 24px; font-weight: bold; }
.summary-in .summary-value { color: var(--success); }
.summary-out .summary-value { color: var(--danger); }
.summary-label { color: var(--text-muted); font-size: 13px; margin-top: 5px; }
.summary-sub { font-size: 12px; color: var(--text-muted); margin-top: 3px; }
.filter-form { margin: 0; }
.filter-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.filter-group { flex: 1; min-width: 120px; }
.filter-group label { display: block; font-size: 12px; margin-bottom: 3px; color: var(--text-muted); }
.filter-group input, .filter-group select { width: 100%; }
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
