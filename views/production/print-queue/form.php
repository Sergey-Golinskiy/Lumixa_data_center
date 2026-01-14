<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/production/print-queue" class="btn btn-secondary">&laquo; Back to Queue</a>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;"><?= $job ? 'Edit Print Job' : 'Create Print Job' ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $job ? "/production/print-queue/{$job['id']}" : '/production/print-queue' ?>">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div>
                    <div class="form-group">
                        <label for="order_id">Production Order (optional)</label>
                        <select name="order_id" id="order_id" class="form-control">
                            <option value="">-- Standalone Job --</option>
                            <?php foreach ($orders as $order): ?>
                            <option value="<?= $order['id'] ?>" <?= ($job['order_id'] ?? '') == $order['id'] ? 'selected' : '' ?>>
                                <?= h($order['order_number']) ?> (<?= h($order['sku']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Link to an existing production order</small>
                    </div>

                    <div class="form-group">
                        <label for="variant_id">Variant</label>
                        <select name="variant_id" id="variant_id" class="form-control">
                            <option value="">-- Select Variant --</option>
                            <?php foreach ($variants as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($job['variant_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                                <?= h($v['sku']) ?> - <?= h($v['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity <span class="required">*</span></label>
                        <input type="number" name="quantity" id="quantity" class="form-control"
                               value="<?= h($job['quantity'] ?? 1) ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select name="priority" id="priority" class="form-control">
                            <option value="0" <?= ($job['priority'] ?? 0) == 0 ? 'selected' : '' ?>>Normal</option>
                            <option value="1" <?= ($job['priority'] ?? 0) == 1 ? 'selected' : '' ?>>1 - Low Priority</option>
                            <option value="2" <?= ($job['priority'] ?? 0) == 2 ? 'selected' : '' ?>>2 - Medium Priority</option>
                            <option value="3" <?= ($job['priority'] ?? 0) == 3 ? 'selected' : '' ?>>3 - High Priority</option>
                        </select>
                        <small class="text-muted">Higher priority jobs are shown first</small>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="printer">Printer</label>
                        <select name="printer" id="printer" class="form-control">
                            <option value="">-- Select Later --</option>
                            <?php foreach ($printers as $p): ?>
                            <option value="<?= h($p['code']) ?>" <?= ($job['printer'] ?? '') === $p['code'] ? 'selected' : '' ?>>
                                <?= h($p['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="material">Material</label>
                        <input type="text" name="material" id="material" class="form-control"
                               value="<?= h($job['material'] ?? '') ?>" placeholder="e.g., PLA, ABS, PETG">
                    </div>

                    <div class="form-group">
                        <label for="estimated_time">Estimated Time (minutes)</label>
                        <input type="number" name="estimated_time" id="estimated_time" class="form-control"
                               value="<?= h($job['estimated_time_minutes'] ?? '') ?>" min="0">
                    </div>

                    <div class="form-group">
                        <label for="file_path">File Path</label>
                        <input type="text" name="file_path" id="file_path" class="form-control"
                               value="<?= h($job['file_path'] ?? '') ?>" placeholder="/path/to/model.stl">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" class="form-control" rows="3"><?= h($job['notes'] ?? '') ?></textarea>
            </div>

            <div class="form-actions" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <button type="submit" class="btn btn-primary"><?= $job ? 'Update' : 'Create' ?> Print Job</button>
                <a href="/production/print-queue" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
