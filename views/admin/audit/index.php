<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= h($title) ?></h5>
            </div>
            <div class="card-body border-bottom">
                <!-- Filters -->
                <form method="GET" action="/admin/audit">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label"><?= $this->__('user') ?></label>
                            <select name="user_id" class="form-select">
                                <option value=""><?= $this->__('all_users') ?></option>
                                <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= h($user['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label"><?= $this->__('action') ?></label>
                            <select name="action" class="form-select">
                                <option value=""><?= $this->__('all_actions') ?></option>
                                <?php foreach ($actions as $action): ?>
                                <option value="<?= h($action) ?>" <?= $filters['action'] == $action ? 'selected' : '' ?>>
                                    <?= h($action) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label"><?= $this->__('table') ?></label>
                            <select name="table" class="form-select">
                                <option value=""><?= $this->__('all_tables') ?></option>
                                <?php foreach ($tables as $table): ?>
                                <option value="<?= h($table) ?>" <?= $filters['table'] == $table ? 'selected' : '' ?>>
                                    <?= h($table) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label"><?= $this->__('date_from') ?></label>
                            <input type="date" name="from" class="form-control" value="<?= h($filters['from']) ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label"><?= $this->__('date_to') ?></label>
                            <input type="date" name="to" class="form-control" value="<?= h($filters['to']) ?>">
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-filter-line me-1"></i>
                                <?= $this->__('filter') ?>
                            </button>
                            <?php if ($filters['user_id'] || $filters['action'] || $filters['table'] || $filters['from'] || $filters['to']): ?>
                            <a href="/admin/audit" class="btn btn-soft-secondary">
                                <i class="ri-refresh-line"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <?php if (empty($entries)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                    </div>
                    <h5 class="text-muted"><?= $this->__('no_audit_entries') ?></h5>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('time') ?></th>
                                <th><?= $this->__('user') ?></th>
                                <th><?= $this->__('action') ?></th>
                                <th><?= $this->__('table') ?></th>
                                <th><?= $this->__('record') ?></th>
                                <th><?= $this->__('ip_address') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td>
                                    <a href="/admin/audit/<?= $entry['id'] ?>" class="fw-medium link-primary">
                                        <?= h($entry['created_at']) ?>
                                    </a>
                                </td>
                                <td><?= h($entry['name'] ?? $this->__('system')) ?></td>
                                <td>
                                    <?php
                                    $action = $entry['action'];
                                    $badgeClass = match($action) {
                                        'create' => 'bg-success-subtle text-success',
                                        'update' => 'bg-warning-subtle text-warning',
                                        'delete' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= h($action) ?></span>
                                </td>
                                <td><?= h($entry['entity_type']) ?></td>
                                <td><code><?= h($entry['record_id'] ?? '-') ?></code></td>
                                <td class="text-muted"><?= h($entry['ip_address'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-end mt-3">
                    <nav>
                        <ul class="pagination pagination-separated mb-0">
                            <?php for ($i = max(1, $page - 5); $i <= min($totalPages, $page + 5); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
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
