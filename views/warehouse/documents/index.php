<?php $this->section('content'); ?>

<div class="page-actions">
    <form method="GET" class="search-form">
        <input type="text" name="search" value="<?= $this->e($filters['search']) ?>" placeholder="Document number...">
        <select name="type">
            <option value="">All Types</option>
            <?php foreach ($types as $value => $label): ?>
            <option value="<?= $this->e($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>><?= $this->e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">All Status</option>
            <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="posted" <?= $filters['status'] === 'posted' ? 'selected' : '' ?>>Posted</option>
            <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    <?php if ($this->can('warehouse.documents.create')): ?>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle">+ New Document</button>
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
                    <th>Document #</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Partner</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                <tr>
                    <td colspan="8" class="text-muted" style="text-align: center; padding: 40px;">
                        No documents found
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                    <tr data-href="/warehouse/documents/<?= $doc['id'] ?>">
                        <td><strong><?= $this->e($doc['document_number']) ?></strong></td>
                        <td><?= $this->e($types[$doc['type']] ?? $doc['type']) ?></td>
                        <td><?= $this->date($doc['document_date'], 'Y-m-d') ?></td>
                        <td><?= $this->e($doc['partner_name'] ?? '-') ?></td>
                        <td><?= $this->currency($doc['total_amount']) ?></td>
                        <td>
                            <?php
                            $statusClass = match($doc['status']) {
                                'draft' => 'warning',
                                'posted' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($doc['status']) ?></span>
                        </td>
                        <td><?= $this->e($doc['created_by_name'] ?? '-') ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $doc['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($pagination['has_prev']): ?>
        <a href="?page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>" class="btn btn-sm">&laquo; Prev</a>
        <?php endif; ?>
        <span class="pagination-info">Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?></span>
        <?php if ($pagination['has_next']): ?>
        <a href="?page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>" class="btn btn-sm">Next &raquo;</a>
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
