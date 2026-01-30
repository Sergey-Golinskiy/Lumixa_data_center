<?php $this->section('content'); ?>

<div class="page-header">
    <h2><?= h($title) ?></h2>
    <?php if ($this->can('admin.users.create')): ?>
    <a href="/admin/users/create" class="btn btn-primary"><?= $this->__('create_user') ?></a>
    <?php endif; ?>
</div>

<!-- Live Filters -->
<div class="live-filters">
    <form method="GET" action="/admin/users">
        <div class="live-filters-row">
            <div class="live-filter-group filter-search">
                <label class="live-filter-label"><?= $this->__('search') ?></label>
                <div class="live-filter-search-wrapper <?= $search ? 'has-value' : '' ?>">
                    <span class="live-filter-search-icon">&#128269;</span>
                    <input type="text" name="search" class="live-filter-input"
                           placeholder="<?= $this->__('search_users') ?>"
                           value="<?= h($search) ?>">
                    <button type="button" class="live-filter-clear-search" title="<?= $this->__('clear') ?>">&times;</button>
                </div>
            </div>

            <div class="live-filter-group filter-actions">
                <button type="button" class="live-filter-clear-all" <?= !$search ? 'disabled' : '' ?>>
                    <?= $this->__('clear_filters') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body">

        <?php if (empty($users)): ?>
        <p class="text-muted"><?= $this->__('no_users_found') ?></p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= $this->__('name') ?></th>
                    <th><?= $this->__('email') ?></th>
                    <th><?= $this->__('roles') ?></th>
                    <th><?= $this->__('status') ?></th>
                    <th><?= $this->__('actions') ?></th>
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
                        <span class="badge badge-success"><?= $this->__('active') ?></span>
                        <?php else: ?>
                        <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
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
