<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\AuditService;

/**
 * Profile Controller
 */
class ProfileController extends Controller
{
    private AuthService $authService;
    private AuditService $auditService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->auditService = new AuditService();
    }

    /**
     * Show profile page
     */
    public function index(): void
    {
        $this->setLayout('main');

        $user = User::find(userId());

        $this->view('profile/index', [
            'title' => 'Profile',
            'user' => $user,
            'errors' => $this->getValidationErrors(),
        ]);
    }

    /**
     * Update profile
     */
    public function update(): void
    {
        $this->validateCsrfOrAbort();

        $user = User::find(userId());

        if (!$user) {
            $this->redirect('login');
        }

        $firstName = trim(input('first_name', ''));
        $lastName = trim(input('last_name', ''));
        $email = trim(input('email', ''));

        // Validation
        $errors = [];

        if (empty($firstName)) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address';
        } elseif ($email !== $user->email) {
            // Check if email is already taken
            $existing = User::findByEmail($email);
            if ($existing && $existing->id !== $user->id) {
                $errors['email'] = 'Email is already in use';
            }
        }

        if ($errors) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = $errors;
            $this->back(['error' => 'Please fix the errors below']);
        }

        // Store old values for audit
        $oldValues = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ];

        // Update user
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->email = $email;
        $user->save();

        // Refresh session
        $this->authService->refresh();

        // Log update
        $this->auditService->logUpdate('user', $user->id, $oldValues, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ], 'Profile updated');

        $this->back(['success' => 'Profile updated successfully']);
    }

    /**
     * Change password
     */
    public function changePassword(): void
    {
        $this->validateCsrfOrAbort();

        $currentPassword = input('current_password', '');
        $newPassword = input('new_password', '');
        $confirmPassword = input('confirm_password', '');

        // Validation
        $errors = [];

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Current password is required';
        }

        if (empty($newPassword)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'Password must be at least 8 characters';
        }

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if ($errors) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = $errors;
            $this->back(['error' => 'Please fix the errors below']);
        }

        // Change password
        $result = $this->authService->changePassword(userId(), $currentPassword, $newPassword);

        if (!$result['success']) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = ['current_password' => $result['message']];
            $this->back(['error' => $result['message']]);
        }

        $this->back(['success' => 'Password changed successfully']);
    }
}
