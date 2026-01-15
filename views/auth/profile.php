<?php $this->section('content'); ?>

<div class="card">
    <div class="card-header">My Profile</div>
    <div class="card-body">
        <form method="POST" action="/profile">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?= $this->e($this->user()['email'] ?? '') ?>" disabled>
                <small>Email cannot be changed</small>
            </div>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name"
                       value="<?= $this->e($this->old('name', $this->user()['name'] ?? '')) ?>" required>
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->e($this->error('name')) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="locale"><?= $this->__('language') ?></label>
                <select id="locale" name="locale" class="form-control">
                    <?php foreach ($localeNames as $code => $name): ?>
                    <option value="<?= $code ?>" <?= ($this->user()['locale'] ?? 'en') === $code ? 'selected' : '' ?>>
                        <?= $this->e($name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Roles</label>
                <p><?= $this->e(implode(', ', array_map('ucfirst', $this->user()['roles'] ?? []))) ?></p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/change-password" class="btn btn-secondary">Change Password</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
