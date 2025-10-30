<?php

namespace App\Services;

use PDO;
use PDOException;
use Exception;

/**
 * Serviço de Conexão com Banco de Dados MySQL
 *
 * Gerencia a conexão com MySQL usando PDO com padrão Singleton
 */
class DatabaseService
{
    private static $instance = null;
    private $connection = null;
    private $config;

    /**
     * Construtor privado (Singleton)
     */
    private function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Obtém a instância única do DatabaseService
     */
    public static function getInstance(array $config = null): self
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new Exception('Database config is required for first initialization');
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Estabelece conexão com o banco de dados
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            throw new Exception(
                'Database connection failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Retorna a conexão PDO
     */
    public function getConnection(): PDO
    {
        // Verifica se a conexão ainda está ativa
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Executa uma query e retorna todos os resultados
     *
     * @param string $sql Query SQL
     * @param array $params Parâmetros para prepared statement
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception('Query failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Executa uma query e retorna apenas um resultado
     *
     * @param string $sql Query SQL
     * @param array $params Parâmetros para prepared statement
     * @return array|null
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            throw new Exception('Query failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Executa um comando (INSERT, UPDATE, DELETE)
     *
     * @param string $sql Query SQL
     * @param array $params Parâmetros para prepared statement
     * @return int Número de linhas afetadas
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception('Execute failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Insere um registro e retorna o ID inserido
     *
     * @param string $sql Query SQL
     * @param array $params Parâmetros para prepared statement
     * @return int ID do registro inserido
     */
    public function insert(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception('Insert failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
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
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Verifica se está em uma transação
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Testa a conexão com o banco de dados
     */
    public function testConnection(): bool
    {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fecha a conexão com o banco de dados
     */
    public function close(): void
    {
        $this->connection = null;
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
        throw new Exception("Cannot unserialize singleton");
    }
}
