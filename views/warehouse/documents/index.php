<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div></div>
            <?php if ($this->can('warehouse.documents.create')): ?>
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-add-line me-1"></i> <?= $this->__('new_document') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php foreach ($types as $value => $label): ?>
                    <li><a class="dropdown-item" href="/warehouse/documents/create/<?= $value ?>"><?= $this->e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i><?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/warehouse/documents">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="<?= $this->__('document_number_search') ?>"
                               value="<?= $this->e($filters['search'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label"><?= $this->__('type') ?></label>
                    <select name="type" class="form-select">
                        <option value=""><?= $this->__('all_types') ?></option>
                        <?php foreach ($types ?? [] as $value => $label): ?>
                        <option value="<?= $this->e($value) ?>" <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>><?= $this->e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label"><?= $this->__('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= $this->__('all_status') ?></option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= $this->__('draft') ?></option>
                        <option value="posted" <?= ($filters['status'] ?? '') === 'posted' ? 'selected' : '' ?>><?= $this->__('posted') ?></option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= $this->__('cancelled') ?></option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-line me-1"></i> <?= $this->__('filter') ?>
                    </button>
                    <?php if (($filters['search'] ?? '') || ($filters['type'] ?? '') || ($filters['status'] ?? '')): ?>
                    <a href="/warehouse/documents" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Documents Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-file-list-3-line me-2"></i><?= $this->__('documents') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('document_num') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('partner') ?></th>
                        <th class="text-end"><?= $this->__('total') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('created_by') ?></th>
                        <th style="width: 100px;"><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documents)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="ri-file-list-3-line fs-1 d-block mb-2 text-secondary"></i>
                            <span class="text-muted"><?= $this->__('no_documents_found') ?></span>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td>
                                <a href="/warehouse/documents/<?= $doc['id'] ?>" class="fw-medium text-primary">
                                    <?= $this->e($doc['document_number'] ?? '') ?>
                                </a>
                            </td>
                            <td><?= $this->e($types[$doc['type'] ?? ''] ?? $doc['type'] ?? '') ?></td>
                            <td><?= $this->date($doc['document_date'] ?? '', 'Y-m-d') ?></td>
                            <td><?= $this->e($doc['partner_name'] ?? '-') ?></td>
                            <td class="text-end fw-medium"><?= $this->currency($doc['total_amount'] ?? 0) ?></td>
                            <td>
                                <?php
                                $statusClass = match($doc['status'] ?? 'draft') {
                                    'draft' => 'warning',
                                    'posted' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= $this->__($doc['status'] ?? 'draft') ?></span>
                            </td>
                            <td><?= $this->e($doc['created_by_name'] ?? '-') ?></td>
                            <td>
                                <a href="/warehouse/documents/<?= $doc['id'] ?>" class="btn btn-sm btn-soft-primary" title="<?= $this->__('view') ?>">
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted">
                <?= $this->__('page') ?> <?= $pagination['current_page'] ?? 1 ?> <?= $this->__('of') ?> <?= $pagination['total_pages'] ?? 1 ?>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= !($pagination['has_prev'] ?? false) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>&<?= http_build_query($filters ?? []) ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>
                    <?php
                    $currentPage = $pagination['current_page'] ?? 1;
                    $totalPagesDoc = $pagination['total_pages'] ?? 1;
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPagesDoc, $currentPage + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters ?? []) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= !($pagination['has_next'] ?? false) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>&<?= http_build_query($filters ?? []) ?>">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
