<?php $this->section('content'); ?>

<form method="POST" action="/login">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="mb-3">
        <label for="email" class="form-label"><?= $this->__('email') ?></label>
        <input type="email" class="form-control <?= $this->hasError('email') ? 'is-invalid' : '' ?>" id="email" name="email"
               value="<?= $this->e($this->old('email')) ?>"
               placeholder="<?= $this->__('enter_email') ?? 'your@email.com' ?>"
               required autofocus>
        <?php if ($this->hasError('email')): ?>
        <div class="invalid-feedback"><?= $this->e($this->error('email')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label" for="password"><?= $this->__('password') ?></label>
        <div class="position-relative auth-pass-inputgroup mb-3">
            <input type="password" class="form-control pe-5 password-input <?= $this->hasError('password') ? 'is-invalid' : '' ?>"
                   id="password" name="password"
                   placeholder="<?= $this->__('enter_password') ?? 'Enter password' ?>"
                   required>
            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button">
                <i class="ri-eye-fill align-middle"></i>
            </button>
            <?php if ($this->hasError('password')): ?>
            <div class="invalid-feedback"><?= $this->e($this->error('password')) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="remember" value="1" id="auth-remember-check">
        <label class="form-check-label" for="auth-remember-check"><?= $this->__('remember_me') ?></label>
    </div>

    <div class="mt-4">
        <button class="btn btn-success w-100" type="submit"><?= $this->__('sign_in') ?></button>
    </div>
</form>

<?php $this->endSection(); ?>
