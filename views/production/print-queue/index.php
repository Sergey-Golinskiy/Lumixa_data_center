<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('print_queue') ?></h4>
            <div class="page-title-right d-flex gap-2">
                <a href="/production" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('nav_production') ?>
                </a>
                <?php if ($this->can('production.print-queue.create')): ?>
                <a href="/production/print-queue/create" class="btn btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> <?= $this->__('add_print_job') ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line align-bottom me-1"></i> <?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="get" action="/production/print-queue">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="queued" <?= $status === 'queued' ? 'selected' : '' ?>><?= $this->__('queued') ?></option>
                        <option value="printing" <?= $status === 'printing' ? 'selected' : '' ?>><?= $this->__('printing') ?></option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>><?= $this->__('completed') ?></option>
                        <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>><?= $this->__('failed') ?></option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>><?= $this->__('cancelled') ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('printer') ?></label>
                    <select name="printer" class="form-select">
                        <option value=""><?= $this->__('all_printers') ?></option>
                        <?php foreach ($printers as $p): ?>
                        <option value="<?= h($p['printer']) ?>" <?= $printer === $p['printer'] ? 'selected' : '' ?>><?= h($p['printer']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-soft-primary">
                        <i class="ri-search-line align-bottom me-1"></i> <?= $this->__('filter') ?>
                    </button>
                    <?php if ($status || $printer): ?>
                    <a href="/production/print-queue" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line align-bottom me-1"></i> <?= $this->__('clear') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Print Jobs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-printer-line align-bottom me-1"></i> <?= $this->__('print_jobs') ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($jobs)): ?>
        <div class="text-center py-5">
            <div class="avatar-lg mx-auto mb-4">
                <div class="avatar-title bg-light text-secondary rounded-circle fs-24">
                    <i class="ri-printer-line"></i>
                </div>
            </div>
            <h5 class="mb-2"><?= $this->__('no_print_jobs_found') ?></h5>
            <p class="text-muted mb-0"><?= $this->__('try_adjusting_filters') ?></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('job_number') ?></th>
                        <th><?= $this->__('variant') ?></th>
                        <th><?= $this->__('order') ?></th>
                        <th><?= $this->__('printer') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th><?= $this->__('estimated_time') ?></th>
                        <th><?= $this->__('priority') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('created') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td>
                            <a href="/production/print-queue/<?= $job['id'] ?>" class="fw-semibold text-primary">
                                <?= h($job['job_number']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($job['variant_id']): ?>
                            <a href="/catalog/variants/<?= $job['variant_id'] ?>"><?= h($job['variant_sku']) ?></a>
                            <br><small class="text-muted"><?= h($job['variant_name']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($job['order_id']): ?>
                            <a href="/production/orders/<?= $job['order_id'] ?>" class="text-primary"><?= h($job['order_number']) ?></a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $job['printer'] ? h($job['printer']) : '<span class="text-muted">-</span>' ?></td>
                        <td class="text-end"><?= (int)$job['quantity'] ?></td>
                        <td><?= $job['estimated_time_minutes'] ? $job['estimated_time_minutes'] . ' ' . $this->__('minutes_short') : '-' ?></td>
                        <td>
                            <?php if ($job['priority'] > 0): ?>
                            <span class="badge bg-warning-subtle text-warning"><?= $job['priority'] ?></span>
                            <?php else: ?>
                            <span class="text-muted"><?= $this->__('normal') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusBadge = match($job['status']) {
                                'queued' => 'bg-secondary-subtle text-secondary',
                                'printing' => 'bg-warning-subtle text-warning',
                                'completed' => 'bg-success-subtle text-success',
                                'failed' => 'bg-danger-subtle text-danger',
                                'cancelled' => 'bg-dark-subtle text-dark',
                                default => 'bg-secondary-subtle text-secondary'
                            };
                            $statusLabels = [
                                'queued' => $this->__('queued'),
                                'printing' => $this->__('printing'),
                                'completed' => $this->__('completed'),
                                'failed' => $this->__('failed'),
                                'cancelled' => $this->__('cancelled')
                            ];
                            ?>
                            <span class="badge <?= $statusBadge ?>"><?= $statusLabels[$job['status']] ?? $this->e($job['status']) ?></span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($job['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&printer=<?= urlencode($printer) ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&printer=<?= urlencode($printer) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&printer=<?= urlencode($printer) ?>">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
