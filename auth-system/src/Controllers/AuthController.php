<?php

namespace Auth\Controllers;

use Auth\Services\AuthService;
use Auth\Middleware\AuthMiddleware;

/**
 * Controller de autenticação
 * Gerencia login e logout
 */
class AuthController
{
    private AuthService $auth;
    private AuthMiddleware $middleware;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->middleware = new AuthMiddleware();
    }

    /**
     * Exibe formulário de login
     */
    public function showLoginForm(): void
    {
        // Se já está logado, redireciona para dashboard
        $this->middleware->redirectIfAuthenticated();

        $csrfToken = $this->middleware->generateCsrfToken();
        include __DIR__ . '/../Views/login.php';
    }

    /**
     * Processa login
     */
    public function login(): void
    {
        try {
            // Verifica CSRF
            if (!isset($_POST['csrf_token']) || !$this->middleware->verifyCsrfToken($_POST['csrf_token'])) {
                throw new \RuntimeException('Token CSRF inválido');
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new \InvalidArgumentException('Email e senha são obrigatórios');
            }

            // Tenta autenticar
            $user = $this->auth->login($email, $password);

            // Redireciona para URL salva ou dashboard
            $redirectTo = $_SESSION['redirect_after_login'] ?? '/dashboard';
            unset($_SESSION['redirect_after_login']);

            header('Location: ' . $redirectTo);
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /login');
            exit;
        }
    }

    /**
     * Faz logout
     */
    public function logout(): void
    {
        $this->auth->logout();
        $_SESSION['success'] = 'Logout realizado com sucesso';
        header('Location: /login');
        exit;
    }

    /**
     * Exibe dashboard principal
     */
    public function dashboard(): void
    {
        $this->middleware->requireAuth();
        $user = $this->middleware->getUser();

        include __DIR__ . '/../Views/dashboard.php';
    }
}
