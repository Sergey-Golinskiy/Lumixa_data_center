<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->e($detail['name']) ?></h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.details.edit')): ?>
        <a href="/catalog/details/<?= $detail['id'] ?>/edit" class="btn btn-primary"><?= $this->__('edit') ?></a>
        <?php endif; ?>
        <a href="/catalog/details" class="btn btn-secondary"><?= $this->__('back_to_list') ?></a>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <div class="details-grid" style="display:grid;grid-template-columns: 120px 1fr;gap: 20px;align-items:start;">
            <div>
                <?php if (!empty($detail['image_path'])): ?>
                <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" style="width: 100px; height: 100px;" data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
                <?php else: ?>
                <span class="text-muted">-</span>
                <?php endif; ?>
            </div>
            <div>
                <h3><?= $this->e($detail['sku']) ?></h3>
                <p><?= $this->e($detail['name']) ?></p>
                <p>
                    <strong><?= $this->__('detail_type') ?>:</strong>
                    <?= $detail['detail_type'] === 'printed'
                        ? $this->__('detail_type_printed')
                        : $this->__('detail_type_purchased') ?>
                </p>
                <p>
                    <strong><?= $this->__('status') ?>:</strong>
                    <?= !empty($detail['is_active']) ? $this->__('active') : $this->__('inactive') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($detail['detail_type'] === 'printed'): ?>
        <div class="detail-row">
            <strong><?= $this->__('material') ?>:</strong>
            <?php if (!empty($detail['material_item_id'])): ?>
            <?= $this->e($detail['material_sku'] ?? '') ?> <?= !empty($detail['material_name']) ? ' - ' . $this->e($detail['material_name']) : '' ?>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('printer') ?>:</strong>
            <?php if (!empty($detail['printer_id'])): ?>
            <?= $this->e($detail['printer_name'] ?? '') ?><?= !empty($detail['printer_model']) ? ' - ' . $this->e($detail['printer_model']) : '' ?>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('material_qty_grams') ?>:</strong>
            <?= $detail['material_qty_grams'] ? $this->e($detail['material_qty_grams']) : '-' ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('print_time_minutes') ?>:</strong>
            <?= $detail['print_time_minutes'] ? $this->e($detail['print_time_minutes']) : '-' ?>
        </div>
        <div class="detail-row">
            <strong><?= $this->__('print_parameters') ?>:</strong>
            <?= $detail['print_parameters'] ? $this->e($detail['print_parameters']) : '-' ?>
        </div>
        <?php endif; ?>

        <?php if ($detail['detail_type'] === 'printed' && isset($costData)): ?>
        <div class="production-cost-section" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--border);">
            <h3 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <?= $this->__('production_cost') ?>
                <?php if ($costData['total_cost'] > 0): ?>
                <span class="cost-total-badge"><?= $this->currency($costData['total_cost']) ?></span>
                <?php endif; ?>
            </h3>

            <?php if (!empty($costData['missing_data'])): ?>
            <div class="alert alert-warning" style="margin-bottom: 15px;">
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
                            <td class="text-muted"><?= $this->e($item['details']) ?></td>
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
            <div class="cost-info-panel" style="margin-top: 15px;">
                <div class="cost-info-header"><?= $this->__('calculation_parameters') ?></div>
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
        <?php endif; ?>
        <div class="detail-row">
            <strong><?= $this->__('model_file') ?>:</strong>
            <?php if (!empty($detail['model_path'])): ?>
            <a href="/<?= $this->e(ltrim($detail['model_path'], '/')) ?>" target="_blank" rel="noopener">
                <?= $this->__('download_model') ?>
            </a>
            <?php else: ?>
            <span class="text-muted">-</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('detail_routing') ?></div>
    <div class="card-body">
        <?php if (!empty($activeRouting)): ?>
        <div class="routing-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <strong><?= $this->e($activeRouting['version']) ?></strong>
                <?php if ($activeRouting['name']): ?>
                <span class="text-muted">- <?= $this->e($activeRouting['name']) ?></span>
                <?php endif; ?>
            </div>
            <a href="/catalog/detail-routing/<?= $activeRouting['id'] ?>" class="btn btn-sm btn-outline"><?= $this->__('view_details') ?></a>
        </div>
        <div class="table-container" style="margin-top: 12px;">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th><?= $this->__('setup_time_minutes') ?></th>
                        <th><?= $this->__('run_time_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor_cost') ?></th>
                        <th class="text-right"><?= $this->__('overhead_cost') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routingOperations as $op): ?>
                    <tr>
                        <td><?= $this->e($op['operation_number']) ?> - <?= $this->e($op['name']) ?></td>
                        <td><?= $this->e($op['work_center'] ?? '-') ?></td>
                        <td><?= $this->e($op['setup_time_minutes']) ?></td>
                        <td><?= $this->e($op['run_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'] ?? 0, 2) ?></td>
                        <td class="text-right"><?= number_format($op['overhead_cost'] ?? 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted"><?= $this->__('no_active_routing') ?></p>
        <?php if ($this->can('catalog.detail_routing.create')): ?>
        <a href="/catalog/detail-routing/create?detail_id=<?= $detail['id'] ?>" class="btn btn-primary btn-sm">+ <?= $this->__('create_detail_routing') ?></a>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.detail-row { padding: 10px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }

/* Production Cost Section */
.production-cost-section h3 { font-size: 1.1rem; color: var(--text); }
.cost-total-badge {
    background: var(--success);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 1rem;
    font-weight: 600;
}
.cost-breakdown { margin-top: 10px; }
.cost-table {
    width: 100%;
    border-collapse: collapse;
}
.cost-table th, .cost-table td {
    padding: 10px 12px;
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
.cost-table tbody tr:hover { background: var(--bg-hover); }
.cost-total-row {
    background: var(--bg-secondary);
    font-size: 1.05rem;
}
.cost-total-row td { border-bottom: none; }

/* Cost icons */
.cost-icon {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
}
.cost-icon-material { background: #3498db; }
.cost-icon-electricity { background: #f1c40f; }
.cost-icon-amortization { background: #9b59b6; }
.cost-icon-maintenance { background: #e67e22; }

/* Cost info panel */
.cost-info-panel {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 15px;
}
.cost-info-header {
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--text-muted);
    font-size: 0.85rem;
    text-transform: uppercase;
}
.cost-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}
.cost-info-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px dashed var(--border);
}
.cost-info-label { color: var(--text-muted); font-size: 0.9rem; }
.cost-info-value { font-weight: 500; font-family: monospace; }

/* Alert */
.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
    padding: 12px 15px;
    border-radius: 6px;
}
</style>

<?php $this->endSection(); ?>
