<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-user-line me-2"></i><?= $this->__('my_profile') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/profile">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label"><?= $this->__('email') ?></label>
                        <input type="email" id="email" class="form-control" value="<?= $this->e($this->user()['email'] ?? '') ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label"><?= $this->__('name') ?></label>
                        <input type="text" id="name" name="name" class="form-control <?= $this->hasError('name') ? 'is-invalid' : '' ?>"
                               value="<?= $this->e($this->old('name', $this->user()['name'] ?? '')) ?>" required>
                        <?php if ($this->hasError('name')): ?>
                        <div class="invalid-feedback"><?= $this->e($this->error('name')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="locale" class="form-label"><?= $this->__('language') ?></label>
                        <select id="locale" name="locale" class="form-select">
                            <?php foreach ($localeNames as $code => $name): ?>
                            <option value="<?= $code ?>" <?= ($this->user()['locale'] ?? 'en') === $code ? 'selected' : '' ?>>
                                <?= $this->e($name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><?= $this->__('roles') ?></label>
                        <div>
                            <?php foreach ($this->user()['roles'] ?? [] as $role): ?>
                            <span class="badge bg-primary-subtle text-primary"><?= $this->e(ucfirst($role)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i> <?= $this->__('save_changes') ?>
                        </button>
                        <a href="/change-password" class="btn btn-soft-secondary">
                            <i class="ri-lock-password-line me-1"></i> <?= $this->__('change_password') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
