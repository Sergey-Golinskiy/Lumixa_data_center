<?php $this->section('content'); ?>

<div class="page-actions">
    <form method="GET" class="search-form">
        <input type="text" name="search" value="<?= $this->e($filters['search'] ?? '') ?>" placeholder="<?= $this->__('document_number_search') ?>">
        <select name="type">
            <option value=""><?= $this->__('all_types') ?></option>
            <?php foreach ($types ?? [] as $value => $label): ?>
            <option value="<?= $this->e($value) ?>" <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>><?= $this->e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value=""><?= $this->__('all_status') ?></option>
            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>><?= $this->__('draft') ?></option>
            <option value="posted" <?= ($filters['status'] ?? '') === 'posted' ? 'selected' : '' ?>><?= $this->__('posted') ?></option>
            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= $this->__('cancelled') ?></option>
        </select>
        <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
    </form>

    <?php if ($this->can('warehouse.documents.create')): ?>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle">+ <?= $this->__('new_document') ?></button>
        <div class="dropdown-menu">
            <?php foreach ($types as $value => $label): ?>
            <a href="/warehouse/documents/create/<?= $value ?>" class="dropdown-item"><?= $this->e($label) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><?= $this->__('document_num') ?></th>
                    <th><?= $this->__('type') ?></th>
                    <th><?= $this->__('date') ?></th>
                    <th><?= $this->__('partner') ?></th>
                    <th><?= $this->__('total') ?></th>
                    <th><?= $this->__('status') ?></th>
                    <th><?= $this->__('created_by') ?></th>
                    <th><?= $this->__('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                <tr>
                    <td colspan="8" class="text-muted" style="text-align: center; padding: 40px;">
                        <?= $this->__('no_documents_found') ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                    <tr data-href="/warehouse/documents/<?= $doc['id'] ?>">
                        <td><strong><?= $this->e($doc['document_number'] ?? '') ?></strong></td>
                        <td><?= $this->e($types[$doc['type'] ?? ''] ?? $doc['type'] ?? '') ?></td>
                        <td><?= $this->date($doc['document_date'] ?? '', 'Y-m-d') ?></td>
                        <td><?= $this->e($doc['partner_name'] ?? '-') ?></td>
                        <td><?= $this->currency($doc['total_amount'] ?? 0) ?></td>
                        <td>
                            <?php
                            $statusClass = match($doc['status'] ?? 'draft') {
                                'draft' => 'warning',
                                'posted' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= $this->__($doc['status'] ?? 'draft') ?></span>
                        </td>
                        <td><?= $this->e($doc['created_by_name'] ?? '-') ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $doc['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php if ($pagination['has_prev'] ?? false): ?>
        <a href="?page=<?= ($pagination['current_page'] ?? 1) - 1 ?>&<?= http_build_query($filters ?? []) ?>" class="btn btn-sm">&laquo; <?= $this->__('prev') ?></a>
        <?php endif; ?>
        <span class="pagination-info"><?= $this->__('page') ?> <?= $pagination['current_page'] ?? 1 ?> <?= $this->__('of') ?> <?= $pagination['total_pages'] ?? 1 ?></span>
        <?php if ($pagination['has_next'] ?? false): ?>
        <a href="?page=<?= ($pagination['current_page'] ?? 1) + 1 ?>&<?= http_build_query($filters ?? []) ?>" class="btn btn-sm"><?= $this->__('next') ?> &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.page-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 20px;
}
.search-form {
    display: flex;
    gap: 10px;
    flex: 1;
}
.search-form input,
.search-form select {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
}
.dropdown {
    position: relative;
}
.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    min-width: 200px;
    z-index: 100;
}
.dropdown:hover .dropdown-menu {
    display: block;
}
.dropdown-item {
    display: block;
    padding: 10px 15px;
    color: var(--text);
}
.dropdown-item:hover {
    background: var(--bg);
    text-decoration: none;
}
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 15px;
    border-top: 1px solid var(--border);
}
</style>

<?php $this->endSection(); ?>
