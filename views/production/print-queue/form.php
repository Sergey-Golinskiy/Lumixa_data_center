<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/print-queue" class="btn btn-secondary">&laquo; <?= $this->__('back_to_queue') ?></a>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;"><?= $job ? $this->__('edit_print_job') : $this->__('create_print_job') ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $job ? "/production/print-queue/{$job['id']}" : '/production/print-queue' ?>">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div>
                    <div class="form-group">
                        <label for="order_id"><?= $this->__('production_order_optional') ?></label>
                        <select name="order_id" id="order_id" class="form-control">
                            <option value=""><?= $this->__('standalone_job') ?></option>
                            <?php foreach ($orders as $order): ?>
                            <option value="<?= $order['id'] ?>" <?= ($job['order_id'] ?? '') == $order['id'] ? 'selected' : '' ?>>
                                <?= h($order['order_number']) ?> (<?= h($order['sku']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted"><?= $this->__('link_existing_order') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="variant_id"><?= $this->__('variant') ?></label>
                        <select name="variant_id" id="variant_id" class="form-control">
                            <option value=""><?= $this->__('select_variant') ?></option>
                            <?php foreach ($variants as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($job['variant_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                                <?= h($v['sku']) ?> - <?= h($v['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity"><?= $this->__('quantity') ?> <span class="required">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control"
                               value="<?= h($job['quantity'] ?? 1) ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="priority"><?= $this->__('priority') ?></label>
                        <select name="priority" id="priority" class="form-control">
                            <option value="0" <?= ($job['priority'] ?? 0) == 0 ? 'selected' : '' ?>><?= $this->__('normal') ?></option>
                            <option value="1" <?= ($job['priority'] ?? 0) == 1 ? 'selected' : '' ?>>1 - <?= $this->__('low_priority') ?></option>
                            <option value="2" <?= ($job['priority'] ?? 0) == 2 ? 'selected' : '' ?>>2 - <?= $this->__('medium_priority') ?></option>
                            <option value="3" <?= ($job['priority'] ?? 0) == 3 ? 'selected' : '' ?>>3 - <?= $this->__('high_priority') ?></option>
                        </select>
                        <small class="text-muted"><?= $this->__('higher_priority_first') ?></small>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="printer"><?= $this->__('printer') ?></label>
                        <select name="printer" id="printer" class="form-control">
                            <option value=""><?= $this->__('select_later') ?></option>
                            <?php foreach ($printers as $p): ?>
                            <?php $printerCode = $p['code'] ?? $p['name']; ?>
                            <option value="<?= h($printerCode) ?>" <?= ($job['printer'] ?? '') === $printerCode ? 'selected' : '' ?>>
                                <?= h($p['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="material"><?= $this->__('material') ?></label>
                        <input type="text" name="material" id="material" class="form-control"
                               value="<?= h($job['material'] ?? '') ?>" placeholder="<?= $this->__('material_placeholder') ?>">
                    </div>

                    <div class="form-group">
                        <label for="estimated_time"><?= $this->__('estimated_time_minutes') ?></label>
                        <input type="number" name="estimated_time" id="estimated_time" class="form-control"
                               value="<?= h($job['estimated_time_minutes'] ?? '') ?>" min="0">
                    </div>

                    <div class="form-group">
                        <label for="file_path"><?= $this->__('file_path') ?></label>
                        <input type="text" name="file_path" id="file_path" class="form-control"
                               value="<?= h($job['file_path'] ?? '') ?>" placeholder="<?= $this->__('file_path_placeholder') ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes"><?= $this->__('notes') ?></label>
                <textarea name="notes" id="notes" class="form-control" rows="3"><?= h($job['notes'] ?? '') ?></textarea>
            </div>

            <div class="form-actions" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary"><?= $job ? $this->__('update_print_job') : $this->__('create_print_job') ?></button>
                <a href="/production/print-queue" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
