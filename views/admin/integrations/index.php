<?php $this->section('content'); ?>

<div class="integrations-page">
    <h2><?= $this->__('integrations') ?></h2>

    <!-- WooCommerce Integration -->
    <div class="card integration-card">
        <div class="card-header">
            <span class="integration-icon">&#128722;</span>
            WooCommerce
            <?php if (!empty($woocommerceSettings['auto_sync_enabled']) && $woocommerceSettings['auto_sync_enabled'] === '1'): ?>
                <span class="badge badge-success"><?= $this->__('active') ?></span>
            <?php else: ?>
                <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/integrations/woocommerce" id="woocommerce-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                <div class="form-section">
                    <h4><?= $this->__('connection_settings') ?></h4>

                    <div class="form-group">
                        <label for="woo_store_url"><?= $this->__('store_url') ?> *</label>
                        <input type="url" id="woo_store_url" name="woo_store_url"
                               placeholder="https://your-store.com"
                               value="<?= $this->e($woocommerceSettings['store_url'] ?? $this->old('woo_store_url')) ?>">
                        <small class="form-help"><?= $this->__('store_url_help') ?></small>
                        <?php if ($this->hasError('woo_store_url')): ?>
                            <span class="error"><?= $this->error('woo_store_url') ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="woo_consumer_key"><?= $this->__('consumer_key') ?> *</label>
                            <input type="text" id="woo_consumer_key" name="woo_consumer_key"
                                   placeholder="ck_..."
                                   value="<?= $this->e($woocommerceSettings['consumer_key'] ?? $this->old('woo_consumer_key')) ?>">
                            <?php if ($this->hasError('woo_consumer_key')): ?>
                                <span class="error"><?= $this->error('woo_consumer_key') ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="woo_consumer_secret"><?= $this->__('consumer_secret') ?> *</label>
                            <input type="password" id="woo_consumer_secret" name="woo_consumer_secret"
                                   placeholder="cs_..."
                                   value="<?= $this->e($woocommerceSettings['consumer_secret'] ?? $this->old('woo_consumer_secret')) ?>">
                            <?php if ($this->hasError('woo_consumer_secret')): ?>
                                <span class="error"><?= $this->error('woo_consumer_secret') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="button" class="btn btn-secondary btn-sm" id="test-woo-connection">
                            <?= $this->__('test_connection') ?>
                        </button>
                        <span id="woo-test-result"></span>
                    </div>
                </div>

                <div class="form-section">
                    <h4><?= $this->__('sync_settings') ?></h4>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="woo_auto_sync" value="1"
                                   <?= (!empty($woocommerceSettings['auto_sync_enabled']) && $woocommerceSettings['auto_sync_enabled'] === '1') ? 'checked' : '' ?>>
                            <?= $this->__('enable_auto_sync') ?>
                        </label>
                        <small class="form-help"><?= $this->__('auto_sync_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="woo_sync_interval"><?= $this->__('sync_interval_minutes') ?></label>
                        <input type="number" id="woo_sync_interval" name="woo_sync_interval"
                               min="5" max="1440" step="5"
                               value="<?= $this->e($woocommerceSettings['sync_interval_minutes'] ?? 30) ?>">
                        <small class="form-help"><?= $this->__('sync_interval_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label><?= $this->__('sync_order_statuses') ?></label>
                        <div class="checkbox-group">
                            <?php
                            $syncStatuses = explode(',', $woocommerceSettings['sync_order_statuses'] ?? 'processing,completed');
                            $allStatuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'];
                            foreach ($allStatuses as $status):
                            ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="woo_sync_statuses[]" value="<?= $status ?>"
                                           <?= in_array($status, $syncStatuses) ? 'checked' : '' ?>>
                                    <?= $this->__('order_status_' . str_replace('-', '_', $status)) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?= $this->__('save_settings') ?></button>
                </div>
            </form>

            <!-- Manual Sync -->
            <div class="form-section sync-actions">
                <h4><?= $this->__('manual_sync') ?></h4>
                <form method="POST" action="/admin/integrations/woocommerce/sync" class="inline-form">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <button type="submit" class="btn btn-secondary">
                        <?= $this->__('sync_now') ?>
                    </button>
                </form>
                <small class="form-help"><?= $this->__('manual_sync_help') ?></small>
            </div>
        </div>
    </div>

    <!-- WooCommerce Order Statuses -->
    <div class="card">
        <div class="card-header">
            <?= $this->__('woocommerce_order_statuses') ?>
            <button type="button" class="btn btn-sm btn-secondary" id="sync-woo-statuses">
                <?= $this->__('sync_statuses') ?>
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($woocommerceStatuses)): ?>
                <p class="text-muted"><?= $this->__('no_statuses_synced') ?></p>
                <p class="small"><?= $this->__('test_connection_to_sync_statuses') ?></p>
            <?php else: ?>
                <p class="small text-muted"><?= $this->__('status_mapping_help') ?></p>
                <table class="data-table statuses-table">
                    <thead>
                        <tr>
                            <th><?= $this->__('woocommerce_status') ?></th>
                            <th><?= $this->__('orders_count') ?></th>
                            <th><?= $this->__('internal_status') ?></th>
                            <th><?= $this->__('sync_orders') ?></th>
                            <th><?= $this->__('last_synced') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($woocommerceStatuses as $status): ?>
                        <tr data-status-id="<?= $status['id'] ?>">
                            <td>
                                <strong><?= $this->e($status['external_name']) ?></strong>
                                <br><small class="text-muted"><?= $this->e($status['external_code']) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary"><?= $status['order_count'] ?></span>
                            </td>
                            <td>
                                <select class="status-mapping-select" data-status-id="<?= $status['id'] ?>">
                                    <option value=""><?= $this->__('no_mapping') ?></option>
                                    <?php foreach ($internalStatuses as $internal): ?>
                                        <option value="<?= $internal ?>" <?= $status['internal_status'] === $internal ? 'selected' : '' ?>>
                                            <?= $this->__('order_status_' . $internal) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="text-center">
                                <label class="switch">
                                    <input type="checkbox" class="status-active-toggle" data-status-id="<?= $status['id'] ?>"
                                           <?= $status['is_active'] ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            <td>
                                <?php if ($status['last_synced_at']): ?>
                                    <?= $this->datetime($status['last_synced_at']) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-secondary save-status-btn" data-status-id="<?= $status['id'] ?>">
                                    <?= $this->__('save') ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sync History -->
    <?php if (!empty($syncLogs)): ?>
    <div class="card">
        <div class="card-header"><?= $this->__('sync_history') ?></div>
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('records') ?></th>
                        <th><?= $this->__('triggered_by') ?></th>
                        <th><?= $this->__('duration') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($syncLogs as $log): ?>
                    <tr>
                        <td><?= $this->datetime($log['started_at']) ?></td>
                        <td><?= $this->e($log['sync_type']) ?></td>
                        <td>
                            <span class="badge badge-<?= $log['status'] === 'completed' ? 'success' : ($log['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                <?= $this->__('sync_status_' . $log['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($log['status'] === 'completed'): ?>
                                +<?= $log['records_created'] ?> / ~<?= $log['records_updated'] ?>
                                <?php if ($log['records_failed'] > 0): ?>
                                    / <span class="text-danger">!</span><?= $log['records_failed'] ?>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= $log['triggered_by_name'] ?? $this->__('automatic') ?></td>
                        <td>
                            <?php if ($log['completed_at']):
                                $duration = strtotime($log['completed_at']) - strtotime($log['started_at']);
                                echo $duration < 60 ? $duration . 's' : floor($duration / 60) . 'm ' . ($duration % 60) . 's';
                            else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($log['status'] === 'failed' && $log['error_message']): ?>
                    <tr class="error-row">
                        <td colspan="6" class="text-danger small"><?= $this->e($log['error_message']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.integrations-page h2 { margin-bottom: 20px; }

.integration-card .card-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.integration-icon {
    font-size: 1.5em;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h4 {
    margin-bottom: 15px;
    color: var(--text-secondary);
}

.form-help {
    display: block;
    margin-top: 4px;
    color: var(--text-muted);
    font-size: 0.85em;
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.sync-actions {
    background: var(--bg-secondary);
    padding: 15px;
    border-radius: 6px;
    margin-top: 20px;
}

.inline-form {
    display: inline;
}

#woo-test-result {
    margin-left: 10px;
}

#woo-test-result.success {
    color: var(--success);
}

#woo-test-result.error {
    color: var(--danger);
}

.error-row td {
    padding-top: 0 !important;
    border-top: none !important;
}

/* Status mapping table */
.statuses-table .status-mapping-select {
    min-width: 150px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Toggle switch */
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 22px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
    border-radius: 22px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--success);
}

input:checked + .slider:before {
    transform: translateX(18px);
}

.status-saved {
    color: var(--success);
    margin-left: 8px;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<script>
document.getElementById('test-woo-connection').addEventListener('click', function() {
    const btn = this;
    const result = document.getElementById('woo-test-result');
    const form = document.getElementById('woocommerce-form');

    btn.disabled = true;
    result.textContent = '<?= $this->__('testing') ?>...';
    result.className = '';

    // First save the settings via form submission simulation
    const formData = new FormData(form);
    formData.append('_test', '1');

    fetch('/admin/integrations/woocommerce/test', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            result.textContent = '<?= $this->__('connection_successful') ?>';
            result.className = 'success';
        } else {
            result.textContent = data.message || '<?= $this->__('connection_failed') ?>';
            result.className = 'error';
        }
    })
    .catch(error => {
        btn.disabled = false;
        result.textContent = '<?= $this->__('connection_error') ?>';
        result.className = 'error';
    });
});

// Sync WooCommerce statuses
document.getElementById('sync-woo-statuses')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = '<?= $this->__('syncing') ?>...';

    const formData = new FormData();
    formData.append('_csrf_token', '<?= $this->e($csrfToken) ?>');

    fetch('/admin/integrations/woocommerce/sync-statuses', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = '<?= $this->__('sync_statuses') ?>';
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '<?= $this->__('sync_failed') ?>');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.textContent = '<?= $this->__('sync_statuses') ?>';
        alert('<?= $this->__('connection_error') ?>');
    });
});

// Save status mapping
document.querySelectorAll('.save-status-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const statusId = this.dataset.statusId;
        const row = document.querySelector('tr[data-status-id="' + statusId + '"]');
        const internalStatus = row.querySelector('.status-mapping-select').value;
        const isActive = row.querySelector('.status-active-toggle').checked;

        btn.disabled = true;
        btn.textContent = '<?= $this->__('saving') ?>...';

        const formData = new FormData();
        formData.append('_csrf_token', '<?= $this->e($csrfToken) ?>');
        formData.append('status_id', statusId);
        formData.append('internal_status', internalStatus);
        if (isActive) {
            formData.append('is_active', '1');
        }

        fetch('/admin/integrations/woocommerce/status', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = '<?= $this->__('save') ?>';
            if (data.success) {
                // Show saved indicator
                const savedSpan = document.createElement('span');
                savedSpan.className = 'status-saved';
                savedSpan.textContent = '✓';
                btn.parentNode.appendChild(savedSpan);
                setTimeout(() => savedSpan.remove(), 2000);
            } else {
                alert(data.message || '<?= $this->__('save_failed') ?>');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.textContent = '<?= $this->__('save') ?>';
            alert('<?= $this->__('connection_error') ?>');
        });
    });
});
</script>

<?php $this->endSection(); ?>
