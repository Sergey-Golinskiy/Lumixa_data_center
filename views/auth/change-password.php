<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-lock-password-line me-2"></i><?= $this->__('change_password') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/change-password">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="mb-3">
                        <label for="current_password" class="form-label"><?= $this->__('current_password') ?></label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control pe-5 password-input <?= $this->hasError('current_password') ? 'is-invalid' : '' ?>" required>
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button">
                                <i class="ri-eye-fill align-middle"></i>
                            </button>
                            <?php if ($this->hasError('current_password')): ?>
                            <div class="invalid-feedback"><?= $this->e($this->error('current_password')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label"><?= $this->__('new_password') ?></label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input type="password" id="new_password" name="new_password"
                                   class="form-control pe-5 password-input <?= $this->hasError('new_password') ? 'is-invalid' : '' ?>" required minlength="8">
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button">
                                <i class="ri-eye-fill align-middle"></i>
                            </button>
                            <?php if ($this->hasError('new_password')): ?>
                            <div class="invalid-feedback"><?= $this->e($this->error('new_password')) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text"><?= $this->__('min_characters', ['count' => 8]) ?></div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label"><?= $this->__('confirm_password') ?></label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="form-control pe-5 password-input <?= $this->hasError('confirm_password') ? 'is-invalid' : '' ?>" required>
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button">
                                <i class="ri-eye-fill align-middle"></i>
                            </button>
                            <?php if ($this->hasError('confirm_password')): ?>
                            <div class="invalid-feedback"><?= $this->e($this->error('confirm_password')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-lock-password-line me-1"></i> <?= $this->__('change_password') ?>
                        </button>
                        <a href="/" class="btn btn-soft-secondary"><?= $this->__('cancel') ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.password-addon').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = this.closest('.auth-pass-inputgroup').querySelector('.password-input');
        var icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ri-eye-fill');
            icon.classList.add('ri-eye-off-fill');
        } else {
            input.type = 'password';
            icon.classList.remove('ri-eye-off-fill');
            icon.classList.add('ri-eye-fill');
        }
    });
});
</script>

<?php $this->endSection(); ?>
