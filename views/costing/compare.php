<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/costing" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('costing')]) ?></a>
</div>

<div class="filters" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
    <form method="get" action="/costing/compare" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <label><?= $this->__('period') ?>:</label>
        <input type="date" name="from" value="<?= h($dateFrom) ?>" class="form-control" style="width: 150px;">
        <span><?= $this->__('to') ?></span>
        <input type="date" name="to" value="<?= h($dateTo) ?>" class="form-control" style="width: 150px;">
        <button type="submit" class="btn btn-secondary"><?= $this->__('apply') ?></button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h4 style="margin: 0;"><?= $this->__('plan_vs_actual_comparison') ?></h4>
    </div>
    <div class="card-body">
        <?php if (empty($comparison)): ?>
        <p class="text-muted"><?= $this->__('no_completed_production_period') ?></p>
        <?php else: ?>

        <table class="table">
            <thead>
                <tr>
                    <th><?= $this->__('variant') ?></th>
                    <th style="text-align: right;"><?= $this->__('produced') ?></th>
                    <th style="text-align: right;"><?= $this->__('unit_planned') ?></th>
                    <th style="text-align: right;"><?= $this->__('unit_actual') ?></th>
                    <th style="text-align: right;"><?= $this->__('total_planned') ?></th>
                    <th style="text-align: right;"><?= $this->__('material_cost') ?></th>
                    <th style="text-align: right;"><?= $this->__('labor_cost') ?></th>
                    <th style="text-align: right;"><?= $this->__('total_actual') ?></th>
                    <th style="text-align: right;"><?= $this->__('variance') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comparison as $row): ?>
                <tr>
                    <td>
                        <a href="/costing/variant/<?= $row['id'] ?>"><?= h($row['sku']) ?></a>
                        <small style="display: block; color: #666;"><?= h($row['name']) ?></small>
                    </td>
                    <td style="text-align: right;"><?= number_format($row['total_produced'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($row['unit_planned_cost'] ?? 0, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($row['unit_actual_cost'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($row['total_planned_cost'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($row['total_material_cost'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($row['total_labor_cost'], 2) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format($row['total_actual_cost'], 2) ?></td>
                    <td style="text-align: right;">
                        <?php
                        $variance = $row['variance'];
                        $class = $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-success' : '');
                        $sign = $variance > 0 ? '+' : '';
                        ?>
                        <span class="<?= $class ?>">
                            <?= $sign ?><?= number_format($variance, 2) ?>
                            <small>(<?= $sign ?><?= number_format($row['variance_percent'], 1) ?>%)</small>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot style="font-weight: bold; background: #f8f9fa;">
                <tr>
                    <td><?= $this->__('total') ?></td>
                    <td style="text-align: right;"><?= number_format($totals['produced'], 2) ?></td>
                    <td colspan="2"></td>
                    <td style="text-align: right;"><?= number_format($totals['planned'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($totals['material'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($totals['labor'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($totals['actual'], 2) ?></td>
                    <td style="text-align: right;">
                        <?php
                        $variance = $totals['variance'];
                        $class = $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-success' : '');
                        $sign = $variance > 0 ? '+' : '';
                        ?>
                        <span class="<?= $class ?>">
                            <?= $sign ?><?= number_format($variance, 2) ?>
                            <small>(<?= $sign ?><?= number_format($totals['variance_percent'], 1) ?>%)</small>
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="summary" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <h5><?= $this->__('summary') ?></h5>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <div>
                    <strong><?= $this->__('total_produced') ?>:</strong> <?= number_format($totals['produced'], 2) ?> <?= $this->__('units') ?>
                </div>
                <div>
                    <strong><?= $this->__('planned_cost') ?>:</strong> <?= number_format($totals['planned'], 2) ?>
                </div>
                <div>
                    <strong><?= $this->__('actual_cost') ?>:</strong> <?= number_format($totals['actual'], 2) ?>
                </div>
                <div>
                    <strong><?= $this->__('total_variance') ?>:</strong>
                    <span class="<?= $totals['variance'] > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= $totals['variance'] > 0 ? '+' : '' ?><?= number_format($totals['variance'], 2) ?>
                        (<?= $totals['variance'] > 0 ? '+' : '' ?><?= number_format($totals['variance_percent'], 1) ?>%)
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.text-danger { color: #dc3545; }
.text-success { color: #28a745; }
</style>

<?php $this->endSection(); ?>
