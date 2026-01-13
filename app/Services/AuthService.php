<?php
/**
 * Auth Service - Authentication logic
 */

namespace App\Services;

use App\Core\Application;
use App\Core\Database;

class AuthService
{
    private Application $app;
    private Database $db;
    private int $maxAttempts;
    private int $lockoutTime;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app->getDatabase();
        $this->maxAttempts = $app->config('login_max_attempts', 5);
        $this->lockoutTime = $app->config('login_lockout_time', 900);
    }

    /**
     * Attempt to authenticate user
     */
    public function attempt(string $email, string $password): array
    {
        // Find user
        $user = $this->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Invalid email or password'
            ];
        }

        // Check if locked
        if ($this->isLocked($user)) {
            $minutes = ceil($this->lockoutTime / 60);
            return [
                'success' => false,
                'error' => "Too many login attempts. Please try again in {$minutes} minutes."
            ];
        }

        // Check if active
        if (!$user['is_active']) {
            return [
                'success' => false,
                'error' => 'Your account has been deactivated'
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            $this->incrementLoginAttempts($user['id']);
            return [
                'success' => false,
                'error' => 'Invalid email or password'
            ];
        }

        // Success - reset attempts and update last login
        $this->resetLoginAttempts($user['id']);
        $this->updateLastLogin($user['id']);

        // Get user roles and permissions
        $userData = $this->getUserData($user);

        // Log audit
        $this->logAudit($user['id'], 'user.login');

        return [
            'success' => true,
            'user' => $userData
        ];
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
    }

    /**
     * Check if user is locked
     */
    private function isLocked(array $user): bool
    {
        if ($user['login_attempts'] < $this->maxAttempts) {
            return false;
        }

        if ($user['locked_until'] === null) {
            return false;
        }

        return strtotime($user['locked_until']) > time();
    }

    /**
     * Increment login attempts
     */
    private function incrementLoginAttempts(int $userId): void
    {
        $user = $this->findById($userId);
        $attempts = ($user['login_attempts'] ?? 0) + 1;

        $lockedUntil = null;
        if ($attempts >= $this->maxAttempts) {
            $lockedUntil = date('Y-m-d H:i:s', time() + $this->lockoutTime);
        }

        $this->db->update('users', [
            'login_attempts' => $attempts,
            'locked_until' => $lockedUntil
        ], ['id' => $userId]);
    }

    /**
     * Reset login attempts
     */
    private function resetLoginAttempts(int $userId): void
    {
        $this->db->update('users', [
            'login_attempts' => 0,
            'locked_until' => null
        ], ['id' => $userId]);
    }

    /**
     * Update last login time
     */
    private function updateLastLogin(int $userId): void
    {
        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);
    }

    /**
     * Get full user data with roles and permissions
     */
    public function getUserData(array $user): array
    {
        // Get roles
        $roles = $this->db->fetchAll("
            SELECT r.slug
            FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ?
        ", [$user['id']]);

        $roleNames = array_column($roles, 'slug');

        // Get permissions
        $permissions = $this->db->fetchAll("
            SELECT DISTINCT p.code
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = ?
        ", [$user['id']]);

        $permissionCodes = array_column($permissions, 'code');

        return [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'is_active' => (bool)$user['is_active'],
            'must_change_password' => (bool)$user['must_change_password'],
            'roles' => $roleNames,
            'permissions' => $permissionCodes,
            'last_login_at' => $user['last_login_at']
        ];
    }

    /**
     * Change user password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = $this->findById($userId);

        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return [
                'success' => false,
                'error' => 'Current password is incorrect'
            ];
        }

        // Hash new password
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $this->db->update('users', [
            'password' => $hash,
            'must_change_password' => 0
        ], ['id' => $userId]);

        // Log audit
        $this->logAudit($userId, 'user.password_changed');

        return ['success' => true];
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $data): array
    {
        $user = $this->findById($userId);

        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found'
            ];
        }

        $allowed = ['name'];
        $update = array_intersect_key($data, array_flip($allowed));

        if (empty($update)) {
            return [
                'success' => false,
                'error' => 'No data to update'
            ];
        }

        $this->db->update('users', $update, ['id' => $userId]);

        // Log audit
        $this->logAudit($userId, 'user.profile_updated', $update);

        return ['success' => true];
    }

    /**
     * Log audit entry
     */
    private function logAudit(int $userId, string $action, array $details = []): void
    {
        $this->db->insert('audit_log', [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => 'user',
            'entity_id' => $userId,
            'details' => json_encode($details),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_id' => $this->app->getRequestId()
        ]);
    }
}
