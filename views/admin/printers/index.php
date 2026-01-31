<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('printers') ?></h5>
                <?php if ($this->can('admin.printers.create')): ?>
                <a href="/admin/printers/create" class="btn btn-success">
                    <i class="ri-add-line me-1"></i>
                    <?= $this->__('create_printer') ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($printers)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-printer-line"></i>
                        </div>
                    </div>
                    <h5 class="text-muted"><?= $this->__('no_results') ?></h5>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('name') ?></th>
                                <th><?= $this->__('model') ?></th>
                                <th class="text-end"><?= $this->__('power_watts') ?></th>
                                <th class="text-end"><?= $this->__('electricity_cost_per_kwh') ?></th>
                                <th class="text-end"><?= $this->__('amortization_per_hour') ?></th>
                                <th><?= $this->__('status') ?></th>
                                <th><?= $this->__('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($printers as $printer): ?>
                            <tr>
                                <td><span class="fw-medium"><?= $this->e($printer['name']) ?></span></td>
                                <td class="text-muted"><?= $this->e($printer['model'] ?? '-') ?></td>
                                <td class="text-end"><?= $this->e($printer['power_watts'] ?? 0) ?></td>
                                <td class="text-end"><?= number_format($printer['electricity_cost_per_kwh'] ?? 0, 4) ?></td>
                                <td class="text-end"><?= number_format($printer['amortization_per_hour'] ?? 0, 2) ?></td>
                                <td>
                                    <?php if ($printer['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($this->can('admin.printers.edit')): ?>
                                        <a href="/admin/printers/<?= $printer['id'] ?>/edit" class="btn btn-soft-primary btn-sm">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($this->can('admin.printers.delete')): ?>
                                        <form method="POST" action="/admin/printers/<?= $printer['id'] ?>/delete" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                            <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('<?= $this->__('confirm_delete_printer') ?>')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
