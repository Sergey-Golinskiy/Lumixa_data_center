<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('sales_orders') ?></h4>
            <div class="page-title-right">
                <a href="/sales/orders/create" class="btn btn-success">
                    <i class="ri-add-line align-bottom me-1"></i> <?= $this->__('create_order') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('total_orders') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $stats['total'] ?></h4>
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
    <div class="col-xl col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('pending') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $stats['pending'] ?></h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="ri-time-line text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('processing') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $stats['processing'] ?></h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle rounded fs-3">
                            <i class="ri-loader-4-line text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('completed') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= $stats['completed'] ?></h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-checkbox-circle-line text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0"><?= $this->__('this_month_revenue') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= number_format($stats['this_month_revenue'], 2) ?></h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-secondary-subtle rounded fs-3">
                            <i class="ri-money-dollar-circle-line text-secondary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-filter-3-line align-bottom me-1"></i> <?= $this->__('filters') ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="/sales/orders">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label"><?= $this->__('search') ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="<?= $this->__('search_orders_placeholder') ?>"
                                       value="<?= $this->e($search) ?>">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label"><?= $this->__('source') ?></label>
                            <select name="source" class="form-select">
                                <option value=""><?= $this->__('all_sources') ?></option>
                                <?php foreach ($sources as $src): ?>
                                    <option value="<?= $src ?>" <?= $source === $src ? 'selected' : '' ?>>
                                        <?= $this->__('source_' . $src) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label"><?= $this->__('status') ?></label>
                            <select name="status" class="form-select">
                                <option value=""><?= $this->__('all_statuses') ?></option>
                                <?php foreach ($statuses as $st): ?>
                                    <option value="<?= $this->e($st['code']) ?>" <?= $status === $st['code'] ? 'selected' : '' ?>>
                                        <?= $this->e($st['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label"><?= $this->__('date_from') ?></label>
                            <input type="date" name="date_from" class="form-control"
                                   value="<?= $this->e($dateFrom) ?>">
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label"><?= $this->__('date_to') ?></label>
                            <input type="date" name="date_to" class="form-control"
                                   value="<?= $this->e($dateTo) ?>">
                        </div>

                        <div class="col-lg-1 col-md-6 d-flex align-items-end">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-search-line"></i>
                                </button>
                                <a href="/sales/orders" class="btn btn-soft-secondary" <?= (!$search && !$source && !$status && !$dateFrom && !$dateTo) ? 'disabled' : '' ?>>
                                    <i class="ri-refresh-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $this->__('orders_list') ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="text-center py-4">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                <i class="ri-shopping-bag-line"></i>
                            </div>
                        </div>
                        <h5 class="mb-2"><?= $this->__('no_orders_found') ?></h5>
                        <p class="text-muted mb-0"><?= $this->__('try_adjusting_filters') ?></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $this->__('order_number') ?></th>
                                    <th><?= $this->__('source') ?></th>
                                    <th><?= $this->__('customer') ?></th>
                                    <th class="text-center"><?= $this->__('items') ?></th>
                                    <th class="text-end"><?= $this->__('total') ?></th>
                                    <th><?= $this->__('status') ?></th>
                                    <th><?= $this->__('payment') ?></th>
                                    <th><?= $this->__('date') ?></th>
                                    <th class="text-center"><?= $this->__('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="/sales/orders/<?= $order['id'] ?>" class="fw-medium link-primary">
                                            <?= $this->e($order['order_number']) ?>
                                        </a>
                                        <?php if ($order['external_id']): ?>
                                            <br><small class="text-muted">#<?= $this->e($order['external_id']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $sourceClasses = [
                                            'woocommerce' => 'bg-purple-subtle text-purple',
                                            'instagram' => 'bg-danger-subtle text-danger',
                                            'offline' => 'bg-secondary-subtle text-secondary',
                                            'manual' => 'bg-info-subtle text-info'
                                        ];
                                        $sourceClass = $sourceClasses[$order['source']] ?? 'bg-secondary-subtle text-secondary';
                                        ?>
                                        <span class="badge <?= $sourceClass ?>">
                                            <?= $this->__('source_' . $order['source']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar-xs">
                                                    <div class="avatar-title bg-light text-primary rounded-circle">
                                                        <i class="ri-user-line"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?= $this->e($order['customer_name'] ?: '-') ?></h6>
                                                <?php if ($order['customer_email']): ?>
                                                    <small class="text-muted"><?= $this->e($order['customer_email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-body"><?= $order['item_count'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold"><?= number_format($order['total'], 2) ?></span>
                                        <small class="text-muted"><?= $this->e($order['currency']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'pending' => 'bg-warning-subtle text-warning',
                                            'processing' => 'bg-info-subtle text-info',
                                            'on_hold' => 'bg-secondary-subtle text-secondary',
                                            'shipped' => 'bg-primary-subtle text-primary',
                                            'delivered' => 'bg-success-subtle text-success',
                                            'completed' => 'bg-success-subtle text-success',
                                            'cancelled' => 'bg-danger-subtle text-danger',
                                            'refunded' => 'bg-dark-subtle text-dark'
                                        ];
                                        $statusClass = $statusClasses[$order['status']] ?? 'bg-secondary-subtle text-secondary';
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= $this->__('order_status_' . $order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $paymentClasses = [
                                            'pending' => 'bg-warning-subtle text-warning',
                                            'paid' => 'bg-success-subtle text-success',
                                            'partial' => 'bg-info-subtle text-info',
                                            'refunded' => 'bg-secondary-subtle text-secondary',
                                            'failed' => 'bg-danger-subtle text-danger'
                                        ];
                                        $paymentClass = $paymentClasses[$order['payment_status']] ?? 'bg-secondary-subtle text-secondary';
                                        ?>
                                        <span class="badge <?= $paymentClass ?>">
                                            <?= $this->__('payment_status_' . $order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?= $this->date($order['ordered_at'] ?: $order['created_at']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/sales/orders/<?= $order['id'] ?>" class="btn btn-soft-primary btn-sm">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination pagination-separated mb-0">
                                <?php
                                $params = array_filter([
                                    'search' => $search,
                                    'source' => $source,
                                    'status' => $status,
                                    'date_from' => $dateFrom,
                                    'date_to' => $dateTo
                                ]);
                                ?>
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="/sales/orders?<?= http_build_query(array_merge($params, ['page' => $page - 1])) ?>">
                                        <i class="ri-arrow-left-s-line"></i>
                                    </a>
                                </li>
                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="/sales/orders?<?= http_build_query(array_merge($params, ['page' => $p])) ?>">
                                            <?= $p ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="/sales/orders?<?= http_build_query(array_merge($params, ['page' => $page + 1])) ?>">
                                        <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
