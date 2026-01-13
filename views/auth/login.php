<form action="<?= url('login') ?>" method="POST" class="auth-form">
    <?= csrfField() ?>

    <div class="form-group">
        <label for="username" class="form-label">Username or Email</label>
        <input type="text" id="username" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
               value="<?= e(old('username')) ?>" required autofocus>
        <?php if (isset($errors['username'])): ?>
        <div class="invalid-feedback"><?= e($errors['username']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
        <?php if (isset($errors['password'])): ?>
        <div class="invalid-feedback"><?= e($errors['password']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </div>
</form>
