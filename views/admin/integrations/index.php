<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $this->__('integrations') ?></h4>
        </div>
    </div>
</div>

<!-- Integration Tabs -->
<ul class="nav nav-tabs nav-tabs-custom nav-primary mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-woocommerce" role="tab">
            <i class="ri-shopping-cart-line me-1"></i>
            WooCommerce
            <?php if (!empty($woocommerceSettings['auto_sync_enabled']) && $woocommerceSettings['auto_sync_enabled'] === '1'): ?>
                <span class="badge bg-success-subtle text-success ms-1"><?= $this->__('active') ?></span>
            <?php endif; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-google" role="tab">
            <i class="ri-google-line me-1"></i>
            Google
            <span class="badge bg-secondary-subtle text-secondary ms-1"><?= $this->__('coming_soon') ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-captcha" role="tab">
            <i class="ri-shield-check-line me-1"></i>
            <?= $this->__('captcha') ?>
            <span class="badge bg-secondary-subtle text-secondary ms-1"><?= $this->__('coming_soon') ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-bambulab" role="tab">
            <i class="ri-printer-line me-1"></i>
            BambuLab
            <span class="badge bg-secondary-subtle text-secondary ms-1"><?= $this->__('coming_soon') ?></span>
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- WooCommerce Tab Content -->
    <div class="tab-pane active" id="tab-woocommerce" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('woocommerce_settings') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/integrations/woocommerce" id="woocommerce-form">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="text-muted mb-3"><?= $this->__('connection_settings') ?></h6>

                        <div class="mb-3">
                            <label for="woo_store_url" class="form-label"><?= $this->__('store_url') ?> *</label>
                            <input type="url" id="woo_store_url" name="woo_store_url" class="form-control"
                                   placeholder="https://your-store.com"
                                   value="<?= $this->e($woocommerceSettings['store_url'] ?? $this->old('woo_store_url')) ?>">
                            <div class="form-text"><?= $this->__('store_url_help') ?></div>
                            <?php if ($this->hasError('woo_store_url')): ?>
                                <div class="text-danger small mt-1"><?= $this->error('woo_store_url') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="woo_consumer_key" class="form-label"><?= $this->__('consumer_key') ?> *</label>
                                <input type="text" id="woo_consumer_key" name="woo_consumer_key" class="form-control"
                                       placeholder="ck_..."
                                       value="<?= $this->e($woocommerceSettings['consumer_key'] ?? $this->old('woo_consumer_key')) ?>">
                                <?php if ($this->hasError('woo_consumer_key')): ?>
                                    <div class="text-danger small mt-1"><?= $this->error('woo_consumer_key') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="woo_consumer_secret" class="form-label"><?= $this->__('consumer_secret') ?> *</label>
                                <input type="password" id="woo_consumer_secret" name="woo_consumer_secret" class="form-control"
                                       placeholder="cs_..."
                                       value="<?= $this->e($woocommerceSettings['consumer_secret'] ?? $this->old('woo_consumer_secret')) ?>">
                                <?php if ($this->hasError('woo_consumer_secret')): ?>
                                    <div class="text-danger small mt-1"><?= $this->error('woo_consumer_secret') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-0">
                            <button type="button" class="btn btn-soft-secondary btn-sm" id="test-woo-connection">
                                <i class="ri-link me-1"></i>
                                <?= $this->__('test_connection') ?>
                            </button>
                            <span id="woo-test-result" class="ms-2"></span>
                        </div>
                    </div>

                    <div class="border-bottom pb-3 mb-4">
                        <h6 class="text-muted mb-3"><?= $this->__('sync_settings') ?></h6>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="woo_auto_sync" name="woo_auto_sync" value="1"
                                       <?= (!empty($woocommerceSettings['auto_sync_enabled']) && $woocommerceSettings['auto_sync_enabled'] === '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="woo_auto_sync"><?= $this->__('enable_auto_sync') ?></label>
                            </div>
                            <div class="form-text"><?= $this->__('auto_sync_help') ?></div>
                        </div>

                        <div class="mb-0">
                            <label for="woo_sync_interval" class="form-label"><?= $this->__('sync_interval_minutes') ?></label>
                            <input type="number" id="woo_sync_interval" name="woo_sync_interval" class="form-control" style="max-width: 200px;"
                                   min="5" max="1440" step="5"
                                   value="<?= $this->e($woocommerceSettings['sync_interval_minutes'] ?? 30) ?>">
                            <div class="form-text"><?= $this->__('sync_interval_help') ?></div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $this->__('save_settings') ?>
                        </button>
                    </div>
                </form>

                <!-- Manual Sync -->
                <div class="bg-light rounded p-3 mt-4">
                    <h6 class="text-muted mb-3"><?= $this->__('manual_sync') ?></h6>
                    <form method="POST" action="/admin/integrations/woocommerce/sync" class="d-inline">
                        <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                        <button type="submit" class="btn btn-soft-primary">
                            <i class="ri-refresh-line me-1"></i>
                            <?= $this->__('sync_now') ?>
                        </button>
                    </form>
                    <div class="form-text mt-2"><?= $this->__('manual_sync_help') ?></div>
                </div>
            </div>
        </div>

        <!-- WooCommerce Order Statuses -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('woocommerce_order_statuses') ?></h5>
                <button type="button" class="btn btn-soft-secondary btn-sm" id="sync-woo-statuses">
                    <i class="ri-refresh-line me-1"></i>
                    <?= $this->__('sync_statuses') ?>
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($woocommerceStatuses)): ?>
                    <div class="text-center py-5">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                                <i class="ri-list-check-2"></i>
                            </div>
                        </div>
                        <h5 class="text-muted"><?= $this->__('no_statuses_synced') ?></h5>
                        <p class="text-muted mb-0"><?= $this->__('test_connection_to_sync_statuses') ?></p>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-3"><?= $this->__('status_mapping_help') ?></p>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $this->__('woocommerce_status') ?></th>
                                    <th class="text-center"><?= $this->__('orders_count') ?></th>
                                    <th><?= $this->__('internal_status') ?></th>
                                    <th class="text-center"><?= $this->__('sync_orders') ?></th>
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
                                        <span class="badge bg-secondary-subtle text-secondary"><?= $status['order_count'] ?></span>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm status-mapping-select" style="min-width: 150px;" data-status-id="<?= $status['id'] ?>">
                                            <option value=""><?= $this->__('no_mapping') ?></option>
                                            <?php foreach ($internalStatuses as $internal): ?>
                                                <option value="<?= $this->e($internal['code']) ?>" <?= $status['internal_status'] === $internal['code'] ? 'selected' : '' ?>>
                                                    <?= $this->e($internal['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                            <input type="checkbox" class="form-check-input status-active-toggle" data-status-id="<?= $status['id'] ?>"
                                                   <?= $status['is_active'] ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($status['last_synced_at']): ?>
                                            <span class="text-muted"><?= $this->datetime($status['last_synced_at']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-soft-primary btn-sm save-status-btn" data-status-id="<?= $status['id'] ?>">
                                            <i class="ri-save-line me-1"></i>
                                            <?= $this->__('save') ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sync History -->
        <?php if (!empty($syncLogs)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $this->__('sync_history') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
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
                                    <?php if ($log['status'] === 'completed'): ?>
                                        <span class="badge bg-success-subtle text-success">
                                            <?= $this->__('sync_status_' . $log['status']) ?>
                                        </span>
                                    <?php elseif ($log['status'] === 'failed'): ?>
                                        <span class="badge bg-danger-subtle text-danger">
                                            <?= $this->__('sync_status_' . $log['status']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning">
                                            <?= $this->__('sync_status_' . $log['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['status'] === 'completed'): ?>
                                        <span class="text-success">+<?= $log['records_created'] ?></span> /
                                        <span class="text-primary">~<?= $log['records_updated'] ?></span>
                                        <?php if ($log['records_failed'] > 0): ?>
                                            / <span class="text-danger">!<?= $log['records_failed'] ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $log['triggered_by_name'] ?? '<span class="text-muted">' . $this->__('automatic') . '</span>' ?></td>
                                <td>
                                    <?php if ($log['completed_at']):
                                        $duration = strtotime($log['completed_at']) - strtotime($log['started_at']);
                                        echo $duration < 60 ? $duration . 's' : floor($duration / 60) . 'm ' . ($duration % 60) . 's';
                                    else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($log['status'] === 'failed' && $log['error_message']): ?>
                            <tr>
                                <td colspan="6" class="text-danger small border-top-0 pt-0"><?= $this->e($log['error_message']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Google Tab Content -->
    <div class="tab-pane" id="tab-google" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $this->__('google_integration') ?></h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="avatar-xl mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle" style="font-size: 3rem;">
                            <i class="ri-google-line"></i>
                        </div>
                    </div>
                    <h4><?= $this->__('google_integration') ?></h4>
                    <p class="text-muted mb-4"><?= $this->__('google_integration_description') ?></p>
                    <ul class="list-unstyled text-start d-inline-block mb-4">
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('google_oauth_login') ?></li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('google_drive_backup') ?></li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('google_sheets_export') ?></li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('google_calendar_sync') ?></li>
                    </ul>
                    <div>
                        <span class="badge bg-info-subtle text-info fs-6"><?= $this->__('coming_soon') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Captcha Tab Content -->
    <div class="tab-pane" id="tab-captcha" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $this->__('captcha_settings') ?></h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="avatar-xl mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle" style="font-size: 3rem;">
                            <i class="ri-shield-check-line"></i>
                        </div>
                    </div>
                    <h4><?= $this->__('captcha_protection') ?></h4>
                    <p class="text-muted mb-4"><?= $this->__('captcha_description') ?></p>
                    <ul class="list-unstyled text-start d-inline-block mb-4">
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i>Google reCAPTCHA v2/v3</li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i>hCaptcha</li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i>Cloudflare Turnstile</li>
                    </ul>
                    <div>
                        <span class="badge bg-info-subtle text-info fs-6"><?= $this->__('coming_soon') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BambuLab Tab Content -->
    <div class="tab-pane" id="tab-bambulab" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $this->__('bambulab_printers') ?></h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="avatar-xl mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle" style="font-size: 3rem;">
                            <i class="ri-printer-line"></i>
                        </div>
                    </div>
                    <h4><?= $this->__('bambulab_integration') ?></h4>
                    <p class="text-muted mb-4"><?= $this->__('bambulab_description') ?></p>
                    <ul class="list-unstyled text-start d-inline-block mb-4">
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('bambulab_printer_status') ?></li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('bambulab_send_jobs') ?></li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('bambulab_monitor_progress') ?></li>
                        <li class="py-1"><i class="ri-check-line text-success me-2"></i><?= $this->__('bambulab_filament_tracking') ?></li>
                    </ul>
                    <div>
                        <span class="badge bg-info-subtle text-info fs-6"><?= $this->__('coming_soon') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Restore tab from URL hash
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash) {
        const tabId = window.location.hash;
        const tabLink = document.querySelector('.nav-link[href="' + tabId + '"]');
        if (tabLink) {
            const tab = new bootstrap.Tab(tabLink);
            tab.show();
        }
    }

    // Store active tab in URL hash on tab change
    document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(function(tabLink) {
        tabLink.addEventListener('shown.bs.tab', function(e) {
            history.replaceState(null, null, e.target.getAttribute('href'));
        });
    });
});

// WooCommerce test connection
document.getElementById('test-woo-connection').addEventListener('click', function() {
    const btn = this;
    const result = document.getElementById('woo-test-result');
    const form = document.getElementById('woocommerce-form');

    btn.disabled = true;
    result.textContent = '<?= $this->__('testing') ?>...';
    result.className = 'ms-2';

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
            result.className = 'ms-2 text-success';
            if (data.statuses_added > 0) {
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            result.textContent = data.message || '<?= $this->__('connection_failed') ?>';
            result.className = 'ms-2 text-danger';
        }
    })
    .catch(error => {
        btn.disabled = false;
        result.textContent = '<?= $this->__('connection_error') ?>';
        result.className = 'ms-2 text-danger';
    });
});

// Sync WooCommerce statuses
document.getElementById('sync-woo-statuses')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i><?= $this->__('syncing') ?>...';

    const formData = new FormData();
    formData.append('_csrf_token', '<?= $this->e($csrfToken) ?>');

    fetch('/admin/integrations/woocommerce/sync-statuses', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-refresh-line me-1"></i><?= $this->__('sync_statuses') ?>';
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '<?= $this->__('sync_failed') ?>');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-refresh-line me-1"></i><?= $this->__('sync_statuses') ?>';
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
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i><?= $this->__('saving') ?>...';

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
            btn.innerHTML = '<i class="ri-save-line me-1"></i><?= $this->__('save') ?>';
            if (data.success) {
                const savedSpan = document.createElement('span');
                savedSpan.className = 'text-success ms-2';
                savedSpan.innerHTML = '<i class="ri-check-line"></i>';
                btn.parentNode.appendChild(savedSpan);
                setTimeout(() => savedSpan.remove(), 2000);
            } else {
                alert(data.message || '<?= $this->__('save_failed') ?>');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line me-1"></i><?= $this->__('save') ?>';
            alert('<?= $this->__('connection_error') ?>');
        });
    });
});
</script>

<?php $this->endSection(); ?>
