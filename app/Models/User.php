<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * User Model
 */
class User extends Model
{
    protected static string $table = 'users';

    protected static array $fillable = [
        'username', 'email', 'password_hash', 'first_name', 'last_name',
        'is_active', 'must_change_password', 'last_login_at',
        'failed_login_attempts', 'locked_until'
    ];

    protected static array $casts = [
        'id' => 'integer',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'failed_login_attempts' => 'integer',
    ];

    /**
     * Get user's full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get user's roles
     */
    public function getRoles(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT r.* FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?',
            [$this->id]
        );
    }

    /**
     * Get user's role codes
     */
    public function getRoleCodes(): array
    {
        return array_column($this->getRoles(), 'code');
    }

    /**
     * Get user's permissions
     */
    public function getPermissions(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            'SELECT DISTINCT p.* FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = ?',
            [$this->id]
        );
    }

    /**
     * Get user's permission codes
     */
    public function getPermissionCodes(): array
    {
        return array_column($this->getPermissions(), 'code');
    }

    /**
     * Check if user has role
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoleCodes());
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        // Admin has all permissions
        if ($this->hasRole('admin')) {
            return true;
        }
        return in_array($permission, $this->getPermissionCodes());
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Set password
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Check if user is locked
     */
    public function isLocked(): bool
    {
        if (!$this->locked_until) {
            return false;
        }
        return strtotime($this->locked_until) > time();
    }

    /**
     * Lock user for specified minutes
     */
    public function lock(int $minutes = 15): void
    {
        $this->locked_until = date('Y-m-d H:i:s', time() + ($minutes * 60));
        $this->save();
    }

    /**
     * Unlock user
     */
    public function unlock(): void
    {
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Record failed login attempt
     */
    public function recordFailedLogin(): void
    {
        $this->failed_login_attempts++;

        // Lock after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lock(15);
        }

        $this->save();
    }

    /**
     * Record successful login
     */
    public function recordSuccessfulLogin(): void
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        $this->last_login_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(array $roleIds): void
    {
        $db = Database::getInstance();

        // Remove existing roles
        $db->delete('user_roles', 'user_id = ?', [$this->id]);

        // Add new roles
        foreach ($roleIds as $roleId) {
            $db->insert('user_roles', [
                'user_id' => $this->id,
                'role_id' => $roleId,
            ]);
        }
    }

    /**
     * Find by username
     */
    public static function findByUsername(string $username): ?self
    {
        return self::firstWhere('username', $username);
    }

    /**
     * Find by email
     */
    public static function findByEmail(string $email): ?self
    {
        return self::firstWhere('email', $email);
    }

    /**
     * Get session data array
     */
    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->getFullName(),
            'roles' => $this->getRoleCodes(),
            'permissions' => $this->getPermissionCodes(),
            'must_change_password' => $this->must_change_password,
        ];
    }
}
