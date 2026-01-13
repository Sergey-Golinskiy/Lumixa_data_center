<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/partners" class="btn btn-secondary">&laquo; Back to Partners</a>
    <?php if ($this->can('warehouse.partners.edit')): ?>
    <a href="/warehouse/partners/<?= $partner['id'] ?>/edit" class="btn btn-outline">Edit</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Partner Information -->
    <div class="card">
        <div class="card-header">Partner Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Code</span>
                <span class="detail-value"><strong><?= $this->e($partner['code']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= $this->e($partner['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value">
                    <?php
                    $typeClass = match($partner['type']) {
                        'supplier' => 'info',
                        'customer' => 'success',
                        'both' => 'warning',
                        default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $typeClass ?>"><?= ucfirst($partner['type']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php if ($partner['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                    <?php else: ?>
                    <span class="badge badge-secondary">Inactive</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tax ID</span>
                <span class="detail-value"><?= $this->e($partner['tax_id'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?= $this->datetime($partner['created_at']) ?></span>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="card">
        <div class="card-header">Contact Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Contact Person</span>
                <span class="detail-value"><?= $this->e($partner['contact_person'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">
                    <?php if ($partner['email']): ?>
                    <a href="mailto:<?= $this->e($partner['email']) ?>"><?= $this->e($partner['email']) ?></a>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone</span>
                <span class="detail-value"><?= $this->e($partner['phone'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address</span>
                <span class="detail-value"><?= nl2br($this->e($partner['address'] ?? '-')) ?></span>
            </div>
            <?php if ($partner['notes']): ?>
            <div class="detail-row">
                <span class="detail-label">Notes</span>
                <span class="detail-value"><?= nl2br($this->e($partner['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="summary-cards" style="margin: 20px 0;">
    <div class="summary-card">
        <div class="summary-value"><?= number_format($stats['total_documents'] ?? 0) ?></div>
        <div class="summary-label">Total Documents</div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= number_format($stats['total_receipts'] ?? 0, 2) ?></div>
        <div class="summary-label">Total Receipts</div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= number_format($stats['total_shipments'] ?? 0, 2) ?></div>
        <div class="summary-label">Total Shipments</div>
    </div>
</div>

<!-- Recent Documents -->
<div class="card">
    <div class="card-header">Recent Documents</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Document #</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-right">Amount</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documents)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No documents</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td><?= $this->date($doc['document_date'], 'Y-m-d') ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $doc['id'] ?>">
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
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($doc['status']) ?></span>
                        </td>
                        <td class="text-right"><?= number_format($doc['total_amount'] ?? 0, 2) ?></td>
                        <td><?= $this->e($doc['created_by_name'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($this->can('warehouse.partners.delete') && ($stats['total_documents'] ?? 0) == 0): ?>
<div class="card" style="margin-top: 20px; border-color: var(--danger);">
    <div class="card-header" style="color: var(--danger);">Danger Zone</div>
    <div class="card-body">
        <form method="POST" action="/warehouse/partners/<?= $partner['id'] ?>/delete"
              onsubmit="return confirm('Are you sure you want to delete this partner?');">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
            <p>Delete this partner permanently. This action cannot be undone.</p>
            <button type="submit" class="btn btn-danger">Delete Partner</button>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}
.detail-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.detail-row:last-child { border-bottom: none; }
.detail-label {
    flex: 0 0 120px;
    color: var(--text-muted);
    font-size: 13px;
}
.detail-value { flex: 1; }
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
}
.summary-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}
.summary-value { font-size: 24px; font-weight: bold; color: var(--primary); }
.summary-label { color: var(--text-muted); font-size: 13px; margin-top: 5px; }
.text-right { text-align: right; }
</style>

<?php $this->endSection(); ?>
