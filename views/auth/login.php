<?php $this->section('content'); ?>

<form method="POST" action="/login" class="auth-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="form-group">
        <label for="email"><?= $this->__('email') ?></label>
        <input type="email" id="email" name="email"
               value="<?= $this->e($this->old('email')) ?>"
               placeholder="your@email.com"
               required autofocus>
        <?php if ($this->hasError('email')): ?>
        <span class="error"><?= $this->e($this->error('email')) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password"><?= $this->__('password') ?></label>
        <input type="password" id="password" name="password"
               required>
        <?php if ($this->hasError('password')): ?>
        <span class="error"><?= $this->e($this->error('password')) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group form-checkbox">
        <label>
            <input type="checkbox" name="remember" value="1">
            <span><?= $this->__('remember_me') ?></span>
        </label>
    </div>

    <button type="submit" class="btn btn-primary btn-block">
        <?= $this->__('sign_in') ?>
    </button>
</form>

<?php $this->endSection(); ?>
