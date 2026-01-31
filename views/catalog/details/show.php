<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="mb-0">
        <i class="ri-shape-2-line me-2"></i>
        <?= $this->e($detail['sku']) ?>
    </h4>
    <div class="d-flex gap-2">
        <a href="/catalog/details" class="btn btn-soft-secondary">
            <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_list') ?>
        </a>
        <?php if ($this->can('catalog.details.edit')): ?>
        <a href="/catalog/details/<?= $detail['id'] ?>/edit" class="btn btn-soft-primary">
            <i class="ri-pencil-line me-1"></i> <?= $this->__('edit_detail') ?>
        </a>
        <?php endif; ?>
        <?php if ($this->can('catalog.details.create')): ?>
        <a href="/catalog/details/<?= $detail['id'] ?>/copy" target="_blank" class="btn btn-soft-info" title="<?= $this->__('copy_detail') ?>">
            <i class="ri-file-copy-line me-1"></i> <?= $this->__('copy') ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Detail Information -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="ri-information-line me-2"></i>
                <?= $this->__('detail_information') ?>
            </div>
            <div class="card-body">
                <!-- Photo -->
                <div class="mb-3 text-center">
                    <?php if (!empty($detail['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>"
                         alt="<?= $this->e($detail['name']) ?>"
                         class="img-thumbnail rounded"
                         style="max-height: 200px; cursor: pointer;"
                         data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>">
                    <?php else: ?>
                    <div class="bg-light rounded d-flex flex-column align-items-center justify-content-center py-5">
                        <i class="ri-image-line fs-1 text-muted"></i>
                        <span class="text-muted mt-2"><?= $this->__('no_photo') ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted" style="width: 140px;"><?= $this->__('sku') ?></th>
                            <td><strong class="text-primary fs-5"><?= $this->e($detail['sku']) ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('name') ?></th>
                            <td><?= $this->e($detail['name']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('detail_type') ?></th>
                            <td>
                                <?php if ($detail['detail_type'] === 'printed'): ?>
                                <span class="badge bg-info-subtle text-info"><?= $this->__('detail_type_printed') ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('detail_type_purchased') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('status') ?></th>
                            <td>
                                <?php if (!empty($detail['is_active'])): ?>
                                <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($detail['model_path'])): ?>
                        <tr>
                            <th class="text-muted"><?= $this->__('model_file') ?></th>
                            <td>
                                <a href="/<?= $this->e(ltrim($detail['model_path'], '/')) ?>" target="_blank" rel="noopener" class="btn btn-soft-primary btn-sm">
                                    <i class="ri-download-2-line me-1"></i> <?= $this->__('download_model') ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Settings (only for printed details) -->
    <?php if ($detail['detail_type'] === 'printed'): ?>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="ri-printer-line me-2"></i>
                <?= $this->__('print_settings') ?>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <?php if (!empty($detailMaterials) && count($detailMaterials) > 1): ?>
                        <!-- Multi-material display -->
                        <tr>
                            <th class="text-muted" style="width: 140px;"><?= $this->__('materials') ?></th>
                            <td>
                                <span class="badge bg-primary-subtle text-primary"><?= count($detailMaterials) ?> <?= $this->__('multi_color_printing') ?></span>
                            </td>
                        </tr>
                        <?php foreach ($detailMaterials as $dm): ?>
                        <tr>
                            <td class="ps-4 text-muted">
                                <strong><?= $this->e($dm['material_qty_grams']) ?></strong> <?= $this->__('grams_short') ?>
                            </td>
                            <td>
                                <a href="/warehouse/items/<?= $dm['material_item_id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($dm['filament_alias'])): ?>
                                    <span class="badge rounded-pill"<?php if (!empty($dm['alias_color'])): ?> style="background: <?= $this->e($dm['alias_color']) ?>; color: <?= $this->contrastColor($dm['alias_color']) ?>"<?php endif; ?>><?= $this->e($dm['filament_alias']) ?></span>
                                    <?php endif; ?>
                                    <span class="badge bg-primary-subtle text-primary"><?= $this->e($dm['material_sku'] ?? '') ?></span>
                                    <?= !empty($dm['material_name']) ? ' ' . $this->e($dm['material_name']) : '' ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <!-- Single material display -->
                        <tr>
                            <th class="text-muted" style="width: 140px;"><?= $this->__('material') ?></th>
                            <td>
                                <?php if (!empty($detail['material_item_id'])): ?>
                                <a href="/warehouse/items/<?= $detail['material_item_id'] ?>" class="text-decoration-none">
                                    <?php if (!empty($detail['material_filament_alias'])): ?>
                                    <span class="badge rounded-pill"<?php if (!empty($aliasColor)): ?> style="background: <?= $this->e($aliasColor) ?>; color: <?= $this->contrastColor($aliasColor) ?>"<?php endif; ?>><?= $this->e($detail['material_filament_alias']) ?></span>
                                    <?php endif; ?>
                                    <span class="badge bg-primary-subtle text-primary"><?= $this->e($detail['material_sku'] ?? '') ?></span>
                                    <?= !empty($detail['material_name']) ? ' ' . $this->e($detail['material_name']) : '' ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <tr>
                            <th class="text-muted"><?= $this->__('printer') ?></th>
                            <td>
                                <?php if (!empty($detail['printer_id'])): ?>
                                <a href="/equipment/printers/<?= $detail['printer_id'] ?>" class="text-decoration-none">
                                    <i class="ri-printer-line me-1"></i>
                                    <?= $this->e($detail['printer_name'] ?? '') ?><?= !empty($detail['printer_model']) ? ' - ' . $this->e($detail['printer_model']) : '' ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if (empty($detailMaterials) || count($detailMaterials) <= 1): ?>
                        <tr>
                            <th class="text-muted"><?= $this->__('material_qty_grams') ?></th>
                            <td>
                                <?php if ($detail['material_qty_grams']): ?>
                                <strong><?= $this->e($detail['material_qty_grams']) ?></strong> <?= $this->__('grams_short') ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <tr>
                            <th class="text-muted"><?= $this->__('print_time_minutes') ?></th>
                            <td>
                                <?php if ($detail['print_time_minutes']): ?>
                                <strong><?= $this->e($detail['print_time_minutes']) ?></strong> <?= $this->__('minutes_short') ?>
                                <?php if ($detail['print_time_minutes'] >= 60): ?>
                                <small class="text-muted">(<?= floor($detail['print_time_minutes'] / 60) ?><?= $this->__('hours_short') ?> <?= $detail['print_time_minutes'] % 60 ?><?= $this->__('minutes_short') ?>)</small>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if (!empty($detail['print_parameters'])): ?>
                        <tr>
                            <th class="text-muted"><?= $this->__('print_parameters') ?></th>
                            <td><code class="bg-light px-2 py-1 rounded"><?= $this->e($detail['print_parameters']) ?></code></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Used in Products Section -->
<?php if (!empty($usedInProducts)): ?>
<div class="card mb-4">
    <div class="card-header">
        <i class="ri-box-3-line me-2"></i>
        <?= $this->__('used_in_products') ?>
        <span class="badge bg-secondary-subtle text-secondary ms-2"><?= count($usedInProducts) ?></span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($usedInProducts as $product): ?>
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <a href="/catalog/products/<?= $product['id'] ?>" class="text-decoration-none">
                    <div class="card border h-100 hover-shadow">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                    <?php if (!empty($product['image_path'])): ?>
                                    <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->e($product['name']) ?>" class="img-fluid rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="ri-box-3-line text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-semibold text-primary"><?= $this->e($product['code']) ?></div>
                                    <div class="text-truncate text-body-secondary small"><?= $this->e($product['name']) ?></div>
                                    <?php if (!empty($product['category_name'])): ?>
                                    <div class="text-muted small"><?= $this->e($product['category_name']) ?></div>
                                    <?php endif; ?>
                                    <div class="small">
                                        <span class="text-muted"><?= $this->__('quantity') ?>:</span>
                                        <strong><?= $this->number($product['quantity'], 4) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?php if (!$product['is_active']): ?>
                            <span class="badge bg-danger-subtle text-danger position-absolute top-0 end-0 m-2"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Production Cost Section (only for printed details) -->
<?php if ($detail['detail_type'] === 'printed' && isset($costData)): ?>
<div class="card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="ri-money-dollar-circle-line me-2"></i>
        <?= $this->__('production_cost') ?>
        <?php if ($costData['total_cost'] > 0): ?>
        <span class="badge bg-success ms-auto"><?= $this->currency($costData['total_cost']) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($costData['missing_data'])): ?>
        <div class="alert alert-warning d-flex align-items-start">
            <i class="ri-error-warning-line me-2 fs-5"></i>
            <div>
                <strong><?= $this->__('missing_data_for_calculation') ?>:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($costData['missing_data'] as $missing): ?>
                    <li><?= $this->__('missing_' . $missing) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($costBreakdown)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('cost_component') ?></th>
                        <th><?= $this->__('calculation') ?></th>
                        <th class="text-end"><?= $this->__('amount') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($costBreakdown as $item): ?>
                    <tr<?php if (!empty($item['is_sub'])): ?> class="bg-light"<?php endif; ?>>
                        <td>
                            <span class="badge rounded-circle p-1 me-2 bg-<?= match($item['type'] ?? '') { 'material' => 'primary', 'electricity' => 'warning', 'amortization' => 'info', 'maintenance' => 'secondary', 'labor' => 'success', default => 'secondary' } ?>-subtle">&nbsp;</span>
                            <?php if (!empty($item['label_raw'])): ?>
                            <?= $this->e($item['label_raw']) ?>
                            <?php else: ?>
                            <?= $this->__($item['label']) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><code><?= $this->e($item['details']) ?></code></td>
                        <td class="text-end"><?= $this->currency($item['value']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="2"><strong><?= $this->__('total_production_cost') ?></strong></td>
                        <td class="text-end"><strong class="text-success"><?= $this->currency($costData['total_cost']) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Calculation Parameters -->
        <div class="bg-light rounded p-3 mt-4">
            <h6 class="text-muted mb-3">
                <i class="ri-information-line me-1"></i>
                <?= $this->__('calculation_parameters') ?>
            </h6>
            <div class="row g-3">
                <!-- Materials Group -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary-subtle border-0 py-2">
                            <small class="fw-semibold"><?= $this->__('materials') ?></small>
                        </div>
                        <div class="card-body p-2">
                            <?php
                            $matDetails = $costData['material_details'] ?? [];
                            if (count($matDetails) > 1):
                                foreach ($matDetails as $md):
                            ?>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted"><?= $this->e($md['filament_alias'] ?: $md['material_sku']) ?>:</span>
                                <span class="font-monospace"><?= number_format($md['cost_per_gram'], 4) ?> / <?= $this->__('grams_short') ?> x <?= number_format($md['qty_grams'], 2) ?> <?= $this->__('grams_short') ?></span>
                            </div>
                            <?php endforeach; else: ?>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted"><?= $this->__('material_cost_per_gram') ?>:</span>
                                <span class="font-monospace"><?= number_format($costData['material_cost_per_gram'], 4) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small py-1">
                                <span class="text-muted"><?= $this->__('material_qty_grams') ?>:</span>
                                <span class="font-monospace"><?= number_format($costData['calculation_details']['material_qty_grams'] ?? 0, 2) ?> <?= $this->__('grams_short') ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Print Time Group -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-warning-subtle border-0 py-2">
                            <small class="fw-semibold"><?= $this->__('print_time_hours') ?></small>
                        </div>
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted"><?= $this->__('print_time_minutes') ?>:</span>
                                <span class="font-monospace"><?= $costData['calculation_details']['print_time_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></span>
                            </div>
                            <div class="d-flex justify-content-between small py-1">
                                <span class="text-muted"><?= $this->__('print_time_hours') ?>:</span>
                                <span class="font-monospace"><?= $costData['calculation_details']['print_time_hours'] ?? 0 ?> <?= $this->__('hours_short') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($costData['printer'])): ?>
                <!-- Printer Group -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info-subtle border-0 py-2">
                            <small class="fw-semibold"><?= $this->__('printer') ?></small>
                            <small class="text-muted ms-1"><?= $this->e($costData['printer']['name'] ?? '') ?><?= !empty($costData['printer']['model']) ? ' - ' . $this->e($costData['printer']['model']) : '' ?></small>
                        </div>
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted"><?= $this->__('printer_power') ?>:</span>
                                <span class="font-monospace"><?= $costData['printer']['power_watts'] ?? 0 ?> <?= $this->__('watts_short') ?></span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted"><?= $this->__('electricity_rate') ?>:</span>
                                <span class="font-monospace"><?= number_format($costData['printer']['electricity_cost_per_kwh'] ?? 0, 4) ?> / <?= $this->__('kwh_short') ?></span>
                            </div>
                            <div class="d-flex justify-content-between small py-1 border-bottom">
                                <span class="text-muted"><?= $this->__('amortization_rate') ?>:</span>
                                <span class="font-monospace"><?= number_format($costData['printer']['amortization_per_hour'] ?? 0, 4) ?> / <?= $this->__('hour_short') ?></span>
                            </div>
                            <div class="d-flex justify-content-between small py-1">
                                <span class="text-muted"><?= $this->__('maintenance_rate') ?>:</span>
                                <span class="font-monospace"><?= number_format($costData['printer']['maintenance_per_hour'] ?? 0, 4) ?> / <?= $this->__('hour_short') ?></span>
                            </div>
                        </div>
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
<div class="card mb-4">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <i class="ri-route-line me-2"></i>
        <?= $this->__('detail_routing') ?>
        <span class="badge bg-secondary-subtle text-secondary"><?= count($operations ?? []) ?> <?= $this->__('operations') ?></span>
        <?php if (($laborCost['total_minutes'] ?? 0) > 0): ?>
        <span class="badge bg-info-subtle text-info"><?= $laborCost['total_minutes'] ?> <?= $this->__('minutes_short') ?></span>
        <?php endif; ?>
        <?php if (($laborCost['total_cost'] ?? 0) > 0): ?>
        <span class="badge bg-success ms-auto"><?= $this->currency($laborCost['total_cost']) ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.details.edit')): ?>
        <!-- Add Operation Form -->
        <div class="bg-light rounded p-3 mb-4">
            <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations" class="add-operation-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">

                <div class="row g-3">
                    <!-- Operation Name -->
                    <div class="col-md-4">
                        <label class="form-label"><?= $this->__('operation_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="<?= $this->__('operation_name_placeholder') ?>">
                    </div>

                    <!-- Time and Rate -->
                    <div class="col-md-2">
                        <label class="form-label"><?= $this->__('time_minutes') ?></label>
                        <input type="number" name="time_minutes" class="form-control" value="0" min="0" step="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                        <input type="number" name="labor_rate" class="form-control" value="0" min="0" step="0.01">
                    </div>

                    <!-- Printer -->
                    <div class="col-md-2">
                        <label class="form-label"><?= $this->__('printer') ?></label>
                        <select name="printer_id" class="form-select">
                            <option value=""><?= $this->__('select_printer') ?></option>
                            <?php foreach ($printers ?? [] as $pr): ?>
                            <option value="<?= $pr['id'] ?>">
                                <?= $this->e($pr['name']) ?><?= !empty($pr['model']) ? ' - ' . $this->e($pr['model']) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tool -->
                    <div class="col-md-2">
                        <label class="form-label"><?= $this->__('tool') ?></label>
                        <select name="tool_id" class="form-select">
                            <option value=""><?= $this->__('select_tool') ?></option>
                            <?php foreach ($tools ?? [] as $tool): ?>
                            <option value="<?= $tool['id'] ?>"><?= $this->e($tool['sku']) ?> - <?= $this->e($tool['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Multi-material Selection -->
                    <div class="col-12">
                        <label class="form-label"><?= $this->__('materials') ?></label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($materials ?? [] as $mat): ?>
                            <label class="btn btn-outline-primary btn-sm position-relative">
                                <input type="checkbox" name="material_ids[]" value="<?= $mat['id'] ?>" class="btn-check">
                                <?php if (!empty($mat['filament_alias'])): ?>
                                <strong><?= $this->e($mat['filament_alias']) ?></strong> -
                                <?php endif; ?>
                                <?= $this->e($mat['sku']) ?>
                                <small class="text-muted"><?= $this->e($mat['name']) ?></small>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label"><?= $this->__('description') ?></label>
                        <textarea name="description" class="form-control" rows="2" placeholder="<?= $this->__('instructions_placeholder') ?>"></textarea>
                    </div>

                    <!-- Add Button -->
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-add-line me-1"></i> <?= $this->__('add_operation') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Operations Table -->
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="operationsTable">
                <thead class="table-light">
                    <tr>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <th style="width: 60px;"><?= $this->__('order') ?></th>
                        <?php endif; ?>
                        <th style="width: 40px;">#</th>
                        <th><?= $this->__('operation_name') ?></th>
                        <th><?= $this->__('resources') ?></th>
                        <th class="text-end"><?= $this->__('time_minutes') ?></th>
                        <th class="text-end"><?= $this->__('labor_rate') ?></th>
                        <th class="text-end"><?= $this->__('operation_cost') ?></th>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <th style="width: 80px;"><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="operationsBody">
                    <?php if (empty($operations)): ?>
                    <tr>
                        <td colspan="<?= $this->can('catalog.details.edit') ? '8' : '6' ?>" class="text-center py-5">
                            <i class="ri-route-line fs-1 text-muted"></i>
                            <p class="text-muted mb-0 mt-2"><?= $this->__('no_operations') ?></p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $opNum = 1; $opCount = count($operations); foreach ($operations as $index => $operation): ?>
                    <?php $opCost = ((int)($operation['time_minutes'] ?? 0) / 60) * (float)($operation['labor_rate'] ?? 0); ?>
                    <tr data-operation-id="<?= $operation['id'] ?>">
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <td>
                            <div class="btn-group-vertical btn-group-sm">
                                <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations/<?= $operation['id'] ?>/move-up" class="d-inline">
                                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-soft-secondary btn-sm py-0" title="<?= $this->__('move_up') ?>" <?= $index === 0 ? 'disabled' : '' ?>>
                                        <i class="ri-arrow-up-s-line"></i>
                                    </button>
                                </form>
                                <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations/<?= $operation['id'] ?>/move-down" class="d-inline">
                                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-soft-secondary btn-sm py-0" title="<?= $this->__('move_down') ?>" <?= $index === $opCount - 1 ? 'disabled' : '' ?>>
                                        <i class="ri-arrow-down-s-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                        <td><strong class="text-primary"><?= $opNum++ ?></strong></td>
                        <td>
                            <strong><?= $this->e($operation['name'] ?? '') ?></strong>
                            <?php if (!empty($operation['description'])): ?>
                            <br><small class="text-muted"><?= nl2br($this->e($operation['description'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <?php
                                $opMats = $operation['operation_materials'] ?? [];
                                $hasMaterials = false;
                                if (!empty($opMats)):
                                    $hasMaterials = true;
                                    $aliasColors = $aliasColors ?? [];
                                    foreach ($opMats as $om):
                                        $omColor = $aliasColors[$om['filament_alias'] ?? ''] ?? null;
                                ?>
                                <span class="badge bg-primary-subtle text-primary" title="<?= $this->e($om['material_name'] ?? '') ?>"<?php if ($omColor): ?> style="background: <?= $this->e($omColor) ?> !important; color: <?= $this->contrastColor($omColor) ?>"<?php endif; ?>>
                                    <?= !empty($om['filament_alias']) ? $this->e($om['filament_alias']) : $this->e($om['material_sku'] ?? '') ?>
                                </span>
                                <?php endforeach; ?>
                                <?php elseif (!empty($operation['material_sku'])): ?>
                                <?php $hasMaterials = true; ?>
                                <span class="badge bg-primary-subtle text-primary" title="<?= $this->e($operation['material_name'] ?? '') ?>">
                                    <?= $this->e($operation['material_sku']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($operation['printer_name'])): ?>
                                <span class="badge bg-info-subtle text-info" title="<?= $this->__('printer') ?>">
                                    <i class="ri-printer-line me-1"></i><?= $this->e($operation['printer_name']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($operation['tool_sku'])): ?>
                                <span class="badge bg-warning-subtle text-warning" title="<?= $this->e($operation['tool_name'] ?? '') ?>">
                                    <i class="ri-tools-line me-1"></i><?= $this->e($operation['tool_sku']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!$hasMaterials && empty($operation['printer_name']) && empty($operation['tool_sku'])): ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-end"><?= (int)($operation['time_minutes'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-end"><?= $this->currency($operation['labor_rate'] ?? 0) ?>/<?= $this->__('hour_short') ?></td>
                        <td class="text-end"><strong><?= $this->currency($opCost) ?></strong></td>
                        <?php if ($this->can('catalog.details.edit')): ?>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-soft-primary edit-operation-btn"
                                        data-id="<?= $operation['id'] ?>"
                                        data-name="<?= $this->e($operation['name'] ?? '') ?>"
                                        data-description="<?= $this->e($operation['description'] ?? '') ?>"
                                        data-time="<?= $operation['time_minutes'] ?? 0 ?>"
                                        data-rate="<?= $operation['labor_rate'] ?? 0 ?>"
                                        data-materials="<?= $this->e(implode(',', array_column($operation['operation_materials'] ?? [], 'material_id')) ?: ($operation['material_id'] ?? '')) ?>"
                                        data-printer="<?= $operation['printer_id'] ?? '' ?>"
                                        data-tool="<?= $operation['tool_id'] ?? '' ?>"
                                        title="<?= $this->__('edit') ?>">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/operations/<?= $operation['id'] ?>/remove" class="d-inline" onsubmit="return confirm('<?= $this->__('confirm_remove_operation') ?>');">
                                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-soft-danger" title="<?= $this->__('delete') ?>">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($operations)): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="<?= $this->can('catalog.details.edit') ? '4' : '3' ?>" class="text-end"><strong><?= $this->__('total') ?>:</strong></td>
                        <td class="text-end"><strong><?= $laborCost['total_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></strong></td>
                        <td></td>
                        <td class="text-end"><strong class="text-success"><?= $this->currency($laborCost['total_cost'] ?? 0) ?></strong></td>
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
<div class="modal fade" id="editOperationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-pencil-line me-2"></i>
                    <?= $this->__('edit_operation') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editOperationForm" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label"><?= $this->__('operation_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editOpName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('time_minutes') ?></label>
                            <input type="number" name="time_minutes" id="editOpTime" class="form-control" min="0" step="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                            <input type="number" name="labor_rate" id="editOpRate" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= $this->__('materials') ?></label>
                            <div class="d-flex flex-wrap gap-2" id="editOpMaterials">
                                <?php foreach ($materials ?? [] as $mat): ?>
                                <label class="btn btn-outline-primary btn-sm position-relative">
                                    <input type="checkbox" name="material_ids[]" value="<?= $mat['id'] ?>" class="btn-check edit-op-mat-cb">
                                    <?php if (!empty($mat['filament_alias'])): ?>
                                    <strong><?= $this->e($mat['filament_alias']) ?></strong> -
                                    <?php endif; ?>
                                    <?= $this->e($mat['sku']) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('printer') ?></label>
                            <select name="printer_id" id="editOpPrinter" class="form-select">
                                <option value=""><?= $this->__('select_printer') ?></option>
                                <?php foreach ($printers ?? [] as $pr): ?>
                                <option value="<?= $pr['id'] ?>">
                                    <?= $this->e($pr['name']) ?><?= !empty($pr['model']) ? ' - ' . $this->e($pr['model']) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('tool') ?></label>
                            <select name="tool_id" id="editOpTool" class="form-select">
                                <option value=""><?= $this->__('select_tool') ?></option>
                                <?php foreach ($tools ?? [] as $tool): ?>
                                <option value="<?= $tool['id'] ?>"><?= $this->e($tool['sku']) ?> - <?= $this->e($tool['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= $this->__('description') ?></label>
                            <textarea name="description" id="editOpDescription" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal"><?= $this->__('cancel') ?></button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line me-1"></i> <?= $this->__('save') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

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
    let bsModal = null;

    if (editModal && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(editModal);
    }

    if (editForm && canEdit) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            ajaxPost(this.action, formData).then(data => {
                if (data.success) {
                    if (bsModal) bsModal.hide();
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
                cb.closest('label').classList.toggle('active', cb.checked);
            });

            document.getElementById('editOpPrinter').value = this.dataset.printer || '';
            document.getElementById('editOpTool').value = this.dataset.tool || '';

            if (bsModal) bsModal.show();
        });
    });

    // Material checkbox toggle styling
    document.querySelectorAll('.btn-check').forEach(cb => {
        cb.addEventListener('change', function() {
            this.closest('label').classList.toggle('active', this.checked);
        });
    });

    // Operation reordering - AJAX
    document.querySelectorAll('form[action*="/move-"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            ajaxPost(this.action, formData).then(data => {
                if (data.success) {
                    location.reload();
                }
            }).catch(err => console.error(err));
        });
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
                    document.querySelectorAll('#operationsBody tr[data-operation-id]').forEach((row, index) => {
                        const numCell = row.querySelector('td:nth-child(2) strong');
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
