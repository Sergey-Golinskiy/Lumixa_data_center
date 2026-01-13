<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <div class="card-body">
                <form action="<?= url('profile') ?>" method="POST">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" class="form-control" value="<?= e($user->username) ?>" disabled>
                        <small class="form-text">Username cannot be changed</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                   value="<?= e(old('first_name', $user->first_name)) ?>" required>
                            <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= e($errors['first_name']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control"
                                   value="<?= e(old('last_name', $user->last_name)) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= e(old('email', $user->email)) ?>" required>
                        <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
            </div>
            <div class="card-body">
                <form action="<?= url('profile/password') ?>" method="POST">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['current_password'])): ?>
                        <div class="invalid-feedback"><?= e($errors['current_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password * (min 8 characters)</label>
                        <input type="password" id="new_password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" required minlength="8">
                        <?php if (isset($errors['new_password'])): ?>
                        <div class="invalid-feedback"><?= e($errors['new_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-warning">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.row { display: flex; flex-wrap: wrap; margin: 0 -0.75rem; }
.col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0 0.75rem; }
@media (max-width: 768px) { .col-md-6 { flex: 0 0 100%; max-width: 100%; } }
</style>
