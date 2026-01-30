<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/sales/orders" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
    <a href="/sales/orders/<?= $order['id'] ?>/edit" class="btn btn-primary"><?= $this->__('edit') ?></a>
</div>

<div class="order-show-grid">
    <!-- Main Content -->
    <div class="order-main">
        <!-- Order Header -->
        <div class="card order-header-card">
            <div class="order-header">
                <div class="order-number">
                    <h1><?= $this->e($order['order_number']) ?></h1>
                    <?php if ($order['external_id']): ?>
                        <span class="external-id"><?= $this->__('external_id') ?>: <?= $this->e($order['external_id']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="order-badges">
                    <span class="badge badge-source-<?= $order['source'] ?>">
                        <?= $this->__('source_' . $order['source']) ?>
                    </span>
                    <span class="badge badge-status-<?= $order['status'] ?>">
                        <?= $this->__('order_status_' . $order['status']) ?>
                    </span>
                    <span class="badge badge-payment-<?= $order['payment_status'] ?>">
                        <?= $this->__('payment_status_' . $order['payment_status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card">
            <div class="card-header"><?= $this->__('order_items') ?></div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= $this->__('sku') ?></th>
                            <th><?= $this->__('product') ?></th>
                            <th class="text-center"><?= $this->__('qty') ?></th>
                            <th class="text-right"><?= $this->__('unit_price') ?></th>
                            <th class="text-right"><?= $this->__('total') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?= $this->e($item['sku'] ?: '-') ?>
                            </td>
                            <td>
                                <strong><?= $this->e($item['name']) ?></strong>
                                <?php if ($item['variant_info']): ?>
                                    <br><small class="text-muted"><?= $this->e($item['variant_info']) ?></small>
                                <?php endif; ?>
                                <?php if ($item['product_id'] && $item['product_name_local']): ?>
                                    <br><a href="/catalog/products/<?= $item['product_id'] ?>" class="text-muted small">
                                        <?= $this->__('local_product') ?>: <?= $this->e($item['product_name_local']) ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= (int)$item['quantity'] ?></td>
                            <td class="text-right"><?= number_format((float)$item['unit_price'], 2) ?></td>
                            <td class="text-right"><strong><?= number_format((float)$item['total'], 2) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><?= $this->__('subtotal') ?>:</td>
                            <td class="text-right"><?= number_format((float)$order['subtotal'], 2) ?></td>
                        </tr>
                        <?php if ((float)$order['shipping_cost'] > 0): ?>
                        <tr>
                            <td colspan="4" class="text-right"><?= $this->__('shipping') ?>:</td>
                            <td class="text-right"><?= number_format((float)$order['shipping_cost'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ((float)$order['discount'] > 0): ?>
                        <tr>
                            <td colspan="4" class="text-right"><?= $this->__('discount') ?>:</td>
                            <td class="text-right">-<?= number_format((float)$order['discount'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td colspan="4" class="text-right"><strong><?= $this->__('total') ?>:</strong></td>
                            <td class="text-right"><strong><?= number_format((float)$order['total'], 2) ?> <?= $this->e($order['currency']) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <?php if ($order['notes'] || $order['internal_notes']): ?>
        <div class="card" style="margin-top: 20px;">
            <div class="card-header"><?= $this->__('notes') ?></div>
            <div class="card-body">
                <?php if ($order['notes']): ?>
                    <div class="note-section">
                        <strong><?= $this->__('customer_notes') ?>:</strong>
                        <p><?= nl2br($this->e($order['notes'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($order['internal_notes']): ?>
                    <div class="note-section internal">
                        <strong><?= $this->__('internal_notes') ?>:</strong>
                        <p><?= nl2br($this->e($order['internal_notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="order-sidebar">
        <!-- Quick Status Update -->
        <div class="card">
            <div class="card-header"><?= $this->__('update_status') ?></div>
            <div class="card-body">
                <form method="POST" action="/sales/orders/<?= $order['id'] ?>/status">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <div class="form-group">
                        <select name="status" class="status-select">
                            <?php
                            $statuses = ['pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'];
                            foreach ($statuses as $st):
                            ?>
                                <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>>
                                    <?= $this->__('order_status_' . $st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><?= $this->__('update') ?></button>
                </form>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card" style="margin-top: 15px;">
            <div class="card-header"><?= $this->__('customer') ?></div>
            <div class="card-body">
                <?php if ($order['customer_name']): ?>
                    <div class="info-row">
                        <strong><?= $this->e($order['customer_name']) ?></strong>
                    </div>
                <?php endif; ?>
                <?php if ($order['customer_email']): ?>
                    <div class="info-row">
                        <a href="mailto:<?= $this->e($order['customer_email']) ?>"><?= $this->e($order['customer_email']) ?></a>
                    </div>
                <?php endif; ?>
                <?php if ($order['customer_phone']): ?>
                    <div class="info-row">
                        <a href="tel:<?= $this->e($order['customer_phone']) ?>"><?= $this->e($order['customer_phone']) ?></a>
                    </div>
                <?php endif; ?>
                <?php if (!$order['customer_name'] && !$order['customer_email'] && !$order['customer_phone']): ?>
                    <p class="text-muted"><?= $this->__('no_customer_info') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Shipping -->
        <div class="card" style="margin-top: 15px;">
            <div class="card-header"><?= $this->__('shipping') ?></div>
            <div class="card-body">
                <?php if ($order['shipping_address'] || $order['shipping_city']): ?>
                    <div class="info-row">
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
                <?php endif; ?>

                <?php if ($order['shipping_method']): ?>
                    <div class="info-row" style="margin-top: 10px;">
                        <small class="text-muted"><?= $this->__('method') ?>:</small><br>
                        <?= $this->e($order['shipping_method']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($order['tracking_number']): ?>
                    <div class="info-row tracking" style="margin-top: 10px;">
                        <small class="text-muted"><?= $this->__('tracking') ?>:</small><br>
                        <?php if ($order['tracking_url']): ?>
                            <a href="<?= $this->e($order['tracking_url']) ?>" target="_blank">
                                <?= $this->e($order['tracking_number']) ?>
                            </a>
                        <?php else: ?>
                            <?= $this->e($order['tracking_number']) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment -->
        <div class="card" style="margin-top: 15px;">
            <div class="card-header"><?= $this->__('payment') ?></div>
            <div class="card-body">
                <div class="info-row">
                    <span class="badge badge-payment-<?= $order['payment_status'] ?>">
                        <?= $this->__('payment_status_' . $order['payment_status']) ?>
                    </span>
                </div>
                <?php if ($order['payment_method']): ?>
                    <div class="info-row" style="margin-top: 10px;">
                        <small class="text-muted"><?= $this->__('method') ?>:</small><br>
                        <?= $this->e($order['payment_method']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($order['paid_at']): ?>
                    <div class="info-row" style="margin-top: 10px;">
                        <small class="text-muted"><?= $this->__('paid_at') ?>:</small><br>
                        <?= $this->datetime($order['paid_at']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card" style="margin-top: 15px;">
            <div class="card-header"><?= $this->__('timeline') ?></div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <span class="timeline-date"><?= $this->datetime($order['ordered_at'] ?: $order['created_at']) ?></span>
                        <span class="timeline-label"><?= $this->__('order_placed') ?></span>
                    </div>
                    <?php if ($order['shipped_at']): ?>
                    <div class="timeline-item">
                        <span class="timeline-date"><?= $this->datetime($order['shipped_at']) ?></span>
                        <span class="timeline-label"><?= $this->__('order_shipped') ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['delivered_at']): ?>
                    <div class="timeline-item">
                        <span class="timeline-date"><?= $this->datetime($order['delivered_at']) ?></span>
                        <span class="timeline-label"><?= $this->__('order_delivered') ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['synced_at']): ?>
                    <div class="timeline-item sync">
                        <span class="timeline-date"><?= $this->datetime($order['synced_at']) ?></span>
                        <span class="timeline-label"><?= $this->__('last_synced') ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card danger-zone" style="margin-top: 15px;">
            <div class="card-header"><?= $this->__('actions') ?></div>
            <div class="card-body">
                <?php if ($order['source_url']): ?>
                    <a href="<?= $this->e($order['source_url']) ?>" target="_blank" class="btn btn-secondary btn-block" style="margin-bottom: 10px;">
                        <?= $this->__('view_in_source') ?>
                    </a>
                <?php endif; ?>

                <form method="POST" action="/sales/orders/<?= $order['id'] ?>/delete"
                      onsubmit="return confirm('<?= $this->__('confirm_delete_order') ?>')">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <button type="submit" class="btn btn-danger btn-block"><?= $this->__('delete_order') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.order-show-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
}

@media (max-width: 1000px) {
    .order-show-grid {
        grid-template-columns: 1fr;
    }
}

.order-header-card {
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.order-number h1 {
    margin: 0;
    font-size: 1.8em;
}

.order-number .external-id {
    color: var(--text-muted);
    font-size: 0.85em;
}

.order-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.total-row td {
    background: var(--bg-secondary);
}

.note-section {
    margin-bottom: 15px;
}

.note-section:last-child {
    margin-bottom: 0;
}

.note-section.internal {
    padding: 10px;
    background: #fff3cd;
    border-radius: 4px;
}

.info-row {
    margin-bottom: 8px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: var(--border-color);
}

.timeline-item {
    position: relative;
    padding-bottom: 15px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 3px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--primary);
    border: 2px solid var(--bg-primary);
}

.timeline-item.sync::before {
    background: var(--info);
}

.timeline-date {
    display: block;
    font-size: 0.8em;
    color: var(--text-muted);
}

.timeline-label {
    font-weight: 500;
}

.danger-zone {
    border-color: var(--danger);
}

.status-select {
    width: 100%;
    padding: 10px;
    font-size: 1em;
}

.badge-source-woocommerce { background: #96588a; color: white; }
.badge-source-instagram { background: #c13584; color: white; }
.badge-source-offline { background: #6c757d; color: white; }
.badge-source-manual { background: #17a2b8; color: white; }

.badge-status-pending { background: #ffc107; color: #000; }
.badge-status-processing { background: #17a2b8; color: white; }
.badge-status-on_hold { background: #fd7e14; color: white; }
.badge-status-shipped { background: #6f42c1; color: white; }
.badge-status-delivered { background: #20c997; color: white; }
.badge-status-completed { background: #28a745; color: white; }
.badge-status-cancelled { background: #dc3545; color: white; }
.badge-status-refunded { background: #6c757d; color: white; }

.badge-payment-pending { background: #ffc107; color: #000; }
.badge-payment-paid { background: #28a745; color: white; }
.badge-payment-partial { background: #fd7e14; color: white; }
.badge-payment-refunded { background: #6c757d; color: white; }
.badge-payment-failed { background: #dc3545; color: white; }
</style>

<?php $this->endSection(); ?>
