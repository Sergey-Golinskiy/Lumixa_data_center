<?php
/**
 * Auth Controller - Authentication handling
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Application;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->authService = new AuthService($app);
    }

    /**
     * Show login page
     */
    public function showLogin(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/');
            return;
        }

        $this->view->setLayout('auth');
        $this->view('auth/login', [
            'title' => 'Login',
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token. Please try again.');
            $this->redirect('/login');
            return;
        }

        $email = $this->post('email', '');
        $password = $this->post('password', '');
        $remember = $this->post('remember') === '1';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->session->setFlash('error', 'Please enter email and password.');
            $this->session->flashInput(['email' => $email]);
            $this->redirect('/login');
            return;
        }

        // Attempt login
        $result = $this->authService->attempt($email, $password);

        if ($result['success']) {
            // Store user in session
            $this->session->set('user', $result['user']);
            $this->session->set('last_activity', time());
            $this->session->regenerate();

            $this->app->getLogger()->info('User logged in', [
                'user_id' => $result['user']['id'],
                'email' => $email
            ]);

            // Check if must change password
            if ($result['user']['must_change_password'] ?? false) {
                $this->session->setFlash('warning', 'Please change your password.');
                $this->redirect('/change-password');
                return;
            }

            $this->session->setFlash('success', 'Welcome back, ' . $result['user']['name'] . '!');
            $this->redirect('/');
        } else {
            $this->app->getLogger()->warning('Failed login attempt', [
                'email' => $email,
                'reason' => $result['error']
            ]);

            $this->session->setFlash('error', $result['error']);
            $this->session->flashInput(['email' => $email]);
            $this->redirect('/login');
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $user = $this->user();

        if ($user) {
            $this->app->getLogger()->info('User logged out', [
                'user_id' => $user['id']
            ]);
        }

        $this->session->destroy();
        $this->session->start();
        $this->session->setFlash('success', 'You have been logged out.');
        $this->redirect('/login');
    }

    /**
     * Show change password page
     */
    public function showChangePassword(): void
    {
        $this->requireAuth();

        $this->view('auth/change-password', [
            'title' => 'Change Password',
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Handle password change
     */
    public function changePassword(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token.');
            $this->redirect('/change-password');
            return;
        }

        $currentPassword = $this->post('current_password', '');
        $newPassword = $this->post('new_password', '');
        $confirmPassword = $this->post('confirm_password', '');

        // Validate
        $errors = [];

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Current password is required';
        }

        if (empty($newPassword)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters';
        }

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $this->session->setErrors($errors);
            $this->redirect('/change-password');
            return;
        }

        // Change password
        $result = $this->authService->changePassword(
            $this->user()['id'],
            $currentPassword,
            $newPassword
        );

        if ($result['success']) {
            // Update session
            $user = $this->user();
            $user['must_change_password'] = false;
            $this->session->set('user', $user);

            $this->session->setFlash('success', 'Password changed successfully.');
            $this->redirect('/');
        } else {
            $this->session->setFlash('error', $result['error']);
            $this->redirect('/change-password');
        }
    }

    /**
     * Show profile page
     */
    public function profile(): void
    {
        $this->requireAuth();

        $this->view('auth/profile', [
            'title' => 'My Profile',
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token.');
            $this->redirect('/profile');
            return;
        }

        $name = trim($this->post('name', ''));

        if (empty($name)) {
            $this->session->setErrors(['name' => 'Name is required']);
            $this->redirect('/profile');
            return;
        }

        $result = $this->authService->updateProfile($this->user()['id'], [
            'name' => $name
        ]);

        if ($result['success']) {
            // Update session
            $user = $this->user();
            $user['name'] = $name;
            $this->session->set('user', $user);

            $this->session->setFlash('success', 'Profile updated.');
        } else {
            $this->session->setFlash('error', $result['error']);
        }

        $this->redirect('/profile');
    }
}
