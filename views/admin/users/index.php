<?php $this->section('content'); ?>

<div class="page-header">
    <h2><?= h($title) ?></h2>
    <?php if ($this->can('admin.users.create')): ?>
    <a href="/admin/users/create" class="btn btn-primary">Create User</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <form method="get" class="filter-form" style="margin-bottom: 20px;">
            <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search users..." class="form-control" style="max-width: 300px; display: inline-block;">
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>

        <?php if (empty($users)): ?>
        <p class="text-muted">No users found.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><a href="/admin/users/<?= $user['id'] ?>"><?= h($user['name']) ?></a></td>
                    <td><?= h($user['email']) ?></td>
                    <td><?= h($user['role_names'] ?? '-') ?></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                        <?php else: ?>
                        <span class="badge badge-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
