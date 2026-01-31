<?php $this->section('content'); ?>

<div class="integrations-page">
    <h2><?= $this->__('integrations') ?></h2>

    <!-- Integration Tabs -->
    <div class="integration-tabs">
        <button class="tab-btn active" data-tab="woocommerce">
            <span class="tab-icon">&#128722;</span>
            WooCommerce
            <?php if (!empty($woocommerceSettings['auto_sync_enabled']) && $woocommerceSettings['auto_sync_enabled'] === '1'): ?>
                <span class="badge badge-success badge-sm"><?= $this->__('active') ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" data-tab="google">
            <span class="tab-icon">&#127760;</span>
            Google
            <span class="badge badge-secondary badge-sm"><?= $this->__('coming_soon') ?></span>
        </button>
        <button class="tab-btn" data-tab="captcha">
            <span class="tab-icon">&#128274;</span>
            <?= $this->__('captcha') ?>
            <span class="badge badge-secondary badge-sm"><?= $this->__('coming_soon') ?></span>
        </button>
        <button class="tab-btn" data-tab="bambulab">
            <span class="tab-icon">&#128424;</span>
            BambuLab
            <span class="badge badge-secondary badge-sm"><?= $this->__('coming_soon') ?></span>
        </button>
    </div>

    <!-- WooCommerce Tab Content -->
    <div class="tab-content active" id="tab-woocommerce">
        <div class="card">
            <div class="card-header">
                <span><?= $this->__('woocommerce_settings') ?></span>
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
                <span><?= $this->__('woocommerce_order_statuses') ?></span>
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
                                            <option value="<?= $this->e($internal['code']) ?>" <?= $status['internal_status'] === $internal['code'] ? 'selected' : '' ?>>
                                                <?= $this->e($internal['name']) ?>
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
            <div class="card-header"><span><?= $this->__('sync_history') ?></span></div>
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

    <!-- Google Tab Content -->
    <div class="tab-content" id="tab-google">
        <div class="card">
            <div class="card-header">
                <span><?= $this->__('google_integration') ?></span>
            </div>
            <div class="card-body">
                <div class="coming-soon-placeholder">
                    <div class="placeholder-icon">&#127760;</div>
                    <h3><?= $this->__('google_integration') ?></h3>
                    <p><?= $this->__('google_integration_description') ?></p>
                    <ul class="feature-list">
                        <li><?= $this->__('google_oauth_login') ?></li>
                        <li><?= $this->__('google_drive_backup') ?></li>
                        <li><?= $this->__('google_sheets_export') ?></li>
                        <li><?= $this->__('google_calendar_sync') ?></li>
                    </ul>
                    <span class="badge badge-info"><?= $this->__('coming_soon') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Captcha Tab Content -->
    <div class="tab-content" id="tab-captcha">
        <div class="card">
            <div class="card-header">
                <span><?= $this->__('captcha_settings') ?></span>
            </div>
            <div class="card-body">
                <div class="coming-soon-placeholder">
                    <div class="placeholder-icon">&#128274;</div>
                    <h3><?= $this->__('captcha_protection') ?></h3>
                    <p><?= $this->__('captcha_description') ?></p>
                    <ul class="feature-list">
                        <li>Google reCAPTCHA v2/v3</li>
                        <li>hCaptcha</li>
                        <li>Cloudflare Turnstile</li>
                    </ul>
                    <span class="badge badge-info"><?= $this->__('coming_soon') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- BambuLab Tab Content -->
    <div class="tab-content" id="tab-bambulab">
        <div class="card">
            <div class="card-header">
                <span><?= $this->__('bambulab_printers') ?></span>
            </div>
            <div class="card-body">
                <div class="coming-soon-placeholder">
                    <div class="placeholder-icon">&#128424;</div>
                    <h3><?= $this->__('bambulab_integration') ?></h3>
                    <p><?= $this->__('bambulab_description') ?></p>
                    <ul class="feature-list">
                        <li><?= $this->__('bambulab_printer_status') ?></li>
                        <li><?= $this->__('bambulab_send_jobs') ?></li>
                        <li><?= $this->__('bambulab_monitor_progress') ?></li>
                        <li><?= $this->__('bambulab_filament_tracking') ?></li>
                    </ul>
                    <span class="badge badge-info"><?= $this->__('coming_soon') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.integrations-page h2 { margin-bottom: 20px; }

/* Tab Navigation */
.integration-tabs {
    display: flex;
    gap: 5px;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 0;
    flex-wrap: wrap;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 14px;
    color: var(--text-secondary);
    transition: all 0.2s;
    margin-bottom: -2px;
}

.tab-btn:hover {
    color: var(--text-primary);
    background: var(--bg-secondary);
}

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    font-weight: 500;
}

.tab-icon {
    font-size: 1.2em;
}

.badge-sm {
    font-size: 0.7em;
    padding: 2px 6px;
}

/* Tab Content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Cards */
.card {
    margin-bottom: 20px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
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

/* Coming Soon Placeholder */
.coming-soon-placeholder {
    text-align: center;
    padding: 40px 20px;
}

.coming-soon-placeholder .placeholder-icon {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.5;
}

.coming-soon-placeholder h3 {
    margin-bottom: 10px;
    color: var(--text-primary);
}

.coming-soon-placeholder p {
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
    display: inline-block;
    text-align: left;
}

.feature-list li {
    padding: 5px 0;
    color: var(--text-secondary);
}

.feature-list li::before {
    content: "•";
    color: var(--primary);
    margin-right: 10px;
}
</style>

<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const tabId = this.dataset.tab;

        // Update active tab button
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Update active tab content
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');

        // Store active tab in URL hash
        history.replaceState(null, null, '#' + tabId);
    });
});

// Restore tab from URL hash
if (window.location.hash) {
    const tabId = window.location.hash.substring(1);
    const tabBtn = document.querySelector('.tab-btn[data-tab="' + tabId + '"]');
    if (tabBtn) tabBtn.click();
}

// WooCommerce test connection
document.getElementById('test-woo-connection').addEventListener('click', function() {
    const btn = this;
    const result = document.getElementById('woo-test-result');
    const form = document.getElementById('woocommerce-form');

    btn.disabled = true;
    result.textContent = '<?= $this->__('testing') ?>...';
    result.className = '';

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
            if (data.statuses_added > 0) {
                setTimeout(() => location.reload(), 1000);
            }
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
