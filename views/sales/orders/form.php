<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $order ? $this->__('edit_order') : $this->__('create_order') ?></h4>
            <div class="page-title-right">
                <a href="/sales/orders" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('back_to_list') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="<?= $order ? "/sales/orders/{$order['id']}" : '/sales/orders' ?>" id="order-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="row">
        <!-- Left Column: Order Items -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="ri-shopping-cart-line align-bottom me-1"></i> <?= $this->__('order_items') ?>
                    </h5>
                    <button type="button" class="btn btn-success btn-sm" id="add-item-btn">
                        <i class="ri-add-line align-bottom me-1"></i> <?= $this->__('add_item') ?>
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($this->hasError('items')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="ri-error-warning-line me-2"></i>
                            <?= $this->error('items') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;"><?= $this->__('sku') ?></th>
                                    <th><?= $this->__('name') ?></th>
                                    <th style="width: 100px;"><?= $this->__('qty') ?></th>
                                    <th style="width: 130px;"><?= $this->__('price') ?></th>
                                    <th style="width: 130px;"><?= $this->__('total') ?></th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                <?php if (!empty($items)): ?>
                                    <?php foreach ($items as $i => $item): ?>
                                    <tr class="item-row">
                                        <td>
                                            <input type="hidden" name="item_product_id[]" value="<?= $item['product_id'] ?? '' ?>">
                                            <input type="text" name="item_sku[]" class="form-control form-control-sm item-sku" value="<?= $this->e($item['sku'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="item_name[]" class="form-control form-control-sm item-name" required value="<?= $this->e($item['name']) ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="item_qty[]" class="form-control form-control-sm item-qty text-center" min="1" value="<?= (int)$item['quantity'] ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="item_price[]" class="form-control form-control-sm item-price text-end" step="0.01" min="0" value="<?= number_format((float)$item['unit_price'], 2, '.', '') ?>">
                                        </td>
                                        <td class="item-total text-end fw-semibold"><?= number_format((float)$item['total'], 2) ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-soft-danger btn-sm remove-item">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="item-row">
                                        <td>
                                            <input type="hidden" name="item_product_id[]" value="">
                                            <input type="text" name="item_sku[]" class="form-control form-control-sm item-sku">
                                        </td>
                                        <td>
                                            <input type="text" name="item_name[]" class="form-control form-control-sm item-name" required>
                                        </td>
                                        <td>
                                            <input type="number" name="item_qty[]" class="form-control form-control-sm item-qty text-center" min="1" value="1">
                                        </td>
                                        <td>
                                            <input type="number" name="item_price[]" class="form-control form-control-sm item-price text-end" step="0.01" min="0" value="0">
                                        </td>
                                        <td class="item-total text-end fw-semibold">0.00</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-soft-danger btn-sm remove-item">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-medium"><?= $this->__('subtotal') ?>:</td>
                                    <td class="text-end fw-semibold" id="order-subtotal"><?= number_format($order['subtotal'] ?? 0, 2) ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><?= $this->__('shipping') ?>:</td>
                                    <td>
                                        <input type="number" name="shipping_cost" id="shipping-cost" class="form-control form-control-sm text-end" step="0.01" min="0"
                                               value="<?= number_format($order['shipping_cost'] ?? 0, 2, '.', '') ?>">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><?= $this->__('discount') ?>:</td>
                                    <td>
                                        <input type="number" name="discount" id="order-discount" class="form-control form-control-sm text-end" step="0.01" min="0"
                                               value="<?= number_format($order['discount'] ?? 0, 2, '.', '') ?>">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold fs-15"><?= $this->__('total') ?>:</td>
                                    <td class="text-end fw-bold fs-15" id="order-total"><?= number_format($order['total'] ?? 0, 2) ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if (!empty($products)): ?>
                    <div class="mt-3 pt-3 border-top">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <label class="form-label mb-0">
                                    <i class="ri-add-circle-line me-1"></i> <?= $this->__('add_from_catalog') ?>:
                                </label>
                            </div>
                            <div class="col">
                                <select id="product-select" class="form-select">
                                    <option value=""><?= $this->__('select_product') ?></option>
                                    <?php foreach ($products as $prod): ?>
                                        <option value="<?= $prod['id'] ?>" data-sku="<?= $this->e($prod['sku']) ?>" data-name="<?= $this->e($prod['name']) ?>">
                                            <?= $this->e($prod['sku']) ?> - <?= $this->e($prod['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-file-text-line align-bottom me-1"></i> <?= $this->__('notes') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label"><?= $this->__('customer_notes') ?></label>
                        <textarea id="notes" name="notes" class="form-control" rows="2"><?= $this->e($order['notes'] ?? $this->old('notes')) ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label for="internal_notes" class="form-label"><?= $this->__('internal_notes') ?></label>
                        <textarea id="internal_notes" name="internal_notes" class="form-control" rows="2"><?= $this->e($order['internal_notes'] ?? $this->old('internal_notes')) ?></textarea>
                        <div class="form-text">
                            <i class="ri-information-line me-1"></i> <?= $this->__('internal_notes_help') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Order Details -->
        <div class="col-xl-4">
            <!-- Customer Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-line align-bottom me-1"></i> <?= $this->__('customer_info') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="customer_name" class="form-label"><?= $this->__('customer_name') ?></label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control"
                               value="<?= $this->e($order['customer_name'] ?? $this->old('customer_name')) ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_email" class="form-label"><?= $this->__('email') ?></label>
                            <input type="email" id="customer_email" name="customer_email" class="form-control"
                                   value="<?= $this->e($order['customer_email'] ?? $this->old('customer_email')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customer_phone" class="form-label"><?= $this->__('phone') ?></label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
                                   value="<?= $this->e($order['customer_phone'] ?? $this->old('customer_phone')) ?>">
                        </div>
                    </div>
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
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label"><?= $this->__('address') ?></label>
                        <textarea id="shipping_address" name="shipping_address" class="form-control" rows="2"><?= $this->e($order['shipping_address'] ?? $this->old('shipping_address')) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shipping_city" class="form-label"><?= $this->__('city') ?></label>
                            <input type="text" id="shipping_city" name="shipping_city" class="form-control"
                                   value="<?= $this->e($order['shipping_city'] ?? $this->old('shipping_city')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shipping_postal_code" class="form-label"><?= $this->__('postal_code') ?></label>
                            <input type="text" id="shipping_postal_code" name="shipping_postal_code" class="form-control"
                                   value="<?= $this->e($order['shipping_postal_code'] ?? $this->old('shipping_postal_code')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shipping_method" class="form-label"><?= $this->__('shipping_method') ?></label>
                        <input type="text" id="shipping_method" name="shipping_method" class="form-control"
                               placeholder="<?= $this->__('nova_poshta') ?>, <?= $this->__('ukrposhta') ?>..."
                               value="<?= $this->e($order['shipping_method'] ?? $this->old('shipping_method')) ?>">
                    </div>
                    <?php if ($order): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tracking_number" class="form-label"><?= $this->__('tracking_number') ?></label>
                            <input type="text" id="tracking_number" name="tracking_number" class="form-control"
                                   value="<?= $this->e($order['tracking_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tracking_url" class="form-label"><?= $this->__('tracking_url') ?></label>
                            <input type="url" id="tracking_url" name="tracking_url" class="form-control"
                                   value="<?= $this->e($order['tracking_url'] ?? '') ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Status -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-settings-3-line align-bottom me-1"></i> <?= $this->__('order_status') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!$order): ?>
                    <div class="mb-3">
                        <label for="source" class="form-label"><?= $this->__('source') ?></label>
                        <select id="source" name="source" class="form-select">
                            <option value="manual"><?= $this->__('source_manual') ?></option>
                            <option value="instagram"><?= $this->__('source_instagram') ?></option>
                            <option value="offline"><?= $this->__('source_offline') ?></option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label"><?= $this->__('status') ?></label>
                            <select id="status" name="status" class="form-select">
                                <?php
                                $statuses = ['pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'];
                                foreach ($statuses as $st):
                                ?>
                                    <option value="<?= $st ?>" <?= ($order['status'] ?? 'pending') === $st ? 'selected' : '' ?>>
                                        <?= $this->__('order_status_' . $st) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_status" class="form-label"><?= $this->__('payment_status') ?></label>
                            <select id="payment_status" name="payment_status" class="form-select">
                                <?php
                                $paymentStatuses = ['pending', 'paid', 'partial', 'refunded', 'failed'];
                                foreach ($paymentStatuses as $ps):
                                ?>
                                    <option value="<?= $ps ?>" <?= ($order['payment_status'] ?? 'pending') === $ps ? 'selected' : '' ?>>
                                        <?= $this->__('payment_status_' . $ps) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label"><?= $this->__('payment_method') ?></label>
                            <input type="text" id="payment_method" name="payment_method" class="form-control"
                                   placeholder="<?= $this->__('cash') ?>, <?= $this->__('card') ?>..."
                                   value="<?= $this->e($order['payment_method'] ?? $this->old('payment_method')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label"><?= $this->__('currency') ?></label>
                            <select id="currency" name="currency" class="form-select">
                                <option value="UAH" <?= ($order['currency'] ?? 'UAH') === 'UAH' ? 'selected' : '' ?>>UAH</option>
                                <option value="USD" <?= ($order['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                                <option value="EUR" <?= ($order['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                            </select>
                        </div>
                    </div>

                    <?php if (!$order): ?>
                    <div class="mb-3">
                        <label for="ordered_at" class="form-label"><?= $this->__('order_date') ?></label>
                        <input type="datetime-local" id="ordered_at" name="ordered_at" class="form-control"
                               value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-save-line align-bottom me-1"></i>
                            <?= $order ? $this->__('save_changes') : $this->__('create_order') ?>
                        </button>
                        <a href="/sales/orders" class="btn btn-soft-secondary">
                            <i class="ri-close-line align-bottom me-1"></i> <?= $this->__('cancel') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="item-row-template">
    <tr class="item-row">
        <td>
            <input type="hidden" name="item_product_id[]" value="">
            <input type="text" name="item_sku[]" class="form-control form-control-sm item-sku">
        </td>
        <td>
            <input type="text" name="item_name[]" class="form-control form-control-sm item-name" required>
        </td>
        <td>
            <input type="number" name="item_qty[]" class="form-control form-control-sm item-qty text-center" min="1" value="1">
        </td>
        <td>
            <input type="number" name="item_price[]" class="form-control form-control-sm item-price text-end" step="0.01" min="0" value="0">
        </td>
        <td class="item-total text-end fw-semibold">0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-soft-danger btn-sm remove-item">
                <i class="ri-delete-bin-line"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsBody = document.getElementById('items-body');
    const template = document.getElementById('item-row-template');
    const subtotalEl = document.getElementById('order-subtotal');
    const totalEl = document.getElementById('order-total');
    const shippingInput = document.getElementById('shipping-cost');
    const discountInput = document.getElementById('order-discount');
    const productSelect = document.getElementById('product-select');

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const lineTotal = qty * price;
            row.querySelector('.item-total').textContent = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });

        const shipping = parseFloat(shippingInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const total = subtotal + shipping - discount;

        subtotalEl.textContent = subtotal.toFixed(2);
        totalEl.textContent = total.toFixed(2);
    }

    function addItemRow(productId = '', sku = '', name = '', qty = 1, price = 0) {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('tr');

        row.querySelector('[name="item_product_id[]"]').value = productId;
        row.querySelector('.item-sku').value = sku;
        row.querySelector('.item-name').value = name;
        row.querySelector('.item-qty').value = qty;
        row.querySelector('.item-price').value = price;

        itemsBody.appendChild(clone);
        calculateTotals();
    }

    // Add item button
    document.getElementById('add-item-btn').addEventListener('click', function() {
        addItemRow();
    });

    // Remove item
    itemsBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
            const rows = itemsBody.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                calculateTotals();
            }
        }
    });

    // Recalculate on input
    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
            calculateTotals();
        }
    });

    shippingInput.addEventListener('input', calculateTotals);
    discountInput.addEventListener('input', calculateTotals);

    // Product selector
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                addItemRow(opt.value, opt.dataset.sku, opt.dataset.name, 1, 0);
                this.value = '';
            }
        });
    }

    // Initial calculation
    calculateTotals();
});
</script>

<?php $this->endSection(); ?>
