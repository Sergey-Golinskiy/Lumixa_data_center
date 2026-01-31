<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex gap-2">
    <a href="/warehouse/partners" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to', ['name' => $this->__('suppliers')]) ?>
    </a>
    <?php if ($this->can('warehouse.partners.edit')): ?>
    <a href="/warehouse/partners/<?= $partner['id'] ?>/edit" class="btn btn-primary">
        <i class="ri-pencil-line me-1"></i> <?= $this->__('edit') ?>
    </a>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Supplier Information -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-user-3-line me-2"></i><?= $this->__('supplier_information') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 35%;"><?= $this->__('code') ?></th>
                                <td class="text-primary fw-semibold"><?= $this->e($partner['code']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('name') ?></th>
                                <td><?= $this->e($partner['name']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('type') ?></th>
                                <td>
                                    <?php
                                    $typeClass = match($partner['type']) {
                                        'supplier' => 'info',
                                        'customer' => 'success',
                                        'both' => 'warning',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $typeClass ?>-subtle text-<?= $typeClass ?>"><?= ucfirst($partner['type']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('status') ?></th>
                                <td>
                                    <?php if ($partner['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('tax_id') ?></th>
                                <td><?= $this->e($partner['tax_id'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('created') ?></th>
                                <td><?= $this->datetime($partner['created_at']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-contacts-line me-2"></i><?= $this->__('contact_information') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 35%;"><?= $this->__('contact_person') ?></th>
                                <td><?= $this->e($partner['contact_person'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('email') ?></th>
                                <td>
                                    <?php if ($partner['email']): ?>
                                    <a href="mailto:<?= $this->e($partner['email']) ?>"><?= $this->e($partner['email']) ?></a>
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('phone') ?></th>
                                <td><?= $this->e($partner['phone'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('address') ?></th>
                                <td><?= nl2br($this->e($partner['address'] ?? '-')) ?></td>
                            </tr>
                            <?php if ($partner['notes']): ?>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('notes') ?></th>
                                <td><?= nl2br($this->e($partner['notes'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                            <i class="ri-file-list-3-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_documents') ?></p>
                        <h4 class="mb-0"><?= number_format($stats['total_documents'] ?? 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                            <i class="ri-arrow-down-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_receipts') ?></p>
                        <h4 class="mb-0"><?= number_format($stats['total_receipts'] ?? 0, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                            <i class="ri-arrow-up-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_shipments') ?></p>
                        <h4 class="mb-0"><?= number_format($stats['total_shipments'] ?? 0, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Documents -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i><?= $this->__('recent_documents') ?></h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($documents)): ?>
        <div class="text-center py-5 text-muted">
            <i class="ri-file-list-3-line fs-1 d-block mb-2"></i>
            <?= $this->__('no_documents') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('document_number') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th class="text-end"><?= $this->__('amount') ?></th>
                        <th><?= $this->__('created_by') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td><?= $this->date($doc['document_date'], 'Y-m-d') ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $doc['id'] ?>" class="text-primary">
                                <?= $this->e($doc['document_number']) ?>
                            </a>
                        </td>
                        <td><?= ucfirst($doc['type']) ?></td>
                        <td>
                            <?php
                            $statusClass = match($doc['status']) {
                                'draft' => 'warning',
                                'posted' => 'success',
                                'cancelled' => 'secondary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= ucfirst($doc['status']) ?></span>
                        </td>
                        <td class="text-end fw-medium"><?= number_format($doc['total_amount'] ?? 0, 2) ?></td>
                        <td><?= $this->e($doc['created_by_name'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($this->can('warehouse.partners.delete') && ($stats['total_documents'] ?? 0) == 0): ?>
<div class="card mt-3 border-danger">
    <div class="card-header bg-danger-subtle">
        <h5 class="card-title mb-0 text-danger"><i class="ri-alert-line me-2"></i><?= $this->__('danger_zone') ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/warehouse/partners/<?= $partner['id'] ?>/delete"
              onsubmit="return confirm('<?= $this->__('confirm_delete_supplier') ?>');">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
            <p class="text-muted mb-3"><?= $this->__('delete_supplier_permanently') ?></p>
            <button type="submit" class="btn btn-danger">
                <i class="ri-delete-bin-line me-1"></i> <?= $this->__('delete_supplier') ?>
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php $this->endSection(); ?>
