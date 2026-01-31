<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="/costing/plan" class="btn btn-soft-primary">
                <i class="ri-file-list-3-line me-1"></i> <?= $this->__('planned_costs') ?>
            </a>
            <a href="/costing/actual" class="btn btn-soft-secondary">
                <i class="ri-money-dollar-circle-line me-1"></i> <?= $this->__('actual_costs') ?>
            </a>
            <a href="/costing/compare" class="btn btn-soft-info">
                <i class="ri-bar-chart-grouped-line me-1"></i> <?= $this->__('plan_vs_actual') ?>
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('active_variants') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= (int)$totalVariants ?></h4>
                        <span class="badge bg-primary-subtle text-primary"><?= $this->__('variants') ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="ri-shopping-bag-line text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('with_calculated_costs') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= (int)$variantsWithCost ?></h4>
                        <span class="badge bg-success-subtle text-success"><?= $this->__('calculated') ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-calculator-line text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('completed_orders') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= (int)$completedOrders ?></h4>
                        <span class="badge bg-info-subtle text-info"><?= $this->__('orders') ?></span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle rounded fs-3">
                            <i class="ri-checkbox-circle-line text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Cost Variances -->
<div class="card">
    <div class="card-header align-items-center d-flex">
        <h5 class="card-title mb-0 flex-grow-1">
            <i class="ri-exchange-funds-line me-2"></i><?= $this->__('recent_cost_variances') ?>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentVariances)): ?>
        <div class="text-center text-muted py-5">
            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
            <?= $this->__('no_completed_orders') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('order') ?></th>
                        <th><?= $this->__('variant') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('planned') ?></th>
                        <th class="text-end"><?= $this->__('actual_cost') ?></th>
                        <th class="text-end"><?= $this->__('variance') ?></th>
                        <th><?= $this->__('completed') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentVariances as $row): ?>
                    <tr>
                        <td>
                            <a href="/production/orders/<?= $row['order_number'] ?>" class="fw-medium text-primary">
                                <?= h($row['order_number']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="/costing/variant/<?= $row['sku'] ?>" class="fw-medium">
                                <?= h($row['sku']) ?>
                            </a>
                            <small class="d-block text-muted"><?= h($row['name']) ?></small>
                        </td>
                        <td class="text-end"><?= number_format($row['quantity'], 2) ?></td>
                        <td class="text-end"><?= number_format(($row['planned_cost'] ?? 0) * $row['quantity'], 2) ?></td>
                        <td class="text-end"><?= number_format($row['actual_cost'] ?? 0, 2) ?></td>
                        <td class="text-end">
                            <?php
                            $variance = $row['variance'];
                            $sign = $variance > 0 ? '+' : '';
                            if ($variance > 0): ?>
                            <span class="badge bg-danger-subtle text-danger">
                                <?= $sign ?><?= number_format($variance, 2) ?>
                                (<?= $sign ?><?= number_format($row['variance_percent'], 1) ?>%)
                            </span>
                            <?php elseif ($variance < 0): ?>
                            <span class="badge bg-success-subtle text-success">
                                <?= number_format($variance, 2) ?>
                                (<?= number_format($row['variance_percent'], 1) ?>%)
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">0.00 (0.0%)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-muted">
                                <i class="ri-calendar-line me-1"></i>
                                <?= date('d.m.Y', strtotime($row['completed_at'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
