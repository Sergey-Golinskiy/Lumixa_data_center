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

                    <!-- Resources Selection -->
                    <div class="form-group">
                        <label><?= $this->__('material') ?></label>
                        <select name="material_id">
                            <option value=""><?= $this->__('select_material') ?></option>
                            <?php foreach ($materials ?? [] as $mat): ?>
                            <option value="<?= $mat['id'] ?>">
                                <?= $this->e($mat['sku']) ?> - <?= $this->e($mat['name']) ?>
                                <?= !empty($mat['filament_alias']) ? ' (' . $this->e($mat['filament_alias']) . ')' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
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
                                <?php if (!empty($operation['material_sku'])): ?>
                                <span class="badge badge-material" title="<?= $this->e($operation['material_name'] ?? '') ?>">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 3px;">
                                        <circle cx="12" cy="12" r="10"></circle>
                                    </svg>
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
                                <?php if (empty($operation['material_sku']) && empty($operation['printer_name']) && empty($operation['tool_sku'])): ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-right"><?= (int)($operation['time_minutes'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-right"><?= $this->currency($operation['labor_rate'] ?? 0) ?>/<?= $this->__('hour_short') ?></td>
                        <td class="text-right"><strong><?= $this->currency($opCost) ?></strong></td>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <td>
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
</style>

<?php $this->endSection(); ?>
