<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <?php if (!$isDebug): ?>
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="ri-error-warning-line fs-4 me-2"></i>
            <div>
                <strong><?= $this->__('debug_mode_disabled') ?></strong>
                <?= $this->__('debug_mode_disabled_note') ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Environment Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-information-line me-1"></i>
                    <?= $this->__('environment') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 250px;"><?= $this->__('php_version') ?></th>
                                <td><?= $this->e($environment['php_version']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('php_sapi') ?></th>
                                <td><?= $this->e($environment['php_sapi']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('app_version') ?></th>
                                <td><?= $this->e($environment['app_version']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('environment_label') ?></th>
                                <td>
                                    <span class="badge <?= $environment['app_env'] === 'prod' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                        <?= $this->e($environment['app_env']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?= $this->__('debug_mode') ?></th>
                                <td>
                                    <span class="badge <?= $environment['app_debug'] ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?>">
                                        <?= $environment['app_debug'] ? $this->__('enabled') : $this->__('disabled') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?= $this->__('server_time') ?></th>
                                <td><?= $this->e($environment['server_time']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('timezone') ?></th>
                                <td><?= $this->e($environment['timezone']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('memory_limit') ?></th>
                                <td><?= $this->e($environment['memory_limit']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('max_execution_time') ?></th>
                                <td><?= $this->e($environment['max_execution_time']) ?>s</td>
                            </tr>
                            <tr>
                                <th><?= $this->__('upload_max_size') ?></th>
                                <td><?= $this->e($environment['upload_max_filesize']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Directories -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-folder-line me-1"></i>
                    <?= $this->__('directories') ?>
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($directories as $name => $dir): ?>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <span class="avatar-xs me-2">
                            <span class="avatar-title rounded <?= $dir['status'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                <i class="<?= $dir['status'] ? 'ri-check-line' : 'ri-close-line' ?>"></i>
                            </span>
                        </span>
                        <span class="fw-medium"><?= $this->e($name) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge <?= $dir['writable'] ? 'bg-success-subtle text-success' : ($dir['exists'] ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') ?>">
                            <?= $dir['writable'] ? $this->__('writable') : ($dir['exists'] ? $this->__('not_writable') : $this->__('missing')) ?>
                        </span>
                        <?php if (!$dir['status'] && $dir['fix']): ?>
                        <code class="small"><?= $this->e($dir['fix']) ?></code>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PHP Extensions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-puzzle-line me-1"></i>
                    <?= $this->__('php_extensions') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($extensions as $ext): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-center justify-content-between p-2 rounded <?= $ext['status'] ? 'bg-success-subtle' : ($ext['required'] ? 'bg-danger-subtle' : 'bg-warning-subtle') ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?= $ext['loaded'] ? 'ri-check-line text-success' : ($ext['required'] ? 'ri-close-line text-danger' : 'ri-error-warning-line text-warning') ?> fs-5 me-2"></i>
                                <div>
                                    <span class="fw-medium"><?= $this->e($ext['name']) ?></span>
                                    <?php if (!$ext['required']): ?>
                                    <small class="text-muted">(<?= $this->__('optional') ?>)</small>
                                    <?php endif; ?>
                                    <div class="small text-muted"><?= $this->e($ext['description']) ?></div>
                                </div>
                            </div>
                            <span class="badge <?= $ext['loaded'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $ext['loaded'] ? $this->__('loaded') : $this->__('missing') ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Database -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-database-2-line me-1"></i>
                    <?= $this->__('database') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3 p-2 rounded <?= $database['status'] ? 'bg-success-subtle' : 'bg-danger-subtle' ?>">
                    <i class="<?= $database['status'] ? 'ri-check-line text-success' : 'ri-close-line text-danger' ?> fs-4 me-2"></i>
                    <span class="fw-medium"><?= $this->__('connection') ?>:</span>
                    <span class="ms-2"><?= $this->e($database['message']) ?></span>
                </div>
                <?php if ($database['status']): ?>
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 200px;"><?= $this->__('host') ?></th>
                                <td><?= $this->e($database['host']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('database_label') ?></th>
                                <td><?= $this->e($database['database']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('user') ?></th>
                                <td><?= $this->e($database['user']) ?></td>
                            </tr>
                            <tr>
                                <th><?= $this->__('response_time') ?></th>
                                <td><?= $this->e($database['response_time_ms']) ?>ms</td>
                            </tr>
                            <tr>
                                <th><?= $this->__('tables') ?></th>
                                <td><?= $this->e($database['table_count']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Migrations -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-stack-line me-1"></i>
                    <?= $this->__('migrations') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3 p-2 rounded <?= $migrations['status'] ? 'bg-success-subtle' : 'bg-warning-subtle' ?>">
                    <i class="<?= $migrations['status'] ? 'ri-check-line text-success' : 'ri-error-warning-line text-warning' ?> fs-4 me-2"></i>
                    <span class="fw-medium"><?= $this->__('status') ?>:</span>
                    <span class="ms-2">
                        <?= $migrations['status'] ? $this->__('up_to_date') : $migrations['pending_count'] . ' ' . $this->__('pending_label') ?>
                    </span>
                </div>

                <?php if (!empty($migrations['pending'])): ?>
                <div class="alert alert-warning">
                    <h6><?= $this->__('pending_migrations_title') ?>:</h6>
                    <ul class="mb-3">
                        <?php foreach ($migrations['pending'] as $m): ?>
                        <li><code><?= $this->e($m) ?></code></li>
                        <?php endforeach; ?>
                    </ul>

                    <form method="POST" action="/admin/diagnostics/run-migrations" class="d-inline">
                        <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                        <button type="submit" class="btn btn-warning">
                            <i class="ri-play-line me-1"></i>
                            <?= $this->__('run_pending_migrations') ?>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($migrations['current_version']): ?>
                <p class="mb-0"><?= $this->__('current_version') ?>: <code><?= $this->e($migrations['current_version']) ?></code></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Self Tests -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    <i class="ri-test-tube-line me-1"></i>
                    <?= $this->__('self_tests') ?>
                </h5>
                <button type="button" id="run-tests-btn" class="btn btn-primary">
                    <i class="ri-play-line me-1"></i>
                    <?= $this->__('run_self_tests') ?>
                </button>
            </div>
            <div class="card-body">
                <div id="self-tests-results"></div>
            </div>
        </div>

        <!-- Logs -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    <i class="ri-file-list-3-line me-1"></i>
                    <?= $this->__('recent_logs') ?>
                </h5>
                <a href="/admin/diagnostics/logs/download" class="btn btn-soft-secondary">
                    <i class="ri-download-line me-1"></i>
                    <?= $this->__('download_logs') ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentLogs)): ?>
                <div class="text-center py-4">
                    <i class="ri-file-list-3-line fs-1 text-muted"></i>
                    <p class="text-muted mt-2"><?= $this->__('no_log_entries_found') ?></p>
                </div>
                <?php else: ?>
                <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 12px;"><?php
                    foreach (array_reverse($recentLogs) as $line) {
                        $line = htmlspecialchars($line);
                        $line = preg_replace('/\[ERROR\]/', '<span class="text-danger fw-bold">[ERROR]</span>', $line);
                        $line = preg_replace('/\[WARNING\]/', '<span class="text-warning fw-bold">[WARNING]</span>', $line);
                        $line = preg_replace('/\[INFO\]/', '<span class="text-info fw-bold">[INFO]</span>', $line);
                        $line = preg_replace('/\[DEBUG\]/', '<span class="text-secondary">[DEBUG]</span>', $line);
                        echo $line . "\n";
                    }
                ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('run-tests-btn').addEventListener('click', function() {
    const btn = this;
    const results = document.getElementById('self-tests-results');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> <?= $this->__('running') ?>';
    results.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary"></div><p class="text-muted mt-2"><?= $this->__('running_tests') ?></p></div>';

    fetch('/admin/diagnostics/run-tests', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= $this->e($csrfToken) ?>'
        },
        body: '_csrf_token=<?= $this->e($csrfToken) ?>'
    })
    .then(response => response.json())
    .then(data => {
        let html = '';

        if (data.tests) {
            data.tests.forEach(test => {
                const bgClass = test.status ? 'bg-success-subtle' : 'bg-danger-subtle';
                const textClass = test.status ? 'text-success' : 'text-danger';
                const icon = test.status ? 'ri-check-line' : 'ri-close-line';
                html += `<div class="d-flex align-items-center justify-content-between py-2 border-bottom ${bgClass} px-3 rounded mb-2">
                    <div class="d-flex align-items-center">
                        <i class="${icon} ${textClass} fs-5 me-2"></i>
                        <span class="fw-medium">${test.name}</span>
                    </div>
                    <span class="text-muted">${test.message}</span>
                </div>`;
            });
        }

        if (data.all_passed) {
            html += '<div class="alert alert-success mt-3 mb-0"><i class="ri-check-double-line me-1"></i> <strong><?= $this->__('all_tests_passed') ?></strong></div>';
        } else {
            html += '<div class="alert alert-danger mt-3 mb-0"><i class="ri-error-warning-line me-1"></i> <strong><?= $this->__('some_tests_failed') ?></strong></div>';
        }

        results.innerHTML = html;
    })
    .catch(error => {
        results.innerHTML = '<div class="alert alert-danger"><?= $this->__('error_running_tests', ['message' => '']) ?>'.replace(':message', error.message) + '</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-play-line me-1"></i> <?= $this->__('run_self_tests') ?>';
    });
});
</script>

<?php $this->endSection(); ?>
