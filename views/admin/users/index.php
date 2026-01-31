<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= h($title) ?></h5>
                <?php if ($this->can('admin.users.create')): ?>
                <a href="/admin/users/create" class="btn btn-success">
                    <i class="ri-add-line me-1"></i>
                    <?= $this->__('create_user') ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body border-bottom">
                <!-- Filters -->
                <form method="GET" action="/admin/users">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label"><?= $this->__('search') ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="<?= $this->__('search_users') ?>"
                                       value="<?= h($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri-filter-line me-1"></i>
                                <?= $this->__('filter') ?>
                            </button>
                            <?php if ($search): ?>
                            <a href="/admin/users" class="btn btn-soft-secondary">
                                <i class="ri-refresh-line me-1"></i>
                                <?= $this->__('clear_filters') ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-user-search-line"></i>
                        </div>
                    </div>
                    <h5 class="text-muted"><?= $this->__('no_users_found') ?></h5>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
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
                                <td>
                                    <a href="/admin/users/<?= $user['id'] ?>" class="fw-medium link-primary">
                                        <?= h($user['name']) ?>
                                    </a>
                                </td>
                                <td><?= h($user['email']) ?></td>
                                <td><?= h($user['role_names'] ?? '-') ?></td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-soft-primary btn-sm">
                                        <i class="ri-edit-line"></i>
                                        <?= $this->__('edit') ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-end mt-3">
                    <nav>
                        <ul class="pagination pagination-separated mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
