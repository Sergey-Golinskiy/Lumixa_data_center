<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $job ? $this->__('edit_print_job') : $this->__('create_print_job') ?></h4>
            <div class="page-title-right">
                <a href="/production/print-queue" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> <?= $this->__('back_to_queue') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="<?= $job ? 'ri-edit-line' : 'ri-add-line' ?> align-bottom me-1"></i>
            <?= $job ? $this->__('edit_print_job') : $this->__('create_print_job') ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $job ? "/production/print-queue/{$job['id']}" : '/production/print-queue' ?>">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="order_id" class="form-label"><?= $this->__('production_order_optional') ?></label>
                        <select name="order_id" id="order_id" class="form-select">
                            <option value=""><?= $this->__('standalone_job') ?></option>
                            <?php foreach ($orders as $order): ?>
                            <option value="<?= $order['id'] ?>" <?= ($job['order_id'] ?? '') == $order['id'] ? 'selected' : '' ?>>
                                <?= h($order['order_number']) ?> (<?= h($order['sku']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted"><?= $this->__('link_existing_order') ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="variant_id" class="form-label"><?= $this->__('variant') ?></label>
                        <select name="variant_id" id="variant_id" class="form-select">
                            <option value=""><?= $this->__('select_variant') ?></option>
                            <?php foreach ($variants as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($job['variant_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                                <?= h($v['sku']) ?> - <?= h($v['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label"><?= $this->__('quantity') ?> <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control"
                               value="<?= h($job['quantity'] ?? 1) ?>" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label"><?= $this->__('priority') ?></label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="0" <?= ($job['priority'] ?? 0) == 0 ? 'selected' : '' ?>><?= $this->__('normal') ?></option>
                            <option value="1" <?= ($job['priority'] ?? 0) == 1 ? 'selected' : '' ?>>1 - <?= $this->__('low_priority') ?></option>
                            <option value="2" <?= ($job['priority'] ?? 0) == 2 ? 'selected' : '' ?>>2 - <?= $this->__('medium_priority') ?></option>
                            <option value="3" <?= ($job['priority'] ?? 0) == 3 ? 'selected' : '' ?>>3 - <?= $this->__('high_priority') ?></option>
                        </select>
                        <div class="form-text text-muted"><?= $this->__('higher_priority_first') ?></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="printer" class="form-label"><?= $this->__('printer') ?></label>
                        <select name="printer" id="printer" class="form-select">
                            <option value=""><?= $this->__('select_later') ?></option>
                            <?php foreach ($printers as $p): ?>
                            <?php $printerCode = $p['code'] ?? $p['name']; ?>
                            <option value="<?= h($printerCode) ?>" <?= ($job['printer'] ?? '') === $printerCode ? 'selected' : '' ?>>
                                <?= h($p['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="material" class="form-label"><?= $this->__('material') ?></label>
                        <input type="text" name="material" id="material" class="form-control"
                               value="<?= h($job['material'] ?? '') ?>" placeholder="<?= $this->__('material_placeholder') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="estimated_time" class="form-label"><?= $this->__('estimated_time_minutes') ?></label>
                        <input type="number" name="estimated_time" id="estimated_time" class="form-control"
                               value="<?= h($job['estimated_time_minutes'] ?? '') ?>" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="file_path" class="form-label"><?= $this->__('file_path') ?></label>
                        <input type="text" name="file_path" id="file_path" class="form-control"
                               value="<?= h($job['file_path'] ?? '') ?>" placeholder="<?= $this->__('file_path_placeholder') ?>">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label"><?= $this->__('notes') ?></label>
                <textarea name="notes" id="notes" class="form-control" rows="3"><?= h($job['notes'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-success">
                    <i class="ri-check-line align-bottom me-1"></i> <?= $job ? $this->__('update_print_job') : $this->__('create_print_job') ?>
                </button>
                <a href="/production/print-queue" class="btn btn-soft-secondary">
                    <i class="ri-close-line align-bottom me-1"></i> <?= $this->__('cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
