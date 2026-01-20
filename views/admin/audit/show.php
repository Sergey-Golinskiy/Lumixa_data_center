<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/audit" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<div class="card">
    <div class="card-header">
        <h3><?= h($title) ?></h3>
    </div>
    <div class="card-body">
        <dl class="details-list">
            <dt><?= $this->__('time') ?></dt>
            <dd><?= h($entry['created_at']) ?></dd>

            <dt><?= $this->__('user') ?></dt>
            <dd><?= h($entry['name'] ?? $this->__('system')) ?></dd>

            <dt><?= $this->__('action') ?></dt>
            <dd><code><?= h($entry['action']) ?></code></dd>

            <dt><?= $this->__('table') ?></dt>
            <dd><?= h($entry['entity_type']) ?></dd>

            <dt><?= $this->__('record') ?></dt>
            <dd><?= h($entry['entity_id'] ?? '-') ?></dd>

            <dt><?= $this->__('ip_address') ?></dt>
            <dd><?= h($entry['ip_address'] ?? '-') ?></dd>

            <dt><?= $this->__('user_agent') ?></dt>
            <dd style="word-break: break-all;"><?= h($entry['user_agent'] ?? '-') ?></dd>
        </dl>

        <?php if (!empty($entry['old_data_decoded'])): ?>
        <h4 style="margin-top: 20px;"><?= $this->__('old_data') ?></h4>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto;"><?= h(json_encode($entry['old_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php endif; ?>

        <?php if (!empty($entry['new_data_decoded'])): ?>
        <h4 style="margin-top: 20px;"><?= $this->__('new_data') ?></h4>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto;"><?= h(json_encode($entry['new_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
