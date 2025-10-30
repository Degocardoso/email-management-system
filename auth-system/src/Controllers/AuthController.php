<?php

namespace App\Controllers;

use App\Bootstrap;
use App\Models\User;
use App\Validators\LoginValidator;
use App\Validators\RegisterValidator;

/**
 * Controller de Autenticação
 *
 * Gerencia login, logout e registro de usuários
 */
class AuthController
{
    private $logger;
    private $userModel;
    private $loginValidator;
    private $registerValidator;

    public function __construct()
    {
        $bootstrap = Bootstrap::getInstance();
        $this->logger = $bootstrap->getLogger();
        $this->userModel = new User();
        $this->loginValidator = new LoginValidator();
        $this->registerValidator = new RegisterValidator();
    }

    /**
     * Exibe o formulário de login
     */
    public function showLoginForm(): void
    {
        // Se já estiver autenticado, redireciona para dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'email' => $_SESSION['old_email'] ?? '',
        ];

        // Limpa mensagens da sessão
        unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old_email']);

        $this->render('login', $data);
    }

    /**
     * Processa o login
     */
    public function login(): void
    {
        $this->logger->info('Tentativa de login', ['email' => $_POST['email'] ?? '']);

        // Valida dados
        if (!$this->loginValidator->validate($_POST)) {
            $_SESSION['error'] = $this->loginValidator->getFirstError();
            $_SESSION['old_email'] = $_POST['email'] ?? '';
            $this->redirect('/login');
            return;
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        // Autentica usuário
        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            $_SESSION['error'] = 'Email ou senha incorretos.';
            $_SESSION['old_email'] = $email;
            $this->redirect('/login');
            return;
        }

        // Verifica se está bloqueado
        if ($user->isBlocked()) {
            $_SESSION['error'] = 'Sua conta está temporariamente bloqueada. Tente novamente mais tarde.';
            $this->redirect('/login');
            return;
        }

        // Cria sessão do usuário
        $this->createUserSession($user);

        $this->logger->info('Login realizado com sucesso', ['user_id' => $user->id]);

        // Redireciona para dashboard
        $this->redirect('/dashboard');
    }

    /**
     * Exibe o formulário de registro
     */
    public function showRegisterForm(): void
    {
        // Se já estiver autenticado, redireciona para dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'old_name' => $_SESSION['old_name'] ?? '',
            'old_email' => $_SESSION['old_email'] ?? '',
        ];

        // Limpa mensagens da sessão
        unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old_name'], $_SESSION['old_email']);

        $this->render('register', $data);
    }

    /**
     * Processa o registro
     */
    public function register(): void
    {
        $this->logger->info('Tentativa de registro', ['email' => $_POST['email'] ?? '']);

        // Valida dados
        if (!$this->registerValidator->validate($_POST)) {
            $_SESSION['error'] = $this->registerValidator->getFirstError();
            $_SESSION['old_name'] = $_POST['name'] ?? '';
            $_SESSION['old_email'] = $_POST['email'] ?? '';
            $this->redirect('/register');
            return;
        }

        $email = $_POST['email'];

        // Verifica se email já existe
        if ($this->userModel->emailExists($email)) {
            $_SESSION['error'] = 'Este email já está cadastrado no sistema.';
            $_SESSION['old_name'] = $_POST['name'] ?? '';
            $_SESSION['old_email'] = $email;
            $this->redirect('/register');
            return;
        }

        // Cria usuário
        try {
            $userId = $this->userModel->create([
                'name' => $_POST['name'],
                'email' => $email,
                'password' => $_POST['password'],
                'role' => 'user',
                'status' => 'active'
            ]);

            $this->logger->info('Usuário registrado com sucesso', [
                'user_id' => $userId,
                'email' => $email
            ]);

            $_SESSION['success'] = 'Cadastro realizado com sucesso! Faça login para continuar.';
            $this->redirect('/login');
        } catch (\Exception $e) {
            $this->logger->error('Erro ao registrar usuário', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);

            $_SESSION['error'] = 'Erro ao realizar cadastro. Tente novamente mais tarde.';
            $_SESSION['old_name'] = $_POST['name'] ?? '';
            $_SESSION['old_email'] = $email;
            $this->redirect('/register');
        }
    }

    /**
     * Realiza logout
     */
    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        $this->logger->info('Logout realizado', ['user_id' => $userId]);

        // Destrói a sessão
        session_unset();
        session_destroy();

        // Inicia nova sessão para mensagem
        session_start();
        $_SESSION['success'] = 'Logout realizado com sucesso!';

        $this->redirect('/login');
    }

    /**
     * Exibe o dashboard (página protegida)
     */
    public function dashboard(): void
    {
        // Verifica autenticação
        if (!$this->isAuthenticated()) {
            $_SESSION['error'] = 'Você precisa estar autenticado para acessar esta página.';
            $this->redirect('/login');
            return;
        }

        $user = $this->getAuthenticatedUser();

        $data = [
            'user' => $user,
            'success' => $_SESSION['success'] ?? null,
        ];

        unset($_SESSION['success']);

        $this->render('dashboard', $data);
    }

    /**
     * Cria sessão do usuário autenticado
     *
     * @param User $user
     */
    private function createUserSession(User $user): void
    {
        // Regenera ID da sessão por segurança
        session_regenerate_id(true);

        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_permissions'] = $user->getPermissions();
        $_SESSION['user_description'] = $user->getRoleDescription();
        $_SESSION['login_time'] = time();
    }

    /**
     * Verifica se usuário está autenticado
     *
     * @return bool
     */
    private function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    /**
     * Retorna dados do usuário autenticado
     *
     * @return array|null
     */
    private function getAuthenticatedUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
            'permissions' => $_SESSION['user_permissions'] ?? [],
            'description' => $_SESSION['user_description'] ?? 'Usuário',
        ];
    }

    /**
     * Renderiza uma view
     *
     * @param string $view
     * @param array $data
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }

    /**
     * Redireciona para uma URL
     *
     * @param string $path
     */
    private function redirect(string $path): void
    {
        header("Location: $path");
        exit;
    }
}
