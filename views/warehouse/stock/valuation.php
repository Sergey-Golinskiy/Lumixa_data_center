<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('inventory_valuation') ?></h1>
    <div class="page-actions">
        <a href="/warehouse/stock" class="btn btn-secondary">&laquo; <?= $this->__('back_to_stock') ?></a>
    </div>
</div>

<!-- Summary -->
<div class="summary-cards" style="margin-bottom: 20px;">
    <div class="summary-card">
        <div class="summary-value"><?= number_format($grandTotal, 2) ?></div>
        <div class="summary-label"><?= $this->__('total_inventory_value') ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= count($valuation) ?></div>
        <div class="summary-label"><?= $this->__('items_with_stock') ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= count($byCategory) ?></div>
        <div class="summary-label"><?= $this->__('categories') ?></div>
    </div>
</div>

<!-- Category Breakdown -->
<?php if (!empty($byCategory)): ?>
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header"><?= $this->__('value_by_category') ?></div>
    <div class="card-body">
        <div class="category-grid">
            <?php foreach ($byCategory as $cat => $data): ?>
            <?php $pct = $grandTotal > 0 ? ($data['value'] / $grandTotal) * 100 : 0; ?>
            <div class="category-item">
                <div class="category-name"><?= $this->e($cat) ?></div>
                <div class="category-bar">
                    <div class="category-fill" style="width: <?= $pct ?>%"></div>
                </div>
                <div class="category-stats">
                    <span><?= number_format($data['value'], 2) ?></span>
                    <span class="text-muted">(<?= number_format($pct, 1) ?>%)</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filter -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <select name="category" onchange="this.form.submit()">
                        <option value=""><?= $this->__('all_categories') ?></option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $this->e($cat['type']) ?>" <?= $category === $cat['type'] ? 'selected' : '' ?>>
                            <?= $this->e(ucfirst($cat['type'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Valuation Table -->
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
                        <th class="text-right"><?= $this->__('avg_cost') ?></th>
                        <th class="text-right"><?= $this->__('total_value') ?></th>
                        <th class="text-right"><?= $this->__('percent_of_total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($valuation)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted"><?= $this->__('no_inventory') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($valuation as $item): ?>
                    <?php $pct = $grandTotal > 0 ? ($item['total_value'] / $grandTotal) * 100 : 0; ?>
                    <tr>
                        <td>
                            <a href="/warehouse/stock/<?= $item['id'] ?>">
                                <strong><?= $this->e($item['sku']) ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($item['name']) ?></td>
                        <td><?= $this->__('item_type_' . ($item['type'] ?? 'material')) ?></td>
                        <td class="text-right">
                            <?= number_format($item['quantity'], 3) ?>
                            <small class="text-muted"><?= $this->__('unit_' . ($item['unit'] ?? 'pcs')) ?></small>
                        </td>
                        <td class="text-right"><?= number_format($item['avg_cost'], 4) ?></td>
                        <td class="text-right"><strong><?= number_format($item['total_value'], 2) ?></strong></td>
                        <td class="text-right">
                            <?= number_format($pct, 1) ?>%
                            <div class="mini-bar">
                                <div class="mini-fill" style="width: <?= min(100, $pct * 2) ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right"><strong><?= $this->__('grand_total') ?>:</strong></td>
                        <td class="text-right"><strong><?= number_format($grandTotal, 2) ?></strong></td>
                        <td class="text-right"><strong>100%</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
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
.summary-value { font-size: 28px; font-weight: bold; color: var(--primary); }
.summary-label { color: var(--text-muted); font-size: 13px; margin-top: 5px; }
.category-grid { display: flex; flex-direction: column; gap: 10px; }
.category-item { display: flex; align-items: center; gap: 15px; }
.category-name { width: 150px; font-weight: 500; }
.category-bar {
    flex: 1;
    height: 20px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
}
.category-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 4px;
}
.category-stats { width: 180px; text-align: right; }
.filter-form { margin: 0; }
.filter-row { display: flex; gap: 10px; }
.filter-group select { width: 200px; }
.text-right { text-align: right; }
.mini-bar {
    height: 3px;
    background: var(--border);
    border-radius: 2px;
    margin-top: 3px;
    overflow: hidden;
}
.mini-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 2px;
}
</style>

<?php $this->endSection(); ?>
