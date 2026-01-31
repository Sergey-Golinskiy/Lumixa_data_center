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
        <form method="get" action="/costing/actual">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('from') ?></label>
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

<!-- Actual Production Costs Table -->
<div class="card">
    <div class="card-header align-items-center d-flex">
        <h5 class="card-title mb-0 flex-grow-1">
            <i class="ri-money-dollar-circle-line me-2"></i><?= $this->__('actual_production_costs') ?>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($orders)): ?>
        <div class="text-center text-muted py-5">
            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
            <?= $this->__('no_completed_orders_period') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('order') ?></th>
                        <th><?= $this->__('variant') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('material_cost') ?></th>
                        <th class="text-end"><?= $this->__('labor_cost') ?></th>
                        <th class="text-end"><?= $this->__('actual_total') ?></th>
                        <th class="text-end"><?= $this->__('planned') ?></th>
                        <th class="text-end"><?= $this->__('variance') ?></th>
                        <th><?= $this->__('completed') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <a href="/production/orders/<?= $order['id'] ?>" class="fw-medium text-primary">
                                <?= h($order['order_number']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="/costing/variant/<?= $order['sku'] ?>" class="fw-medium">
                                <?= h($order['sku']) ?>
                            </a>
                            <small class="d-block text-muted"><?= h($order['variant_name']) ?></small>
                        </td>
                        <td class="text-end"><?= number_format($order['completed_quantity'], 2) ?></td>
                        <td class="text-end">
                            <span><?= number_format($order['actual_material_cost'], 2) ?></span>
                            <small class="d-block text-muted">
                                <i class="ri-stack-line me-1"></i><?= (int)$order['material_count'] ?> <?= $this->__('items') ?>
                            </small>
                        </td>
                        <td class="text-end">
                            <span><?= number_format($order['actual_labor_cost'], 2) ?></span>
                            <small class="d-block text-muted">
                                <i class="ri-time-line me-1"></i><?= (int)$order['labor_minutes'] ?> <?= $this->__('minutes_short') ?>
                            </small>
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold"><?= number_format($order['actual_total'], 2) ?></span>
                        </td>
                        <td class="text-end"><?= number_format($order['planned_total'], 2) ?></td>
                        <td class="text-end">
                            <?php
                            $variance = $order['variance'];
                            $sign = $variance > 0 ? '+' : '';
                            if ($variance > 0): ?>
                            <span class="badge bg-danger-subtle text-danger">
                                <?= $sign ?><?= number_format($variance, 2) ?>
                            </span>
                            <?php elseif ($variance < 0): ?>
                            <span class="badge bg-success-subtle text-success">
                                <?= number_format($variance, 2) ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">0.00</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-muted">
                                <i class="ri-calendar-line me-1"></i>
                                <?= date('d.m.Y', strtotime($order['completed_at'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted">
                <?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&from=<?= $dateFrom ?>&to=<?= $dateTo ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&from=<?= $dateFrom ?>&to=<?= $dateTo ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&from=<?= $dateFrom ?>&to=<?= $dateTo ?>">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
