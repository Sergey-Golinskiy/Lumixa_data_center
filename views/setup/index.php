<?php $this->section('content'); ?>

<div class="setup-steps">
    <!-- Step 1: Environment Checks -->
    <div class="setup-step">
        <h2>Step 1: Environment Checks</h2>

        <!-- PHP Version -->
        <div class="check-section">
            <h3>PHP Version</h3>
            <div class="check-item <?= $checks['php_version']['status'] ? 'check-pass' : 'check-fail' ?>">
                <span class="check-name"><?= $this->e($checks['php_version']['name']) ?></span>
                <span class="check-value">
                    Required: <?= $this->e($checks['php_version']['required']) ?> |
                    Current: <?= $this->e($checks['php_version']['current']) ?>
                </span>
                <span class="check-status"><?= $checks['php_version']['status'] ? '&#10003;' : '&#10007;' ?></span>
            </div>
            <?php if (!$checks['php_version']['status'] && $checks['php_version']['fix']): ?>
            <div class="check-fix">
                <strong>Fix:</strong> <?= $this->e($checks['php_version']['fix']) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- PHP Extensions -->
        <div class="check-section">
            <h3>PHP Extensions</h3>
            <?php foreach ($checks['extensions'] as $ext): ?>
            <div class="check-item <?= $ext['status'] ? 'check-pass' : ($ext['required'] ? 'check-fail' : 'check-warn') ?>">
                <span class="check-name">
                    <?= $this->e($ext['name']) ?>
                    <?php if (!$ext['required']): ?><small>(optional)</small><?php endif; ?>
                </span>
                <span class="check-value"><?= $this->e($ext['description']) ?></span>
                <span class="check-status"><?= $ext['status'] ? '&#10003;' : ($ext['required'] ? '&#10007;' : '&#9888;') ?></span>
            </div>
            <?php if (!$ext['status'] && $ext['fix']): ?>
            <div class="check-fix">
                <code><?= $this->e($ext['fix']) ?></code>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Directories -->
        <div class="check-section">
            <h3>Writable Directories</h3>
            <?php foreach ($checks['directories'] as $dir): ?>
            <div class="check-item <?= $dir['status'] ? 'check-pass' : 'check-fail' ?>">
                <span class="check-name"><?= $this->e($dir['name']) ?></span>
                <span class="check-value"><?= $this->e($dir['message']) ?></span>
                <span class="check-status"><?= $dir['status'] ? '&#10003;' : '&#10007;' ?></span>
            </div>
            <?php if (!$dir['status'] && $dir['fix']): ?>
            <div class="check-fix">
                <code><?= $this->e($dir['fix']) ?></code>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Timezone -->
        <div class="check-section">
            <h3>Timezone</h3>
            <div class="check-item <?= $checks['timezone']['status'] ? 'check-pass' : 'check-fail' ?>">
                <span class="check-name"><?= $this->e($checks['timezone']['name']) ?></span>
                <span class="check-value"><?= $this->e($checks['timezone']['current']) ?></span>
                <span class="check-status"><?= $checks['timezone']['status'] ? '&#10003;' : '&#10007;' ?></span>
            </div>
        </div>
    </div>

    <!-- Step 2: Database Configuration -->
    <div class="setup-step">
        <h2>Step 2: Database Configuration</h2>

        <form method="POST" action="/setup" id="setup-form">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host"
                           value="<?= $this->e($this->old('db_host', 'localhost')) ?>"
                           placeholder="localhost" required>
                    <?php if ($this->hasError('db_host')): ?>
                    <span class="error"><?= $this->e($this->error('db_host')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group form-group-small">
                    <label for="db_port">Port</label>
                    <input type="number" id="db_port" name="db_port"
                           value="<?= $this->e($this->old('db_port', '3306')) ?>"
                           placeholder="3306">
                </div>
            </div>

            <div class="form-group">
                <label for="db_name">Database Name</label>
                <input type="text" id="db_name" name="db_name"
                       value="<?= $this->e($this->old('db_name', 'lms_db')) ?>"
                       placeholder="lms_db" required>
                <?php if ($this->hasError('db_name')): ?>
                <span class="error"><?= $this->e($this->error('db_name')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="db_user">Database User</label>
                    <input type="text" id="db_user" name="db_user"
                           value="<?= $this->e($this->old('db_user')) ?>"
                           placeholder="username" required>
                    <?php if ($this->hasError('db_user')): ?>
                    <span class="error"><?= $this->e($this->error('db_user')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass"
                           placeholder="password">
                </div>
            </div>

            <?php if (isset($checks['database'])): ?>
            <div class="check-item <?= $checks['database']['status'] ? 'check-pass' : 'check-fail' ?>">
                <span class="check-name">Database Connection</span>
                <span class="check-value"><?= $this->e($checks['database']['message']) ?></span>
                <span class="check-status"><?= $checks['database']['status'] ? '&#10003;' : '&#10007;' ?></span>
            </div>
            <?php endif; ?>

            <hr>

            <h3>Administrator Account</h3>

            <div class="form-group">
                <label for="admin_name">Admin Name</label>
                <input type="text" id="admin_name" name="admin_name"
                       value="<?= $this->e($this->old('admin_name', 'Administrator')) ?>"
                       placeholder="Administrator" required>
            </div>

            <div class="form-group">
                <label for="admin_email">Admin Email</label>
                <input type="email" id="admin_email" name="admin_email"
                       value="<?= $this->e($this->old('admin_email')) ?>"
                       placeholder="admin@example.com" required>
                <?php if ($this->hasError('admin_email')): ?>
                <span class="error"><?= $this->e($this->error('admin_email')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="admin_password">Admin Password</label>
                <input type="password" id="admin_password" name="admin_password"
                       placeholder="Minimum 8 characters" required minlength="8">
                <?php if ($this->hasError('admin_password')): ?>
                <span class="error"><?= $this->e($this->error('admin_password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" name="dry_run" value="1" class="btn btn-secondary">
                    Dry Run (Check Only)
                </button>

                <button type="submit" class="btn btn-primary" <?= !$allPassed ? 'disabled' : '' ?>>
                    Install
                </button>
            </div>

            <?php if (!$allPassed): ?>
            <p class="text-warning">
                Please fix all environment issues before installing.
            </p>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
