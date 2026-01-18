<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/costing" class="btn btn-secondary">&laquo; Cost Analysis</a>
</div>

<div class="filters" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
    <form method="get" action="/costing/compare" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <label>Period:</label>
        <input type="date" name="from" value="<?= h($dateFrom) ?>" class="form-control" style="width: 150px;">
        <span>to</span>
        <input type="date" name="to" value="<?= h($dateTo) ?>" class="form-control" style="width: 150px;">
        <button type="submit" class="btn btn-secondary">Apply</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h4 style="margin: 0;">Plan vs Actual Comparison</h4>
    </div>
    <div class="card-body">
        <?php if (empty($comparison)): ?>
        <p class="text-muted">No completed production in this period.</p>
        <?php else: ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Variant</th>
                    <th style="text-align: right;">Produced</th>
                    <th style="text-align: right;">Unit Planned</th>
                    <th style="text-align: right;">Unit Actual</th>
                    <th style="text-align: right;">Total Planned</th>
                    <th style="text-align: right;">Material</th>
                    <th style="text-align: right;">Labor</th>
                    <th style="text-align: right;">Total Actual</th>
                    <th style="text-align: right;">Variance</th>
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
                    <td>TOTAL</td>
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
            <h5>Summary</h5>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <div>
                    <strong>Total Produced:</strong> <?= number_format($totals['produced'], 2) ?> units
                </div>
                <div>
                    <strong>Planned Cost:</strong> <?= number_format($totals['planned'], 2) ?>
                </div>
                <div>
                    <strong>Actual Cost:</strong> <?= number_format($totals['actual'], 2) ?>
                </div>
                <div>
                    <strong>Total Variance:</strong>
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
