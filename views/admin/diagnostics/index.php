<?php $this->section('content'); ?>

<div class="diagnostics">
    <?php if (!$isDebug): ?>
    <div class="alert alert-warning">
        <strong>Debug Mode Disabled.</strong>
        Some features are limited. Enable APP_DEBUG in config to see full diagnostics.
    </div>
    <?php endif; ?>

    <!-- Environment Info -->
    <div class="diag-section">
        <h2>Environment</h2>
        <div class="diag-table">
            <table>
                <tbody>
                    <tr>
                        <th>PHP Version</th>
                        <td><?= $this->e($environment['php_version']) ?></td>
                    </tr>
                    <tr>
                        <th>PHP SAPI</th>
                        <td><?= $this->e($environment['php_sapi']) ?></td>
                    </tr>
                    <tr>
                        <th>App Version</th>
                        <td><?= $this->e($environment['app_version']) ?></td>
                    </tr>
                    <tr>
                        <th>Environment</th>
                        <td>
                            <span class="badge badge-<?= $environment['app_env'] === 'prod' ? 'success' : 'warning' ?>">
                                <?= $this->e($environment['app_env']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Debug Mode</th>
                        <td>
                            <span class="badge badge-<?= $environment['app_debug'] ? 'warning' : 'success' ?>">
                                <?= $environment['app_debug'] ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Server Time</th>
                        <td><?= $this->e($environment['server_time']) ?></td>
                    </tr>
                    <tr>
                        <th>Timezone</th>
                        <td><?= $this->e($environment['timezone']) ?></td>
                    </tr>
                    <tr>
                        <th>Memory Limit</th>
                        <td><?= $this->e($environment['memory_limit']) ?></td>
                    </tr>
                    <tr>
                        <th>Max Execution Time</th>
                        <td><?= $this->e($environment['max_execution_time']) ?>s</td>
                    </tr>
                    <tr>
                        <th>Upload Max Size</th>
                        <td><?= $this->e($environment['upload_max_filesize']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Directories -->
    <div class="diag-section">
        <h2>Directories</h2>
        <div class="diag-checks">
            <?php foreach ($directories as $name => $dir): ?>
            <div class="check-row <?= $dir['status'] ? 'check-pass' : 'check-fail' ?>">
                <span class="check-icon"><?= $dir['status'] ? '&#10003;' : '&#10007;' ?></span>
                <span class="check-name"><?= $this->e($name) ?></span>
                <span class="check-status"><?= $dir['writable'] ? 'Writable' : ($dir['exists'] ? 'Not Writable' : 'Missing') ?></span>
                <?php if (!$dir['status'] && $dir['fix']): ?>
                <code class="check-fix"><?= $this->e($dir['fix']) ?></code>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PHP Extensions -->
    <div class="diag-section">
        <h2>PHP Extensions</h2>
        <div class="diag-checks">
            <?php foreach ($extensions as $ext): ?>
            <div class="check-row <?= $ext['status'] ? 'check-pass' : ($ext['required'] ? 'check-fail' : 'check-warn') ?>">
                <span class="check-icon">
                    <?= $ext['loaded'] ? '&#10003;' : ($ext['required'] ? '&#10007;' : '&#9888;') ?>
                </span>
                <span class="check-name">
                    <?= $this->e($ext['name']) ?>
                    <?php if (!$ext['required']): ?><small>(optional)</small><?php endif; ?>
                </span>
                <span class="check-status"><?= $ext['loaded'] ? 'Loaded' : 'Missing' ?></span>
                <span class="check-desc"><?= $this->e($ext['description']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Database -->
    <div class="diag-section">
        <h2>Database</h2>
        <div class="check-row <?= $database['status'] ? 'check-pass' : 'check-fail' ?>">
            <span class="check-icon"><?= $database['status'] ? '&#10003;' : '&#10007;' ?></span>
            <span class="check-name">Connection</span>
            <span class="check-status"><?= $this->e($database['message']) ?></span>
        </div>
        <?php if ($database['status']): ?>
        <div class="diag-table">
            <table>
                <tbody>
                    <tr>
                        <th>Host</th>
                        <td><?= $this->e($database['host']) ?></td>
                    </tr>
                    <tr>
                        <th>Database</th>
                        <td><?= $this->e($database['database']) ?></td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td><?= $this->e($database['user']) ?></td>
                    </tr>
                    <tr>
                        <th>Response Time</th>
                        <td><?= $this->e($database['response_time_ms']) ?>ms</td>
                    </tr>
                    <tr>
                        <th>Tables</th>
                        <td><?= $this->e($database['table_count']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Migrations -->
    <div class="diag-section">
        <h2>Migrations</h2>
        <div class="check-row <?= $migrations['status'] ? 'check-pass' : 'check-warn' ?>">
            <span class="check-icon"><?= $migrations['status'] ? '&#10003;' : '&#9888;' ?></span>
            <span class="check-name">Status</span>
            <span class="check-status">
                <?= $migrations['status'] ? 'Up to date' : $migrations['pending_count'] . ' pending' ?>
            </span>
        </div>

        <?php if (!empty($migrations['pending'])): ?>
        <div class="pending-migrations">
            <h4>Pending Migrations:</h4>
            <ul>
                <?php foreach ($migrations['pending'] as $m): ?>
                <li><?= $this->e($m) ?></li>
                <?php endforeach; ?>
            </ul>

            <form method="POST" action="/admin/diagnostics/run-migrations" class="inline-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-warning">Run Pending Migrations</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($migrations['current_version']): ?>
        <p>Current version: <code><?= $this->e($migrations['current_version']) ?></code></p>
        <?php endif; ?>
    </div>

    <!-- Self Tests -->
    <div class="diag-section">
        <h2>Self Tests</h2>
        <div id="self-tests-results"></div>
        <button type="button" id="run-tests-btn" class="btn btn-primary">
            Run Self Tests
        </button>
    </div>

    <!-- Logs -->
    <div class="diag-section">
        <h2>Recent Logs</h2>
        <div class="logs-actions">
            <a href="/admin/diagnostics/logs/download" class="btn btn-secondary">Download Logs</a>
        </div>
        <div class="logs-viewer">
            <?php if (empty($recentLogs)): ?>
            <p class="text-muted">No log entries found.</p>
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
    btn.textContent = 'Running...';
    results.innerHTML = '<p>Running tests...</p>';

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
            html += '<p class="text-success"><strong>All tests passed!</strong></p>';
        } else {
            html += '<p class="text-danger"><strong>Some tests failed.</strong></p>';
        }

        results.innerHTML = html;
    })
    .catch(error => {
        results.innerHTML = '<p class="text-danger">Error running tests: ' + error.message + '</p>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Run Self Tests';
    });
});
</script>

<?php $this->endSection(); ?>
