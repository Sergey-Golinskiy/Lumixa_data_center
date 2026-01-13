<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

/**
 * Authentication Controller
 */
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Show login form
     */
    public function loginForm(): void
    {
        $this->setLayout('auth');
        $this->view('auth/login', [
            'title' => 'Login',
            'errors' => $this->getValidationErrors()
        ]);
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        $this->validateCsrfOrAbort();

        $username = trim(input('username', ''));
        $password = input('password', '');

        // Basic validation
        if (empty($username) || empty($password)) {
            $this->storeOldInput();
            $this->redirect('login', ['error' => 'Please enter username and password']);
        }

        // Attempt login
        $result = $this->authService->attempt($username, $password);

        if (!$result['success']) {
            $this->storeOldInput();
            $this->redirect('login', ['error' => $result['message']]);
        }

        // Check if password change required
        if ($result['must_change_password']) {
            $this->redirect('profile', [
                'warning' => 'You must change your password before continuing'
            ]);
        }

        $this->redirect('dashboard', ['success' => 'Welcome back!']);
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('login', ['success' => 'You have been logged out']);
    }
}
