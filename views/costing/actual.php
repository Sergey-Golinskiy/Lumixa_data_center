<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/costing" class="btn btn-secondary">&laquo; Cost Analysis</a>
</div>

<div class="filters" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
    <form method="get" action="/costing/actual" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <label>From:</label>
        <input type="date" name="from" value="<?= h($dateFrom) ?>" class="form-control" style="width: 150px;">
        <label>To:</label>
        <input type="date" name="to" value="<?= h($dateTo) ?>" class="form-control" style="width: 150px;">
        <button type="submit" class="btn btn-secondary">Apply</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h4 style="margin: 0;">Actual Production Costs</h4>
    </div>
    <div class="card-body">
        <?php if (empty($orders)): ?>
        <p class="text-muted">No completed orders in this period.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Variant</th>
                    <th style="text-align: right;">Qty</th>
                    <th style="text-align: right;">Material</th>
                    <th style="text-align: right;">Labor</th>
                    <th style="text-align: right;">Actual Total</th>
                    <th style="text-align: right;">Planned</th>
                    <th style="text-align: right;">Variance</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <a href="/production/orders/<?= $order['id'] ?>"><?= h($order['order_number']) ?></a>
                    </td>
                    <td>
                        <a href="/costing/variant/<?= $order['sku'] ?>"><?= h($order['sku']) ?></a>
                        <small style="display: block; color: #666;"><?= h($order['variant_name']) ?></small>
                    </td>
                    <td style="text-align: right;"><?= number_format($order['completed_quantity'], 2) ?></td>
                    <td style="text-align: right;">
                        <?= number_format($order['actual_material_cost'], 2) ?>
                        <small style="display: block; color: #666;"><?= (int)$order['material_count'] ?> items</small>
                    </td>
                    <td style="text-align: right;">
                        <?= number_format($order['actual_labor_cost'], 2) ?>
                        <small style="display: block; color: #666;"><?= (int)$order['labor_minutes'] ?> min</small>
                    </td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format($order['actual_total'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($order['planned_total'], 2) ?></td>
                    <td style="text-align: right;">
                        <?php
                        $variance = $order['variance'];
                        $class = $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-success' : '');
                        $sign = $variance > 0 ? '+' : '';
                        ?>
                        <span class="<?= $class ?>"><?= $sign ?><?= number_format($variance, 2) ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($order['completed_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top: 20px; display: flex; gap: 5px; justify-content: center;">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&from=<?= $dateFrom ?>&to=<?= $dateTo ?>" class="btn btn-outline">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&from=<?= $dateFrom ?>&to=<?= $dateTo ?>"
               class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&from=<?= $dateFrom ?>&to=<?= $dateTo ?>" class="btn btn-outline">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.text-danger { color: #dc3545; }
.text-success { color: #28a745; }
</style>

<?php $this->endSection(); ?>
