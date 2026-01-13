<?php $this->section('content'); ?>

<div class="card">
    <div class="card-header">Change Password</div>
    <div class="card-body">
        <form method="POST" action="/change-password">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
                <?php if ($this->hasError('current_password')): ?>
                <span class="error"><?= $this->e($this->error('current_password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8">
                <small>Minimum 8 characters</small>
                <?php if ($this->hasError('new_password')): ?>
                <span class="error"><?= $this->e($this->error('new_password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if ($this->hasError('confirm_password')): ?>
                <span class="error"><?= $this->e($this->error('confirm_password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Change Password</button>
                <a href="/" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
