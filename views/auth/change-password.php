<?php $this->section('content'); ?>

<div class="card">
    <div class="card-header"><?= $this->__('change_password') ?></div>
    <div class="card-body">
        <form method="POST" action="/change-password">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="current_password"><?= $this->__('current_password') ?></label>
                <input type="password" id="current_password" name="current_password" required>
                <?php if ($this->hasError('current_password')): ?>
                <span class="error"><?= $this->e($this->error('current_password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_password"><?= $this->__('new_password') ?></label>
                <input type="password" id="new_password" name="new_password" required minlength="8">
                <small><?= $this->__('min_characters', ['count' => 8]) ?></small>
                <?php if ($this->hasError('new_password')): ?>
                <span class="error"><?= $this->e($this->error('new_password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password"><?= $this->__('confirm_password') ?></label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if ($this->hasError('confirm_password')): ?>
                <span class="error"><?= $this->e($this->error('confirm_password')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $this->__('change_password') ?></button>
                <a href="/" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
