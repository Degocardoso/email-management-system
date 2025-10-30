<?php

namespace Auth\Middleware;

use Auth\Services\AuthService;
use Auth\Models\User;

/**
 * Middleware de autenticação
 * Protege rotas que requerem autenticação
 */
class AuthMiddleware
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    /**
     * Verifica se usuário está autenticado
     * Redireciona para login se não estiver
     */
    public function requireAuth(): void
    {
        if (!$this->auth->check()) {
            // Salva URL de destino para redirecionar após login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';

            header('Location: /login');
            exit;
        }
    }

    /**
     * Verifica se usuário tem role específica
     */
    public function requireRole(string $role): void
    {
        $this->requireAuth();

        $user = $this->auth->user();

        if (!$user) {
            header('Location: /login');
            exit;
        }

        // Admin tem acesso a tudo
        if ($user['role'] === User::ROLE_ADMIN) {
            return;
        }

        // Verifica se tem a role requerida
        if ($user['role'] !== $role) {
            $this->unauthorized();
        }
    }

    /**
     * Verifica se usuário é admin
     */
    public function requireAdmin(): void
    {
        $this->requireAuth();

        $user = $this->auth->user();

        if (!$user || $user['role'] !== User::ROLE_ADMIN) {
            $this->unauthorized();
        }
    }

    /**
     * Verifica se usuário pode gerar emails
     */
    public function requireGeneratorAccess(): void
    {
        $this->requireAuth();

        if (!$this->auth->can('generate_emails')) {
            $this->unauthorized('Você não tem permissão para gerar emails');
        }
    }

    /**
     * Verifica se usuário pode analisar emails
     */
    public function requireAnalystAccess(): void
    {
        $this->requireAuth();

        if (!$this->auth->can('analyze_emails')) {
            $this->unauthorized('Você não tem permissão para analisar emails');
        }
    }

    /**
     * Verifica se usuário pode gerenciar usuários
     */
    public function requireUserManagement(): void
    {
        $this->requireAuth();

        if (!$this->auth->can('manage_users')) {
            $this->unauthorized('Você não tem permissão para gerenciar usuários');
        }
    }

    /**
     * Página de acesso negado
     */
    private function unauthorized(string $message = 'Você não tem permissão para acessar esta página'): void
    {
        http_response_code(403);
        include __DIR__ . '/../Views/unauthorized.php';
        exit;
    }

    /**
     * Verifica se usuário já está logado
     * Redireciona para dashboard se estiver
     */
    public function redirectIfAuthenticated(): void
    {
        if ($this->auth->check()) {
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Retorna o usuário autenticado
     */
    public function getUser(): ?array
    {
        return $this->auth->user();
    }

    /**
     * Verifica CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Gera CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
