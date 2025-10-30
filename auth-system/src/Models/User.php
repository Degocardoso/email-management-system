<?php

namespace Auth\Models;

use Auth\Services\DatabaseService;

/**
 * Modelo de usuário
 * Gerencia operações CRUD de usuários
 */
class User
{
    private DatabaseService $db;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_ANALYST = 'analyst';
    public const ROLE_GENERATOR = 'generator';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrador',
        self::ROLE_ANALYST => 'Analista',
        self::ROLE_GENERATOR => 'Gerador',
    ];

    public function __construct()
    {
        $this->db = DatabaseService::getInstance();
    }

    /**
     * Cria um novo usuário
     */
    public function create(array $data): ?int
    {
        // Validação
        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            throw new \InvalidArgumentException('Todos os campos são obrigatórios');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }

        if (!in_array($data['role'], array_keys(self::ROLES))) {
            throw new \InvalidArgumentException('Role inválida');
        }

        // Verifica se email já existe
        if ($this->findByEmail($data['email'])) {
            throw new \RuntimeException('Email já cadastrado');
        }

        // Hash da senha
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $sql = "INSERT INTO users (name, email, password, role, active)
                VALUES (:name, :email, :password, :role, :active)";

        $params = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => $hashedPassword,
            'role' => $data['role'],
            'active' => $data['active'] ?? 1,
        ];

        if ($this->db->execute($sql, $params)) {
            return (int)$this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Busca usuário por ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, name, email, role, active, created_at, updated_at
                FROM users WHERE id = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }

    /**
     * Busca usuário por email (incluindo senha para autenticação)
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, name, email, password, role, active, created_at, updated_at
                FROM users WHERE email = :email";
        return $this->db->queryOne($sql, ['email' => strtolower(trim($email))]);
    }

    /**
     * Busca usuário por email (sem senha)
     */
    public function findByEmailPublic(string $email): ?array
    {
        $sql = "SELECT id, name, email, role, active, created_at, updated_at
                FROM users WHERE email = :email";
        return $this->db->queryOne($sql, ['email' => strtolower(trim($email))]);
    }

    /**
     * Lista todos os usuários
     */
    public function findAll(bool $includeInactive = false): array
    {
        $sql = "SELECT id, name, email, role, active, created_at, updated_at
                FROM users";

        if (!$includeInactive) {
            $sql .= " WHERE active = 1";
        }

        $sql .= " ORDER BY name ASC";

        return $this->db->query($sql);
    }

    /**
     * Lista usuários por role
     */
    public function findByRole(string $role): array
    {
        if (!in_array($role, array_keys(self::ROLES))) {
            throw new \InvalidArgumentException('Role inválida');
        }

        $sql = "SELECT id, name, email, role, active, created_at, updated_at
                FROM users WHERE role = :role AND active = 1
                ORDER BY name ASC";

        return $this->db->query($sql, ['role' => $role]);
    }

    /**
     * Atualiza dados do usuário
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            throw new \RuntimeException('Usuário não encontrado');
        }

        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name']) && !empty($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = trim($data['name']);
        }

        if (isset($data['email']) && !empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Email inválido');
            }

            // Verifica se email já existe (exceto o próprio usuário)
            $existingUser = $this->findByEmailPublic($data['email']);
            if ($existingUser && $existingUser['id'] != $id) {
                throw new \RuntimeException('Email já cadastrado');
            }

            $fields[] = "email = :email";
            $params['email'] = strtolower(trim($data['email']));
        }

        if (isset($data['role']) && !empty($data['role'])) {
            if (!in_array($data['role'], array_keys(self::ROLES))) {
                throw new \InvalidArgumentException('Role inválida');
            }
            $fields[] = "role = :role";
            $params['role'] = $data['role'];
        }

        if (isset($data['active'])) {
            $fields[] = "active = :active";
            $params['active'] = $data['active'] ? 1 : 0;
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($fields)) {
            return true; // Nada para atualizar
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

        return $this->db->execute($sql, $params);
    }

    /**
     * Deleta um usuário (soft delete - marca como inativo)
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE users SET active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Deleta permanentemente um usuário
     */
    public function hardDelete(int $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Verifica se a senha está correta
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Conta total de usuários
     */
    public function count(bool $onlyActive = true): int
    {
        $sql = "SELECT COUNT(*) as total FROM users";
        if ($onlyActive) {
            $sql .= " WHERE active = 1";
        }

        $result = $this->db->queryOne($sql);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Verifica se usuário tem permissão
     */
    public function hasPermission(array $user, string $requiredRole): bool
    {
        // Admin tem acesso a tudo
        if ($user['role'] === self::ROLE_ADMIN) {
            return true;
        }

        // Verifica se a role do usuário corresponde à requerida
        return $user['role'] === $requiredRole;
    }

    /**
     * Verifica se usuário pode acessar funcionalidade
     */
    public function canAccess(array $user, string $feature): bool
    {
        $permissions = [
            'generate_emails' => [self::ROLE_ADMIN, self::ROLE_GENERATOR],
            'analyze_emails' => [self::ROLE_ADMIN, self::ROLE_ANALYST],
            'manage_users' => [self::ROLE_ADMIN],
        ];

        if (!isset($permissions[$feature])) {
            return false;
        }

        return in_array($user['role'], $permissions[$feature]);
    }

    /**
     * Retorna nome amigável da role
     */
    public static function getRoleName(string $role): string
    {
        return self::ROLES[$role] ?? 'Desconhecido';
    }
}
