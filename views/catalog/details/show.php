<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/details" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
    <?php if ($this->can('catalog.details.edit')): ?>
    <a href="/catalog/details/<?= $detail['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit_detail') ?></a>
    <?php endif; ?>
    <?php if ($this->can('catalog.details.create')): ?>
    <a href="/catalog/details/<?= $detail['id'] ?>/copy" target="_blank" class="btn btn-outline" title="<?= $this->__('copy_detail') ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        <?= $this->__('copy') ?>
    </a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Detail Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('detail_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('photo') ?></span>
                <span class="detail-value">
                    <?php if (!empty($detail['image_path'])): ?>
                    <div class="detail-image-container">
                        <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>"
                             alt="<?= $this->e($detail['name']) ?>"
                             class="detail-image-large"
                             data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
                    </div>
                    <?php else: ?>
                    <div class="detail-image-placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.4">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <span><?= $this->__('no_photo') ?></span>
                    </div>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('sku') ?></span>
                <span class="detail-value"><strong class="detail-sku"><?= $this->e($detail['sku']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($detail['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('detail_type') ?></span>
                <span class="detail-value">
                    <?php if ($detail['detail_type'] === 'printed'): ?>
                    <span class="badge badge-info"><?= $this->__('detail_type_printed') ?></span>
                    <?php else: ?>
                    <span class="badge badge-secondary"><?= $this->__('detail_type_purchased') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php if (!empty($detail['is_active'])): ?>
                    <span class="badge badge-success"><?= $this->__('active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($detail['model_path'])): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('model_file') ?></span>
                <span class="detail-value">
                    <a href="/<?= $this->e(ltrim($detail['model_path'], '/')) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <?= $this->__('download_model') ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Print Settings (only for printed details) -->
    <?php if ($detail['detail_type'] === 'printed'): ?>
    <div class="card">
        <div class="card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <?= $this->__('print_settings') ?>
        </div>
        <div class="card-body">
            <?php if (!empty($detailMaterials) && count($detailMaterials) > 1): ?>
            <!-- Multi-material display -->
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('materials') ?> (<?= count($detailMaterials) ?>)</span>
                <span class="detail-value">
                    <?= $this->__('multi_color_printing') ?>
                </span>
            </div>
            <?php foreach ($detailMaterials as $dm): ?>
            <div class="detail-row detail-row-sub">
                <span class="detail-label">
                    <strong><?= $this->e($dm['material_qty_grams']) ?></strong> <?= $this->__('grams_short') ?>
                </span>
                <span class="detail-value">
                    <a href="/warehouse/items/<?= $dm['material_item_id'] ?>" class="material-link">
                        <?php if (!empty($dm['filament_alias'])): ?>
                        <span class="badge badge-alias"<?php if (!empty($dm['alias_color'])): ?> style="background: <?= $this->e($dm['alias_color']) ?>; color: <?= $this->contrastColor($dm['alias_color']) ?>"<?php endif; ?>><?= $this->e($dm['filament_alias']) ?></span>
                        <?php endif; ?>
                        <span class="badge badge-material"><?= $this->e($dm['material_sku'] ?? '') ?></span>
                        <?= !empty($dm['material_name']) ? ' ' . $this->e($dm['material_name']) : '' ?>
                    </a>
                </span>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <!-- Single material display -->
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('material') ?></span>
                <span class="detail-value">
                    <?php if (!empty($detail['material_item_id'])): ?>
                    <a href="/warehouse/items/<?= $detail['material_item_id'] ?>" class="material-link">
                        <?php if (!empty($detail['material_filament_alias'])): ?>
                        <span class="badge badge-alias"<?php if (!empty($aliasColor)): ?> style="background: <?= $this->e($aliasColor) ?>; color: <?= $this->contrastColor($aliasColor) ?>"<?php endif; ?>><?= $this->e($detail['material_filament_alias']) ?></span>
                        <?php endif; ?>
                        <span class="badge badge-material"><?= $this->e($detail['material_sku'] ?? '') ?></span>
                        <?= !empty($detail['material_name']) ? ' ' . $this->e($detail['material_name']) : '' ?>
                    </a>
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('printer') ?></span>
                <span class="detail-value">
                    <?php if (!empty($detail['printer_id'])): ?>
                    <a href="/equipment/printers/<?= $detail['printer_id'] ?>" class="printer-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        <?= $this->e($detail['printer_name'] ?? '') ?><?= !empty($detail['printer_model']) ? ' - ' . $this->e($detail['printer_model']) : '' ?>
                    </a>
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (empty($detailMaterials) || count($detailMaterials) <= 1): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('material_qty_grams') ?></span>
                <span class="detail-value">
                    <?php if ($detail['material_qty_grams']): ?>
                    <strong><?= $this->e($detail['material_qty_grams']) ?></strong> <?= $this->__('grams_short') ?>
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('print_time_minutes') ?></span>
                <span class="detail-value">
                    <?php if ($detail['print_time_minutes']): ?>
                    <strong><?= $this->e($detail['print_time_minutes']) ?></strong> <?= $this->__('minutes_short') ?>
                    <?php if ($detail['print_time_minutes'] >= 60): ?>
                    <small class="text-muted">(<?= floor($detail['print_time_minutes'] / 60) ?><?= $this->__('hours_short') ?> <?= $detail['print_time_minutes'] % 60 ?><?= $this->__('minutes_short') ?>)</small>
                    <?php endif; ?>
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($detail['print_parameters'])): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('print_parameters') ?></span>
                <span class="detail-value">
                    <code class="print-params"><?= $this->e($detail['print_parameters']) ?></code>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Used in Products Section -->
<?php if (!empty($usedInProducts)): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>
        <?= $this->__('used_in_products') ?>
        <span class="badge badge-secondary"><?= count($usedInProducts) ?></span>
    </div>
    <div class="card-body">
        <div class="products-grid">
            <?php foreach ($usedInProducts as $product): ?>
            <a href="/catalog/products/<?= $product['id'] ?>" class="product-card-link">
                <div class="product-card-mini">
                    <div class="product-card-image">
                        <?php if (!empty($product['image_path'])): ?>
                        <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->e($product['name']) ?>">
                        <?php else: ?>
                        <div class="product-image-placeholder">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.4">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-info">
                        <div class="product-card-code"><?= $this->e($product['code']) ?></div>
                        <div class="product-card-name"><?= $this->e($product['name']) ?></div>
                        <?php if (!empty($product['category_name'])): ?>
                        <div class="product-card-category"><?= $this->e($product['category_name']) ?></div>
                        <?php endif; ?>
                        <div class="product-card-qty">
                            <span class="qty-label"><?= $this->__('quantity') ?>:</span>
                            <strong><?= $this->number($product['quantity'], 4) ?></strong>
                        </div>
                    </div>
                    <?php if (!$product['is_active']): ?>
                    <span class="product-inactive-badge"><?= $this->__('inactive') ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Production Cost Section (only for printed details) -->
<?php if ($detail['detail_type'] === 'printed' && isset($costData)): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
        <?= $this->__('production_cost') ?>
        <?php if ($costData['total_cost'] > 0): ?>
        <span class="cost-total-badge"><?= $this->currency($costData['total_cost']) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($costData['missing_data'])): ?>
        <div class="alert alert-warning">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <strong><?= $this->__('missing_data_for_calculation') ?>:</strong>
            <ul style="margin: 5px 0 0 20px;">
                <?php foreach ($costData['missing_data'] as $missing): ?>
                <li><?= $this->__('missing_' . $missing) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($costBreakdown)): ?>
        <div class="cost-breakdown">
            <table class="cost-table">
                <thead>
                    <tr>
                        <th><?= $this->__('cost_component') ?></th>
                        <th><?= $this->__('calculation') ?></th>
                        <th class="text-right"><?= $this->__('amount') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($costBreakdown as $item): ?>
                    <tr<?php if (!empty($item['is_sub'])): ?> class="cost-row-sub"<?php endif; ?>>
                        <td>
                            <span class="cost-icon cost-icon-<?= $this->e($item['type']) ?>"></span>
                            <?php if (!empty($item['label_raw'])): ?>
                            <?= $this->e($item['label_raw']) ?>
                            <?php else: ?>
                            <?= $this->__($item['label']) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><code><?= $this->e($item['details']) ?></code></td>
                        <td class="text-right"><?= $this->currency($item['value']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="cost-total-row">
                        <td colspan="2"><strong><?= $this->__('total_production_cost') ?></strong></td>
                        <td class="text-right"><strong><?= $this->currency($costData['total_cost']) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="cost-info-panel">
            <div class="cost-info-header">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <?= $this->__('calculation_parameters') ?>
            </div>
            <div class="cost-params-groups">
                <!-- Materials Group -->
                <div class="cost-params-group">
                    <div class="cost-params-group-title">
                        <span class="cost-icon cost-icon-material"></span>
                        <?= $this->__('materials') ?>
                    </div>
                    <?php
                    $matDetails = $costData['material_details'] ?? [];
                    if (count($matDetails) > 1):
                        foreach ($matDetails as $md):
                    ?>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->e($md['filament_alias'] ?: $md['material_sku']) ?>:</span>
                        <span class="cost-info-value"><?= number_format($md['cost_per_gram'], 4) ?> / <?= $this->__('grams_short') ?> &times; <?= number_format($md['qty_grams'], 2) ?> <?= $this->__('grams_short') ?></span>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('material_cost_per_gram') ?>:</span>
                        <span class="cost-info-value"><?= number_format($costData['material_cost_per_gram'], 4) ?></span>
                    </div>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('material_qty_grams') ?>:</span>
                        <span class="cost-info-value"><?= number_format($costData['calculation_details']['material_qty_grams'] ?? 0, 2) ?> <?= $this->__('grams_short') ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Print Time Group -->
                <div class="cost-params-group">
                    <div class="cost-params-group-title">
                        <span class="cost-icon cost-icon-electricity"></span>
                        <?= $this->__('print_time_hours') ?>
                    </div>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('print_time_minutes') ?>:</span>
                        <span class="cost-info-value"><?= $costData['calculation_details']['print_time_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></span>
                    </div>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('print_time_hours') ?>:</span>
                        <span class="cost-info-value"><?= $costData['calculation_details']['print_time_hours'] ?? 0 ?> <?= $this->__('hours_short') ?></span>
                    </div>
                </div>

                <?php if (!empty($costData['printer'])): ?>
                <!-- Printer Group -->
                <div class="cost-params-group">
                    <div class="cost-params-group-title">
                        <span class="cost-icon cost-icon-amortization"></span>
                        <?= $this->__('printer') ?>
                        <small class="text-muted"><?= $this->e($costData['printer']['name'] ?? '') ?><?= !empty($costData['printer']['model']) ? ' - ' . $this->e($costData['printer']['model']) : '' ?></small>
                    </div>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('printer_power') ?>:</span>
                        <span class="cost-info-value"><?= $costData['printer']['power_watts'] ?? 0 ?> <?= $this->__('watts_short') ?></span>
                    </div>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('electricity_rate') ?>:</span>
                        <span class="cost-info-value"><?= number_format($costData['printer']['electricity_cost_per_kwh'] ?? 0, 4) ?> / <?= $this->__('kwh_short') ?></span>
                    </div>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('amortization_rate') ?>:</span>
                        <span class="cost-info-value"><?= number_format($costData['printer']['amortization_per_hour'] ?? 0, 4) ?> / <?= $this->__('hour_short') ?></span>
                    </div>
                    <div class="cost-info-item">
                        <span class="cost-info-label"><?= $this->__('maintenance_rate') ?>:</span>
                        <span class="cost-info-value"><?= number_format($costData['printer']['maintenance_per_hour'] ?? 0, 4) ?> / <?= $this->__('hour_short') ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Detail Operations (Routing) -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
        </svg>
        <?= $this->__('detail_routing') ?>
        <span class="badge badge-secondary"><?= count($operations ?? []) ?> <?= $this->__('operations') ?></span>
        <?php if (($laborCost['total_minutes'] ?? 0) > 0): ?>
        <span class="badge badge-info"><?= $laborCost['total_minutes'] ?> <?= $this->__('minutes_short') ?></span>
        <?php endif; ?>
        <?php if (($laborCost['total_cost'] ?? 0) > 0): ?>
        <span class="cost-total-badge"><?= $this->currency($laborCost['total_cost']) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.details.edit')): ?>
        <!-- Add Operation Form -->
        <div class="add-component-section">
            <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations" class="add-operation-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">

                <div class="operation-form-grid">
                    <!-- Operation Name -->
                    <div class="form-group">
                        <label><?= $this->__('operation_name') ?> *</label>
                        <input type="text" name="name" required placeholder="<?= $this->__('operation_name_placeholder') ?>">
                    </div>

                    <!-- Time and Rate -->
                    <div class="form-row-inline">
                        <div class="form-group">
                            <label><?= $this->__('time_minutes') ?></label>
                            <input type="number" name="time_minutes" value="0" min="0" step="1">
                        </div>
                        <div class="form-group">
                            <label><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                            <input type="number" name="labor_rate" value="0" min="0" step="0.01">
                        </div>
                    </div>

                    <!-- Multi-material Selection -->
                    <div class="form-group form-group-full">
                        <label><?= $this->__('materials') ?></label>
                        <div class="material-checkboxes">
                            <?php foreach ($materials ?? [] as $mat): ?>
                            <label class="material-checkbox-label">
                                <input type="checkbox" name="material_ids[]" value="<?= $mat['id'] ?>">
                                <span class="material-checkbox-badge">
                                    <?php if (!empty($mat['filament_alias'])): ?>
                                    <strong><?= $this->e($mat['filament_alias']) ?></strong>
                                    <?php endif; ?>
                                    <span><?= $this->e($mat['sku']) ?></span>
                                    <small><?= $this->e($mat['name']) ?></small>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= $this->__('printer') ?></label>
                        <select name="printer_id">
                            <option value=""><?= $this->__('select_printer') ?></option>
                            <?php foreach ($printers ?? [] as $pr): ?>
                            <option value="<?= $pr['id'] ?>">
                                <?= $this->e($pr['name']) ?><?= !empty($pr['model']) ? ' - ' . $this->e($pr['model']) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?= $this->__('tool') ?></label>
                        <select name="tool_id">
                            <option value=""><?= $this->__('select_tool') ?></option>
                            <?php foreach ($tools ?? [] as $tool): ?>
                            <option value="<?= $tool['id'] ?>"><?= $this->e($tool['sku']) ?> - <?= $this->e($tool['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="form-group form-group-full">
                        <label><?= $this->__('description') ?></label>
                        <textarea name="description" rows="2" placeholder="<?= $this->__('instructions_placeholder') ?>"></textarea>
                    </div>
                </div>

                <!-- Add Button -->
                <div class="form-actions-center" style="margin-top: 15px;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <?= $this->__('add_operation') ?>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Operations Table -->
        <div class="table-container" style="margin-top: 20px;">
            <table id="operationsTable">
                <thead>
                    <tr>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <th style="width: 60px;"><?= $this->__('order') ?></th>
                        <?php endif; ?>
                        <th style="width: 40px;">#</th>
                        <th><?= $this->__('operation_name') ?></th>
                        <th><?= $this->__('resources') ?></th>
                        <th class="text-right"><?= $this->__('time_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor_rate') ?></th>
                        <th class="text-right"><?= $this->__('operation_cost') ?></th>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <th style="width: 80px;"><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="operationsBody">
                    <?php if (empty($operations)): ?>
                    <tr>
                        <td colspan="<?= $this->can('catalog.details.edit') ? '8' : '6' ?>" class="text-center text-muted"><?= $this->__('no_operations') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php $opNum = 1; $opCount = count($operations); foreach ($operations as $index => $operation): ?>
                    <?php $opCost = ((int)($operation['time_minutes'] ?? 0) / 60) * (float)($operation['labor_rate'] ?? 0); ?>
                    <tr data-operation-id="<?= $operation['id'] ?>">
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <td class="reorder-cell">
                            <div class="reorder-buttons">
                                <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations/<?= $operation['id'] ?>/move-up" style="display:inline;">
                                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline reorder-btn" title="<?= $this->__('move_up') ?>"
                                            <?= $index === 0 ? 'disabled style="opacity: 0.3;"' : '' ?>>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 15l-6-6-6 6"/>
                                        </svg>
                                    </button>
                                </form>
                                <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations/<?= $operation['id'] ?>/move-down" style="display:inline;">
                                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline reorder-btn" title="<?= $this->__('move_down') ?>"
                                            <?= $index === $opCount - 1 ? 'disabled style="opacity: 0.3;"' : '' ?>>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 9l6 6 6-6"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                        <td class="op-number"><strong><?= $opNum++ ?></strong></td>
                        <td>
                            <strong><?= $this->e($operation['name'] ?? '') ?></strong>
                            <?php if (!empty($operation['description'])): ?>
                            <br><small class="text-muted"><?= nl2br($this->e($operation['description'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="operation-resources">
                                <?php
                                $opMats = $operation['operation_materials'] ?? [];
                                $hasMaterials = false;
                                if (!empty($opMats)):
                                    $hasMaterials = true;
                                    $aliasColors = $aliasColors ?? [];
                                    foreach ($opMats as $om):
                                        $omColor = $aliasColors[$om['filament_alias'] ?? ''] ?? null;
                                ?>
                                <span class="badge badge-material" title="<?= $this->e($om['material_name'] ?? '') ?>"<?php if ($omColor): ?> style="background: <?= $this->e($omColor) ?>; color: <?= $this->contrastColor($omColor) ?>"<?php endif; ?>>
                                    <?php if (!empty($om['filament_alias'])): ?>
                                    <?= $this->e($om['filament_alias']) ?>
                                    <?php else: ?>
                                    <?= $this->e($om['material_sku'] ?? '') ?>
                                    <?php endif; ?>
                                </span>
                                <?php endforeach; ?>
                                <?php elseif (!empty($operation['material_sku'])): ?>
                                <?php $hasMaterials = true; ?>
                                <span class="badge badge-material" title="<?= $this->e($operation['material_name'] ?? '') ?>">
                                    <?= $this->e($operation['material_sku']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($operation['printer_name'])): ?>
                                <span class="badge badge-printer" title="<?= $this->__('printer') ?>">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 3px;">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                    </svg>
                                    <?= $this->e($operation['printer_name']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($operation['tool_sku'])): ?>
                                <span class="badge badge-tool" title="<?= $this->e($operation['tool_name'] ?? '') ?>">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 3px;">
                                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                    </svg>
                                    <?= $this->e($operation['tool_sku']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!$hasMaterials && empty($operation['printer_name']) && empty($operation['tool_sku'])): ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-right"><?= (int)($operation['time_minutes'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-right"><?= $this->currency($operation['labor_rate'] ?? 0) ?>/<?= $this->__('hour_short') ?></td>
                        <td class="text-right"><strong><?= $this->currency($opCost) ?></strong></td>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline edit-operation-btn"
                                    data-id="<?= $operation['id'] ?>"
                                    data-name="<?= $this->e($operation['name'] ?? '') ?>"
                                    data-description="<?= $this->e($operation['description'] ?? '') ?>"
                                    data-time="<?= $operation['time_minutes'] ?? 0 ?>"
                                    data-rate="<?= $operation['labor_rate'] ?? 0 ?>"
                                    data-materials="<?= $this->e(implode(',', array_column($operation['operation_materials'] ?? [], 'material_id')) ?: ($operation['material_id'] ?? '')) ?>"
                                    data-printer="<?= $operation['printer_id'] ?? '' ?>"
                                    data-tool="<?= $operation['tool_id'] ?? '' ?>"
                                    title="<?= $this->__('edit') ?>">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations/<?= $operation['id'] ?>/remove" style="display:inline;" onsubmit="return confirm('<?= $this->__('confirm_remove_operation') ?>');">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="<?= $this->__('delete') ?>">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($operations)): ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="<?= $this->can('catalog.details.edit') ? '4' : '3' ?>" class="text-right"><strong><?= $this->__('total') ?>:</strong></td>
                        <td class="text-right"><strong><?= $laborCost['total_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></strong></td>
                        <td></td>
                        <td class="text-right"><strong><?= $this->currency($laborCost['total_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <td></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Edit Operation Modal -->
<?php if ($this->can('catalog.details.edit')): ?>
<div id="editOperationModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= $this->__('edit_operation') ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <form id="editOperationForm" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label><?= $this->__('operation_name') ?> *</label>
                    <input type="text" name="name" id="editOpName" required>
                </div>
                <div class="form-row-modal">
                    <div class="form-group">
                        <label><?= $this->__('time_minutes') ?></label>
                        <input type="number" name="time_minutes" id="editOpTime" min="0" step="1">
                    </div>
                    <div class="form-group">
                        <label><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                        <input type="number" name="labor_rate" id="editOpRate" min="0" step="0.01">
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $this->__('materials') ?></label>
                    <div class="material-checkboxes" id="editOpMaterials">
                        <?php foreach ($materials ?? [] as $mat): ?>
                        <label class="material-checkbox-label">
                            <input type="checkbox" name="material_ids[]" value="<?= $mat['id'] ?>" class="edit-op-mat-cb">
                            <span class="material-checkbox-badge">
                                <?php if (!empty($mat['filament_alias'])): ?>
                                <strong><?= $this->e($mat['filament_alias']) ?></strong>
                                <?php endif; ?>
                                <span><?= $this->e($mat['sku']) ?></span>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $this->__('printer') ?></label>
                    <select name="printer_id" id="editOpPrinter">
                        <option value=""><?= $this->__('select_printer') ?></option>
                        <?php foreach ($printers ?? [] as $pr): ?>
                        <option value="<?= $pr['id'] ?>">
                            <?= $this->e($pr['name']) ?><?= !empty($pr['model']) ? ' - ' . $this->e($pr['model']) : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= $this->__('tool') ?></label>
                    <select name="tool_id" id="editOpTool">
                        <option value=""><?= $this->__('select_tool') ?></option>
                        <?php foreach ($tools ?? [] as $tool): ?>
                        <option value="<?= $tool['id'] ?>"><?= $this->e($tool['sku']) ?> - <?= $this->e($tool['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= $this->__('description') ?></label>
                    <textarea name="description" id="editOpDescription" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close"><?= $this->__('cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= $this->__('save') ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
/* Detail Grid Layout */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

@media (max-width: 900px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}

/* Detail Rows */
.detail-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
    align-items: flex-start;
}

.detail-row-sub {
    padding: 8px 0 8px 20px;
    border-bottom: 1px dashed var(--border);
    font-size: 0.95rem;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    flex: 0 0 140px;
    color: var(--text-muted);
    font-size: 13px;
    padding-top: 2px;
}
.detail-value {
    flex: 1;
    word-break: break-word;
}

/* Detail SKU */
.detail-sku {
    font-size: 1.2rem;
    color: var(--primary);
}

/* Large Detail Image */
.detail-image-container {
    width: 200px;
    height: 200px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 2px solid var(--border);
    transition: transform 0.2s, box-shadow 0.2s;
}
.detail-image-container:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}
.detail-image-large {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s;
}
.detail-image-large:hover {
    transform: scale(1.05);
}

/* Image Placeholder */
.detail-image-placeholder {
    width: 200px;
    height: 200px;
    border-radius: 12px;
    background: var(--bg-secondary);
    border: 2px dashed var(--border);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: var(--text-muted);
    font-size: 13px;
}

/* Material & Printer Links */
.material-link,
.printer-link {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    color: var(--text);
    transition: color 0.2s;
}
.material-link:hover,
.printer-link:hover {
    color: var(--primary);
}
.badge-material {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    margin-right: 8px;
}

.badge-alias {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-right: 6px;
}

/* Print Parameters */
.print-params {
    background: var(--bg-secondary);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: var(--text-muted);
}

/* Production Cost Section */
.cost-total-badge {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.95rem;
    font-weight: 600;
    margin-left: 10px;
    box-shadow: 0 2px 6px rgba(39, 174, 96, 0.3);
}

.cost-breakdown {
    margin-top: 15px;
}
.cost-table {
    width: 100%;
    border-collapse: collapse;
}
.cost-table th,
.cost-table td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--border);
    text-align: left;
}
.cost-table thead th {
    background: var(--bg-secondary);
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: var(--text-muted);
}
.cost-table tbody tr:hover {
    background: var(--bg-hover);
}
.cost-total-row {
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-hover) 100%);
    font-size: 1.05rem;
}
.cost-total-row td {
    border-bottom: none;
    padding: 15px;
}

/* Cost icons */
.cost-icon {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.cost-icon-material { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.cost-icon-electricity { background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%); }
.cost-icon-amortization { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); }
.cost-icon-maintenance { background: linear-gradient(135deg, #e67e22 0%, #d35400 100%); }
.cost-icon-labor { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }

/* Cost sub-rows (multi-material) */
.cost-row-sub td:first-child {
    padding-left: 30px;
}

/* Cost info panel */
.cost-info-panel {
    background: var(--bg-secondary);
    border-radius: 10px;
    padding: 18px;
    margin-top: 20px;
    border: 1px solid var(--border);
}
.cost-info-header {
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--text-muted);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Grouped parameters */
.cost-params-groups {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}
.cost-params-group {
    background: var(--bg-primary);
    border-radius: 8px;
    padding: 14px;
    border: 1px solid var(--border);
}
.cost-params-group-title {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text);
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
}
.cost-params-group-title small {
    font-weight: 400;
    margin-left: auto;
}

.cost-info-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px dashed var(--border);
}
.cost-info-item:last-child {
    border-bottom: none;
}
.cost-info-label {
    color: var(--text-muted);
    font-size: 0.85rem;
}
.cost-info-value {
    font-weight: 600;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
    font-size: 0.85rem;
    color: var(--text);
}

/* Alert */
.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border: 1px solid #ffc107;
    color: #856404;
    padding: 15px 18px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* Operations Section */
.add-component-section {
    background: var(--bg-secondary);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid var(--border);
}

.operation-form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.form-group-full {
    grid-column: 1 / -1;
}

.form-row-inline {
    display: flex;
    gap: 15px;
}

.form-row-inline .form-group {
    flex: 1;
}

.form-actions-center {
    display: flex;
    justify-content: center;
}

@media (max-width: 900px) {
    .operation-form-grid {
        grid-template-columns: 1fr;
    }
}

/* Operation Resources Badges */
.operation-resources {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.badge-material {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

.badge-printer {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

.badge-tool {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

/* Reorder Buttons */
.reorder-cell {
    padding: 5px !important;
}

.reorder-buttons {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.reorder-btn {
    padding: 4px 6px !important;
    min-width: auto !important;
}

/* Operation Number */
.op-number {
    color: var(--primary);
    font-size: 1.1rem;
}

/* Total Row */
.total-row {
    background: var(--bg-secondary);
    font-weight: 600;
}

.total-row td {
    padding: 15px;
    border-top: 2px solid var(--border);
}

/* Text utilities */
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-muted { color: var(--text-muted); }

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.product-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.product-card-mini {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: all 0.2s ease;
    position: relative;
}

.product-card-mini:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.product-card-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--bg-primary);
}

.product-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-primary);
}

.product-card-info {
    flex: 1;
    min-width: 0;
}

.product-card-code {
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--primary);
}

.product-card-name {
    font-size: 0.85rem;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}

.product-card-category {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 2px;
}

.product-card-qty {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 4px;
}

.product-card-qty .qty-label {
    margin-right: 4px;
}

.product-card-qty strong {
    color: var(--text);
}

.product-inactive-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    font-size: 9px;
    text-transform: uppercase;
    background: var(--danger);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: var(--bg-primary, #ffffff);
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: 1px solid var(--border);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
}
.modal-header h3 { margin: 0; }
.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-muted);
    padding: 0;
    line-height: 1;
}
.modal-close:hover {
    color: var(--danger);
}
.modal-body { padding: 20px; }
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px 20px;
    border-top: 1px solid var(--border);
}
.form-row-modal {
    display: flex;
    gap: 15px;
}
.form-row-modal .form-group {
    flex: 1;
}

/* Material checkboxes */
.material-checkboxes {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px 0;
}
.material-checkbox-label {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}
.material-checkbox-label input[type="checkbox"] {
    display: none;
}
.material-checkbox-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    background: var(--bg-secondary);
    border: 2px solid var(--border);
    font-size: 12px;
    transition: all 0.2s;
}
.material-checkbox-badge strong {
    color: var(--primary);
}
.material-checkbox-badge small {
    color: var(--text-muted);
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.material-checkbox-label input[type="checkbox"]:checked + .material-checkbox-badge {
    border-color: var(--primary);
    background: var(--primary);
    color: white;
}
.material-checkbox-label input[type="checkbox"]:checked + .material-checkbox-badge strong,
.material-checkbox-label input[type="checkbox"]:checked + .material-checkbox-badge small {
    color: rgba(255,255,255,0.85);
}

/* Edit button in operations */
.edit-operation-btn {
    margin-right: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailId = '<?= $detail['id'] ?>';
    const csrfToken = '<?= $this->e($csrfToken ?? '') ?>';
    const canEdit = <?= $this->can('catalog.details.edit') ? 'true' : 'false' ?>;

    // AJAX helper
    function ajaxPost(url, formData) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        }).then(r => r.json());
    }

    // Edit operation modal
    const editModal = document.getElementById('editOperationModal');
    const editForm = document.getElementById('editOperationForm');

    if (editForm && canEdit) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            ajaxPost(this.action, formData).then(data => {
                if (data.success) {
                    editModal.style.display = 'none';
                    location.reload();
                } else {
                    alert(data.error || 'Error updating operation');
                }
            }).catch(err => { console.error(err); alert('Error'); });
        });
    }

    // Open edit modal
    document.querySelectorAll('.edit-operation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            editForm.action = '/catalog/details/' + detailId + '/operations/' + id;
            document.getElementById('editOpName').value = this.dataset.name || '';
            document.getElementById('editOpDescription').value = this.dataset.description || '';
            document.getElementById('editOpTime').value = this.dataset.time || 0;
            document.getElementById('editOpRate').value = this.dataset.rate || 0;

            // Multi-material checkboxes
            const matIds = (this.dataset.materials || '').split(',').filter(Boolean);
            document.querySelectorAll('.edit-op-mat-cb').forEach(cb => {
                cb.checked = matIds.includes(cb.value);
            });

            document.getElementById('editOpPrinter').value = this.dataset.printer || '';
            document.getElementById('editOpTool').value = this.dataset.tool || '';
            editModal.style.display = 'flex';
        });
    });

    // Close modal
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
    }

    // Operation reordering - AJAX
    document.querySelectorAll('.reorder-btn').forEach(btn => {
        const form = btn.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                ajaxPost(this.action, formData).then(data => {
                    if (data.success) {
                        location.reload();
                    }
                }).catch(err => console.error(err));
            });
        }
    });

    // Operation delete - AJAX
    document.querySelectorAll('form[action*="/operations/"][action$="/remove"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('<?= $this->__('confirm_remove_operation') ?>')) return;
            const formData = new FormData(this);
            ajaxPost(this.action, formData).then(data => {
                if (data.success) {
                    this.closest('tr').remove();
                    // Update row numbers
                    document.querySelectorAll('#operationsBody tr[data-operation-id]').forEach((row, index) => {
                        const numCell = row.querySelector('.op-number strong');
                        if (numCell) numCell.textContent = index + 1;
                    });
                }
            });
        });
    });

    // Add operation form - AJAX
    const addOperationForm = document.querySelector('.add-operation-form');
    if (addOperationForm) {
        addOperationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            ajaxPost(this.action, formData).then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error adding operation');
                }
            }).catch(err => { console.error(err); alert('Error'); });
        });
    }
});
</script>

<?php $this->endSection(); ?>
