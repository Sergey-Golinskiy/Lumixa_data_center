<?php $this->section('content'); ?>

<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128100;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['users'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('users') ?></span>
            </div>
            <a href="/admin/users" class="stat-link"><?= $this->__('manage') ?></a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128101;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['roles'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('roles') ?></span>
            </div>
            <a href="/admin/roles" class="stat-link"><?= $this->__('manage') ?></a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128190;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['backups'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('backups') ?></span>
            </div>
            <a href="/admin/backups" class="stat-link"><?= $this->__('manage') ?></a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128203;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['audit_entries'] ?? 0) ?></span>
                <span class="stat-label"><?= $this->__('audit_7_days') ?></span>
            </div>
            <a href="/admin/audit" class="stat-link"><?= $this->__('view') ?></a>
        </div>
    </div>

    <div class="dashboard-sections">
        <div class="dashboard-section">
            <h2><?= $this->__('quick_actions') ?></h2>
            <div class="quick-actions">
                <a href="/admin/users/create" class="quick-action">
                    <span class="qa-icon">+</span>
                    <span><?= $this->__('create_user') ?></span>
                </a>
                <a href="/admin/backups" class="quick-action">
                    <span class="qa-icon">&#128190;</span>
                    <span><?= $this->__('create_backup') ?></span>
                </a>
                <a href="/admin/diagnostics" class="quick-action">
                    <span class="qa-icon">&#128295;</span>
                    <span><?= $this->__('diagnostics') ?></span>
                </a>
                <a href="/admin/audit" class="quick-action">
                    <span class="qa-icon">&#128203;</span>
                    <span><?= $this->__('audit_log') ?></span>
                </a>
            </div>
        </div>

        <div class="dashboard-section">
            <h2><?= $this->__('admin_menu') ?></h2>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <a href="/admin/users"><?= $this->__('users_access') ?></a>
                    <small style="display: block; color: var(--text-muted);"><?= $this->__('manage_users_roles_permissions') ?></small>
                </li>
                <li style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <a href="/admin/backups"><?= $this->__('backup_restore') ?></a>
                    <small style="display: block; color: var(--text-muted);"><?= $this->__('database_files_backup') ?></small>
                </li>
                <li style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <a href="/admin/audit"><?= $this->__('audit_log') ?></a>
                    <small style="display: block; color: var(--text-muted);"><?= $this->__('system_activity_history') ?></small>
                </li>
                <li style="padding: 10px 0;">
                    <a href="/admin/diagnostics"><?= $this->__('system_diagnostics') ?></a>
                    <small style="display: block; color: var(--text-muted);"><?= $this->__('health_checks_debugging') ?></small>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
