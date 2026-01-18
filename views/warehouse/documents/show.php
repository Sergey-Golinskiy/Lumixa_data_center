<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/documents" class="btn btn-secondary">&laquo; Back to Documents</a>

    <div class="actions-right">
        <?php if ($document['status'] === 'draft'): ?>
            <?php if ($this->can('warehouse.documents.edit')): ?>
            <a href="/warehouse/documents/<?= $document['id'] ?>/edit" class="btn btn-secondary">Edit</a>
            <?php endif; ?>
            <?php if ($this->can('warehouse.documents.post')): ?>
            <form method="POST" action="/warehouse/documents/<?= $document['id'] ?>/post" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('Post this document? This will update stock balances.')">Post Document</button>
            </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($document['status'] !== 'cancelled' && $this->can('warehouse.documents.cancel')): ?>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('cancel-modal').style.display='flex'">Cancel Document</button>
        <?php endif; ?>
    </div>
</div>

<!-- Document Info -->
<div class="detail-grid">
    <div class="card">
        <div class="card-header">Document Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Document #</span>
                <span class="detail-value"><strong><?= $this->e($document['document_number']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value"><?= $this->e(ucfirst($document['type'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date</span>
                <span class="detail-value"><?= $this->date($document['document_date'], 'Y-m-d') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($document['status']) {
                        'draft' => 'warning',
                        'posted' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($document['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Partner</span>
                <span class="detail-value"><?= $this->e($document['partner_name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value"><strong><?= $this->currency($document['total_amount']) ?></strong></span>
            </div>
            <?php if ($document['notes']): ?>
            <div class="detail-row">
                <span class="detail-label">Notes</span>
                <span class="detail-value"><?= nl2br($this->e($document['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Audit Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Created By</span>
                <span class="detail-value"><?= $this->e($document['created_by_name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created At</span>
                <span class="detail-value"><?= $this->date($document['created_at']) ?></span>
            </div>
            <?php if ($document['posted_at']): ?>
            <div class="detail-row">
                <span class="detail-label">Posted By</span>
                <span class="detail-value"><?= $this->e($document['posted_by_name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Posted At</span>
                <span class="detail-value"><?= $this->date($document['posted_at']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($document['cancelled_at']): ?>
            <div class="detail-row">
                <span class="detail-label">Cancelled By</span>
                <span class="detail-value"><?= $this->e($document['cancelled_by_name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cancelled At</span>
                <span class="detail-value"><?= $this->date($document['cancelled_at']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cancel Reason</span>
                <span class="detail-value text-danger"><?= $this->e($document['cancel_reason']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Document Lines -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Document Lines</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>SKU</th>
                        <th>Item</th>
                        <th>Lot</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                    <tr>
                        <td><?= $line['line_number'] ?></td>
                        <td>
                            <a href="/warehouse/items/<?= $line['item_id'] ?>">
                                <?= $this->e($line['sku']) ?>
                            </a>
                        </td>
                        <td><?= $this->e($line['item_name']) ?></td>
                        <td><?= $this->e($line['lot_number'] ?? '-') ?></td>
                        <td><?= $this->number($line['quantity']) ?> <?= $line['unit'] ?></td>
                        <td><?= $this->currency($line['unit_price']) ?></td>
                        <td><?= $this->currency($line['total_price']) ?></td>
                        <td><?= $this->e($line['notes'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Total:</strong></td>
                        <td><strong><?= $this->currency($document['total_amount']) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancel-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cancel Document</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('cancel-modal').style.display='none'">&times;</button>
        </div>
        <form method="POST" action="/warehouse/documents/<?= $document['id'] ?>/cancel">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
            <div class="modal-body">
                <p>Are you sure you want to cancel this document?</p>
                <?php if ($document['status'] === 'posted'): ?>
                <p class="text-danger"><strong>Warning:</strong> This will reverse all stock movements!</p>
                <?php endif; ?>
                <div class="form-group">
                    <label for="cancel_reason">Cancellation Reason *</label>
                    <textarea id="cancel_reason" name="cancel_reason" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('cancel-modal').style.display='none'">Close</button>
                <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}
.detail-row {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    flex: 0 0 120px;
    color: var(--text-muted);
    font-size: 13px;
}
.detail-value {
    flex: 1;
}
.page-actions {
    display: flex;
    justify-content: space-between;
}
.actions-right {
    display: flex;
    gap: 10px;
}
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    border-radius: var(--radius);
    max-width: 500px;
    width: 90%;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
}
.modal-header h3 {
    margin: 0;
}
.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
}
.modal-body {
    padding: 20px;
}
.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>

<?php $this->endSection(); ?>
