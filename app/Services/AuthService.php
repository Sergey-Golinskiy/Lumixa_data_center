<?php

namespace App\Services;

use App\Models\User;
use App\Services\AuditService;

/**
 * Authentication Service
 */
class AuthService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Attempt login
     */
    public function attempt(string $username, string $password): array
    {
        // Find user by username or email
        $user = User::findByUsername($username);
        if (!$user) {
            $user = User::findByEmail($username);
        }

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        // Check if user is active
        if (!$user->is_active) {
            return [
                'success' => false,
                'message' => 'Your account has been deactivated'
            ];
        }

        // Check if user is locked
        if ($user->isLocked()) {
            $unlockTime = date('H:i', strtotime($user->locked_until));
            return [
                'success' => false,
                'message' => "Account is locked. Try again after {$unlockTime}"
            ];
        }

        // Verify password
        if (!$user->verifyPassword($password)) {
            $user->recordFailedLogin();

            $this->auditService->log(
                'login_failed',
                'user',
                $user->id,
                'Failed login attempt',
                null,
                ['username' => $username]
            );

            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        // Login successful
        $user->recordSuccessfulLogin();
        $this->login($user);

        $this->auditService->log(
            'login',
            'user',
            $user->id,
            'User logged in'
        );

        return [
            'success' => true,
            'user' => $user,
            'must_change_password' => $user->must_change_password
        ];
    }

    /**
     * Login user (set session)
     */
    public function login(User $user): void
    {
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Store user data in session
        $_SESSION['user'] = $user->toSessionArray();
        $_SESSION['logged_in_at'] = time();
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $user = auth();

        if ($user) {
            $this->auditService->log(
                'logout',
                'user',
                $user['id'],
                'User logged out'
            );
        }

        // Clear session
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    /**
     * Get authenticated user
     */
    public function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Refresh user session data (after role/permission changes)
     */
    public function refresh(): void
    {
        $userData = $this->user();
        if (!$userData) {
            return;
        }

        $user = User::find($userData['id']);
        if ($user) {
            $_SESSION['user'] = $user->toSessionArray();
        }
    }

    /**
     * Change password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = User::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Verify current password
        if (!$user->verifyPassword($currentPassword)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters'
            ];
        }

        // Update password
        $user->setPassword($newPassword);
        $user->must_change_password = false;
        $user->save();

        // Refresh session
        $_SESSION['user']['must_change_password'] = false;

        $this->auditService->log(
            'password_changed',
            'user',
            $userId,
            'Password changed'
        );

        return [
            'success' => true,
            'message' => 'Password changed successfully'
        ];
    }

    /**
     * Admin reset password
     */
    public function resetPassword(int $userId, string $newPassword): array
    {
        $user = User::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $user->setPassword($newPassword);
        $user->must_change_password = true;
        $user->unlock(); // Clear any locks

        $this->auditService->log(
            'password_reset',
            'user',
            $userId,
            'Password reset by admin'
        );

        return [
            'success' => true,
            'message' => 'Password reset successfully. User must change password on next login.'
        ];
    }
}
