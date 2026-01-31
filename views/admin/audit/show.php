<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= h($title) ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 200px;"><?= $this->__('time') ?></th>
                                <td class="text-muted"><?= h($entry['created_at']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('user') ?></th>
                                <td class="text-muted"><?= h($entry['name'] ?? $this->__('system')) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('action') ?></th>
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
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('table') ?></th>
                                <td class="text-muted"><?= h($entry['entity_type']) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('record') ?></th>
                                <td><code><?= h($entry['entity_id'] ?? '-') ?></code></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('ip_address') ?></th>
                                <td class="text-muted"><?= h($entry['ip_address'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('user_agent') ?></th>
                                <td class="text-muted text-break"><?= h($entry['user_agent'] ?? '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($entry['old_data_decoded'])): ?>
                <h6 class="mt-4 mb-3"><?= $this->__('old_data') ?></h6>
                <pre class="bg-light p-3 rounded"><code><?= h(json_encode($entry['old_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
                <?php endif; ?>

                <?php if (!empty($entry['new_data_decoded'])): ?>
                <h6 class="mt-4 mb-3"><?= $this->__('new_data') ?></h6>
                <pre class="bg-light p-3 rounded"><code><?= h(json_encode($entry['new_data_decoded'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></code></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <a href="/admin/audit" class="btn btn-soft-primary w-100">
                    <i class="ri-arrow-left-line me-1"></i>
                    <?= $this->__('back_to_list') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
