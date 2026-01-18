<?php $this->section('content'); ?>

<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128100;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['users'] ?? 0) ?></span>
                <span class="stat-label">Users</span>
            </div>
            <a href="/admin/users" class="stat-link">Manage</a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128101;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['roles'] ?? 0) ?></span>
                <span class="stat-label">Roles</span>
            </div>
            <a href="/admin/roles" class="stat-link">Manage</a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128190;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['backups'] ?? 0) ?></span>
                <span class="stat-label">Backups</span>
            </div>
            <a href="/admin/backups" class="stat-link">Manage</a>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128203;</div>
            <div class="stat-content">
                <span class="stat-value"><?= $this->e($stats['audit_entries'] ?? 0) ?></span>
                <span class="stat-label">Audit (7 days)</span>
            </div>
            <a href="/admin/audit" class="stat-link">View</a>
        </div>
    </div>

    <div class="dashboard-sections">
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="/admin/users/create" class="quick-action">
                    <span class="qa-icon">+</span>
                    <span>New User</span>
                </a>
                <a href="/admin/backups" class="quick-action">
                    <span class="qa-icon">&#128190;</span>
                    <span>Create Backup</span>
                </a>
                <a href="/admin/diagnostics" class="quick-action">
                    <span class="qa-icon">&#128295;</span>
                    <span>Diagnostics</span>
                </a>
                <a href="/admin/audit" class="quick-action">
                    <span class="qa-icon">&#128203;</span>
                    <span>Audit Log</span>
                </a>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Admin Menu</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <a href="/admin/users">Users & Access</a>
                    <small style="display: block; color: var(--text-muted);">Manage users, roles and permissions</small>
                </li>
                <li style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <a href="/admin/backups">Backup & Restore</a>
                    <small style="display: block; color: var(--text-muted);">Database and files backup</small>
                </li>
                <li style="padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <a href="/admin/audit">Audit Log</a>
                    <small style="display: block; color: var(--text-muted);">System activity history</small>
                </li>
                <li style="padding: 10px 0;">
                    <a href="/admin/diagnostics">System Diagnostics</a>
                    <small style="display: block; color: var(--text-muted);">Health checks and debugging</small>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
