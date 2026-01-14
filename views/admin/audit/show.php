<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/audit" class="btn btn-secondary">&laquo; Back to Audit Log</a>
</div>

<div class="card">
    <div class="card-header">
        <h3><?= h($title) ?></h3>
    </div>
    <div class="card-body">
        <dl class="details-list">
            <dt>Time</dt>
            <dd><?= h($entry['created_at']) ?></dd>

            <dt>User</dt>
            <dd><?= h($entry['username'] ?? 'System') ?></dd>

            <dt>Action</dt>
            <dd><code><?= h($entry['action']) ?></code></dd>

            <dt>Table</dt>
            <dd><?= h($entry['table_name']) ?></dd>

            <dt>Record ID</dt>
            <dd><?= h($entry['record_id'] ?? '-') ?></dd>

            <dt>IP Address</dt>
            <dd><?= h($entry['ip_address'] ?? '-') ?></dd>

            <dt>User Agent</dt>
            <dd style="word-break: break-all;"><?= h($entry['user_agent'] ?? '-') ?></dd>
        </dl>

        <?php if ($entry['old_data_decoded']): ?>
        <h4 style="margin-top: 20px;">Old Data</h4>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto;"><?= h(json_encode($entry['old_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php endif; ?>

        <?php if ($entry['new_data_decoded']): ?>
        <h4 style="margin-top: 20px;">New Data</h4>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto;"><?= h(json_encode($entry['new_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
