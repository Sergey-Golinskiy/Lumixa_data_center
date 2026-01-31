<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('order_details') ?></h4>
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="/sales/orders" class="btn btn-soft-secondary">
                        <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('back_to_list') ?>
                    </a>
                    <a href="/sales/orders/<?= $order['id'] ?>/edit" class="btn btn-soft-primary">
                        <i class="ri-pencil-line align-bottom me-1"></i> <?= $this->__('edit') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-xl-8">
        <!-- Order Header -->
        <div class="card">
            <div class="card-header bg-primary-subtle">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="card-title mb-1"><?= $this->e($order['order_number']) ?></h5>
                        <?php if ($order['external_id']): ?>
                            <p class="text-muted mb-0">
                                <i class="ri-external-link-line me-1"></i>
                                <?= $this->__('external_id') ?>: <?= $this->e($order['external_id']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php
                        $sourceClasses = [
                            'woocommerce' => 'bg-purple-subtle text-purple',
                            'instagram' => 'bg-danger-subtle text-danger',
                            'offline' => 'bg-secondary-subtle text-secondary',
                            'manual' => 'bg-info-subtle text-info'
                        ];
                        $sourceClass = $sourceClasses[$order['source']] ?? 'bg-secondary-subtle text-secondary';
                        ?>
                        <span class="badge <?= $sourceClass ?> fs-12">
                            <i class="ri-store-2-line me-1"></i><?= $this->__('source_' . $order['source']) ?>
                        </span>
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
                        <span class="badge <?= $statusClass ?> fs-12">
                            <i class="ri-checkbox-circle-line me-1"></i><?= $this->__('order_status_' . $order['status']) ?>
                        </span>
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
                        <span class="badge <?= $paymentClass ?> fs-12">
                            <i class="ri-money-dollar-circle-line me-1"></i><?= $this->__('payment_status_' . $order['payment_status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-shopping-cart-line align-bottom me-1"></i> <?= $this->__('order_items') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('sku') ?></th>
                                <th><?= $this->__('product') ?></th>
                                <th class="text-center"><?= $this->__('qty') ?></th>
                                <th class="text-end"><?= $this->__('unit_price') ?></th>
                                <th class="text-end"><?= $this->__('total') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-body"><?= $this->e($item['sku'] ?: '-') ?></span>
                                </td>
                                <td>
                                    <h6 class="mb-0"><?= $this->e($item['name']) ?></h6>
                                    <?php if ($item['variant_info']): ?>
                                        <small class="text-muted"><?= $this->e($item['variant_info']) ?></small>
                                    <?php endif; ?>
                                    <?php if ($item['product_id'] && $item['product_name_local']): ?>
                                        <br><a href="/catalog/products/<?= $item['product_id'] ?>" class="link-secondary small">
                                            <i class="ri-link me-1"></i><?= $this->__('local_product') ?>: <?= $this->e($item['product_name_local']) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary"><?= (int)$item['quantity'] ?></span>
                                </td>
                                <td class="text-end"><?= number_format((float)$item['unit_price'], 2) ?></td>
                                <td class="text-end fw-semibold"><?= number_format((float)$item['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><?= $this->__('subtotal') ?>:</td>
                                <td class="text-end"><?= number_format((float)$order['subtotal'], 2) ?></td>
                            </tr>
                            <?php if ((float)$order['shipping_cost'] > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end"><?= $this->__('shipping') ?>:</td>
                                <td class="text-end"><?= number_format((float)$order['shipping_cost'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ((float)$order['discount'] > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end"><?= $this->__('discount') ?>:</td>
                                <td class="text-end text-danger">-<?= number_format((float)$order['discount'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end fs-15"><?= $this->__('total') ?>:</td>
                                <td class="text-end fs-15"><?= number_format((float)$order['total'], 2) ?> <?= $this->e($order['currency']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <?php if ($order['notes'] || $order['internal_notes']): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-file-text-line align-bottom me-1"></i> <?= $this->__('notes') ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($order['notes']): ?>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">
                            <i class="ri-user-line me-1"></i> <?= $this->__('customer_notes') ?>
                        </h6>
                        <p class="mb-0"><?= nl2br($this->e($order['notes'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($order['internal_notes']): ?>
                    <div class="alert alert-warning mb-0">
                        <h6 class="alert-heading mb-2">
                            <i class="ri-lock-line me-1"></i> <?= $this->__('internal_notes') ?>
                        </h6>
                        <p class="mb-0"><?= nl2br($this->e($order['internal_notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-xl-4">
        <!-- Quick Status Update -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-refresh-line align-bottom me-1"></i> <?= $this->__('update_status') ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/sales/orders/<?= $order['id'] ?>/status">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <div class="mb-3">
                        <select name="status" class="form-select">
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $this->e($st['code']) ?>" <?= $order['status'] === $st['code'] ? 'selected' : '' ?>
                                        data-color="<?= $this->e($st['color']) ?>">
                                    <?= $this->e($st['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri-check-line me-1"></i> <?= $this->__('update') ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-user-line align-bottom me-1"></i> <?= $this->__('customer') ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($order['customer_name'] || $order['customer_email'] || $order['customer_phone']): ?>
                    <?php if ($order['customer_name']): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <i class="ri-user-3-line text-muted me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= $this->e($order['customer_name']) ?></h6>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['customer_email']): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <i class="ri-mail-line text-muted me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <a href="mailto:<?= $this->e($order['customer_email']) ?>" class="link-primary">
                                    <?= $this->e($order['customer_email']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['customer_phone']): ?>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ri-phone-line text-muted me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <a href="tel:<?= $this->e($order['customer_phone']) ?>" class="link-primary">
                                    <?= $this->e($order['customer_phone']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">
                        <i class="ri-information-line me-1"></i> <?= $this->__('no_customer_info') ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Shipping -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-truck-line align-bottom me-1"></i> <?= $this->__('shipping') ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($order['shipping_address'] || $order['shipping_city']): ?>
                    <div class="mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="ri-map-pin-line text-muted me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <?php if ($order['shipping_address']): ?>
                                    <?= nl2br($this->e($order['shipping_address'])) ?><br>
                                <?php endif; ?>
                                <?php if ($order['shipping_city']): ?>
                                    <?= $this->e($order['shipping_city']) ?>
                                    <?php if ($order['shipping_postal_code']): ?>
                                        , <?= $this->e($order['shipping_postal_code']) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($order['shipping_method']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><?= $this->__('method') ?></small>
                        <span class="badge bg-light text-body">
                            <i class="ri-truck-line me-1"></i><?= $this->e($order['shipping_method']) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($order['tracking_number']): ?>
                    <div>
                        <small class="text-muted d-block mb-1"><?= $this->__('tracking') ?></small>
                        <?php if ($order['tracking_url']): ?>
                            <a href="<?= $this->e($order['tracking_url']) ?>" target="_blank" class="btn btn-soft-info btn-sm">
                                <i class="ri-truck-line me-1"></i><?= $this->e($order['tracking_number']) ?>
                                <i class="ri-external-link-line ms-1"></i>
                            </a>
                        <?php else: ?>
                            <span class="badge bg-info-subtle text-info"><?= $this->e($order['tracking_number']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-bank-card-line align-bottom me-1"></i> <?= $this->__('payment') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
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
                    <span class="badge <?= $paymentClass ?> fs-12">
                        <?= $this->__('payment_status_' . $order['payment_status']) ?>
                    </span>
                </div>
                <?php if ($order['payment_method']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><?= $this->__('method') ?></small>
                        <span><?= $this->e($order['payment_method']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($order['paid_at']): ?>
                    <div>
                        <small class="text-muted d-block mb-1"><?= $this->__('paid_at') ?></small>
                        <span><i class="ri-calendar-check-line me-1"></i><?= $this->datetime($order['paid_at']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-time-line align-bottom me-1"></i> <?= $this->__('timeline') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline-2">
                    <div class="timeline-2-item">
                        <i class="ri-checkbox-circle-fill text-success timeline-2-icon"></i>
                        <div class="timeline-2-content">
                            <p class="mb-0 fw-medium"><?= $this->__('order_placed') ?></p>
                            <small class="text-muted"><?= $this->datetime($order['ordered_at'] ?: $order['created_at']) ?></small>
                        </div>
                    </div>
                    <?php if ($order['shipped_at']): ?>
                    <div class="timeline-2-item">
                        <i class="ri-truck-fill text-primary timeline-2-icon"></i>
                        <div class="timeline-2-content">
                            <p class="mb-0 fw-medium"><?= $this->__('order_shipped') ?></p>
                            <small class="text-muted"><?= $this->datetime($order['shipped_at']) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['delivered_at']): ?>
                    <div class="timeline-2-item">
                        <i class="ri-home-smile-fill text-success timeline-2-icon"></i>
                        <div class="timeline-2-content">
                            <p class="mb-0 fw-medium"><?= $this->__('order_delivered') ?></p>
                            <small class="text-muted"><?= $this->datetime($order['delivered_at']) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['synced_at']): ?>
                    <div class="timeline-2-item">
                        <i class="ri-refresh-fill text-info timeline-2-icon"></i>
                        <div class="timeline-2-content">
                            <p class="mb-0 fw-medium"><?= $this->__('last_synced') ?></p>
                            <small class="text-muted"><?= $this->datetime($order['synced_at']) ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card border-danger">
            <div class="card-header bg-danger-subtle">
                <h5 class="card-title mb-0 text-danger">
                    <i class="ri-error-warning-line align-bottom me-1"></i> <?= $this->__('actions') ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($order['source_url']): ?>
                    <a href="<?= $this->e($order['source_url']) ?>" target="_blank" class="btn btn-soft-secondary w-100 mb-2">
                        <i class="ri-external-link-line me-1"></i> <?= $this->__('view_in_source') ?>
                    </a>
                <?php endif; ?>

                <form method="POST" action="/sales/orders/<?= $order['id'] ?>/delete"
                      onsubmit="return confirm('<?= $this->__('confirm_delete_order') ?>')">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="ri-delete-bin-line me-1"></i> <?= $this->__('delete_order') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-2 {
    position: relative;
    padding-left: 1.5rem;
}
.timeline-2::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0.5rem;
    bottom: 0.5rem;
    width: 2px;
    background-color: var(--vz-border-color);
}
.timeline-2-item {
    position: relative;
    padding-bottom: 1rem;
    display: flex;
    align-items: flex-start;
}
.timeline-2-item:last-child {
    padding-bottom: 0;
}
.timeline-2-icon {
    position: absolute;
    left: -1.25rem;
    width: 1.5rem;
    height: 1.5rem;
    background: var(--vz-card-bg);
    font-size: 1rem;
}
.timeline-2-content {
    padding-left: 1rem;
}
</style>

<?php $this->endSection(); ?>
