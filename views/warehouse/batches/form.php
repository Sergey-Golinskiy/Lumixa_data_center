<?php
/** @var array $item */
/** @var array|null $batch */
/** @var array $suppliers */
/** @var string $csrfToken */
$isEdit = $batch !== null;
?>

<div class="page-header">
    <div>
        <h1><?= h($title) ?></h1>
        <p class="text-muted">SKU: <?= h($item['sku']) ?> | <?= h($item['name']) ?></p>
    </div>
    <div>
        <a href="/warehouse/batches?item_id=<?= $item['id'] ?>" class="btn btn-secondary">
            <?= __('Back to Batches') ?>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? "/warehouse/batches/{$batch['id']}" : '/warehouse/batches' ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">

            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="batch_code"><?= __('Batch Code') ?></label>
                        <input type="text"
                               class="form-control"
                               id="batch_code"
                               name="batch_code"
                               value="<?= h(old('batch_code', $batch['batch_code'] ?? '')) ?>"
                               placeholder="<?= __('Leave empty for auto-generation') ?>">
                        <small class="form-text text-muted">
                            <?= __('Unique identifier for this batch. Auto-generated if left empty.') ?>
                        </small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="received_date"><?= __('Received Date') ?> *</label>
                        <input type="date"
                               class="form-control"
                               id="received_date"
                               name="received_date"
                               value="<?= h(old('received_date', $batch['received_date'] ?? date('Y-m-d'))) ?>"
                               required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity"><?= __('Quantity') ?> *</label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control"
                                   id="quantity"
                                   name="quantity"
                                   value="<?= h(old('quantity', $batch['qty_received'] ?? '')) ?>"
                                   step="0.01"
                                   min="0.01"
                                   required>
                            <div class="input-group-append">
                                <span class="input-group-text"><?= h($item['unit']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="unit_cost"><?= __('Unit Cost') ?> *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number"
                                   class="form-control"
                                   id="unit_cost"
                                   name="unit_cost"
                                   value="<?= h(old('unit_cost', $batch['unit_cost'] ?? '')) ?>"
                                   step="0.0001"
                                   min="0"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="supplier_id"><?= __('Supplier') ?></label>
                        <select class="form-control" id="supplier_id" name="supplier_id">
                            <option value=""><?= __('-- Not specified --') ?></option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>"
                                    <?= (old('supplier_id', $batch['supplier_id'] ?? '') == $supplier['id']) ? 'selected' : '' ?>>
                                    <?= h($supplier['name']) ?> (<?= h($supplier['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expiry_date"><?= __('Expiry Date') ?></label>
                        <input type="date"
                               class="form-control"
                               id="expiry_date"
                               name="expiry_date"
                               value="<?= h(old('expiry_date', $batch['expiry_date'] ?? '')) ?>">
                        <small class="form-text text-muted">
                            <?= __('Optional. Used for tracking and expiry warnings.') ?>
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes"><?= __('Notes') ?></label>
                <textarea class="form-control"
                          id="notes"
                          name="notes"
                          rows="3"><?= h(old('notes', $batch['notes'] ?? '')) ?></textarea>
            </div>

            <div class="alert alert-info">
                <strong><?= __('Note') ?>:</strong>
                <?= __('Creating a batch manually will add inventory to this item. The costing method for this item is') ?>
                <strong><?= h($item['costing_method']) ?></strong>.
                <?= __('This batch will be used according to that method when issuing stock.') ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? __('Update Batch') : __('Create Batch') ?>
                </button>
                <a href="/warehouse/batches?item_id=<?= $item['id'] ?>" class="btn btn-secondary">
                    <?= __('Cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate total value on quantity/cost change
    const qtyInput = document.getElementById('quantity');
    const costInput = document.getElementById('unit_cost');

    function updateTotalValue() {
        const qty = parseFloat(qtyInput.value) || 0;
        const cost = parseFloat(costInput.value) || 0;
        const total = qty * cost;

        // You could display this total somewhere if needed
        console.log('Total value:', total.toFixed(2));
    }

    if (qtyInput && costInput) {
        qtyInput.addEventListener('input', updateTotalValue);
        costInput.addEventListener('input', updateTotalValue);
        updateTotalValue();
    }
});
</script>
