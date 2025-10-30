<?php

namespace App;

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use App\Services\DatabaseService;

/**
 * Classe responsável pela inicialização da aplicação
 *
 * Carrega configurações, inicializa serviços e prepara o ambiente
 */
class Bootstrap
{
    private static $instance = null;
    private $config = [];
    private $logger;
    private $cache;
    private $db;

    /**
     * Construtor privado (Singleton)
     */
    private function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfigs();
        $this->setupLogger();
        $this->setupCache();
        $this->setupSession();
        $this->setupDirectories();
        $this->setupDatabase();
    }

    /**
     * Retorna a instância única do Bootstrap
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Carrega variáveis de ambiente do arquivo .env
     */
    private function loadEnvironment(): void
    {
        $envPath = dirname(__DIR__);

        if (file_exists($envPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->load();
        }
    }

    /**
     * Carrega arquivos de configuração
     */
    private function loadConfigs(): void
    {
        $this->config['app'] = require __DIR__ . '/../config/app.php';
        $this->config['database'] = require __DIR__ . '/../config/database.php';
    }

    /**
     * Configura o sistema de logs com Monolog
     */
    private function setupLogger(): void
    {
        $logConfig = $this->config['app']['log'];
        $this->logger = new Logger('auth_system');

        $logDir = dirname($logConfig['path']);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Garante que o arquivo existe e tem permissão de escrita
        $logFile = $logConfig['path'];
        if (!file_exists($logFile)) {
            touch($logFile);
            chmod($logFile, 0666);
        }

        // Verifica se é gravável
        if (!is_writable($logFile)) {
            chmod($logFile, 0666);
        }

        $this->logger->pushHandler(
            new StreamHandler($logFile, $this->mapLogLevel($logConfig['level']))
        );

        // Teste de gravação
        $this->logger->info('Logger inicializado com sucesso', [
            'log_file' => $logFile,
            'log_level' => $logConfig['level']
        ]);
    }

    /**
     * Configura o sistema de cache (Redis ou Filesystem)
     */
    private function setupCache(): void
    {
        $cacheConfig = $this->config['app']['cache'];

        if ($cacheConfig['driver'] === 'redis' && extension_loaded('redis')) {
            try {
                $redis = RedisAdapter::createConnection(
                    "redis://{$cacheConfig['redis']['host']}:{$cacheConfig['redis']['port']}",
                    ['auth' => $cacheConfig['redis']['password']]
                );
                $this->cache = new RedisAdapter($redis, 'auth_', 3600);
                $this->logger->info('Cache Redis inicializado');
            } catch (\Exception $e) {
                $this->logger->warning('Redis não disponível, usando filesystem', ['error' => $e->getMessage()]);
                $this->cache = $this->createFilesystemCache($cacheConfig);
            }
        } else {
            $this->cache = $this->createFilesystemCache($cacheConfig);
        }
    }

    /**
     * Cria cache baseado em sistema de arquivos
     */
    private function createFilesystemCache(array $config): FilesystemAdapter
    {
        $cachePath = $config['filesystem']['path'];
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        $this->logger->info('Cache Filesystem inicializado', ['path' => $cachePath]);
        return new FilesystemAdapter('auth_', 3600, $cachePath);
    }

    /**
     * Configura o sistema de sessões
     */
    private function setupSession(): void
    {
        // Só configura sessão se ainda não foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            $sessionConfig = $this->config['app']['session'];
            $sessionPath = $sessionConfig['path'];

            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0755, true);
            }

            // Configura ANTES de iniciar a sessão
            ini_set('session.save_path', $sessionPath);
            ini_set('session.gc_maxlifetime', $sessionConfig['lifetime'] * 60);
            ini_set('session.cookie_httponly', $sessionConfig['cookie_httponly']);
            ini_set('session.cookie_secure', $sessionConfig['cookie_secure']);
            session_name($sessionConfig['cookie_name']);

            session_start();

            $this->logger->info('Sessão inicializada', [
                'session_id' => session_id(),
                'session_path' => $sessionPath
            ]);
        }
    }

    /**
     * Cria diretórios necessários para a aplicação
     */
    private function setupDirectories(): void
    {
        $dirs = [
            __DIR__ . '/../logs',
            __DIR__ . '/../storage/cache',
            __DIR__ . '/../storage/sessions',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Inicializa a conexão com o banco de dados
     */
    private function setupDatabase(): void
    {
        try {
            $this->db = DatabaseService::getInstance($this->config['database']);

            // Testa a conexão
            if ($this->db->testConnection()) {
                $this->logger->info('Conexão com banco de dados estabelecida', [
                    'host' => $this->config['database']['host'],
                    'database' => $this->config['database']['database']
                ]);
            } else {
                $this->logger->error('Falha ao testar conexão com banco de dados');
            }
        } catch (\Exception $e) {
            $this->logger->error('Erro ao conectar ao banco de dados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Mapeia o nível de log de string para constante do Monolog
     */
    private function mapLogLevel(string $level): int
    {
        $levels = [
            'debug' => Logger::DEBUG,
            'info' => Logger::INFO,
            'notice' => Logger::NOTICE,
            'warning' => Logger::WARNING,
            'error' => Logger::ERROR,
            'critical' => Logger::CRITICAL,
        ];

        return $levels[strtolower($level)] ?? Logger::ERROR;
    }

    /**
     * Retorna uma configuração específica
     *
     * @param string|null $key Chave da configuração (ex: 'app.debug')
     * @return mixed
     */
    public function getConfig(string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Retorna a instância do Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Retorna a instância do Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Retorna a instância do DatabaseService
     */
    public function getDatabase(): DatabaseService
    {
        return $this->db;
    }

    /**
     * Previne clonagem do objeto (Singleton)
     */
    private function __clone() {}

    /**
     * Previne deserialização do objeto (Singleton)
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
