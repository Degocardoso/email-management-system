<?php

namespace Auth\Services;

use Auth\Models\User;

/**
 * Serviço de autenticação
 * Gerencia login, logout e sessões de usuários
 */
class AuthService
{
    private User $userModel;
    private DatabaseService $db;

    public function __construct()
    {
        $this->userModel = new User();
        $this->db = DatabaseService::getInstance();

        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Autentica um usuário
     */
    public function login(string $email, string $password): array
    {
        // Busca usuário
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->logAudit(null, 'login_failed', "Tentativa de login com email inexistente: $email");
            throw new \RuntimeException('Email ou senha incorretos');
        }

        // Verifica se está ativo
        if (!$user['active']) {
            $this->logAudit($user['id'], 'login_blocked', 'Tentativa de login com usuário inativo');
            throw new \RuntimeException('Usuário inativo');
        }

        // Verifica senha
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            $this->logAudit($user['id'], 'login_failed', 'Senha incorreta');
            throw new \RuntimeException('Email ou senha incorretos');
        }

        // Remove senha do array
        unset($user['password']);

        // Cria sessão
        $this->createSession($user);

        // Log de sucesso
        $this->logAudit($user['id'], 'login_success', 'Login realizado com sucesso');

        return $user;
    }

    /**
     * Cria sessão do usuário
     */
    private function createSession(array $user): void
    {
        // Regenera ID da sessão para segurança
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // Salva token de sessão no banco
        $this->saveSessionToken($user['id']);
    }

    /**
     * Salva token de sessão no banco de dados
     */
    private function saveSessionToken(int $userId): void
    {
        $sessionToken = session_id();
        $expiresAt = date('Y-m-d H:i:s', time() + (int)($_ENV['SESSION_LIFETIME'] ?? 7200));

        $sql = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at)
                VALUES (:user_id, :session_token, :ip_address, :user_agent, :expires_at)";

        $params = [
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => $expiresAt,
        ];

        $this->db->execute($sql, $params);
    }

    /**
     * Verifica se usuário está autenticado
     */
    public function check(): bool
    {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return false;
        }

        // Verifica timeout de inatividade
        $sessionLifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 7200);
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionLifetime) {
            $this->logout();
            return false;
        }

        // Atualiza última atividade
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Obtém usuário autenticado
     */
    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
        ];
    }

    /**
     * Faz logout do usuário
     */
    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->logAudit($_SESSION['user_id'], 'logout', 'Logout realizado');

            // Remove sessão do banco
            $this->deleteSessionToken(session_id());
        }

        // Limpa sessão
        $_SESSION = [];

        // Destroi cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroi sessão
        session_destroy();
    }

    /**
     * Remove token de sessão do banco
     */
    private function deleteSessionToken(string $sessionToken): void
    {
        $sql = "DELETE FROM user_sessions WHERE session_token = :session_token";
        $this->db->execute($sql, ['session_token' => $sessionToken]);
    }

    /**
     * Verifica se usuário tem permissão
     */
    public function hasRole(string $role): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return $user['role'] === $role || $user['role'] === User::ROLE_ADMIN;
    }

    /**
     * Verifica se usuário pode acessar funcionalidade
     */
    public function can(string $feature): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return $this->userModel->canAccess($user, $feature);
    }

    /**
     * Limpa sessões expiradas do banco
     */
    public function cleanExpiredSessions(): int
    {
        $sql = "DELETE FROM user_sessions WHERE expires_at < datetime('now')";
        $this->db->execute($sql);

        return $this->db->getConnection()->rowCount();
    }

    /**
     * Registra ação no log de auditoria
     */
    public function logAudit(?int $userId, string $action, string $description): void
    {
        $sql = "INSERT INTO audit_log (user_id, action, description, ip_address)
                VALUES (:user_id, :action, :description, :ip_address)";

        $params = [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];

        $this->db->execute($sql, $params);
    }

    /**
     * Obtém logs de auditoria
     */
    public function getAuditLogs(int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email
                FROM audit_log a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT :limit OFFSET :offset";

        return $this->db->query($sql, ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * Obtém logs de um usuário específico
     */
    public function getUserAuditLogs(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM audit_log
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit";

        return $this->db->query($sql, ['user_id' => $userId, 'limit' => $limit]);
    }

    /**
     * Obtém sessões ativas
     */
    public function getActiveSessions(): array
    {
        $sql = "SELECT s.*, u.name, u.email
                FROM user_sessions s
                JOIN users u ON s.user_id = u.id
                WHERE s.expires_at > datetime('now')
                ORDER BY s.created_at DESC";

        return $this->db->query($sql);
    }

    /**
     * Força logout de um usuário (revoga todas as sessões)
     */
    public function forceLogout(int $userId): void
    {
        $sql = "DELETE FROM user_sessions WHERE user_id = :user_id";
        $this->db->execute($sql, ['user_id' => $userId]);

        $this->logAudit($userId, 'force_logout', 'Logout forçado por administrador');
    }
}
