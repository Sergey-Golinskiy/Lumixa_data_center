<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex gap-2">
    <a href="/warehouse/stock" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_stock') ?>
    </a>
    <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-soft-primary">
        <i class="ri-eye-line me-1"></i> <?= $this->__('view_item') ?>
    </a>
</div>

<!-- Item Header -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h4 class="mb-1"><?= $this->e($item['sku']) ?></h4>
                <p class="text-muted mb-0"><?= $this->e($item['name']) ?></p>
            </div>
            <div class="col-lg-6">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="py-2">
                            <h5 class="mb-1"><?= number_format($totals['total_quantity'] ?? 0, 3) ?></h5>
                            <p class="text-muted mb-0 small"><?= $this->__('total') ?> <?= $this->__('unit_' . $item['unit']) ?></p>
                        </div>
                    </div>
                    <div class="col-3 border-start">
                        <div class="py-2">
                            <h5 class="mb-1 text-warning"><?= number_format($totals['total_reserved'] ?? 0, 3) ?></h5>
                            <p class="text-muted mb-0 small"><?= $this->__('reserved') ?></p>
                        </div>
                    </div>
                    <div class="col-3 border-start">
                        <div class="py-2">
                            <?php $available = ($totals['total_quantity'] ?? 0) - ($totals['total_reserved'] ?? 0); ?>
                            <h5 class="mb-1 <?= $available < 0 ? 'text-danger' : 'text-success' ?>"><?= number_format($available, 3) ?></h5>
                            <p class="text-muted mb-0 small"><?= $this->__('available') ?></p>
                        </div>
                    </div>
                    <div class="col-3 border-start">
                        <div class="py-2">
                            <h5 class="mb-1 text-primary"><?= number_format($totals['total_value'] ?? 0, 2) ?></h5>
                            <p class="text-muted mb-0 small"><?= $this->__('total_value') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Movements -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i><?= $this->__('recent_movements') ?></h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($movements)): ?>
        <div class="text-center py-5 text-muted">
            <i class="ri-inbox-line fs-1 d-block mb-2"></i>
            <?= $this->__('no_movements') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('document') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('direction') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('unit_cost') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td><?= $this->datetime($movement['created_at']) ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $movement['document_id'] ?>" class="text-primary">
                                <?= $this->e($movement['document_number']) ?>
                            </a>
                        </td>
                        <td><?= ucfirst($movement['document_type']) ?></td>
                        <td>
                            <?php if ($movement['movement_type'] === 'in'): ?>
                            <span class="badge bg-success-subtle text-success"><?= $this->__('in') ?></span>
                            <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger"><?= $this->__('out') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($movement['movement_type'] === 'in'): ?>
                            <span class="text-success">+<?= number_format($movement['quantity'], 3) ?></span>
                            <?php else: ?>
                            <span class="text-danger">-<?= number_format($movement['quantity'], 3) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= number_format($movement['unit_cost'], 4) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
