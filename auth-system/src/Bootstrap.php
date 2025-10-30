<?php

namespace Auth;

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Auth\Services\DatabaseService;

/**
 * Classe de inicialização da aplicação
 * Configura ambiente, logging e banco de dados
 */
class Bootstrap
{
    private static ?Bootstrap $instance = null;
    private Logger $logger;

    private function __construct()
    {
        $this->loadHelpers();
        $this->loadEnvironment();
        $this->setupErrorHandling();
        $this->setupLogging();
        $this->startSession();
        $this->initializeDatabase();
    }

    private function loadHelpers(): void
    {
        require_once __DIR__ . '/helpers.php';
    }

    public static function getInstance(): Bootstrap
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $envPath = __DIR__ . '/../';

        if (file_exists($envPath . '.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->load();
        }

        // Define padrões se não estiverem definidos
        $_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'production';
        $_ENV['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? 'false';
        $_ENV['SESSION_LIFETIME'] = $_ENV['SESSION_LIFETIME'] ?? '7200';
    }

    private function setupErrorHandling(): void
    {
        $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
        }
    }

    private function setupLogging(): void
    {
        $logPath = __DIR__ . '/../logs/app.log';
        $logDir = dirname($logPath);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->logger = new Logger('auth-system');
        $this->logger->pushHandler(new StreamHandler($logPath, Logger::INFO));
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionLifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 7200);

            ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
            ini_set('session.cookie_lifetime', (string)$sessionLifetime);
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');

            // Em produção, habilita secure cookies
            if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
                ini_set('session.cookie_secure', '1');
            }

            $sessionPath = __DIR__ . '/../storage/sessions';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0755, true);
            }

            session_save_path($sessionPath);
            session_start();
        }
    }

    private function initializeDatabase(): void
    {
        try {
            $db = DatabaseService::getInstance();

            // Verifica se banco já foi inicializado
            $dbPath = __DIR__ . '/../database/auth.db';
            if (!file_exists($dbPath)) {
                $db->initializeTables();
                $this->logger->info('Database initialized');
            }
        } catch (\Exception $e) {
            $this->logger->error('Database initialization failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
