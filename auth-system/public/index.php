<?php

/**
 * Front Controller - Ponto de entrada da aplicação
 *
 * Gerencia o roteamento e inicialização do sistema de autenticação
 */

// Configurações de timeout e memória
set_time_limit(300); // 5 minutos
ini_set('max_execution_time', '300');
ini_set('memory_limit', '512M');

// Exibição de erros (desabilite em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload do Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Bootstrap;
use App\Controllers\AuthController;

// Inicia sessão (se ainda não foi iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa a aplicação
try {
    $bootstrap = Bootstrap::getInstance();
    $logger = $bootstrap->getLogger();

    $logger->info('=== REQUISIÇÃO RECEBIDA ===', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);

    // Tratamento global de erros
    set_exception_handler(function ($exception) use ($logger, $bootstrap) {
        $logger->error('Exceção não capturada', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Em modo debug, exibe a exceção
        if ($bootstrap->getConfig('app.debug')) {
            echo '<pre style="background: #f8d7da; color: #721c24; padding: 20px; border-left: 4px solid #dc3545; margin: 20px;">';
            echo '<h2>Erro Fatal</h2>';
            echo '<strong>Mensagem:</strong> ' . htmlspecialchars($exception->getMessage()) . "\n";
            echo '<strong>Arquivo:</strong> ' . htmlspecialchars($exception->getFile()) . "\n";
            echo '<strong>Linha:</strong> ' . htmlspecialchars($exception->getLine()) . "\n\n";
            echo '<strong>Stack Trace:</strong>' . "\n";
            echo htmlspecialchars($exception->getTraceAsString());
            echo '</pre>';
        } else {
            echo '<h1>Erro</h1>';
            echo '<p>Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.</p>';
        }
        exit;
    });

    set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logger) {
        $logger->error('Erro PHP', [
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
        ]);
        return false;
    });

    // Instancia o controller
    $controller = new AuthController();

    // Sistema de Roteamento Simples
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Remove query string
    $path = parse_url($requestUri, PHP_URL_PATH);

    // Remove trailing slash (exceto se for a raiz)
    if ($path !== '/' && substr($path, -1) === '/') {
        $path = rtrim($path, '/');
    }

    $logger->info('Roteamento', [
        'path' => $path,
        'method' => $requestMethod
    ]);

    // Rotas da aplicação
    switch ($path) {
        case '/':
        case '/login':
            if ($requestMethod === 'GET') {
                $controller->showLoginForm();
            } elseif ($requestMethod === 'POST') {
                $controller->login();
            }
            break;

        case '/register':
            if ($requestMethod === 'GET') {
                $controller->showRegisterForm();
            } elseif ($requestMethod === 'POST') {
                $controller->register();
            }
            break;

        case '/dashboard':
            if ($requestMethod === 'GET') {
                $controller->dashboard();
            }
            break;

        case '/logout':
            if ($requestMethod === 'GET') {
                $controller->logout();
            }
            break;

        default:
            // Página 404
            http_response_code(404);
            $logger->warning('Rota não encontrada', ['path' => $path]);
            echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página não encontrada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
        }
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 3rem;
            color: #333;
            margin: 0 0 1rem;
        }
        p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        a {
            display: inline-block;
            background: #1aa97f;
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        a:hover {
            background: #168a68;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="fas fa-exclamation-triangle error-icon"></i>
        <h1>404</h1>
        <p>Página não encontrada!</p>
        <a href="/login"><i class="fas fa-home"></i> Voltar para o Login</a>
    </div>
</body>
</html>';
            break;
    }

} catch (Exception $e) {
    // Se o Bootstrap falhar, exibe erro básico
    error_log('Erro crítico: ' . $e->getMessage());

    echo '<h1>Erro de Inicialização</h1>';
    echo '<p>Não foi possível inicializar a aplicação.</p>';

    if (isset($bootstrap) && $bootstrap->getConfig('app.debug')) {
        echo '<pre>';
        echo 'Erro: ' . htmlspecialchars($e->getMessage()) . "\n";
        echo 'Arquivo: ' . htmlspecialchars($e->getFile()) . "\n";
        echo 'Linha: ' . htmlspecialchars($e->getLine()) . "\n\n";
        echo 'Stack Trace:' . "\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    }
    exit;
}
