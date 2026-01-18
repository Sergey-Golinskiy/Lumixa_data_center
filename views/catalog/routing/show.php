<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/routing" class="btn btn-secondary">&laquo; Back to Routings</a>
    <a href="/catalog/variants/<?= $routing['variant_id'] ?>" class="btn btn-outline">View Variant</a>
    <?php if ($routing['status'] === 'draft' && $this->can('catalog.routing.edit')): ?>
    <a href="/catalog/routing/<?= $routing['id'] ?>/edit" class="btn btn-outline">Edit</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Routing Info -->
    <div class="card">
        <div class="card-header">Routing Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Variant</span>
                <span class="detail-value">
                    <a href="/catalog/variants/<?= $routing['variant_id'] ?>"><?= $this->e($routing['variant_sku']) ?></a>
                    <br><small class="text-muted"><?= $this->e($routing['variant_name']) ?></small>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Version</span>
                <span class="detail-value"><strong><?= $this->e($routing['version']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= $this->e($routing['name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($routing['status']) {
                        'draft' => 'warning',
                        'active' => 'success',
                        'archived' => 'secondary',
                        default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($routing['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Effective Date</span>
                <span class="detail-value"><?= $routing['effective_date'] ? $this->date($routing['effective_date'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created By</span>
                <span class="detail-value"><?= $this->e($routing['created_by_name'] ?? '-') ?></span>
            </div>
            <?php if ($routing['notes']): ?>
            <div class="detail-row">
                <span class="detail-label">Notes</span>
                <span class="detail-value"><?= nl2br($this->e($routing['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="card">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <?php if ($routing['status'] === 'draft'): ?>
            <p>This routing is in draft status.</p>
            <?php if ($this->can('catalog.routing.activate')): ?>
            <form method="POST" action="/catalog/routing/<?= $routing['id'] ?>/activate" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('Activate this routing?')">Activate</button>
            </form>
            <?php endif; ?>
            <?php elseif ($routing['status'] === 'active'): ?>
            <p>This routing is currently active.</p>
            <?php if ($this->can('catalog.routing.edit')): ?>
            <form method="POST" action="/catalog/routing/<?= $routing['id'] ?>/archive">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-warning" onclick="return confirm('Archive this routing?')">Archive</button>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <p>This routing is archived.</p>
            <?php if ($this->can('catalog.routing.activate')): ?>
            <form method="POST" action="/catalog/routing/<?= $routing['id'] ?>/activate">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('Re-activate this routing?')">Re-Activate</button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Operations -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Operations</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Op #</th>
                        <th>Operation</th>
                        <th>Work Center</th>
                        <th class="text-right">Setup (min)</th>
                        <th class="text-right">Run (min)</th>
                        <th class="text-right">Labor Cost</th>
                        <th class="text-right">Overhead</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalSetup = 0; $totalRun = 0; $totalLabor = 0; $totalOverhead = 0;
                    foreach ($operations as $op):
                        $totalSetup += $op['setup_time_minutes'];
                        $totalRun += $op['run_time_minutes'];
                        $totalLabor += $op['labor_cost'];
                        $totalOverhead += $op['overhead_cost'];
                    ?>
                    <tr>
                        <td><?= $op['operation_number'] ?></td>
                        <td>
                            <strong><?= $this->e($op['name']) ?></strong>
                            <?php if ($op['instructions']): ?>
                            <br><small class="text-muted"><?= $this->e(substr($op['instructions'], 0, 100)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= $this->e($op['work_center'] ?? '-') ?></td>
                        <td class="text-right"><?= number_format($op['setup_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['run_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'], 2) ?></td>
                        <td class="text-right"><?= number_format($op['overhead_cost'], 2) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'] + $op['overhead_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Totals:</strong></td>
                        <td class="text-right"><strong><?= number_format($totalSetup) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalRun) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalLabor, 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalOverhead, 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalLabor + $totalOverhead, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 120px; color: var(--text-muted); font-size: 13px; }
.detail-value { flex: 1; }
.text-right { text-align: right; }
</style>

<?php $this->endSection(); ?>
