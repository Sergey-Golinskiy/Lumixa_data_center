<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/lots" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('lots')]) ?></a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header"><?= $lot ? $this->__('edit_lot') : $this->__('create_new_lot') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $lot ? "/warehouse/lots/{$lot['id']}" : '/warehouse/lots' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="item_id"><?= $this->__('item') ?> *</label>
                <?php if ($lot): ?>
                <input type="text" value="<?= $this->e($lot['sku']) ?> - <?= $this->e($lot['item_name']) ?>" disabled>
                <input type="hidden" name="item_id" value="<?= $lot['item_id'] ?>">
                <?php else: ?>
                <select id="item_id" name="item_id" required>
                    <option value=""><?= $this->__('select_item') ?></option>
                    <?php foreach ($items as $item): ?>
                    <option value="<?= $item['id'] ?>" <?= $this->old('item_id') == $item['id'] ? 'selected' : '' ?>>
                        <?= $this->e($item['sku']) ?> - <?= $this->e($item['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <?php if ($this->hasError('item_id')): ?>
                <span class="error"><?= $this->error('item_id') ?></span>
                <?php endif; ?>
                <?php if (empty($items) && !$lot): ?>
                <p class="text-muted" style="margin-top: 5px; font-size: 13px;">
                    <?= $this->__('no_items_with_lot_tracking') ?>
                    <a href="/warehouse/items/create"><?= $this->__('create_item') ?></a>
                    <?= $this->__('track_lots_first', ['label' => $this->__('track_lots')]) ?>
                </p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="lot_number"><?= $this->__('lot_number') ?> *</label>
                <input type="text" id="lot_number" name="lot_number" required
                       value="<?= $this->e($lot['lot_number'] ?? $this->old('lot_number')) ?>"
                       placeholder="<?= $this->__('lot_number_placeholder') ?>">
                <?php if ($this->hasError('lot_number')): ?>
                <span class="error"><?= $this->error('lot_number') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="manufacture_date"><?= $this->__('manufacture_date') ?></label>
                    <input type="date" id="manufacture_date" name="manufacture_date"
                           value="<?= $this->e($lot['manufacture_date'] ?? $this->old('manufacture_date')) ?>">
                    <?php if ($this->hasError('manufacture_date')): ?>
                    <span class="error"><?= $this->error('manufacture_date') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="expiry_date"><?= $this->__('expiry_date') ?></label>
                    <input type="date" id="expiry_date" name="expiry_date"
                           value="<?= $this->e($lot['expiry_date'] ?? $this->old('expiry_date')) ?>">
                    <?php if ($this->hasError('expiry_date')): ?>
                    <span class="error"><?= $this->error('expiry_date') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="supplier_lot"><?= $this->__('supplier_lot_number') ?></label>
                <input type="text" id="supplier_lot" name="supplier_lot"
                       value="<?= $this->e($lot['supplier_lot'] ?? $this->old('supplier_lot')) ?>"
                       placeholder="<?= $this->__('supplier_lot_placeholder') ?>">
                <?php if ($this->hasError('supplier_lot')): ?>
                <span class="error"><?= $this->error('supplier_lot') ?></span>
                <?php endif; ?>
            </div>

            <?php if ($lot): ?>
            <div class="form-group">
                <label for="status"><?= $this->__('status') ?></label>
                <select id="status" name="status">
                    <option value="active" <?= ($lot['status'] ?? '') === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                    <option value="quarantine" <?= ($lot['status'] ?? '') === 'quarantine' ? 'selected' : '' ?>><?= $this->__('quarantine') ?></option>
                    <option value="blocked" <?= ($lot['status'] ?? '') === 'blocked' ? 'selected' : '' ?>><?= $this->__('blocked') ?></option>
                    <option value="expired" <?= ($lot['status'] ?? '') === 'expired' ? 'selected' : '' ?>><?= $this->__('expired') ?></option>
                </select>
                <?php if ($this->hasError('status')): ?>
                <span class="error"><?= $this->error('status') ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="notes"><?= $this->__('notes') ?></label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="<?= $this->__('notes_placeholder') ?>"><?= $this->e($lot['notes'] ?? $this->old('notes')) ?></textarea>
                <?php if ($this->hasError('notes')): ?>
                <span class="error"><?= $this->error('notes') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $lot ? $this->__('update_lot') : $this->__('create_lot') ?></button>
                <a href="/warehouse/lots" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
</style>

<?php $this->endSection(); ?>
