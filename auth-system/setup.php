<?php

/**
 * Script de instalação do sistema de autenticação
 * Cria o banco de dados e o usuário administrador padrão
 */

require_once __DIR__ . '/vendor/autoload.php';

use Auth\Services\DatabaseService;
use Auth\Models\User;

echo "\n=== Sistema de Autenticação - Setup ===\n\n";

try {
    // Inicializa o banco de dados
    echo "Inicializando banco de dados...\n";
    $db = DatabaseService::getInstance();
    $db->initializeTables();
    echo "✓ Banco de dados inicializado com sucesso\n\n";

    // Verifica se já existe algum usuário admin
    $userModel = new User();
    $adminCount = count($userModel->findByRole('admin'));

    if ($adminCount > 0) {
        echo "⚠ Já existe(m) $adminCount usuário(s) administrador(es) no sistema.\n";
        echo "Deseja criar outro usuário admin? (s/n): ";
        $response = trim(fgets(STDIN));

        if (strtolower($response) !== 's') {
            echo "\nSetup cancelado.\n";
            exit(0);
        }
    }

    // Coleta dados do administrador
    echo "Criando usuário administrador...\n\n";

    echo "Nome completo: ";
    $name = trim(fgets(STDIN));

    echo "Email: ";
    $email = trim(fgets(STDIN));

    echo "Senha: ";
    // Desabilita eco do terminal para senha
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $password = trim(fgets(STDIN));
    } else {
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
    }
    echo "\n";

    echo "Confirmar senha: ";
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $passwordConfirm = trim(fgets(STDIN));
    } else {
        system('stty -echo');
        $passwordConfirm = trim(fgets(STDIN));
        system('stty echo');
    }
    echo "\n\n";

    // Validações
    if (empty($name) || empty($email) || empty($password)) {
        throw new \InvalidArgumentException('Todos os campos são obrigatórios');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException('Email inválido');
    }

    if ($password !== $passwordConfirm) {
        throw new \InvalidArgumentException('As senhas não conferem');
    }

    if (strlen($password) < 6) {
        throw new \InvalidArgumentException('A senha deve ter no mínimo 6 caracteres');
    }

    // Cria o usuário admin
    $userId = $userModel->create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'role' => 'admin',
        'active' => 1,
    ]);

    if ($userId) {
        echo "✓ Usuário administrador criado com sucesso!\n";
        echo "\n=== Credenciais de Acesso ===\n";
        echo "Email: $email\n";
        echo "Senha: ********\n\n";
        echo "Você pode fazer login em: http://localhost/auth-system/public/\n";
        echo "\n✓ Setup concluído com sucesso!\n\n";
    } else {
        throw new \RuntimeException('Erro ao criar usuário administrador');
    }

} catch (\Exception $e) {
    echo "\n✗ Erro: " . $e->getMessage() . "\n\n";
    exit(1);
}
