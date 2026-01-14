<?php $this->section('content'); ?>

<div class="page-header">
    <h2><?= h($title) ?></h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="get" class="filter-form" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
            <select name="user_id" class="form-control" style="max-width: 150px;">
                <option value="">All Users</option>
                <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                    <?= h($user['username']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="action" class="form-control" style="max-width: 150px;">
                <option value="">All Actions</option>
                <?php foreach ($actions as $action): ?>
                <option value="<?= h($action) ?>" <?= $filters['action'] == $action ? 'selected' : '' ?>>
                    <?= h($action) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="table" class="form-control" style="max-width: 150px;">
                <option value="">All Tables</option>
                <?php foreach ($tables as $table): ?>
                <option value="<?= h($table) ?>" <?= $filters['table'] == $table ? 'selected' : '' ?>>
                    <?= h($table) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="from" value="<?= h($filters['from']) ?>" class="form-control" style="max-width: 150px;">
            <input type="date" name="to" value="<?= h($filters['to']) ?>" class="form-control" style="max-width: 150px;">
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>

        <?php if (empty($entries)): ?>
        <p class="text-muted">No audit entries found.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                <tr>
                    <td>
                        <a href="/admin/audit/<?= $entry['id'] ?>">
                            <?= h($entry['created_at']) ?>
                        </a>
                    </td>
                    <td><?= h($entry['username'] ?? 'System') ?></td>
                    <td><code><?= h($entry['action']) ?></code></td>
                    <td><?= h($entry['table_name']) ?></td>
                    <td><?= h($entry['record_id'] ?? '-') ?></td>
                    <td><?= h($entry['ip_address'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = max(1, $page - 5); $i <= min($totalPages, $page + 5); $i++): ?>
            <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
