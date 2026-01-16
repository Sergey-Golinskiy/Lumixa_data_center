<?php $this->section('content'); ?>

<div class="diagnostics">
    <?php if (!$isDebug): ?>
    <div class="alert alert-warning">
        <strong><?= $this->__('debug_mode_disabled') ?></strong>
        <?= $this->__('debug_mode_disabled_note') ?>
    </div>
    <?php endif; ?>

    <!-- Environment Info -->
    <div class="diag-section">
        <h2><?= $this->__('environment') ?></h2>
        <div class="diag-table">
            <table>
                <tbody>
                    <tr>
                        <th><?= $this->__('php_version') ?></th>
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
                            <span class="badge badge-<?= $environment['app_env'] === 'prod' ? 'success' : 'warning' ?>">
                                <?= $this->e($environment['app_env']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $this->__('debug_mode') ?></th>
                        <td>
                            <span class="badge badge-<?= $environment['app_debug'] ? 'warning' : 'success' ?>">
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

    <!-- Directories -->
    <div class="diag-section">
        <h2><?= $this->__('directories') ?></h2>
        <div class="diag-checks">
            <?php foreach ($directories as $name => $dir): ?>
            <div class="check-row <?= $dir['status'] ? 'check-pass' : 'check-fail' ?>">
                <span class="check-icon"><?= $dir['status'] ? '&#10003;' : '&#10007;' ?></span>
                <span class="check-name"><?= $this->e($name) ?></span>
                <span class="check-status">
                    <?= $dir['writable'] ? $this->__('writable') : ($dir['exists'] ? $this->__('not_writable') : $this->__('missing')) ?>
                </span>
                <?php if (!$dir['status'] && $dir['fix']): ?>
                <code class="check-fix"><?= $this->e($dir['fix']) ?></code>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PHP Extensions -->
    <div class="diag-section">
        <h2><?= $this->__('php_extensions') ?></h2>
        <div class="diag-checks">
            <?php foreach ($extensions as $ext): ?>
            <div class="check-row <?= $ext['status'] ? 'check-pass' : ($ext['required'] ? 'check-fail' : 'check-warn') ?>">
                <span class="check-icon">
                    <?= $ext['loaded'] ? '&#10003;' : ($ext['required'] ? '&#10007;' : '&#9888;') ?>
                </span>
                <span class="check-name">
                    <?= $this->e($ext['name']) ?>
                    <?php if (!$ext['required']): ?><small>(<?= $this->__('optional') ?>)</small><?php endif; ?>
                </span>
                <span class="check-status"><?= $ext['loaded'] ? $this->__('loaded') : $this->__('missing') ?></span>
                <span class="check-desc"><?= $this->e($ext['description']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Database -->
    <div class="diag-section">
        <h2><?= $this->__('database') ?></h2>
        <div class="check-row <?= $database['status'] ? 'check-pass' : 'check-fail' ?>">
            <span class="check-icon"><?= $database['status'] ? '&#10003;' : '&#10007;' ?></span>
            <span class="check-name"><?= $this->__('connection') ?></span>
            <span class="check-status"><?= $this->e($database['message']) ?></span>
        </div>
        <?php if ($database['status']): ?>
        <div class="diag-table">
            <table>
                <tbody>
                    <tr>
                        <th><?= $this->__('host') ?></th>
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

    <!-- Migrations -->
    <div class="diag-section">
        <h2><?= $this->__('migrations') ?></h2>
        <div class="check-row <?= $migrations['status'] ? 'check-pass' : 'check-warn' ?>">
            <span class="check-icon"><?= $migrations['status'] ? '&#10003;' : '&#9888;' ?></span>
            <span class="check-name"><?= $this->__('status') ?></span>
            <span class="check-status">
                <?= $migrations['status'] ? $this->__('up_to_date') : $migrations['pending_count'] . ' ' . $this->__('pending_label') ?>
            </span>
        </div>

        <?php if (!empty($migrations['pending'])): ?>
        <div class="pending-migrations">
            <h4><?= $this->__('pending_migrations_title') ?>:</h4>
            <ul>
                <?php foreach ($migrations['pending'] as $m): ?>
                <li><?= $this->e($m) ?></li>
                <?php endforeach; ?>
            </ul>

            <form method="POST" action="/admin/diagnostics/run-migrations" class="inline-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-warning"><?= $this->__('run_pending_migrations') ?></button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($migrations['current_version']): ?>
        <p><?= $this->__('current_version') ?>: <code><?= $this->e($migrations['current_version']) ?></code></p>
        <?php endif; ?>
    </div>

    <!-- Self Tests -->
    <div class="diag-section">
        <h2><?= $this->__('self_tests') ?></h2>
        <div id="self-tests-results"></div>
        <button type="button" id="run-tests-btn" class="btn btn-primary">
            <?= $this->__('run_self_tests') ?>
        </button>
    </div>

    <!-- Logs -->
    <div class="diag-section">
        <h2><?= $this->__('recent_logs') ?></h2>
        <div class="logs-actions">
            <a href="/admin/diagnostics/logs/download" class="btn btn-secondary"><?= $this->__('download_logs') ?></a>
        </div>
        <div class="logs-viewer">
            <?php if (empty($recentLogs)): ?>
            <p class="text-muted"><?= $this->__('no_log_entries_found') ?></p>
            <?php else: ?>
            <pre class="log-content"><?php
                foreach (array_reverse($recentLogs) as $line) {
                    // Highlight log levels
                    $line = htmlspecialchars($line);
                    $line = preg_replace('/\[ERROR\]/', '<span class="log-error">[ERROR]</span>', $line);
                    $line = preg_replace('/\[WARNING\]/', '<span class="log-warning">[WARNING]</span>', $line);
                    $line = preg_replace('/\[INFO\]/', '<span class="log-info">[INFO]</span>', $line);
                    $line = preg_replace('/\[DEBUG\]/', '<span class="log-debug">[DEBUG]</span>', $line);
                    echo $line . "\n";
                }
            ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('run-tests-btn').addEventListener('click', function() {
    const btn = this;
    const results = document.getElementById('self-tests-results');

    btn.disabled = true;
    btn.textContent = '<?= $this->__('running') ?>';
    results.innerHTML = '<p><?= $this->__('running_tests') ?></p>';

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
        let html = '<div class="test-results">';

        if (data.tests) {
            data.tests.forEach(test => {
                const cls = test.status ? 'check-pass' : 'check-fail';
                const icon = test.status ? '&#10003;' : '&#10007;';
                html += `<div class="check-row ${cls}">
                    <span class="check-icon">${icon}</span>
                    <span class="check-name">${test.name}</span>
                    <span class="check-status">${test.message}</span>
                </div>`;
            });
        }

        html += '</div>';

        if (data.all_passed) {
            html += '<p class="text-success"><strong><?= $this->__('all_tests_passed') ?></strong></p>';
        } else {
            html += '<p class="text-danger"><strong><?= $this->__('some_tests_failed') ?></strong></p>';
        }

        results.innerHTML = html;
    })
    .catch(error => {
        results.innerHTML = '<p class="text-danger"><?= $this->__('error_running_tests', ['message' => '']) ?>'.replace(':message', error.message) + '</p>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = '<?= $this->__('run_self_tests') ?>';
    });
});
</script>

<?php $this->endSection(); ?>
