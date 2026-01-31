<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex justify-content-between flex-wrap gap-2">
    <a href="/warehouse/documents" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_documents') ?>
    </a>

    <div class="d-flex gap-2">
        <?php if ($document['status'] === 'draft'): ?>
            <?php if ($this->can('warehouse.documents.edit')): ?>
            <a href="/warehouse/documents/<?= $document['id'] ?>/edit" class="btn btn-soft-secondary">
                <i class="ri-pencil-line me-1"></i> <?= $this->__('edit') ?>
            </a>
            <?php endif; ?>
            <?php if ($this->can('warehouse.documents.post')): ?>
            <form method="POST" action="/warehouse/documents/<?= $document['id'] ?>/post" class="d-inline">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('confirm_post_document') ?>')">
                    <i class="ri-check-line me-1"></i> <?= $this->__('post_document') ?>
                </button>
            </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($document['status'] !== 'cancelled' && $this->can('warehouse.documents.cancel')): ?>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
            <i class="ri-close-line me-1"></i> <?= $this->__('cancel_document') ?>
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Document Info -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-file-text-line me-2"></i><?= $this->__('document_information') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 35%;"><?= $this->__('document_number') ?></th>
                                <td class="text-primary fw-semibold"><?= $this->e($document['document_number']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('type') ?></th>
                                <td><?= $this->e(ucfirst($document['type'])) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('date') ?></th>
                                <td><?= $this->date($document['document_date'], 'Y-m-d') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('status') ?></th>
                                <td>
                                    <?php
                                    $statusClass = match($document['status']) {
                                        'draft' => 'warning',
                                        'posted' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= ucfirst($document['status']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('partner') ?></th>
                                <td><?= $this->e($document['partner_name'] ?? '-') ?></td>
                            </tr>
                            <?php if (in_array($document['type'], ['issue', 'adjustment', 'stocktake'], true)): ?>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('issue_costing_method') ?></th>
                                <td><span class="badge bg-info-subtle text-info"><?= $this->e($document['costing_method'] ?? $this->__('costing_method_fifo')) ?></span></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('total_amount') ?></th>
                                <td class="fw-semibold"><?= $this->currency($document['total_amount']) ?></td>
                            </tr>
                            <?php if ($document['notes']): ?>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('notes') ?></th>
                                <td><?= nl2br($this->e($document['notes'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i><?= $this->__('audit_information') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 35%;"><?= $this->__('created_by') ?></th>
                                <td><?= $this->e($document['created_by_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('created_at') ?></th>
                                <td><?= $this->date($document['created_at']) ?></td>
                            </tr>
                            <?php if ($document['posted_at']): ?>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('posted_by') ?></th>
                                <td><?= $this->e($document['posted_by_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('posted_at') ?></th>
                                <td><?= $this->date($document['posted_at']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($document['cancelled_at']): ?>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('cancelled_by') ?></th>
                                <td><?= $this->e($document['cancelled_by_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('cancelled_at') ?></th>
                                <td><?= $this->date($document['cancelled_at']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('cancel_reason') ?></th>
                                <td class="text-danger"><?= $this->e($document['cancel_reason']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Lines -->
<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-list-check me-2"></i><?= $this->__('document_lines') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('item') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('unit_price') ?></th>
                        <th class="text-end"><?= $this->__('total') ?></th>
                        <th><?= $this->__('notes') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                    <tr>
                        <td><?= $line['line_number'] ?></td>
                        <td>
                            <a href="/warehouse/items/<?= $line['item_id'] ?>" class="text-primary">
                                <?= $this->e($line['sku']) ?>
                            </a>
                        </td>
                        <td><?= $this->e($line['item_name']) ?></td>
                        <td class="text-end"><?= $this->number($line['quantity']) ?> <?= $this->__('unit_' . ($line['unit'] ?? 'pcs')) ?></td>
                        <td class="text-end"><?= $this->currency($line['unit_price']) ?></td>
                        <td class="text-end fw-medium"><?= $this->currency($line['total_price']) ?></td>
                        <td><?= $this->e($line['notes'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-semibold"><?= $this->__('total') ?>:</td>
                        <td class="text-end fw-semibold"><?= $this->currency($document['total_amount']) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($batchAllocations)): ?>
<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-stack-line me-2"></i><?= $this->__('batch_allocations') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('batch') ?></th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('unit_cost') ?></th>
                        <th class="text-end"><?= $this->__('total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($batchAllocations as $allocation): ?>
                    <tr>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?= $this->e($allocation['batch_code']) ?></span></td>
                        <td><?= $this->e($allocation['sku']) ?></td>
                        <td><?= $this->e($allocation['item_name']) ?></td>
                        <td class="text-end"><?= $this->number($allocation['quantity']) ?></td>
                        <td class="text-end"><?= $this->currency($allocation['unit_cost']) ?></td>
                        <td class="text-end fw-medium"><?= $this->currency($allocation['total_cost']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel"><i class="ri-close-circle-line me-2"></i><?= $this->__('cancel_document') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/warehouse/documents/<?= $document['id'] ?>/cancel">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <div class="modal-body">
                    <p><?= $this->__('confirm_cancel_document') ?></p>
                    <?php if ($document['status'] === 'posted'): ?>
                    <div class="alert alert-danger">
                        <i class="ri-alert-line me-2"></i>
                        <strong><?= $this->__('warning') ?>:</strong> <?= $this->__('cancel_will_reverse_movements') ?>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label"><?= $this->__('cancellation_reason') ?> <span class="text-danger">*</span></label>
                        <textarea id="cancel_reason" name="cancel_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal"><?= $this->__('close') ?></button>
                    <button type="submit" class="btn btn-danger"><?= $this->__('confirm_cancellation') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
