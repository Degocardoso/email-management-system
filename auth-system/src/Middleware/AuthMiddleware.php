<?php

namespace App\Middleware;

/**
 * Middleware de Autenticação
 *
 * Verifica se o usuário está autenticado antes de acessar rotas protegidas
 */
class AuthMiddleware
{
    /**
     * Verifica se o usuário está autenticado
     *
     * @return bool
     */
    public static function check(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    /**
     * Requer autenticação, redireciona se não estiver autenticado
     *
     * @param string $redirectTo
     */
    public static function require(string $redirectTo = '/login'): void
    {
        if (!self::check()) {
            $_SESSION['error'] = 'Você precisa estar autenticado para acessar esta página.';
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Redireciona se JÁ estiver autenticado
     *
     * @param string $redirectTo
     */
    public static function guest(string $redirectTo = '/dashboard'): void
    {
        if (self::check()) {
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Verifica se o usuário tem uma role específica
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        if (!self::check()) {
            return false;
        }

        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Requer uma role específica
     *
     * @param string $role
     * @param string $redirectTo
     */
    public static function requireRole(string $role, string $redirectTo = '/dashboard'): void
    {
        if (!self::hasRole($role)) {
            $_SESSION['error'] = 'Você não tem permissão para acessar esta página.';
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Retorna dados do usuário autenticado
     *
     * @return array|null
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
        ];
    }
}
