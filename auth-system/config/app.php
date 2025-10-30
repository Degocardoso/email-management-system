<?php

/**
 * Configurações da Aplicação
 */

return [
    'name' => 'Sistema de Autenticação',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/auth-system/public',

    // Configuração de Logs
    'log' => [
        'path' => __DIR__ . '/../logs/app.log',
        'level' => $_ENV['LOG_LEVEL'] ?? 'error',
    ],

    // Configuração de Cache
    'cache' => [
        'driver' => !empty($_ENV['REDIS_HOST']) ? 'redis' : 'filesystem',
        'redis' => [
            'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => (int)($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        ],
        'filesystem' => [
            'path' => __DIR__ . '/../storage/cache',
        ],
    ],

    // Configuração de Sessão
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120), // minutos
        'path' => __DIR__ . '/../storage/sessions',
        'cookie_name' => 'auth_session',
        'cookie_httponly' => true,
        'cookie_secure' => false, // true em produção com HTTPS
    ],

    // Configuração de Segurança
    'security' => [
        'max_login_attempts' => 5,
        'lockout_time' => 15, // minutos
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numbers' => true,
        'password_require_special' => true,
    ],
];
