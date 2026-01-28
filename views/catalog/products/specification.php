<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/products/<?= $product['id'] ?>" class="btn btn-secondary">&laquo; <?= $this->__('back_to_product') ?></a>
    <a href="/catalog/products/<?= $product['id'] ?>/specification/pdf" class="btn btn-primary" target="_blank">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        <?= $this->__('download_specification') ?>
    </a>
    <button onclick="window.print()" class="btn btn-outline">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        <?= $this->__('print') ?>
    </button>
</div>

<div class="specification-container">
    <!-- Header -->
    <div class="spec-header">
        <div class="spec-title">
            <h1><?= $this->__('product_specification') ?></h1>
            <div class="spec-subtitle"><?= $this->__('bill_of_materials') ?> (BOM)</div>
        </div>
        <div class="spec-meta">
            <div class="spec-date"><?= $this->__('generated') ?>: <?= $this->e($generatedAt) ?></div>
        </div>
    </div>

    <!-- Product Info -->
    <div class="spec-section">
        <div class="spec-section-header"><?= $this->__('product_information') ?></div>
        <div class="spec-product-info">
            <div class="spec-product-main">
                <?php if (!empty($product['image_path'])): ?>
                <div class="spec-product-image">
                    <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->e($product['name']) ?>">
                </div>
                <?php endif; ?>
                <div class="spec-product-details">
                    <div class="spec-row">
                        <span class="spec-label"><?= $this->__('code') ?>:</span>
                        <span class="spec-value spec-code"><?= $this->e($product['code']) ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label"><?= $this->__('name') ?>:</span>
                        <span class="spec-value"><?= $this->e($product['name']) ?></span>
                    </div>
                    <?php if (!empty($product['category_name'])): ?>
                    <div class="spec-row">
                        <span class="spec-label"><?= $this->__('category') ?>:</span>
                        <span class="spec-value"><?= $this->e($product['category_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($product['collection_name'])): ?>
                    <div class="spec-row">
                        <span class="spec-label"><?= $this->__('collection') ?>:</span>
                        <span class="spec-value"><?= $this->e($product['collection_name']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="spec-product-cost">
                <div class="spec-cost-item">
                    <span class="spec-cost-label"><?= $this->__('production_cost') ?>:</span>
                    <span class="spec-cost-value"><?= $this->currency($costData['total_cost'] ?? 0) ?></span>
                </div>
                <div class="spec-cost-item">
                    <span class="spec-cost-label"><?= $this->__('base_price') ?>:</span>
                    <span class="spec-cost-value"><?= $this->currency($product['base_price'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Components (Details & Items) -->
    <div class="spec-section">
        <div class="spec-section-header">
            <?= $this->__('product_composition') ?>
            <span class="spec-badge"><?= count($components) ?> <?= $this->__('items') ?></span>
        </div>
        <?php if (!empty($components)): ?>
        <table class="spec-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th><?= $this->__('sku') ?></th>
                    <th><?= $this->__('name') ?></th>
                    <th><?= $this->__('type') ?></th>
                    <th><?= $this->__('material') ?></th>
                    <th class="text-right"><?= $this->__('quantity') ?></th>
                    <th class="text-right"><?= $this->__('unit_cost') ?></th>
                    <th class="text-right"><?= $this->__('total_cost') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $n = 1; foreach ($components as $comp): ?>
                <tr>
                    <td><?= $n++ ?></td>
                    <td><strong><?= $this->e($comp['component_type'] === 'detail' ? ($comp['detail_sku'] ?? '') : ($comp['item_sku'] ?? '')) ?></strong></td>
                    <td><?= $this->e($comp['component_type'] === 'detail' ? ($comp['detail_name'] ?? '') : ($comp['item_name'] ?? '')) ?></td>
                    <td>
                        <?php if ($comp['component_type'] === 'detail'): ?>
                        <span class="badge badge-info"><?= $this->__('detail') ?></span>
                        <?php else: ?>
                        <span class="badge badge-component"><?= $this->__('component') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($comp['component_type'] === 'detail' && !empty($comp['material_name'])): ?>
                        <?= $this->e($comp['material_alias'] ?? $comp['material_sku'] ?? '') ?>
                        <small class="text-muted"><?= $this->e($comp['material_name']) ?></small>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= $this->number($comp['quantity'], 4) ?></td>
                    <td class="text-right"><?= $this->currency($comp['calculated_cost'] ?? 0) ?></td>
                    <td class="text-right"><strong><?= $this->currency($comp['total_cost'] ?? 0) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="7" class="text-right"><strong><?= $this->__('total_components_cost') ?>:</strong></td>
                    <td class="text-right"><strong><?= $this->currency(($costData['details_cost'] ?? 0) + ($costData['items_cost'] ?? 0)) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="spec-empty"><?= $this->__('no_components') ?></div>
        <?php endif; ?>
    </div>

    <!-- Packaging -->
    <div class="spec-section">
        <div class="spec-section-header">
            <?= $this->__('packaging') ?>
            <span class="spec-badge"><?= count($packaging) ?> <?= $this->__('items') ?></span>
        </div>
        <?php if (!empty($packaging)): ?>
        <table class="spec-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th><?= $this->__('sku') ?></th>
                    <th><?= $this->__('name') ?></th>
                    <th><?= $this->__('unit') ?></th>
                    <th class="text-right"><?= $this->__('quantity') ?></th>
                    <th class="text-right"><?= $this->__('unit_cost') ?></th>
                    <th class="text-right"><?= $this->__('total_cost') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $n = 1; foreach ($packaging as $pack): ?>
                <tr>
                    <td><?= $n++ ?></td>
                    <td><strong><?= $this->e($pack['item_sku'] ?? '') ?></strong></td>
                    <td><?= $this->e($pack['item_name'] ?? '') ?></td>
                    <td><?= $this->e($pack['unit'] ?? 'pcs') ?></td>
                    <td class="text-right"><?= $this->number($pack['quantity'], 4) ?></td>
                    <td class="text-right"><?= $this->currency($pack['calculated_cost'] ?? 0) ?></td>
                    <td class="text-right"><strong><?= $this->currency($pack['total_cost'] ?? 0) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-right"><strong><?= $this->__('total_packaging_cost') ?>:</strong></td>
                    <td class="text-right"><strong><?= $this->currency($costData['packaging_cost'] ?? 0) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="spec-empty"><?= $this->__('no_packaging') ?></div>
        <?php endif; ?>
    </div>

    <!-- Operations -->
    <div class="spec-section">
        <div class="spec-section-header">
            <?= $this->__('operations') ?>
            <span class="spec-badge"><?= count($operations) ?> <?= $this->__('operations') ?></span>
            <?php if (($costData['total_time_minutes'] ?? 0) > 0): ?>
            <span class="spec-badge spec-badge-time"><?= $costData['total_time_minutes'] ?> <?= $this->__('minutes_short') ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($operations)): ?>
        <table class="spec-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th><?= $this->__('operation_name') ?></th>
                    <th><?= $this->__('description') ?></th>
                    <th><?= $this->__('components') ?></th>
                    <th class="text-right"><?= $this->__('time_minutes') ?></th>
                    <th class="text-right"><?= $this->__('labor_rate') ?></th>
                    <th class="text-right"><?= $this->__('operation_cost') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $n = 1; foreach ($operations as $op): ?>
                <tr>
                    <td><?= $n++ ?></td>
                    <td><strong><?= $this->e($op['name'] ?? '') ?></strong></td>
                    <td><small><?= $this->e($op['description'] ?? '-') ?></small></td>
                    <td>
                        <?php if (!empty($op['components'])): ?>
                        <?php foreach ($op['components'] as $opComp): ?>
                        <span class="badge badge-sm"><?= $this->e($opComp['component_type'] === 'detail' ? ($opComp['detail_sku'] ?? '') : ($opComp['item_sku'] ?? '')) ?></span>
                        <?php endforeach; ?>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= (int)($op['time_minutes'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                    <td class="text-right"><?= $this->currency($op['labor_rate'] ?? 0) ?>/<?= $this->__('hour_short') ?></td>
                    <td class="text-right"><strong><?= $this->currency($op['operation_cost'] ?? 0) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong><?= $this->__('total_labor_cost') ?>:</strong></td>
                    <td class="text-right"><strong><?= $costData['total_time_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></strong></td>
                    <td></td>
                    <td class="text-right"><strong><?= $this->currency($costData['labor_cost'] ?? 0) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="spec-empty"><?= $this->__('no_operations') ?></div>
        <?php endif; ?>
    </div>

    <!-- Cost Summary -->
    <div class="spec-section spec-summary">
        <div class="spec-section-header"><?= $this->__('cost_summary') ?></div>
        <div class="spec-summary-grid">
            <div class="spec-summary-item">
                <span class="spec-summary-label"><?= $this->__('details_cost') ?></span>
                <span class="spec-summary-value"><?= $this->currency($costData['details_cost'] ?? 0) ?></span>
            </div>
            <div class="spec-summary-item">
                <span class="spec-summary-label"><?= $this->__('components_cost') ?></span>
                <span class="spec-summary-value"><?= $this->currency($costData['items_cost'] ?? 0) ?></span>
            </div>
            <div class="spec-summary-item">
                <span class="spec-summary-label"><?= $this->__('labor_cost') ?></span>
                <span class="spec-summary-value"><?= $this->currency($costData['labor_cost'] ?? 0) ?></span>
            </div>
            <div class="spec-summary-item">
                <span class="spec-summary-label"><?= $this->__('packaging_cost') ?></span>
                <span class="spec-summary-value"><?= $this->currency($costData['packaging_cost'] ?? 0) ?></span>
            </div>
            <div class="spec-summary-total">
                <span class="spec-summary-label"><?= $this->__('total_production_cost') ?></span>
                <span class="spec-summary-value"><?= $this->currency($costData['total_cost'] ?? 0) ?></span>
            </div>
            <div class="spec-summary-total spec-summary-price">
                <span class="spec-summary-label"><?= $this->__('total_price') ?></span>
                <span class="spec-summary-value"><?= $this->currency($costData['total_price'] ?? 0) ?></span>
            </div>
        </div>
    </div>
</div>

<style>
.specification-container {
    max-width: 1000px;
    margin: 0 auto;
    background: var(--bg-primary);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.spec-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 30px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark, #1a5a9e) 100%);
    color: white;
}

.spec-title h1 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.spec-subtitle {
    opacity: 0.8;
    font-size: 0.9rem;
    margin-top: 4px;
}

.spec-meta {
    text-align: right;
    font-size: 0.85rem;
    opacity: 0.9;
}

.spec-section {
    border-bottom: 1px solid var(--border);
}

.spec-section:last-child {
    border-bottom: none;
}

.spec-section-header {
    padding: 15px 30px;
    background: var(--bg-secondary);
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.spec-badge {
    background: var(--primary);
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.spec-badge-time {
    background: var(--info);
}

.spec-product-info {
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    gap: 30px;
}

.spec-product-main {
    display: flex;
    gap: 20px;
    flex: 1;
}

.spec-product-image {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid var(--border);
}

.spec-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.spec-product-details {
    flex: 1;
}

.spec-row {
    display: flex;
    padding: 6px 0;
}

.spec-label {
    width: 120px;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.spec-value {
    flex: 1;
}

.spec-code {
    font-size: 1.2rem;
    color: var(--primary);
}

.spec-product-cost {
    background: var(--bg-secondary);
    padding: 15px 20px;
    border-radius: 8px;
    min-width: 200px;
}

.spec-cost-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed var(--border);
}

.spec-cost-item:last-child {
    border-bottom: none;
}

.spec-cost-label {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.spec-cost-value {
    font-weight: 600;
    font-family: monospace;
}

.spec-table {
    width: 100%;
    border-collapse: collapse;
}

.spec-table th,
.spec-table td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--border);
    text-align: left;
}

.spec-table th {
    background: var(--bg-secondary);
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text-muted);
    text-transform: uppercase;
}

.spec-table th:first-child,
.spec-table td:first-child {
    padding-left: 30px;
}

.spec-table th:last-child,
.spec-table td:last-child {
    padding-right: 30px;
}

.spec-table tbody tr:hover {
    background: var(--bg-secondary);
}

.spec-table .total-row {
    background: var(--bg-secondary);
    font-weight: 600;
}

.spec-table .total-row td {
    border-top: 2px solid var(--border);
    padding: 15px;
}

.spec-empty {
    padding: 30px;
    text-align: center;
    color: var(--text-muted);
}

.spec-summary {
    background: var(--bg-secondary);
}

.spec-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    padding: 20px 30px;
}

.spec-summary-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 15px;
    background: var(--bg-primary);
    border-radius: 8px;
}

.spec-summary-label {
    color: var(--text-muted);
}

.spec-summary-value {
    font-weight: 600;
    font-family: monospace;
}

.spec-summary-total {
    grid-column: span 2;
    display: flex;
    justify-content: space-between;
    padding: 15px 20px;
    background: var(--primary);
    color: white;
    border-radius: 8px;
    font-size: 1.1rem;
}

.spec-summary-total .spec-summary-label,
.spec-summary-total .spec-summary-value {
    color: white;
}

.spec-summary-price {
    background: var(--success);
}

.text-right { text-align: right; }
.text-muted { color: var(--text-muted); }

.badge-sm {
    font-size: 0.7rem;
    padding: 2px 6px;
}

@media print {
    .page-actions { display: none; }
    .specification-container {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<?php $this->endSection(); ?>
