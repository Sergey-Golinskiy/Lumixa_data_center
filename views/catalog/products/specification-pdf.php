<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->__('product_specification') ?>: <?= $this->e($product['code']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-left h1 {
            font-size: 20px;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .header-left .subtitle {
            color: #666;
            font-size: 11px;
        }
        .header-right {
            text-align: right;
            font-size: 11px;
            color: #666;
        }
        .product-info {
            display: flex;
            gap: 30px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .product-details {
            flex: 1;
        }
        .product-row {
            display: flex;
            padding: 5px 0;
        }
        .product-label {
            width: 100px;
            color: #666;
            font-size: 11px;
        }
        .product-value {
            flex: 1;
            font-weight: 500;
        }
        .product-code {
            font-size: 16px;
            color: #2563eb;
            font-weight: 600;
        }
        .cost-box {
            background: #2563eb;
            color: white;
            padding: 15px;
            border-radius: 8px;
            min-width: 180px;
        }
        .cost-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .cost-row:last-child {
            border-bottom: none;
            padding-top: 10px;
            font-weight: 600;
            font-size: 14px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-header {
            background: #f1f5f9;
            padding: 10px 15px;
            font-weight: 600;
            font-size: 13px;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }
        .section-badge {
            background: #2563eb;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background: #f1f5f9;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 500;
        }
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-component {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-sm {
            padding: 1px 4px;
            font-size: 8px;
            margin: 1px;
        }
        .empty-message {
            padding: 20px;
            text-align: center;
            color: #94a3b8;
            font-style: italic;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .summary-item {
            background: #f8fafc;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }
        .summary-label {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }
        .summary-total {
            grid-column: span 2;
            background: #2563eb;
            color: white;
        }
        .summary-total .summary-label,
        .summary-total .summary-value {
            color: white;
        }
        .summary-price {
            background: #16a34a;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
        @media print {
            body { padding: 0; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1><?= $this->__('product_specification') ?></h1>
            <div class="subtitle"><?= $this->__('bill_of_materials') ?> (BOM)</div>
        </div>
        <div class="header-right">
            <div><strong><?= $this->__('document') ?>:</strong> SPEC-<?= $this->e($product['code']) ?></div>
            <div><strong><?= $this->__('generated') ?>:</strong> <?= $this->e($generatedAt) ?></div>
        </div>
    </div>

    <div class="product-info">
        <div class="product-details">
            <div class="product-row">
                <span class="product-label"><?= $this->__('code') ?>:</span>
                <span class="product-value product-code"><?= $this->e($product['code']) ?></span>
            </div>
            <div class="product-row">
                <span class="product-label"><?= $this->__('name') ?>:</span>
                <span class="product-value"><?= $this->e($product['name']) ?></span>
            </div>
            <?php if (!empty($product['category_name'])): ?>
            <div class="product-row">
                <span class="product-label"><?= $this->__('category') ?>:</span>
                <span class="product-value"><?= $this->e($product['category_name']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($product['collection_name'])): ?>
            <div class="product-row">
                <span class="product-label"><?= $this->__('collection') ?>:</span>
                <span class="product-value"><?= $this->e($product['collection_name']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="cost-box">
            <div class="cost-row">
                <span><?= $this->__('production_cost') ?>:</span>
                <span><?= $this->currency($costData['total_cost'] ?? 0) ?></span>
            </div>
            <div class="cost-row">
                <span><?= $this->__('total_price') ?>:</span>
                <span><?= $this->currency($costData['total_price'] ?? 0) ?></span>
            </div>
        </div>
    </div>

    <!-- Components -->
    <div class="section">
        <div class="section-header">
            <?= $this->__('product_composition') ?>
            <span class="section-badge"><?= count($components) ?></span>
        </div>
        <?php if (!empty($components)): ?>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
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
                        <span class="badge <?= $comp['component_type'] === 'detail' ? 'badge-info' : 'badge-component' ?>">
                            <?= $comp['component_type'] === 'detail' ? $this->__('detail') : $this->__('component') ?>
                        </span>
                    </td>
                    <td><?= $comp['component_type'] === 'detail' && !empty($comp['material_name']) ? $this->e($comp['material_alias'] ?? $comp['material_sku'] ?? '') . ' ' . $this->e($comp['material_name']) : '-' ?></td>
                    <td class="text-right"><?= $this->number($comp['quantity'], 4) ?></td>
                    <td class="text-right"><?= $this->currency($comp['calculated_cost'] ?? 0) ?></td>
                    <td class="text-right"><strong><?= $this->currency($comp['total_cost'] ?? 0) ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="7" class="text-right"><?= $this->__('total_components_cost') ?>:</td>
                    <td class="text-right"><?= $this->currency(($costData['details_cost'] ?? 0) + ($costData['items_cost'] ?? 0)) ?></td>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-message"><?= $this->__('no_components') ?></div>
        <?php endif; ?>
    </div>

    <!-- Packaging -->
    <div class="section">
        <div class="section-header">
            <?= $this->__('packaging') ?>
            <span class="section-badge"><?= count($packaging) ?></span>
        </div>
        <?php if (!empty($packaging)): ?>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
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
                <tr class="total-row">
                    <td colspan="6" class="text-right"><?= $this->__('total_packaging_cost') ?>:</td>
                    <td class="text-right"><?= $this->currency($costData['packaging_cost'] ?? 0) ?></td>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-message"><?= $this->__('no_packaging') ?></div>
        <?php endif; ?>
    </div>

    <!-- Operations -->
    <div class="section">
        <div class="section-header">
            <?= $this->__('operations') ?>
            <span class="section-badge"><?= count($operations) ?></span>
            <?php if (($costData['total_time_minutes'] ?? 0) > 0): ?>
            <span class="section-badge"><?= $costData['total_time_minutes'] ?> <?= $this->__('minutes_short') ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($operations)): ?>
        <table>
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
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
                    <td style="font-size:10px;"><?= $this->e($op['description'] ?? '-') ?></td>
                    <td>
                        <?php if (!empty($op['components'])): ?>
                        <?php foreach ($op['components'] as $opComp): ?>
                        <span class="badge badge-sm badge-info"><?= $this->e($opComp['component_type'] === 'detail' ? ($opComp['detail_sku'] ?? '') : ($opComp['item_sku'] ?? '')) ?></span>
                        <?php endforeach; ?>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td class="text-right"><?= (int)($op['time_minutes'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                    <td class="text-right"><?= $this->currency($op['labor_rate'] ?? 0) ?>/<?= $this->__('hour_short') ?></td>
                    <td class="text-right"><strong><?= $this->currency($op['operation_cost'] ?? 0) ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><?= $this->__('total_labor_cost') ?>:</td>
                    <td class="text-right"><?= $costData['total_time_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></td>
                    <td></td>
                    <td class="text-right"><?= $this->currency($costData['labor_cost'] ?? 0) ?></td>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-message"><?= $this->__('no_operations') ?></div>
        <?php endif; ?>
    </div>

    <!-- Summary -->
    <div class="section">
        <div class="section-header"><?= $this->__('cost_summary') ?></div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label"><?= $this->__('details_cost') ?></div>
                <div class="summary-value"><?= $this->currency($costData['details_cost'] ?? 0) ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label"><?= $this->__('components_cost') ?></div>
                <div class="summary-value"><?= $this->currency($costData['items_cost'] ?? 0) ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label"><?= $this->__('labor_cost') ?></div>
                <div class="summary-value"><?= $this->currency($costData['labor_cost'] ?? 0) ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label"><?= $this->__('packaging_cost') ?></div>
                <div class="summary-value"><?= $this->currency($costData['packaging_cost'] ?? 0) ?></div>
            </div>
            <div class="summary-item summary-total">
                <div class="summary-label"><?= $this->__('total_production_cost') ?></div>
                <div class="summary-value"><?= $this->currency($costData['total_cost'] ?? 0) ?></div>
            </div>
            <div class="summary-item summary-total summary-price">
                <div class="summary-label"><?= $this->__('total_price') ?></div>
                <div class="summary-value"><?= $this->currency($costData['total_price'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <div class="footer">
        <?= $this->__('document_generated_by') ?? 'Generated by' ?> Lumixa LMS | <?= $generatedAt ?>
    </div>
</body>
</html>
