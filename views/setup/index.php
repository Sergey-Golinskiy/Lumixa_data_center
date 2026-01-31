<?php $this->section('content'); ?>

<!-- Step 1: Environment Checks -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-settings-3-line me-2"></i>Step 1: Environment Checks</h5>
    </div>
    <div class="card-body">

        <!-- PHP Version -->
        <h6 class="mb-3">PHP Version</h6>
        <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2 <?= $checks['php_version']['status'] ? 'bg-success-subtle' : 'bg-danger-subtle' ?>">
            <div>
                <strong><?= $this->e($checks['php_version']['name']) ?></strong>
                <span class="text-muted ms-2">Required: <?= $this->e($checks['php_version']['required']) ?> | Current: <?= $this->e($checks['php_version']['current']) ?></span>
            </div>
            <span class="<?= $checks['php_version']['status'] ? 'text-success' : 'text-danger' ?>">
                <i class="ri-<?= $checks['php_version']['status'] ? 'checkbox-circle' : 'close-circle' ?>-fill fs-18"></i>
            </span>
        </div>
        <?php if (!$checks['php_version']['status'] && $checks['php_version']['fix']): ?>
        <div class="alert alert-danger mb-3"><strong>Fix:</strong> <?= $this->e($checks['php_version']['fix']) ?></div>
        <?php endif; ?>

        <!-- PHP Extensions -->
        <h6 class="mb-3 mt-4">PHP Extensions</h6>
        <?php foreach ($checks['extensions'] as $ext): ?>
        <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2 <?= $ext['status'] ? 'bg-success-subtle' : ($ext['required'] ? 'bg-danger-subtle' : 'bg-warning-subtle') ?>">
            <div>
                <strong><?= $this->e($ext['name']) ?></strong>
                <?php if (!$ext['required']): ?><span class="badge bg-secondary-subtle text-secondary ms-1">optional</span><?php endif; ?>
                <span class="text-muted ms-2"><?= $this->e($ext['description']) ?></span>
            </div>
            <span class="<?= $ext['status'] ? 'text-success' : ($ext['required'] ? 'text-danger' : 'text-warning') ?>">
                <i class="ri-<?= $ext['status'] ? 'checkbox-circle' : ($ext['required'] ? 'close-circle' : 'alert') ?>-fill fs-18"></i>
            </span>
        </div>
        <?php if (!$ext['status'] && $ext['fix']): ?>
        <div class="alert alert-<?= $ext['required'] ? 'danger' : 'warning' ?> mb-2"><code><?= $this->e($ext['fix']) ?></code></div>
        <?php endif; ?>
        <?php endforeach; ?>

        <!-- Writable Directories -->
        <h6 class="mb-3 mt-4">Writable Directories</h6>
        <?php foreach ($checks['directories'] as $dir): ?>
        <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2 <?= $dir['status'] ? 'bg-success-subtle' : 'bg-danger-subtle' ?>">
            <div>
                <strong><?= $this->e($dir['name']) ?></strong>
                <span class="text-muted ms-2"><?= $this->e($dir['message']) ?></span>
            </div>
            <span class="<?= $dir['status'] ? 'text-success' : 'text-danger' ?>">
                <i class="ri-<?= $dir['status'] ? 'checkbox-circle' : 'close-circle' ?>-fill fs-18"></i>
            </span>
        </div>
        <?php if (!$dir['status'] && $dir['fix']): ?>
        <div class="alert alert-danger mb-2"><code><?= $this->e($dir['fix']) ?></code></div>
        <?php endif; ?>
        <?php endforeach; ?>

        <!-- Timezone -->
        <h6 class="mb-3 mt-4">Timezone</h6>
        <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2 <?= $checks['timezone']['status'] ? 'bg-success-subtle' : 'bg-danger-subtle' ?>">
            <div>
                <strong><?= $this->e($checks['timezone']['name']) ?></strong>
                <span class="text-muted ms-2"><?= $this->e($checks['timezone']['current']) ?></span>
            </div>
            <span class="<?= $checks['timezone']['status'] ? 'text-success' : 'text-danger' ?>">
                <i class="ri-<?= $checks['timezone']['status'] ? 'checkbox-circle' : 'close-circle' ?>-fill fs-18"></i>
            </span>
        </div>
    </div>
</div>

<!-- Step 2: Database Configuration -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-database-2-line me-2"></i>Step 2: Database Configuration</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/setup" id="setup-form">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="db_host" class="form-label">Database Host</label>
                    <input type="text" id="db_host" name="db_host" class="form-control"
                           value="<?= $this->e($this->old('db_host', 'localhost')) ?>"
                           placeholder="localhost" required>
                    <?php if ($this->hasError('db_host')): ?>
                    <div class="invalid-feedback d-block"><?= $this->e($this->error('db_host')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="db_port" class="form-label">Port</label>
                    <input type="number" id="db_port" name="db_port" class="form-control"
                           value="<?= $this->e($this->old('db_port', '3306')) ?>"
                           placeholder="3306">
                </div>
            </div>

            <div class="mb-3">
                <label for="db_name" class="form-label">Database Name</label>
                <input type="text" id="db_name" name="db_name" class="form-control"
                       value="<?= $this->e($this->old('db_name', 'lms_db')) ?>"
                       placeholder="lms_db" required>
                <?php if ($this->hasError('db_name')): ?>
                <div class="invalid-feedback d-block"><?= $this->e($this->error('db_name')) ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="db_user" class="form-label">Database User</label>
                    <input type="text" id="db_user" name="db_user" class="form-control"
                           value="<?= $this->e($this->old('db_user')) ?>"
                           placeholder="username" required>
                    <?php if ($this->hasError('db_user')): ?>
                    <div class="invalid-feedback d-block"><?= $this->e($this->error('db_user')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="db_pass" class="form-label">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass" class="form-control" placeholder="password">
                </div>
            </div>

            <?php if (isset($checks['database'])): ?>
            <div class="d-flex align-items-center justify-content-between p-2 rounded mb-3 <?= $checks['database']['status'] ? 'bg-success-subtle' : 'bg-danger-subtle' ?>">
                <div>
                    <strong>Database Connection</strong>
                    <span class="text-muted ms-2"><?= $this->e($checks['database']['message']) ?></span>
                </div>
                <span class="<?= $checks['database']['status'] ? 'text-success' : 'text-danger' ?>">
                    <i class="ri-<?= $checks['database']['status'] ? 'checkbox-circle' : 'close-circle' ?>-fill fs-18"></i>
                </span>
            </div>
            <?php endif; ?>

            <hr class="my-4">

            <h5 class="mb-3"><i class="ri-user-add-line me-2"></i>Administrator Account</h5>

            <div class="mb-3">
                <label for="admin_name" class="form-label">Admin Name</label>
                <input type="text" id="admin_name" name="admin_name" class="form-control"
                       value="<?= $this->e($this->old('admin_name', 'Administrator')) ?>"
                       placeholder="Administrator" required>
            </div>

            <div class="mb-3">
                <label for="admin_email" class="form-label">Admin Email</label>
                <input type="email" id="admin_email" name="admin_email" class="form-control"
                       value="<?= $this->e($this->old('admin_email')) ?>"
                       placeholder="admin@example.com" required>
                <?php if ($this->hasError('admin_email')): ?>
                <div class="invalid-feedback d-block"><?= $this->e($this->error('admin_email')) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="admin_password" class="form-label">Admin Password</label>
                <input type="password" id="admin_password" name="admin_password" class="form-control"
                       placeholder="Minimum 8 characters" required minlength="8">
                <?php if ($this->hasError('admin_password')): ?>
                <div class="invalid-feedback d-block"><?= $this->e($this->error('admin_password')) ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="dry_run" value="1" class="btn btn-soft-secondary">
                    <i class="ri-test-tube-line me-1"></i> Dry Run (Check Only)
                </button>

                <button type="submit" class="btn btn-success" <?= !$allPassed ? 'disabled' : '' ?>>
                    <i class="ri-install-line me-1"></i> Install
                </button>
            </div>

            <?php if (!$allPassed): ?>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-1"></i> Please fix all environment issues before installing.
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
