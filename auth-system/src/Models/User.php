<?php

namespace App\Models;

use App\Services\DatabaseService;
use App\Bootstrap;

/**
 * Model de Usuário
 *
 * Gerencia operações relacionadas a usuários no banco de dados
 */
class User
{
    private $db;
    private $logger;

    // Propriedades do usuário
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $permissions; // JSON array de permissões
    public $description;
    public $status;
    public $last_login;
    public $login_attempts;
    public $blocked_until;
    public $created_at;
    public $updated_at;

    public function __construct()
    {
        $bootstrap = Bootstrap::getInstance();
        $this->db = $bootstrap->getDatabase();
        $this->logger = $bootstrap->getLogger();
    }

    /**
     * Busca usuário por email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?self
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $result = $this->db->queryOne($sql, [$email]);

        if ($result) {
            return $this->hydrate($result);
        }

        return null;
    }

    /**
     * Busca usuário por ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?self
    {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $result = $this->db->queryOne($sql, [$id]);

        if ($result) {
            return $this->hydrate($result);
        }

        return null;
    }

    /**
     * Cria um novo usuário
     *
     * @param array $data
     * @return int ID do usuário criado
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO users (name, email, password, role, status)
                VALUES (?, ?, ?, ?, ?)";

        $params = [
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'] ?? 'user',
            $data['status'] ?? 'active'
        ];

        $userId = $this->db->insert($sql, $params);

        $this->logger->info('Novo usuário criado', [
            'user_id' => $userId,
            'email' => $data['email'],
            'role' => $data['role'] ?? 'user'
        ]);

        // Registra no log de auditoria
        $this->logAudit($userId, 'user_created', 'Novo usuário registrado no sistema');

        return $userId;
    }

    /**
     * Atualiza dados do usuário
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'email', 'role', 'status'])) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        $affected = $this->db->execute($sql, $params);

        if ($affected > 0) {
            $this->logger->info('Usuário atualizado', ['user_id' => $id, 'fields' => array_keys($data)]);
            $this->logAudit($id, 'user_updated', 'Dados do usuário foram atualizados');
        }

        return $affected > 0;
    }

    /**
     * Verifica credenciais de login
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function authenticate(string $email, string $password): ?self
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            $this->logger->warning('Tentativa de login com email inexistente', ['email' => $email]);
            return null;
        }

        // Verifica se o usuário está bloqueado
        if ($user->isBlocked()) {
            $this->logger->warning('Tentativa de login com conta bloqueada', [
                'email' => $email,
                'blocked_until' => $user->blocked_until
            ]);
            return null;
        }

        // Verifica se o usuário está inativo
        if ($user->status !== 'active') {
            $this->logger->warning('Tentativa de login com conta inativa', [
                'email' => $email,
                'status' => $user->status
            ]);
            return null;
        }

        // Verifica a senha
        if (!password_verify($password, $user->password)) {
            $this->incrementLoginAttempts($user->id);
            $this->logger->warning('Senha incorreta', [
                'email' => $email,
                'attempts' => $user->login_attempts + 1
            ]);
            return null;
        }

        // Login bem-sucedido
        $this->resetLoginAttempts($user->id);
        $this->updateLastLogin($user->id);
        $this->logAudit($user->id, 'user_login', 'Usuário autenticado com sucesso');

        $this->logger->info('Login bem-sucedido', ['user_id' => $user->id, 'email' => $email]);

        return $user;
    }

    /**
     * Verifica se o usuário está bloqueado
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        if ($this->status === 'blocked') {
            // Se tem data de desbloqueio, verifica se já passou
            if ($this->blocked_until) {
                $blockedUntil = strtotime($this->blocked_until);
                $now = time();

                if ($now > $blockedUntil) {
                    // Desbloqueia automaticamente
                    $this->unblock($this->id);
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Incrementa tentativas de login
     *
     * @param int $userId
     */
    private function incrementLoginAttempts(int $userId): void
    {
        $sql = "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?";
        $this->db->execute($sql, [$userId]);

        // Verifica se atingiu o limite de tentativas
        $user = $this->findById($userId);
        $maxAttempts = Bootstrap::getInstance()->getConfig('app.security.max_login_attempts');

        if ($user && $user->login_attempts >= $maxAttempts) {
            $this->blockUser($userId);
        }
    }

    /**
     * Bloqueia usuário por excesso de tentativas
     *
     * @param int $userId
     */
    private function blockUser(int $userId): void
    {
        $lockoutTime = Bootstrap::getInstance()->getConfig('app.security.lockout_time');
        $blockedUntil = date('Y-m-d H:i:s', strtotime("+{$lockoutTime} minutes"));

        $sql = "UPDATE users SET status = 'blocked', blocked_until = ? WHERE id = ?";
        $this->db->execute($sql, [$blockedUntil, $userId]);

        $this->logger->warning('Usuário bloqueado por excesso de tentativas', [
            'user_id' => $userId,
            'blocked_until' => $blockedUntil
        ]);

        $this->logAudit($userId, 'user_blocked', 'Usuário bloqueado por excesso de tentativas de login');
    }

    /**
     * Desbloqueia usuário
     *
     * @param int $userId
     */
    private function unblock(int $userId): void
    {
        $sql = "UPDATE users SET status = 'active', login_attempts = 0, blocked_until = NULL WHERE id = ?";
        $this->db->execute($sql, [$userId]);

        $this->logger->info('Usuário desbloqueado', ['user_id' => $userId]);
        $this->logAudit($userId, 'user_unblocked', 'Usuário desbloqueado automaticamente');
    }

    /**
     * Reseta tentativas de login
     *
     * @param int $userId
     */
    private function resetLoginAttempts(int $userId): void
    {
        $sql = "UPDATE users SET login_attempts = 0 WHERE id = ?";
        $this->db->execute($sql, [$userId]);
    }

    /**
     * Atualiza data do último login
     *
     * @param int $userId
     */
    private function updateLastLogin(int $userId): void
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->execute($sql, [$userId]);
    }

    /**
     * Registra ação no log de auditoria
     *
     * @param int $userId
     * @param string $action
     * @param string $description
     */
    private function logAudit(int $userId, string $action, string $description): void
    {
        $sql = "INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?)";

        $params = [
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        $this->db->insert($sql, $params);
    }

    /**
     * Preenche o objeto com dados do banco
     *
     * @param array $data
     * @return User
     */
    private function hydrate(array $data): self
    {
        $user = new self();
        $user->id = $data['id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->role = $data['role'];
        $user->permissions = isset($data['permissions']) ? json_decode($data['permissions'], true) : [];
        $user->description = $data['description'] ?? null;
        $user->status = $data['status'];
        $user->last_login = $data['last_login'];
        $user->login_attempts = $data['login_attempts'];
        $user->blocked_until = $data['blocked_until'];
        $user->created_at = $data['created_at'];
        $user->updated_at = $data['updated_at'];

        return $user;
    }

    /**
     * Retorna todos os usuários
     *
     * @return array
     */
    public function all(): array
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        return $this->db->query($sql);
    }

    /**
     * Verifica se o email já existe
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $result = $this->db->queryOne($sql, [$email]);
        return $result['count'] > 0;
    }

    /**
     * Verifica se o usuário tem uma permissão específica
     *
     * @param string $permission Nome da permissão (gerador, report)
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Admin tem acesso a tudo
        if ($this->role === 'admin') {
            return true;
        }

        // Verifica se a permissão está no array
        if (is_array($this->permissions)) {
            return in_array($permission, $this->permissions);
        }

        return false;
    }

    /**
     * Verifica se tem acesso ao Gerador de E-mails
     *
     * @return bool
     */
    public function hasGeradorAccess(): bool
    {
        return $this->hasPermission('gerador');
    }

    /**
     * Verifica se tem acesso aos Relatórios
     *
     * @return bool
     */
    public function hasReportAccess(): bool
    {
        return $this->hasPermission('report');
    }

    /**
     * Retorna array com todas as permissões do usuário
     *
     * @return array
     */
    public function getPermissions(): array
    {
        // Admin tem todas as permissões
        if ($this->role === 'admin') {
            return ['gerador', 'report'];
        }

        return is_array($this->permissions) ? $this->permissions : [];
    }

    /**
     * Retorna descrição legível da role
     *
     * @return string
     */
    public function getRoleDescription(): string
    {
        $descriptions = [
            'admin' => 'Administrador - Acesso Total',
            'gerador' => 'Gerador de E-mails',
            'report' => 'Relatórios',
            'user' => 'Usuário Padrão'
        ];

        return $descriptions[$this->role] ?? 'Usuário';
    }
}
