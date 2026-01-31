<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <a href="/costing" class="btn btn-soft-secondary">
                <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to', ['name' => $this->__('costing')]) ?>
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i><?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="get" action="/costing/compare">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('period') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                        <input type="date" name="from" value="<?= h($dateFrom) ?>" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('to') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                        <input type="date" name="to" value="<?= h($dateTo) ?>" class="form-control">
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-filter-line me-1"></i> <?= $this->__('apply') ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Plan vs Actual Comparison Table -->
<div class="card">
    <div class="card-header align-items-center d-flex">
        <h5 class="card-title mb-0 flex-grow-1">
            <i class="ri-bar-chart-grouped-line me-2"></i><?= $this->__('plan_vs_actual_comparison') ?>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($comparison)): ?>
        <div class="text-center text-muted py-5">
            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
            <?= $this->__('no_completed_production_period') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('variant') ?></th>
                        <th class="text-end"><?= $this->__('produced') ?></th>
                        <th class="text-end"><?= $this->__('unit_planned') ?></th>
                        <th class="text-end"><?= $this->__('unit_actual') ?></th>
                        <th class="text-end"><?= $this->__('total_planned') ?></th>
                        <th class="text-end"><?= $this->__('material_cost') ?></th>
                        <th class="text-end"><?= $this->__('labor_cost') ?></th>
                        <th class="text-end"><?= $this->__('total_actual') ?></th>
                        <th class="text-end"><?= $this->__('variance') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comparison as $row): ?>
                    <tr>
                        <td>
                            <a href="/costing/variant/<?= $row['id'] ?>" class="fw-medium text-primary">
                                <?= h($row['sku']) ?>
                            </a>
                            <small class="d-block text-muted"><?= h($row['name']) ?></small>
                        </td>
                        <td class="text-end"><?= number_format($row['total_produced'], 2) ?></td>
                        <td class="text-end"><?= number_format($row['unit_planned_cost'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($row['unit_actual_cost'], 2) ?></td>
                        <td class="text-end"><?= number_format($row['total_planned_cost'], 2) ?></td>
                        <td class="text-end"><?= number_format($row['total_material_cost'], 2) ?></td>
                        <td class="text-end"><?= number_format($row['total_labor_cost'], 2) ?></td>
                        <td class="text-end">
                            <span class="fw-semibold"><?= number_format($row['total_actual_cost'], 2) ?></span>
                        </td>
                        <td class="text-end">
                            <?php
                            $variance = $row['variance'];
                            $sign = $variance > 0 ? '+' : '';
                            if ($variance > 0): ?>
                            <span class="badge bg-danger-subtle text-danger">
                                <?= $sign ?><?= number_format($variance, 2) ?>
                                <small>(<?= $sign ?><?= number_format($row['variance_percent'], 1) ?>%)</small>
                            </span>
                            <?php elseif ($variance < 0): ?>
                            <span class="badge bg-success-subtle text-success">
                                <?= number_format($variance, 2) ?>
                                <small>(<?= number_format($row['variance_percent'], 1) ?>%)</small>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">0.00 (0.0%)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-semibold">
                        <td><?= $this->__('total') ?></td>
                        <td class="text-end"><?= number_format($totals['produced'], 2) ?></td>
                        <td colspan="2"></td>
                        <td class="text-end"><?= number_format($totals['planned'], 2) ?></td>
                        <td class="text-end"><?= number_format($totals['material'], 2) ?></td>
                        <td class="text-end"><?= number_format($totals['labor'], 2) ?></td>
                        <td class="text-end"><?= number_format($totals['actual'], 2) ?></td>
                        <td class="text-end">
                            <?php
                            $variance = $totals['variance'];
                            $sign = $variance > 0 ? '+' : '';
                            if ($variance > 0): ?>
                            <span class="badge bg-danger-subtle text-danger">
                                <?= $sign ?><?= number_format($variance, 2) ?>
                                <small>(<?= $sign ?><?= number_format($totals['variance_percent'], 1) ?>%)</small>
                            </span>
                            <?php elseif ($variance < 0): ?>
                            <span class="badge bg-success-subtle text-success">
                                <?= number_format($variance, 2) ?>
                                <small>(<?= number_format($totals['variance_percent'], 1) ?>%)</small>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">0.00 (0.0%)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Summary Card -->
        <div class="card-body border-top">
            <div class="row g-4">
                <div class="col-12">
                    <h6 class="mb-3"><i class="ri-pie-chart-2-line me-2"></i><?= $this->__('summary') ?></h6>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="ri-stack-line text-primary"></i>
                            </span>
                        </div>
                        <div>
                            <p class="text-muted mb-1"><?= $this->__('total_produced') ?></p>
                            <h5 class="mb-0"><?= number_format($totals['produced'], 2) ?> <?= $this->__('units') ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="ri-file-list-3-line text-info"></i>
                            </span>
                        </div>
                        <div>
                            <p class="text-muted mb-1"><?= $this->__('planned_cost') ?></p>
                            <h5 class="mb-0"><?= number_format($totals['planned'], 2) ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="ri-money-dollar-circle-line text-warning"></i>
                            </span>
                        </div>
                        <div>
                            <p class="text-muted mb-1"><?= $this->__('actual_cost') ?></p>
                            <h5 class="mb-0"><?= number_format($totals['actual'], 2) ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 <?= $totals['variance'] > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' ?> rounded">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-title <?= $totals['variance'] > 0 ? 'bg-danger' : 'bg-success' ?> rounded fs-3">
                                <i class="ri-exchange-funds-line text-white"></i>
                            </span>
                        </div>
                        <div>
                            <p class="text-muted mb-1"><?= $this->__('total_variance') ?></p>
                            <h5 class="mb-0 <?= $totals['variance'] > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= $totals['variance'] > 0 ? '+' : '' ?><?= number_format($totals['variance'], 2) ?>
                                <small>(<?= $totals['variance'] > 0 ? '+' : '' ?><?= number_format($totals['variance_percent'], 1) ?>%)</small>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
