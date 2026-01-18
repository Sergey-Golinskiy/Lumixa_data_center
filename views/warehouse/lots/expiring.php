<?php $this->section('content'); ?>

<div class="page-header">
    <h1>Expiring Lots</h1>
    <div class="page-actions">
        <a href="/warehouse/lots" class="btn btn-secondary">&laquo; Back to Lots</a>
    </div>
</div>

<!-- Filter -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <label>Show lots expiring within:</label>
                <select name="days" onchange="this.form.submit()">
                    <option value="7" <?= $days == 7 ? 'selected' : '' ?>>7 days</option>
                    <option value="14" <?= $days == 14 ? 'selected' : '' ?>>14 days</option>
                    <option value="30" <?= $days == 30 ? 'selected' : '' ?>>30 days</option>
                    <option value="60" <?= $days == 60 ? 'selected' : '' ?>>60 days</option>
                    <option value="90" <?= $days == 90 ? 'selected' : '' ?>>90 days</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Alert Summary -->
<?php
$expiredCount = 0;
$criticalCount = 0;
$warningCount = 0;
foreach ($lots as $lot) {
    if ($lot['days_until_expiry'] < 0) $expiredCount++;
    elseif ($lot['days_until_expiry'] <= 7) $criticalCount++;
    else $warningCount++;
}
?>

<?php if ($expiredCount > 0 || $criticalCount > 0): ?>
<div class="alert alert-danger" style="margin-bottom: 20px;">
    <strong>Attention Required!</strong>
    <?php if ($expiredCount > 0): ?>
    <?= $expiredCount ?> lot(s) have already expired.
    <?php endif; ?>
    <?php if ($criticalCount > 0): ?>
    <?= $criticalCount ?> lot(s) will expire within 7 days.
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Results -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Lot #</th>
                        <th>Item</th>
                        <th>Expiry Date</th>
                        <th>Days Left</th>
                        <th>Stock Qty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lots)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No lots expiring within <?= $days ?> days
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($lots as $lot): ?>
                    <?php
                    $daysLeft = $lot['days_until_expiry'];
                    if ($daysLeft < 0) {
                        $rowClass = 'row-expired';
                        $statusBadge = '<span class="badge badge-danger">EXPIRED</span>';
                    } elseif ($daysLeft <= 7) {
                        $rowClass = 'row-critical';
                        $statusBadge = '<span class="badge badge-danger">Critical</span>';
                    } else {
                        $rowClass = 'row-warning';
                        $statusBadge = '<span class="badge badge-warning">Warning</span>';
                    }
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= $statusBadge ?></td>
                        <td>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>">
                                <strong><?= $this->e($lot['lot_number']) ?></strong>
                            </a>
                        </td>
                        <td>
                            <a href="/warehouse/items/<?= $lot['item_id'] ?>">
                                <?= $this->e($lot['sku']) ?>
                            </a>
                            <br>
                            <small class="text-muted"><?= $this->e($lot['item_name']) ?></small>
                        </td>
                        <td><?= $this->date($lot['expiry_date'], 'Y-m-d') ?></td>
                        <td>
                            <?php if ($daysLeft < 0): ?>
                            <strong class="text-danger"><?= abs($daysLeft) ?> days ago</strong>
                            <?php else: ?>
                            <strong><?= $daysLeft ?> days</strong>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= number_format($lot['stock_quantity'] ?? 0, 3) ?>
                            <?= $this->e($lot['unit']) ?>
                        </td>
                        <td>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                            <?php if ($this->can('warehouse.lots.edit')): ?>
                            <a href="/warehouse/lots/<?= $lot['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.page-header h1 {
    margin: 0;
}
.filter-form {
    margin: 0;
}
.filter-row {
    display: flex;
    gap: 10px;
    align-items: center;
}
.filter-row label {
    margin: 0;
}
.filter-row select {
    width: auto;
}
.row-expired {
    background-color: rgba(220, 53, 69, 0.1);
}
.row-critical {
    background-color: rgba(220, 53, 69, 0.05);
}
.row-warning {
    background-color: rgba(255, 193, 7, 0.1);
}
.alert {
    padding: 15px 20px;
    border-radius: 6px;
}
.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
    color: var(--danger);
}
</style>

<?php $this->endSection(); ?>
