<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/costing" class="btn btn-secondary">&laquo; Cost Analysis</a>
</div>

<div class="filters" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
    <form method="get" action="/costing/plan" style="display: flex; gap: 10px; align-items: center;">
        <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search SKU or name..."
               class="form-control" style="width: 250px;">
        <button type="submit" class="btn btn-secondary">Search</button>
        <?php if ($search): ?>
        <a href="/costing/plan" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h4 style="margin: 0;">Planned Costs (from BOM & Routing)</h4>
    </div>
    <div class="card-body">
        <?php if (empty($variants)): ?>
        <p class="text-muted">No variants found.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>BOM</th>
                    <th>Routing</th>
                    <th style="text-align: right;">Material</th>
                    <th style="text-align: right;">Labor</th>
                    <th style="text-align: right;">Overhead</th>
                    <th style="text-align: right;">Total Cost</th>
                    <th>Calculated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variants as $v): ?>
                <tr>
                    <td>
                        <a href="/costing/variant/<?= $v['id'] ?>"><?= h($v['sku']) ?></a>
                    </td>
                    <td><?= h($v['name']) ?></td>
                    <td>
                        <?php if ($v['bom_id']): ?>
                        <a href="/catalog/bom/<?= $v['bom_id'] ?>"><?= h($v['bom_name']) ?></a>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($v['routing_id']): ?>
                        <a href="/catalog/routing/<?= $v['routing_id'] ?>"><?= h($v['routing_name']) ?></a>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;"><?= number_format($v['material_cost'] ?? 0, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($v['labor_cost'] ?? 0, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($v['overhead_cost'] ?? 0, 2) ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php if ($v['planned_cost']): ?>
                        <?= number_format($v['planned_cost'], 2) ?>
                        <?php else: ?>
                        <span class="text-muted">Not calculated</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($v['calculated_at']): ?>
                        <?= date('d.m.Y', strtotime($v['calculated_at'])) ?>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top: 20px; display: flex; gap: 5px; justify-content: center;">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="btn btn-outline">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
               class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="btn btn-outline">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
