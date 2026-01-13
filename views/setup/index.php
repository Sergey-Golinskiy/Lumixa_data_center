<?php
$step = (int) ($step ?? 1);
?>

<div class="setup-steps">
    <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">
        <span class="step-number">1</span>
        <span class="step-title">Database</span>
    </div>
    <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">
        <span class="step-number">2</span>
        <span class="step-title">Admin User</span>
    </div>
    <div class="step <?= $step >= 3 ? 'active' : '' ?>">
        <span class="step-number">3</span>
        <span class="step-title">Complete</span>
    </div>
</div>

<?php if ($step === 1): ?>
<!-- Step 1: Database Configuration -->
<form action="<?= url('setup') ?>" method="POST" class="setup-form" id="dbForm">
    <?= csrfField() ?>
    <input type="hidden" name="step" value="1">

    <h3 class="setup-step-title">Database Configuration</h3>

    <div class="form-group">
        <label for="db_host" class="form-label">Database Host</label>
        <input type="text" id="db_host" name="db_host" class="form-control"
               value="<?= e(old('db_host', 'localhost')) ?>" required>
    </div>

    <div class="form-row">
        <div class="form-group col-6">
            <label for="db_port" class="form-label">Port</label>
            <input type="number" id="db_port" name="db_port" class="form-control"
                   value="<?= e(old('db_port', '3306')) ?>" required>
        </div>
        <div class="form-group col-6">
            <label for="db_name" class="form-label">Database Name</label>
            <input type="text" id="db_name" name="db_name" class="form-control"
                   value="<?= e(old('db_name', 'lms')) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="db_user" class="form-label">Username</label>
        <input type="text" id="db_user" name="db_user" class="form-control"
               value="<?= e(old('db_user', 'root')) ?>" required>
    </div>

    <div class="form-group">
        <label for="db_pass" class="form-label">Password</label>
        <input type="password" id="db_pass" name="db_pass" class="form-control"
               value="<?= e(old('db_pass')) ?>">
    </div>

    <div class="form-group">
        <button type="button" class="btn btn-secondary" id="testDbBtn">Test Connection</button>
        <span id="testResult" class="ml-2"></span>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Continue</button>
    </div>
</form>

<script>
document.getElementById('testDbBtn').addEventListener('click', async function() {
    const form = document.getElementById('dbForm');
    const formData = new FormData(form);
    const resultEl = document.getElementById('testResult');

    resultEl.textContent = 'Testing...';
    resultEl.className = 'ml-2';

    try {
        const response = await fetch('<?= url('setup/test-db') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            resultEl.textContent = 'Connection successful!';
            resultEl.className = 'ml-2 text-success';
            if (!data.database_exists) {
                resultEl.textContent += ' (Database will be created)';
            }
        } else {
            resultEl.textContent = 'Failed: ' + data.message;
            resultEl.className = 'ml-2 text-error';
        }
    } catch (e) {
        resultEl.textContent = 'Error: ' + e.message;
        resultEl.className = 'ml-2 text-error';
    }
});
</script>

<?php elseif ($step === 2): ?>
<!-- Step 2: Admin User -->
<form action="<?= url('setup') ?>" method="POST" class="setup-form">
    <?= csrfField() ?>
    <input type="hidden" name="step" value="2">

    <h3 class="setup-step-title">Create Admin User</h3>

    <div class="form-row">
        <div class="form-group col-6">
            <label for="admin_first_name" class="form-label">First Name *</label>
            <input type="text" id="admin_first_name" name="admin_first_name" class="form-control <?= isset($errors['admin_first_name']) ? 'is-invalid' : '' ?>"
                   value="<?= e(old('admin_first_name')) ?>" required>
            <?php if (isset($errors['admin_first_name'])): ?>
            <div class="invalid-feedback"><?= e($errors['admin_first_name']) ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group col-6">
            <label for="admin_last_name" class="form-label">Last Name</label>
            <input type="text" id="admin_last_name" name="admin_last_name" class="form-control"
                   value="<?= e(old('admin_last_name')) ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="admin_username" class="form-label">Username *</label>
        <input type="text" id="admin_username" name="admin_username" class="form-control <?= isset($errors['admin_username']) ? 'is-invalid' : '' ?>"
               value="<?= e(old('admin_username')) ?>" required>
        <?php if (isset($errors['admin_username'])): ?>
        <div class="invalid-feedback"><?= e($errors['admin_username']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="admin_email" class="form-label">Email *</label>
        <input type="email" id="admin_email" name="admin_email" class="form-control <?= isset($errors['admin_email']) ? 'is-invalid' : '' ?>"
               value="<?= e(old('admin_email')) ?>" required>
        <?php if (isset($errors['admin_email'])): ?>
        <div class="invalid-feedback"><?= e($errors['admin_email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="admin_password" class="form-label">Password * (min 8 characters)</label>
        <input type="password" id="admin_password" name="admin_password" class="form-control <?= isset($errors['admin_password']) ? 'is-invalid' : '' ?>"
               required minlength="8">
        <?php if (isset($errors['admin_password'])): ?>
        <div class="invalid-feedback"><?= e($errors['admin_password']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="admin_password_confirm" class="form-label">Confirm Password *</label>
        <input type="password" id="admin_password_confirm" name="admin_password_confirm" class="form-control <?= isset($errors['admin_password_confirm']) ? 'is-invalid' : '' ?>"
               required>
        <?php if (isset($errors['admin_password_confirm'])): ?>
        <div class="invalid-feedback"><?= e($errors['admin_password_confirm']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <a href="<?= url('setup?step=1') ?>" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-primary">Continue</button>
    </div>
</form>

<?php elseif ($step === 3): ?>
<!-- Step 3: Complete Setup -->
<form action="<?= url('setup') ?>" method="POST" class="setup-form">
    <?= csrfField() ?>
    <input type="hidden" name="step" value="3">

    <h3 class="setup-step-title">Complete Setup</h3>

    <div class="form-group">
        <label for="app_name" class="form-label">Application Name</label>
        <input type="text" id="app_name" name="app_name" class="form-control"
               value="<?= e(old('app_name', 'Lumixa Manufacturing System')) ?>">
    </div>

    <div class="form-group">
        <label for="app_url" class="form-label">Application URL</label>
        <input type="text" id="app_url" name="app_url" class="form-control"
               value="<?= e(old('app_url', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'])) ?>"
               placeholder="https://lms.lumixa.io">
        <small class="form-text">Leave empty to auto-detect</small>
    </div>

    <div class="alert alert-info">
        <strong>Ready to install!</strong><br>
        This will:
        <ul>
            <li>Create database tables</li>
            <li>Initialize system data</li>
            <li>Create admin user</li>
        </ul>
    </div>

    <div class="form-group">
        <a href="<?= url('setup?step=2') ?>" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-primary">Complete Setup</button>
    </div>
</form>
<?php endif; ?>
