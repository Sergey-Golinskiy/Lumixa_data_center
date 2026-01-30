<?php $this->section('content'); ?>

<div class="page-header">
    <h2><?= h($title) ?></h2>
</div>

<!-- Live Filters -->
<div class="live-filters">
    <form method="GET" action="/admin/audit">
        <div class="live-filters-row">
            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('user') ?></label>
                <select name="user_id" class="live-filter-select">
                    <option value=""><?= $this->__('all_users') ?></option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $filters['user_id'] == $user['id'] ? 'selected' : '' ?>>
                        <?= h($user['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('action') ?></label>
                <select name="action" class="live-filter-select">
                    <option value=""><?= $this->__('all_actions') ?></option>
                    <?php foreach ($actions as $action): ?>
                    <option value="<?= h($action) ?>" <?= $filters['action'] == $action ? 'selected' : '' ?>>
                        <?= h($action) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="live-filter-group">
                <label class="live-filter-label"><?= $this->__('table') ?></label>
                <select name="table" class="live-filter-select">
                    <option value=""><?= $this->__('all_tables') ?></option>
                    <?php foreach ($tables as $table): ?>
                    <option value="<?= h($table) ?>" <?= $filters['table'] == $table ? 'selected' : '' ?>>
                        <?= h($table) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="live-filter-group filter-date">
                <label class="live-filter-label"><?= $this->__('date_from') ?></label>
                <input type="date" name="from" class="live-filter-input" value="<?= h($filters['from']) ?>">
            </div>

            <div class="live-filter-group filter-date">
                <label class="live-filter-label"><?= $this->__('date_to') ?></label>
                <input type="date" name="to" class="live-filter-input" value="<?= h($filters['to']) ?>">
            </div>

            <div class="live-filter-group filter-actions">
                <button type="button" class="live-filter-clear-all" <?= (!$filters['user_id'] && !$filters['action'] && !$filters['table'] && !$filters['from'] && !$filters['to']) ? 'disabled' : '' ?>>
                    <?= $this->__('clear_filters') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body">

        <?php if (empty($entries)): ?>
        <p class="text-muted"><?= $this->__('no_audit_entries') ?></p>
        <?php else: ?>
        <table class="table">
            <thead>
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
                        <a href="/admin/audit/<?= $entry['id'] ?>">
                            <?= h($entry['created_at']) ?>
                        </a>
                    </td>
                    <td><?= h($entry['name'] ?? $this->__('system')) ?></td>
                    <td><code><?= h($entry['action']) ?></code></td>
                    <td><?= h($entry['entity_type']) ?></td>
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
