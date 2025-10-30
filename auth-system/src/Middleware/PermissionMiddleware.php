<?php

namespace App\Middleware;

use App\Models\User;

/**
 * Middleware de Permissões
 *
 * Verifica se o usuário tem permissão para acessar recursos específicos
 */
class PermissionMiddleware
{
    /**
     * Verifica se o usuário tem permissão específica
     *
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(string $permission): bool
    {
        // Primeiro verifica se está autenticado
        if (!AuthMiddleware::check()) {
            return false;
        }

        // Busca o usuário
        $user = self::getAuthenticatedUser();
        if (!$user) {
            return false;
        }

        return $user->hasPermission($permission);
    }

    /**
     * Requer permissão específica, redireciona se não tiver
     *
     * @param string $permission
     * @param string $redirectTo
     */
    public static function requirePermission(string $permission, string $redirectTo = '/dashboard'): void
    {
        if (!self::hasPermission($permission)) {
            $_SESSION['error'] = 'Você não tem permissão para acessar este recurso.';
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Verifica acesso ao Gerador de E-mails
     *
     * @return bool
     */
    public static function canAccessGerador(): bool
    {
        return self::hasPermission('gerador');
    }

    /**
     * Verifica acesso aos Relatórios
     *
     * @return bool
     */
    public static function canAccessReport(): bool
    {
        return self::hasPermission('report');
    }

    /**
     * Requer acesso ao Gerador
     *
     * @param string $redirectTo
     */
    public static function requireGerador(string $redirectTo = '/dashboard'): void
    {
        self::requirePermission('gerador', $redirectTo);
    }

    /**
     * Requer acesso aos Relatórios
     *
     * @param string $redirectTo
     */
    public static function requireReport(string $redirectTo = '/dashboard'): void
    {
        self::requirePermission('report', $redirectTo);
    }

    /**
     * Verifica se é admin
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        if (!AuthMiddleware::check()) {
            return false;
        }

        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Requer permissão de admin
     *
     * @param string $redirectTo
     */
    public static function requireAdmin(string $redirectTo = '/dashboard'): void
    {
        if (!self::isAdmin()) {
            $_SESSION['error'] = 'Acesso restrito a administradores.';
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Retorna o usuário autenticado com todas as informações
     *
     * @return User|null
     */
    private static function getAuthenticatedUser(): ?User
    {
        if (!AuthMiddleware::check()) {
            return null;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $userModel = new User();
        return $userModel->findById($userId);
    }

    /**
     * Retorna array com permissões do usuário
     *
     * @return array
     */
    public static function getUserPermissions(): array
    {
        $user = self::getAuthenticatedUser();
        return $user ? $user->getPermissions() : [];
    }

    /**
     * Retorna informações de acesso do usuário
     *
     * @return array
     */
    public static function getAccessInfo(): array
    {
        if (!AuthMiddleware::check()) {
            return [
                'authenticated' => false,
                'is_admin' => false,
                'can_access_gerador' => false,
                'can_access_report' => false,
                'permissions' => []
            ];
        }

        return [
            'authenticated' => true,
            'is_admin' => self::isAdmin(),
            'can_access_gerador' => self::canAccessGerador(),
            'can_access_report' => self::canAccessReport(),
            'permissions' => self::getUserPermissions()
        ];
    }
}
