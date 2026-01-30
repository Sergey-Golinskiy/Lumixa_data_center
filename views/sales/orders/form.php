<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/sales/orders" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<form method="POST" action="<?= $order ? "/sales/orders/{$order['id']}" : '/sales/orders' ?>" id="order-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="order-form-grid">
        <!-- Left Column: Order Items -->
        <div class="order-items-section">
            <div class="card">
                <div class="card-header">
                    <?= $this->__('order_items') ?>
                    <button type="button" class="btn btn-sm btn-primary" id="add-item-btn">+ <?= $this->__('add_item') ?></button>
                </div>
                <div class="card-body">
                    <?php if ($this->hasError('items')): ?>
                        <div class="alert alert-danger"><?= $this->error('items') ?></div>
                    <?php endif; ?>

                    <table class="data-table items-table" id="items-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;"><?= $this->__('sku') ?></th>
                                <th><?= $this->__('name') ?></th>
                                <th style="width: 80px;"><?= $this->__('qty') ?></th>
                                <th style="width: 120px;"><?= $this->__('price') ?></th>
                                <th style="width: 120px;"><?= $this->__('total') ?></th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $i => $item): ?>
                                <tr class="item-row">
                                    <td>
                                        <input type="hidden" name="item_product_id[]" value="<?= $item['product_id'] ?? '' ?>">
                                        <input type="text" name="item_sku[]" class="item-sku" value="<?= $this->e($item['sku'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="item_name[]" class="item-name" required value="<?= $this->e($item['name']) ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="item_qty[]" class="item-qty" min="1" value="<?= (int)$item['quantity'] ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="item_price[]" class="item-price" step="0.01" min="0" value="<?= number_format((float)$item['unit_price'], 2, '.', '') ?>">
                                    </td>
                                    <td class="item-total"><?= number_format((float)$item['total'], 2) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item">&times;</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="item-row">
                                    <td>
                                        <input type="hidden" name="item_product_id[]" value="">
                                        <input type="text" name="item_sku[]" class="item-sku">
                                    </td>
                                    <td>
                                        <input type="text" name="item_name[]" class="item-name" required>
                                    </td>
                                    <td>
                                        <input type="number" name="item_qty[]" class="item-qty" min="1" value="1">
                                    </td>
                                    <td>
                                        <input type="number" name="item_price[]" class="item-price" step="0.01" min="0" value="0">
                                    </td>
                                    <td class="item-total">0.00</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-item">&times;</button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong><?= $this->__('subtotal') ?>:</strong></td>
                                <td id="order-subtotal"><?= number_format($order['subtotal'] ?? 0, 2) ?></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><?= $this->__('shipping') ?>:</td>
                                <td>
                                    <input type="number" name="shipping_cost" id="shipping-cost" step="0.01" min="0"
                                           value="<?= number_format($order['shipping_cost'] ?? 0, 2, '.', '') ?>" style="width: 100px;">
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><?= $this->__('discount') ?>:</td>
                                <td>
                                    <input type="number" name="discount" id="order-discount" step="0.01" min="0"
                                           value="<?= number_format($order['discount'] ?? 0, 2, '.', '') ?>" style="width: 100px;">
                                </td>
                                <td></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" class="text-right"><strong><?= $this->__('total') ?>:</strong></td>
                                <td><strong id="order-total"><?= number_format($order['total'] ?? 0, 2) ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if (!empty($products)): ?>
                    <div class="product-selector" style="margin-top: 15px;">
                        <label><?= $this->__('add_from_catalog') ?>:</label>
                        <select id="product-select">
                            <option value=""><?= $this->__('select_product') ?></option>
                            <?php foreach ($products as $prod): ?>
                                <option value="<?= $prod['id'] ?>" data-sku="<?= $this->e($prod['sku']) ?>" data-name="<?= $this->e($prod['name']) ?>">
                                    <?= $this->e($prod['sku']) ?> - <?= $this->e($prod['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header"><?= $this->__('notes') ?></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="notes"><?= $this->__('customer_notes') ?></label>
                        <textarea id="notes" name="notes" rows="2"><?= $this->e($order['notes'] ?? $this->old('notes')) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="internal_notes"><?= $this->__('internal_notes') ?></label>
                        <textarea id="internal_notes" name="internal_notes" rows="2"><?= $this->e($order['internal_notes'] ?? $this->old('internal_notes')) ?></textarea>
                        <small class="form-help"><?= $this->__('internal_notes_help') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Order Details -->
        <div class="order-details-section">
            <!-- Customer Info -->
            <div class="card">
                <div class="card-header"><?= $this->__('customer_info') ?></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="customer_name"><?= $this->__('customer_name') ?></label>
                        <input type="text" id="customer_name" name="customer_name"
                               value="<?= $this->e($order['customer_name'] ?? $this->old('customer_name')) ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_email"><?= $this->__('email') ?></label>
                            <input type="email" id="customer_email" name="customer_email"
                                   value="<?= $this->e($order['customer_email'] ?? $this->old('customer_email')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone"><?= $this->__('phone') ?></label>
                            <input type="tel" id="customer_phone" name="customer_phone"
                                   value="<?= $this->e($order['customer_phone'] ?? $this->old('customer_phone')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping -->
            <div class="card" style="margin-top: 15px;">
                <div class="card-header"><?= $this->__('shipping') ?></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="shipping_address"><?= $this->__('address') ?></label>
                        <textarea id="shipping_address" name="shipping_address" rows="2"><?= $this->e($order['shipping_address'] ?? $this->old('shipping_address')) ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_city"><?= $this->__('city') ?></label>
                            <input type="text" id="shipping_city" name="shipping_city"
                                   value="<?= $this->e($order['shipping_city'] ?? $this->old('shipping_city')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="shipping_postal_code"><?= $this->__('postal_code') ?></label>
                            <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                                   value="<?= $this->e($order['shipping_postal_code'] ?? $this->old('shipping_postal_code')) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="shipping_method"><?= $this->__('shipping_method') ?></label>
                        <input type="text" id="shipping_method" name="shipping_method"
                               placeholder="<?= $this->__('nova_poshta') ?>, <?= $this->__('ukrposhta') ?>..."
                               value="<?= $this->e($order['shipping_method'] ?? $this->old('shipping_method')) ?>">
                    </div>
                    <?php if ($order): ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tracking_number"><?= $this->__('tracking_number') ?></label>
                            <input type="text" id="tracking_number" name="tracking_number"
                                   value="<?= $this->e($order['tracking_number'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="tracking_url"><?= $this->__('tracking_url') ?></label>
                            <input type="url" id="tracking_url" name="tracking_url"
                                   value="<?= $this->e($order['tracking_url'] ?? '') ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Status -->
            <div class="card" style="margin-top: 15px;">
                <div class="card-header"><?= $this->__('order_status') ?></div>
                <div class="card-body">
                    <?php if (!$order): ?>
                    <div class="form-group">
                        <label for="source"><?= $this->__('source') ?></label>
                        <select id="source" name="source">
                            <option value="manual"><?= $this->__('source_manual') ?></option>
                            <option value="instagram"><?= $this->__('source_instagram') ?></option>
                            <option value="offline"><?= $this->__('source_offline') ?></option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status"><?= $this->__('status') ?></label>
                            <select id="status" name="status">
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
                        <div class="form-group">
                            <label for="payment_status"><?= $this->__('payment_status') ?></label>
                            <select id="payment_status" name="payment_status">
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

                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment_method"><?= $this->__('payment_method') ?></label>
                            <input type="text" id="payment_method" name="payment_method"
                                   placeholder="<?= $this->__('cash') ?>, <?= $this->__('card') ?>..."
                                   value="<?= $this->e($order['payment_method'] ?? $this->old('payment_method')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="currency"><?= $this->__('currency') ?></label>
                            <select id="currency" name="currency">
                                <option value="UAH" <?= ($order['currency'] ?? 'UAH') === 'UAH' ? 'selected' : '' ?>>UAH</option>
                                <option value="USD" <?= ($order['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                                <option value="EUR" <?= ($order['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                            </select>
                        </div>
                    </div>

                    <?php if (!$order): ?>
                    <div class="form-group">
                        <label for="ordered_at"><?= $this->__('order_date') ?></label>
                        <input type="datetime-local" id="ordered_at" name="ordered_at"
                               value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $order ? $this->__('save_changes') : $this->__('create_order') ?>
                </button>
                <a href="/sales/orders" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </div>
    </div>
</form>

<template id="item-row-template">
    <tr class="item-row">
        <td>
            <input type="hidden" name="item_product_id[]" value="">
            <input type="text" name="item_sku[]" class="item-sku">
        </td>
        <td>
            <input type="text" name="item_name[]" class="item-name" required>
        </td>
        <td>
            <input type="number" name="item_qty[]" class="item-qty" min="1" value="1">
        </td>
        <td>
            <input type="number" name="item_price[]" class="item-price" step="0.01" min="0" value="0">
        </td>
        <td class="item-total">0.00</td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item">&times;</button>
        </td>
    </tr>
</template>

<style>
.order-form-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 20px;
}

@media (max-width: 1200px) {
    .order-form-grid {
        grid-template-columns: 1fr;
    }
}

.items-table input[type="text"],
.items-table input[type="number"] {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
}

.items-table .item-total {
    text-align: right;
    font-weight: 500;
}

.items-table tfoot td {
    padding: 8px;
}

.total-row td {
    background: var(--bg-secondary);
    font-size: 1.1em;
}

.form-help {
    display: block;
    margin-top: 4px;
    color: var(--text-muted);
    font-size: 0.85em;
}

.product-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}

.product-selector select {
    flex: 1;
}
</style>

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
        if (e.target.classList.contains('remove-item')) {
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
