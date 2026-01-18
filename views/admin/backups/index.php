<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin" class="btn btn-secondary">&laquo; Admin</a>
    <?php if ($this->can('admin.backups.create')): ?>
    <button type="button" id="createBackupBtn" class="btn btn-primary">Create Backup</button>
    <?php endif; ?>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
        <div style="font-size: 2em; font-weight: bold; color: #007bff;"><?= (int)$backupCount ?></div>
        <div style="color: #666;">Total Backups</div>
    </div>
    <div class="stat-card" style="padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
        <div style="font-size: 2em; font-weight: bold; color: #28a745;"><?= h($totalSize) ?></div>
        <div style="color: #666;">Total Size</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 style="margin: 0;">Backup Files</h4>
    </div>
    <div class="card-body">
        <?php if (empty($backups)): ?>
        <div class="empty-state" style="text-align: center; padding: 40px; color: #666;">
            <p>No backups yet. Create your first backup using the button above.</p>
        </div>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th style="text-align: right;">Size</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                <tr id="backup-<?= h($backup['id']) ?>">
                    <td>
                        <code><?= h($backup['filename']) ?></code>
                    </td>
                    <td style="text-align: right;"><?= h($backup['size_formatted']) ?></td>
                    <td><?= h($backup['created_at_formatted']) ?></td>
                    <td>
                        <a href="/admin/backups/<?= h($backup['id']) ?>/download" class="btn btn-sm btn-outline">Download</a>
                        <?php if ($this->can('admin.backups.restore')): ?>
                        <button type="button" class="btn btn-sm btn-warning restore-btn"
                                data-id="<?= h($backup['id']) ?>"
                                data-filename="<?= h($backup['filename']) ?>">Restore</button>
                        <?php endif; ?>
                        <?php if ($this->can('admin.backups.delete')): ?>
                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                data-id="<?= h($backup['id']) ?>"
                                data-filename="<?= h($backup['filename']) ?>">Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div id="restoreModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Confirm Restore</h3>
        <p><strong>Warning:</strong> This will replace ALL current data with the backup data!</p>
        <p>A safety backup will be created before restoring.</p>
        <p>Are you sure you want to restore from: <strong id="restoreFilename"></strong>?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal('restoreModal')">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmRestoreBtn">Restore</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete: <strong id="deleteFilename"></strong>?</p>
        <p>This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div id="progressModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3 id="progressTitle">Processing...</h3>
        <div class="progress-bar" style="height: 20px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
            <div id="progressFill" style="width: 0%; height: 100%; background: #007bff; transition: width 0.3s;"></div>
        </div>
        <p id="progressMessage" style="margin-top: 10px; color: #666;">Please wait...</p>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
}
.modal-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>

<script>
const csrfToken = '<?= h($csrfToken) ?>';
let currentBackupId = null;

// Create Backup
document.getElementById('createBackupBtn')?.addEventListener('click', function() {
    showProgress('Creating Backup', 'Exporting database and creating archive...');

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
        closeModal('progressModal');
        if (data.success) {
            alert('Backup created: ' + data.backup.filename);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        closeModal('progressModal');
        alert('Error: ' + error.message);
    });
});

// Restore buttons
document.querySelectorAll('.restore-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentBackupId = this.dataset.id;
        document.getElementById('restoreFilename').textContent = this.dataset.filename;
        document.getElementById('restoreModal').style.display = 'flex';
    });
});

// Confirm Restore
document.getElementById('confirmRestoreBtn')?.addEventListener('click', function() {
    closeModal('restoreModal');
    showProgress('Restoring Backup', 'Creating safety backup and restoring data...');

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
        closeModal('progressModal');
        if (data.success) {
            alert('Backup restored successfully!\nSafety backup: ' + data.safety_backup);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        closeModal('progressModal');
        alert('Error: ' + error.message);
    });
});

// Delete buttons
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentBackupId = this.dataset.id;
        document.getElementById('deleteFilename').textContent = this.dataset.filename;
        document.getElementById('deleteModal').style.display = 'flex';
    });
});

// Confirm Delete
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    closeModal('deleteModal');

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
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});

function showProgress(title, message) {
    document.getElementById('progressTitle').textContent = title;
    document.getElementById('progressMessage').textContent = message;
    document.getElementById('progressFill').style.width = '50%';
    document.getElementById('progressModal').style.display = 'flex';

    // Animate progress
    setTimeout(() => {
        document.getElementById('progressFill').style.width = '80%';
    }, 500);
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Close modal on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});
</script>

<?php $this->endSection(); ?>
