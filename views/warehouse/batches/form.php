<?php
/** @var array $item */
/** @var array|null $batch */
/** @var array $suppliers */
/** @var string $csrfToken */
$isEdit = $batch !== null;
?>

<?php $this->section('content'); ?>

<!-- Back button -->
<div class="mb-3">
    <a href="/warehouse/batches?item_id=<?= $item['id'] ?>" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_batches') ?>
    </a>
</div>

<!-- Item Info -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                    <i class="ri-box-3-line"></i>
                </span>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="mb-0"><?= $this->e($item['sku']) ?></h5>
                <p class="text-muted mb-0"><?= $this->e($item['name']) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-stack-line me-2"></i>
                    <?= $isEdit ? $this->__('edit_batch') : $this->__('create_new_batch') ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $isEdit ? "/warehouse/batches/{$batch['id']}" : '/warehouse/batches' ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">

                    <?php if ($isEdit): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="batch_code" class="form-label"><?= $this->__('batch_code') ?></label>
                            <input type="text"
                                   class="form-control"
                                   id="batch_code"
                                   name="batch_code"
                                   value="<?= $this->e(old('batch_code', $batch['batch_code'] ?? '')) ?>"
                                   placeholder="<?= $this->__('leave_empty_auto_generate') ?>">
                            <div class="form-text text-muted">
                                <?= $this->__('unique_batch_id_auto_generated') ?>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="received_date" class="form-label"><?= $this->__('received_date') ?> <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   id="received_date"
                                   name="received_date"
                                   value="<?= $this->e(old('received_date', $batch['received_date'] ?? date('Y-m-d'))) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label"><?= $this->__('quantity') ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control"
                                       id="quantity"
                                       name="quantity"
                                       value="<?= $this->e(old('quantity', $batch['qty_received'] ?? '')) ?>"
                                       step="0.01"
                                       min="0.01"
                                       required>
                                <span class="input-group-text"><?= $this->__('unit_' . $item['unit']) ?></span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="unit_cost" class="form-label"><?= $this->__('unit_cost') ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number"
                                       class="form-control"
                                       id="unit_cost"
                                       name="unit_cost"
                                       value="<?= $this->e(old('unit_cost', $batch['unit_cost'] ?? '')) ?>"
                                       step="0.0001"
                                       min="0"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label"><?= $this->__('supplier') ?></label>
                            <select class="form-select" id="supplier_id" name="supplier_id">
                                <option value="">-- <?= $this->__('not_specified') ?> --</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>"
                                    <?= (old('supplier_id', $batch['supplier_id'] ?? '') == $supplier['id']) ? 'selected' : '' ?>>
                                    <?= $this->e($supplier['name']) ?> (<?= $this->e($supplier['code']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label"><?= $this->__('expiry_date') ?></label>
                            <input type="date"
                                   class="form-control"
                                   id="expiry_date"
                                   name="expiry_date"
                                   value="<?= $this->e(old('expiry_date', $batch['expiry_date'] ?? '')) ?>">
                            <div class="form-text text-muted">
                                <?= $this->__('optional_expiry_tracking') ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label"><?= $this->__('notes') ?></label>
                        <textarea class="form-control"
                                  id="notes"
                                  name="notes"
                                  rows="3"><?= $this->e(old('notes', $batch['notes'] ?? '')) ?></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong><?= $this->__('note') ?>:</strong>
                        <?= $this->__('batch_creation_info') ?>
                        <strong><?= $this->e($item['costing_method']) ?></strong>.
                        <?= $this->__('batch_costing_method_info') ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $isEdit ? $this->__('update_batch') : $this->__('create_batch') ?>
                        </button>
                        <a href="/warehouse/batches?item_id=<?= $item['id'] ?>" class="btn btn-soft-secondary">
                            <?= $this->__('cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Total Value Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-calculator-line me-2"></i><?= $this->__('calculated_value') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('quantity') ?></th>
                                <td id="calc-qty">0</td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('unit_cost') ?></th>
                                <td id="calc-cost">$0.00</td>
                            </tr>
                            <tr class="border-top">
                                <th class="ps-0 text-muted"><?= $this->__('total_value') ?></th>
                                <td class="fw-semibold text-primary" id="calc-total">$0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('quantity');
    const costInput = document.getElementById('unit_cost');

    function updateTotalValue() {
        const qty = parseFloat(qtyInput.value) || 0;
        const cost = parseFloat(costInput.value) || 0;
        const total = qty * cost;

        document.getElementById('calc-qty').textContent = qty.toFixed(2);
        document.getElementById('calc-cost').textContent = '$' + cost.toFixed(4);
        document.getElementById('calc-total').textContent = '$' + total.toFixed(2);
    }

    if (qtyInput && costInput) {
        qtyInput.addEventListener('input', updateTotalValue);
        costInput.addEventListener('input', updateTotalValue);
        updateTotalValue();
    }
});
</script>

<?php $this->endSection(); ?>
