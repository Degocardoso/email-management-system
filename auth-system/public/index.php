<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Auth\Bootstrap;
use Auth\Controllers\AuthController;
use Auth\Controllers\UserController;

// Inicializa a aplicação
Bootstrap::getInstance();

// Roteamento simples
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove o base path para funcionar em subdiretórios
$basePath = getBasePath();
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Garante que sempre começa com /
if (empty($requestUri) || $requestUri[0] !== '/') {
    $requestUri = '/' . $requestUri;
}

// Remove trailing slash (exceto para root)
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Rotas
try {
    switch ($requestUri) {
        // Rota raiz - redireciona para login ou dashboard
        case '':
        case '/':
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                redirect('dashboard');
            } else {
                redirect('login');
            }
            break;

        // Autenticação
        case '/login':
            $controller = new AuthController();
            if ($requestMethod === 'POST') {
                $controller->login();
            } else {
                $controller->showLoginForm();
            }
            break;

        case '/logout':
            $controller = new AuthController();
            $controller->logout();
            break;

        case '/dashboard':
            $controller = new AuthController();
            $controller->dashboard();
            break;

        // Gerenciamento de usuários
        case '/users':
            $controller = new UserController();
            $controller->index();
            break;

        case '/users/create':
            $controller = new UserController();
            $controller->create();
            break;

        case '/users/store':
            if ($requestMethod === 'POST') {
                $controller = new UserController();
                $controller->store();
            } else {
                header('HTTP/1.0 404 Not Found');
                echo '404 - Página não encontrada';
            }
            break;

        case '/users/edit':
            $controller = new UserController();
            $controller->edit();
            break;

        case '/users/update':
            if ($requestMethod === 'POST') {
                $controller = new UserController();
                $controller->update();
            } else {
                header('HTTP/1.0 404 Not Found');
                echo '404 - Página não encontrada';
            }
            break;

        case '/users/delete':
            if ($requestMethod === 'POST') {
                $controller = new UserController();
                $controller->delete();
            } else {
                header('HTTP/1.0 404 Not Found');
                echo '404 - Página não encontrada';
            }
            break;

        case '/users/audit':
            $controller = new UserController();
            $controller->auditLogs();
            break;

        case '/users/sessions':
            $controller = new UserController();
            $controller->activeSessions();
            break;

        case '/users/force-logout':
            if ($requestMethod === 'POST') {
                $controller = new UserController();
                $controller->forceLogout();
            } else {
                header('HTTP/1.0 404 Not Found');
                echo '404 - Página não encontrada';
            }
            break;

        default:
            header('HTTP/1.0 404 Not Found');
            echo '<!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>404 - Página não encontrada</title>
                <style>
                    body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f5f7fa; }
                    .error { text-align: center; }
                    h1 { font-size: 72px; color: #667eea; margin: 0; }
                    p { color: #666; font-size: 18px; }
                    a { color: #667eea; text-decoration: none; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="error">
                    <h1>404</h1>
                    <p>Página não encontrada</p>
                    <a href="' . url('dashboard') . '">Voltar ao Dashboard</a>
                </div>
            </body>
            </html>';
            break;
    }
} catch (\Exception $e) {
    // Em produção, não mostra detalhes do erro
    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        echo '<pre>';
        echo 'Erro: ' . $e->getMessage() . "\n";
        echo 'Arquivo: ' . $e->getFile() . ':' . $e->getLine() . "\n";
        echo $e->getTraceAsString();
        echo '</pre>';
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo '<!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Erro</title>
            <style>
                body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f5f7fa; }
                .error { text-align: center; }
                h1 { color: #dc3545; }
            </style>
        </head>
        <body>
            <div class="error">
                <h1>Erro no sistema</h1>
                <p>Ocorreu um erro. Por favor, tente novamente mais tarde.</p>
                <a href="' . url('dashboard') . '">Voltar ao Dashboard</a>
            </div>
        </body>
        </html>';
    }

    // Log do erro
    $logger = Bootstrap::getInstance()->getLogger();
    $logger->error('Application error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'uri' => $requestUri,
    ]);
}
