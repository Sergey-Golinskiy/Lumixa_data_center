<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/orders" class="btn btn-secondary">&laquo; Back to Orders</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">Create Production Order</div>
    <div class="card-body">
        <form method="POST" action="/production/orders">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="variant_id">Product Variant *</label>
                <select id="variant_id" name="variant_id" required>
                    <option value="">Select Variant</option>
                    <?php foreach ($variants as $v): ?>
                    <option value="<?= $v['id'] ?>" data-bom="<?= $v['bom_id'] ?>" data-routing="<?= $v['routing_id'] ?>">
                        <?= $this->e($v['sku']) ?> - <?= $this->e($v['name']) ?>
                        <?= !$v['bom_id'] ? '(No BOM)' : '' ?>
                        <?= !$v['routing_id'] ? '(No Routing)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Variants without BOM/Routing will create orders without materials/tasks</small>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" id="quantity" name="quantity" required min="1" step="1" value="1">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="planned_start">Planned Start</label>
                    <input type="date" id="planned_start" name="planned_start" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="planned_end">Planned End</label>
                    <input type="date" id="planned_end" name="planned_end">
                </div>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="low">Low</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Order</button>
                <a href="/production/orders" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
</style>

<?php $this->endSection(); ?>
