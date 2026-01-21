<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/partners" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('suppliers')]) ?></a>
    <?php if ($this->can('warehouse.partners.edit')): ?>
    <a href="/warehouse/partners/<?= $partner['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Supplier Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('supplier_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('code') ?></span>
                <span class="detail-value"><strong><?= $this->e($partner['code']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($partner['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('type') ?></span>
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
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php if ($partner['is_active']): ?>
                    <span class="badge badge-success"><?= $this->__('active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('tax_id') ?></span>
                <span class="detail-value"><?= $this->e($partner['tax_id'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created') ?></span>
                <span class="detail-value"><?= $this->datetime($partner['created_at']) ?></span>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('contact_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('contact_person') ?></span>
                <span class="detail-value"><?= $this->e($partner['contact_person'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('email') ?></span>
                <span class="detail-value">
                    <?php if ($partner['email']): ?>
                    <a href="mailto:<?= $this->e($partner['email']) ?>"><?= $this->e($partner['email']) ?></a>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('phone') ?></span>
                <span class="detail-value"><?= $this->e($partner['phone'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('address') ?></span>
                <span class="detail-value"><?= nl2br($this->e($partner['address'] ?? '-')) ?></span>
            </div>
            <?php if ($partner['notes']): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('notes') ?></span>
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
        <div class="summary-label"><?= $this->__('total_documents') ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= number_format($stats['total_receipts'] ?? 0, 2) ?></div>
        <div class="summary-label"><?= $this->__('total_receipts') ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-value"><?= number_format($stats['total_shipments'] ?? 0, 2) ?></div>
        <div class="summary-label"><?= $this->__('total_shipments') ?></div>
    </div>
</div>

<!-- Recent Documents -->
<div class="card">
    <div class="card-header"><?= $this->__('recent_documents') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('document_number') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th class="text-right"><?= $this->__('amount') ?></th>
                        <th><?= $this->__('created_by') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documents)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted"><?= $this->__('no_documents') ?></td>
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
    <div class="card-header" style="color: var(--danger);"><?= $this->__('danger_zone') ?></div>
    <div class="card-body">
        <form method="POST" action="/warehouse/partners/<?= $partner['id'] ?>/delete"
              onsubmit="return confirm('<?= $this->__('confirm_delete_supplier') ?>');">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
            <p><?= $this->__('delete_supplier_permanently') ?></p>
            <button type="submit" class="btn btn-danger"><?= $this->__('delete_supplier') ?></button>
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
