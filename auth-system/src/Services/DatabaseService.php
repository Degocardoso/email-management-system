<?php

namespace Auth\Services;

use PDO;
use PDOException;

/**
 * Serviço de banco de dados SQLite
 * Gerencia conexão e operações do banco de dados
 */
class DatabaseService
{
    private static ?DatabaseService $instance = null;
    private ?PDO $connection = null;
    private string $dbPath;

    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct()
    {
        $this->dbPath = __DIR__ . '/../../database/auth.db';
        $this->connect();
    }

    /**
     * Obtém instância única do DatabaseService
     */
    public static function getInstance(): DatabaseService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conecta ao banco de dados SQLite
     */
    private function connect(): void
    {
        try {
            // Garante que o diretório existe
            $dir = dirname($this->dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $this->connection = new PDO(
                'sqlite:' . $this->dbPath,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Habilita foreign keys no SQLite
            $this->connection->exec('PRAGMA foreign_keys = ON');
        } catch (PDOException $e) {
            throw new \RuntimeException('Erro ao conectar ao banco de dados: ' . $e->getMessage());
        }
    }

    /**
     * Obtém a conexão PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Inicializa as tabelas do banco de dados
     */
    public function initializeTables(): void
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('admin', 'analyst', 'generator')),
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
        CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
        CREATE INDEX IF NOT EXISTS idx_users_active ON users(active);

        CREATE TABLE IF NOT EXISTS user_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            session_token TEXT NOT NULL UNIQUE,
            ip_address TEXT,
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_sessions_token ON user_sessions(session_token);
        CREATE INDEX IF NOT EXISTS idx_sessions_user ON user_sessions(user_id);

        CREATE TABLE IF NOT EXISTS audit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            description TEXT,
            ip_address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_log(user_id);
        CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_log(created_at);
        SQL;

        try {
            $this->connection->exec($sql);
        } catch (PDOException $e) {
            throw new \RuntimeException('Erro ao criar tabelas: ' . $e->getMessage());
        }
    }

    /**
     * Executa uma query e retorna os resultados
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new \RuntimeException('Erro ao executar query: ' . $e->getMessage());
        }
    }

    /**
     * Executa uma query e retorna uma única linha
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            throw new \RuntimeException('Erro ao executar query: ' . $e->getMessage());
        }
    }

    /**
     * Executa uma query de inserção/atualização/deleção
     */
    public function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \RuntimeException('Erro ao executar comando: ' . $e->getMessage());
        }
    }

    /**
     * Retorna o ID do último insert
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Inicia uma transação
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirma uma transação
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Reverte uma transação
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Previne clonagem da instância
     */
    private function __clone() {}

    /**
     * Previne unserialization da instância
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
