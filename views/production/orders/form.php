<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $this->__('create_production_order') ?></h4>
            <div class="page-title-right">
                <a href="/production/orders" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('back_to_orders') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-add-line align-bottom me-1"></i> <?= $this->__('create_production_order') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/production/orders">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="mb-3">
                        <label for="variant_id" class="form-label"><?= $this->__('product_variant') ?> <span class="text-danger">*</span></label>
                        <select id="variant_id" name="variant_id" class="form-select" required>
                            <option value=""><?= $this->__('select_variant') ?></option>
                            <?php foreach ($variants as $v): ?>
                            <option value="<?= $v['id'] ?>" data-bom="<?= $v['bom_id'] ?>" data-routing="<?= $v['routing_id'] ?>">
                                <?= $this->e($v['sku']) ?> - <?= $this->e($v['name']) ?>
                                <?= !$v['bom_id'] ? '(' . $this->__('no_bom') . ')' : '' ?>
                                <?= !$v['routing_id'] ? '(' . $this->__('no_routing') . ')' : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted"><?= $this->__('variants_without_bom_routing_note') ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label"><?= $this->__('quantity') ?> <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required min="1" step="1" value="1">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="planned_start" class="form-label"><?= $this->__('planned_start') ?></label>
                                <input type="date" id="planned_start" name="planned_start" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="planned_end" class="form-label"><?= $this->__('planned_end') ?></label>
                                <input type="date" id="planned_end" name="planned_end" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label"><?= $this->__('priority') ?></label>
                        <select id="priority" name="priority" class="form-select">
                            <option value="low"><?= $this->__('low') ?></option>
                            <option value="normal" selected><?= $this->__('normal') ?></option>
                            <option value="high"><?= $this->__('high') ?></option>
                            <option value="urgent"><?= $this->__('urgent') ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label"><?= $this->__('notes') ?></label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-check-line align-bottom me-1"></i> <?= $this->__('create_order') ?>
                        </button>
                        <a href="/production/orders" class="btn btn-soft-secondary">
                            <i class="ri-close-line align-bottom me-1"></i> <?= $this->__('cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
