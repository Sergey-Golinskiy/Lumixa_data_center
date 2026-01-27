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
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('material') ?></span>
                <span class="detail-value">
                    <?php if (!empty($detail['material_item_id'])): ?>
                    <a href="/warehouse/items/<?= $detail['material_item_id'] ?>" class="material-link">
                        <span class="badge badge-material"><?= $this->e($detail['material_sku'] ?? '') ?></span>
                        <?= !empty($detail['material_name']) ? ' ' . $this->e($detail['material_name']) : '' ?>
                    </a>
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
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
                    <tr>
                        <td>
                            <span class="cost-icon cost-icon-<?= $this->e($item['type']) ?>"></span>
                            <?= $this->__($item['label']) ?>
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

        <?php if (!empty($costData['printer'])): ?>
        <div class="cost-info-panel">
            <div class="cost-info-header">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <?= $this->__('calculation_parameters') ?>
            </div>
            <div class="cost-info-grid">
                <div class="cost-info-item">
                    <span class="cost-info-label"><?= $this->__('material_cost_per_gram') ?>:</span>
                    <span class="cost-info-value"><?= number_format($costData['material_cost_per_gram'], 4) ?></span>
                </div>
                <div class="cost-info-item">
                    <span class="cost-info-label"><?= $this->__('print_time_hours') ?>:</span>
                    <span class="cost-info-value"><?= $costData['calculation_details']['print_time_hours'] ?? 0 ?> <?= $this->__('hours_short') ?></span>
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
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Detail Routing -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
        </svg>
        <?= $this->__('detail_routing') ?>
        <?php if (!empty($activeRouting)): ?>
        <span class="badge badge-success"><?= $this->e($activeRouting['version']) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($activeRouting)): ?>
        <div class="routing-header">
            <div class="routing-info">
                <strong><?= $this->e($activeRouting['version']) ?></strong>
                <?php if ($activeRouting['name']): ?>
                <span class="text-muted">- <?= $this->e($activeRouting['name']) ?></span>
                <?php endif; ?>
            </div>
            <a href="/catalog/detail-routing/<?= $activeRouting['id'] ?>" class="btn btn-sm btn-outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <?= $this->__('view_details') ?>
            </a>
        </div>
        <?php if (!empty($routingOperations)): ?>
        <div class="table-container" style="margin-top: 15px;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th class="text-right"><?= $this->__('setup_time_minutes') ?></th>
                        <th class="text-right"><?= $this->__('run_time_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor_cost') ?></th>
                        <th class="text-right"><?= $this->__('overhead_cost') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalLabor = 0; $totalOverhead = 0; ?>
                    <?php foreach ($routingOperations as $op): ?>
                    <?php $totalLabor += $op['labor_cost'] ?? 0; $totalOverhead += $op['overhead_cost'] ?? 0; ?>
                    <tr>
                        <td><span class="op-number"><?= $this->e($op['operation_number']) ?></span></td>
                        <td><strong><?= $this->e($op['name']) ?></strong></td>
                        <td><?= $this->e($op['work_center'] ?? '-') ?></td>
                        <td class="text-right"><?= $this->e($op['setup_time_minutes']) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-right"><?= $this->e($op['run_time_minutes']) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'] ?? 0, 2) ?></td>
                        <td class="text-right"><?= number_format($op['overhead_cost'] ?? 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" class="text-right"><strong><?= $this->__('total') ?>:</strong></td>
                        <td class="text-right"><strong><?= number_format($totalLabor, 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalOverhead, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.4">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            <p><?= $this->__('no_active_routing') ?></p>
            <?php if ($this->can('catalog.detail_routing.create')): ?>
            <a href="/catalog/detail-routing/create?detail_id=<?= $detail['id'] ?>" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?= $this->__('create_detail_routing') ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

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
.cost-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
}
.cost-info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed var(--border);
}
.cost-info-label {
    color: var(--text-muted);
    font-size: 0.9rem;
}
.cost-info-value {
    font-weight: 600;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', monospace;
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

/* Routing Header */
.routing-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}
.routing-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Operation Number Badge */
.op-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
}

/* Total Row */
.total-row {
    background: var(--bg-secondary);
    font-weight: 600;
}
.total-row td {
    padding: 15px;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}
.empty-state p {
    color: var(--text-muted);
    margin: 15px 0 20px;
    font-size: 14px;
}

/* Text utilities */
.text-right { text-align: right; }
.text-center { text-align: center; }
.text-muted { color: var(--text-muted); }
</style>

<?php $this->endSection(); ?>
