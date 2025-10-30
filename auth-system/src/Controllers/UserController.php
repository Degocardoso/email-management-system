<?php

namespace Auth\Controllers;

use Auth\Models\User;
use Auth\Services\AuthService;
use Auth\Middleware\AuthMiddleware;

/**
 * Controller de usuários
 * Gerencia CRUD de usuários (apenas para admins)
 */
class UserController
{
    private User $userModel;
    private AuthService $auth;
    private AuthMiddleware $middleware;

    public function __construct()
    {
        $this->userModel = new User();
        $this->auth = new AuthService();
        $this->middleware = new AuthMiddleware();
    }

    /**
     * Lista todos os usuários
     */
    public function index(): void
    {
        $this->middleware->requireUserManagement();

        $users = $this->userModel->findAll(true);
        $currentUser = $this->middleware->getUser();
        $csrfToken = $this->middleware->generateCsrfToken();

        include __DIR__ . '/../Views/users/index.php';
    }

    /**
     * Exibe formulário de criar usuário
     */
    public function create(): void
    {
        $this->middleware->requireUserManagement();

        $csrfToken = $this->middleware->generateCsrfToken();
        $roles = User::ROLES;

        include __DIR__ . '/../Views/users/create.php';
    }

    /**
     * Salva novo usuário
     */
    public function store(): void
    {
        $this->middleware->requireUserManagement();

        try {
            // Verifica CSRF
            if (!isset($_POST['csrf_token']) || !$this->middleware->verifyCsrfToken($_POST['csrf_token'])) {
                throw new \RuntimeException('Token CSRF inválido');
            }

            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? '',
                'active' => isset($_POST['active']) ? 1 : 0,
            ];

            // Valida confirmação de senha
            if (empty($data['password'])) {
                throw new \InvalidArgumentException('Senha é obrigatória');
            }

            if ($data['password'] !== ($_POST['password_confirmation'] ?? '')) {
                throw new \InvalidArgumentException('As senhas não conferem');
            }

            $userId = $this->userModel->create($data);

            if ($userId) {
                $this->auth->logAudit(
                    $this->middleware->getUser()['id'],
                    'user_created',
                    "Usuário criado: {$data['email']} (ID: $userId)"
                );

                $_SESSION['success'] = 'Usuário criado com sucesso!';
                redirect('users');
                exit;
            }

            throw new \RuntimeException('Erro ao criar usuário');

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            redirect('users/create');
            exit;
        }
    }

    /**
     * Exibe formulário de editar usuário
     */
    public function edit(): void
    {
        $this->middleware->requireUserManagement();

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = 'ID inválido';
            redirect('users');
            exit;
        }

        $user = $this->userModel->findById($id);
        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado';
            redirect('users');
            exit;
        }

        $csrfToken = $this->middleware->generateCsrfToken();
        $roles = User::ROLES;

        include __DIR__ . '/../Views/users/edit.php';
    }

    /**
     * Atualiza usuário
     */
    public function update(): void
    {
        $this->middleware->requireUserManagement();

        try {
            // Verifica CSRF
            if (!isset($_POST['csrf_token']) || !$this->middleware->verifyCsrfToken($_POST['csrf_token'])) {
                throw new \RuntimeException('Token CSRF inválido');
            }

            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                throw new \InvalidArgumentException('ID inválido');
            }

            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? '',
                'active' => isset($_POST['active']) ? 1 : 0,
            ];

            // Atualiza senha apenas se fornecida
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== ($_POST['password_confirmation'] ?? '')) {
                    throw new \InvalidArgumentException('As senhas não conferem');
                }
                $data['password'] = $_POST['password'];
            }

            if ($this->userModel->update($id, $data)) {
                $this->auth->logAudit(
                    $this->middleware->getUser()['id'],
                    'user_updated',
                    "Usuário atualizado: {$data['email']} (ID: $id)"
                );

                $_SESSION['success'] = 'Usuário atualizado com sucesso!';
            } else {
                throw new \RuntimeException('Erro ao atualizar usuário');
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('users');
        exit;
    }

    /**
     * Deleta usuário (soft delete)
     */
    public function delete(): void
    {
        $this->middleware->requireUserManagement();

        try {
            // Verifica CSRF
            if (!isset($_POST['csrf_token']) || !$this->middleware->verifyCsrfToken($_POST['csrf_token'])) {
                throw new \RuntimeException('Token CSRF inválido');
            }

            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                throw new \InvalidArgumentException('ID inválido');
            }

            // Não permite deletar a si mesmo
            if ($id === $this->middleware->getUser()['id']) {
                throw new \RuntimeException('Você não pode deletar sua própria conta');
            }

            $user = $this->userModel->findById($id);
            if (!$user) {
                throw new \RuntimeException('Usuário não encontrado');
            }

            if ($this->userModel->delete($id)) {
                $this->auth->logAudit(
                    $this->middleware->getUser()['id'],
                    'user_deleted',
                    "Usuário inativado: {$user['email']} (ID: $id)"
                );

                // Força logout do usuário deletado
                $this->auth->forceLogout($id);

                $_SESSION['success'] = 'Usuário inativado com sucesso!';
            } else {
                throw new \RuntimeException('Erro ao deletar usuário');
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('users');
        exit;
    }

    /**
     * Exibe logs de auditoria
     */
    public function auditLogs(): void
    {
        $this->middleware->requireUserManagement();

        $page = (int)($_GET['page'] ?? 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $logs = $this->auth->getAuditLogs($limit, $offset);
        $currentUser = $this->middleware->getUser();

        include __DIR__ . '/../Views/users/audit.php';
    }

    /**
     * Exibe sessões ativas
     */
    public function activeSessions(): void
    {
        $this->middleware->requireUserManagement();

        $sessions = $this->auth->getActiveSessions();
        $currentUser = $this->middleware->getUser();
        $csrfToken = $this->middleware->generateCsrfToken();

        include __DIR__ . '/../Views/users/sessions.php';
    }

    /**
     * Força logout de um usuário
     */
    public function forceLogout(): void
    {
        $this->middleware->requireUserManagement();

        try {
            // Verifica CSRF
            if (!isset($_POST['csrf_token']) || !$this->middleware->verifyCsrfToken($_POST['csrf_token'])) {
                throw new \RuntimeException('Token CSRF inválido');
            }

            $userId = (int)($_POST['user_id'] ?? 0);
            if (!$userId) {
                throw new \InvalidArgumentException('ID inválido');
            }

            $this->auth->forceLogout($userId);
            $_SESSION['success'] = 'Usuário desconectado com sucesso!';

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('users/sessions');
        exit;
    }
}
