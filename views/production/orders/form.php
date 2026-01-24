<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/orders" class="btn btn-secondary">&laquo; <?= $this->__('back_to_orders') ?></a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header"><?= $this->__('create_production_order') ?></div>
    <div class="card-body">
        <form method="POST" action="/production/orders">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="variant_id"><?= $this->__('product_variant') ?> *</label>
                <select id="variant_id" name="variant_id" required>
                    <option value=""><?= $this->__('select_variant') ?></option>
                    <?php foreach ($variants as $v): ?>
                    <option value="<?= $v['id'] ?>" data-bom="<?= $v['bom_id'] ?>" data-routing="<?= $v['routing_id'] ?>">
                        <?= $this->e($v['sku']) ?> - <?= $this->e($v['name']) ?>
                        <?= !$v['bom_id'] ? '(' . $this->__('no_bom') . ')' : '' ?>
                        <?= !$v['routing_id'] ? '(' . $this->__('no_routing') . ')' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted"><?= $this->__('variants_without_bom_routing_note') ?></small>
            </div>

            <div class="form-group">
                <label for="quantity"><?= $this->__('quantity') ?> *</label>
                <input type="number" id="quantity" name="quantity" required min="1" step="1" value="1">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="planned_start"><?= $this->__('planned_start') ?></label>
                    <input type="date" id="planned_start" name="planned_start" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="planned_end"><?= $this->__('planned_end') ?></label>
                    <input type="date" id="planned_end" name="planned_end">
                </div>
            </div>

            <div class="form-group">
                <label for="priority"><?= $this->__('priority') ?></label>
                <select id="priority" name="priority">
                    <option value="low"><?= $this->__('low') ?></option>
                    <option value="normal" selected><?= $this->__('normal') ?></option>
                    <option value="high"><?= $this->__('high') ?></option>
                    <option value="urgent"><?= $this->__('urgent') ?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes"><?= $this->__('notes') ?></label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $this->__('create_order') ?></button>
                <a href="/production/orders" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
</style>

<?php $this->endSection(); ?>
