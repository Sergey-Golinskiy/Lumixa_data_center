<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0"><?= $this->__('total_backups') ?></p>
                                <h2 class="mt-2 ff-secondary fw-semibold"><?= (int)$backupCount ?></h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                    <i class="ri-hard-drive-2-line text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0"><?= $this->__('total_size') ?></p>
                                <h2 class="mt-2 ff-secondary fw-semibold"><?= h($totalSize) ?></h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                    <i class="ri-database-2-line text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backups Card -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('backup_files') ?></h5>
                <div class="d-flex gap-2">
                    <a href="/admin" class="btn btn-soft-secondary">
                        <i class="ri-arrow-left-line me-1"></i>
                        <?= $this->__('admin_dashboard') ?>
                    </a>
                    <?php if ($this->can('admin.backups.create')): ?>
                    <button type="button" id="createBackupBtn" class="btn btn-success">
                        <i class="ri-add-line me-1"></i>
                        <?= $this->__('create_backup') ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($backups)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-hard-drive-2-line"></i>
                        </div>
                    </div>
                    <h5 class="text-muted"><?= $this->__('no_backups_yet') ?></h5>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('filename') ?></th>
                                <th class="text-end"><?= $this->__('size') ?></th>
                                <th><?= $this->__('created') ?></th>
                                <th><?= $this->__('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                            <tr id="backup-<?= h($backup['id']) ?>">
                                <td><code class="text-primary"><?= h($backup['filename']) ?></code></td>
                                <td class="text-end"><?= h($backup['size_formatted']) ?></td>
                                <td class="text-muted"><?= h($backup['created_at_formatted']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="/admin/backups/<?= h($backup['id']) ?>/download" class="btn btn-soft-primary btn-sm">
                                            <i class="ri-download-line me-1"></i>
                                            <?= $this->__('download') ?>
                                        </a>
                                        <?php if ($this->can('admin.backups.restore')): ?>
                                        <button type="button" class="btn btn-soft-warning btn-sm restore-btn"
                                                data-id="<?= h($backup['id']) ?>"
                                                data-filename="<?= h($backup['filename']) ?>">
                                            <i class="ri-restart-line me-1"></i>
                                            <?= $this->__('restore_backup') ?>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($this->can('admin.backups.delete')): ?>
                                        <button type="button" class="btn btn-soft-danger btn-sm delete-btn"
                                                data-id="<?= h($backup['id']) ?>"
                                                data-filename="<?= h($backup['filename']) ?>">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $this->__('confirm_restore') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg mx-auto">
                        <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-1">
                            <i class="ri-error-warning-line"></i>
                        </div>
                    </div>
                </div>
                <p><strong><?= $this->__('warning') ?>:</strong> <?= $this->__('restore_warning') ?></p>
                <p><?= $this->__('safety_backup_note') ?></p>
                <p><?= $this->__('confirm_restore_from') ?> <strong id="restoreFilename"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal"><?= $this->__('cancel') ?></button>
                <button type="button" class="btn btn-danger" id="confirmRestoreBtn">
                    <i class="ri-restart-line me-1"></i>
                    <?= $this->__('restore_backup') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $this->__('confirm_delete_title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg mx-auto">
                        <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-1">
                            <i class="ri-delete-bin-line"></i>
                        </div>
                    </div>
                </div>
                <p><?= $this->__('confirm_delete_item') ?> <strong id="deleteFilename"></strong>?</p>
                <p class="text-danger"><?= $this->__('delete_warning') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal"><?= $this->__('cancel') ?></button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="ri-delete-bin-line me-1"></i>
                    <?= $this->__('delete') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5 id="progressTitle"><?= $this->__('processing') ?></h5>
                <div class="progress mt-3" style="height: 10px;">
                    <div id="progressFill" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 50%;"></div>
                </div>
                <p id="progressMessage" class="text-muted mt-2"><?= $this->__('please_wait') ?></p>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?= h($csrfToken) ?>';
let currentBackupId = null;
let restoreModal, deleteModal, progressModal;

document.addEventListener('DOMContentLoaded', function() {
    restoreModal = new bootstrap.Modal(document.getElementById('restoreModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
});

// Create Backup
document.getElementById('createBackupBtn')?.addEventListener('click', function() {
    showProgress('<?= $this->__('creating_backup') ?>', '<?= $this->__('exporting_database') ?>');

    fetch('/admin/backups/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: '_token=' + csrfToken
    })
    .then(response => response.json())
    .then(data => {
        progressModal.hide();
        if (data.success) {
            alert('<?= $this->__('backup_created', ['name' => '']) ?>'.replace(':name', data.backup.filename));
            location.reload();
        } else {
            alert('<?= $this->__('error') ?>: ' + data.error);
        }
    })
    .catch(error => {
        progressModal.hide();
        alert('<?= $this->__('error') ?>: ' + error.message);
    });
});

// Restore buttons
document.querySelectorAll('.restore-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentBackupId = this.dataset.id;
        document.getElementById('restoreFilename').textContent = this.dataset.filename;
        restoreModal.show();
    });
});

// Confirm Restore
document.getElementById('confirmRestoreBtn')?.addEventListener('click', function() {
    restoreModal.hide();
    showProgress('<?= $this->__('restoring_backup') ?>', '<?= $this->__('restoring_backup_message') ?>');

    fetch('/admin/backups/' + currentBackupId + '/restore', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: '_token=' + csrfToken
    })
    .then(response => response.json())
    .then(data => {
        progressModal.hide();
        if (data.success) {
            alert('<?= $this->__('backup_restored', ['name' => '']) ?>'.replace(':name', data.safety_backup));
            location.reload();
        } else {
            alert('<?= $this->__('error') ?>: ' + data.error);
        }
    })
    .catch(error => {
        progressModal.hide();
        alert('<?= $this->__('error') ?>: ' + error.message);
    });
});

// Delete buttons
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentBackupId = this.dataset.id;
        document.getElementById('deleteFilename').textContent = this.dataset.filename;
        deleteModal.show();
    });
});

// Confirm Delete
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    deleteModal.hide();

    fetch('/admin/backups/' + currentBackupId + '/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: '_token=' + csrfToken
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('backup-' + currentBackupId)?.remove();
            location.reload();
        } else {
            alert('<?= $this->__('error') ?>: ' + data.error);
        }
    })
    .catch(error => {
        alert('<?= $this->__('error') ?>: ' + error.message);
    });
});

function showProgress(title, message) {
    document.getElementById('progressTitle').textContent = title;
    document.getElementById('progressMessage').textContent = message;
    document.getElementById('progressFill').style.width = '50%';
    progressModal.show();

    setTimeout(() => {
        document.getElementById('progressFill').style.width = '80%';
    }, 500);
}
</script>

<?php $this->endSection(); ?>
