# 🚀 Instalação no WAMP - Guia Rápido e Simplificado

## ⚡ Solução Rápida para o Erro

Se você viu o erro do Composer, execute:

```bash
cd C:\wamp64\www\email-management-system\auth-system
composer dump-autoload
```

---

## 📋 PASSO A PASSO COMPLETO

### 🗄️ PASSO 1: Criar Banco de Dados

1. Acesse: **http://localhost/phpmyadmin**
2. Clique em **"SQL"** (aba no topo)
3. Cole e execute:

```sql
CREATE DATABASE auth_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. ✅ Pronto! Banco criado.

---

### 📝 PASSO 2: Configurar Credenciais

1. Abra o arquivo: `auth-system\.env`

2. **Se o arquivo não existir**, renomeie `.env.example` para `.env`

3. Edite para ficar assim:

```env
APP_ENV=development
APP_DEBUG=true
SESSION_LIFETIME=7200
LOG_LEVEL=info

# MySQL - Configure aqui
DB_HOST=localhost
DB_DATABASE=auth_system
DB_USERNAME=root
DB_PASSWORD=
```

**⚠️ IMPORTANTE:** Se seu MySQL tem senha, coloque em `DB_PASSWORD=suasenha`

---

### 🔧 PASSO 3: Atualizar DatabaseService.php

Abra: `auth-system\src\Services\DatabaseService.php`

#### 3.1 - Encontre a linha 43 (método `connect()`):

**ANTES:**
```php
$this->connection = new PDO(
    'sqlite:' . $this->dbPath,
    null,
    null,
```

**DEPOIS:**
```php
// Carrega configurações do .env
$host = $_ENV['DB_HOST'] ?? 'localhost';
$database = $_ENV['DB_DATABASE'] ?? 'auth_system';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

$this->connection = new PDO(
    "mysql:host={$host};dbname={$database};charset=utf8mb4",
    $username,
    $password,
```

#### 3.2 - Remova a linha 57 (ou próxima a ela):

**REMOVER ESTA LINHA:**
```php
// Habilita foreign keys no SQLite
$this->connection->exec('PRAGMA foreign_keys = ON');
```

#### 3.3 - Atualize o método `initializeTables()` (linha 73):

**Substitua TODO o conteúdo de `$sql` por:**

```php
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'analyst', 'generator') NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_token (session_token),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
```

**💾 Salve o arquivo!**

---

### 📦 PASSO 4: Instalar Dependências

Abra o **Prompt de Comando** (CMD) ou **PowerShell**:

```bash
cd C:\wamp64\www\email-management-system\auth-system

composer install

composer dump-autoload
```

---

### 👤 PASSO 5: Criar Usuário Admin

No mesmo terminal:

```bash
php setup.php
```

Preencha:
```
Nome completo: Admin Sistema
Email: admin@teste.com
Senha: admin123
Confirmar senha: admin123
```

✅ **Sucesso!** Você deve ver: "Setup concluído com sucesso!"

---

### 🌐 PASSO 6: Acessar o Sistema

Abra o navegador:

```
http://localhost/email-management-system/auth-system/public/
```

**Login:**
- Email: `admin@teste.com`
- Senha: `admin123`

---

## ✅ Checklist Rápido

- [ ] Banco `auth_system` criado no phpMyAdmin
- [ ] Arquivo `.env` criado e configurado
- [ ] `DatabaseService.php` atualizado (3 mudanças)
- [ ] Executei `composer install`
- [ ] Executei `composer dump-autoload`
- [ ] Executei `php setup.php` com sucesso
- [ ] Consigo acessar http://localhost/email-management-system/auth-system/public/
- [ ] Consigo fazer login

---

## 🔥 Atalho: Arquivo DatabaseService.php Completo

Se preferir, aqui está o arquivo **COMPLETO** já corrigido para MySQL.

Substitua TODO o conteúdo de `src\Services\DatabaseService.php` por este:

```php
<?php

namespace Auth\Services;

use PDO;
use PDOException;

/**
 * Serviço de banco de dados MySQL
 * Gerencia conexão e operações do banco de dados
 */
class DatabaseService
{
    private static ?DatabaseService $instance = null;
    private ?PDO $connection = null;

    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct()
    {
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
     * Conecta ao banco de dados MySQL
     */
    private function connect(): void
    {
        try {
            // Carrega configurações do .env
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $database = $_ENV['DB_DATABASE'] ?? 'auth_system';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            $this->connection = new PDO(
                "mysql:host={$host};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
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
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'analyst', 'generator') NOT NULL,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_role (role),
            INDEX idx_active (active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL UNIQUE,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            INDEX idx_token (session_token),
            INDEX idx_user (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_created (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
```

---

## ⚠️ Erros Comuns e Soluções

### ❌ Erro: "could not find driver"

**Solução:** Ativar PDO MySQL no WAMP

1. Clique no ícone do WAMP (bandeja do sistema)
2. PHP → PHP Extensions
3. Marque: `php_pdo_mysql`
4. Restart All Services

### ❌ Erro: "Access denied for user 'root'@'localhost'"

**Solução:** Verificar senha do MySQL

Se você tem senha no MySQL, edite `.env`:
```env
DB_PASSWORD=suasenha
```

### ❌ Erro: "Unknown database 'auth_system'"

**Solução:** Banco não foi criado

Acesse phpMyAdmin e execute:
```sql
CREATE DATABASE auth_system;
```

### ❌ Erro: 404 - Not Found

**Solução:** Habilitar mod_rewrite

1. WAMP → Apache → Apache Modules
2. Marque: `rewrite_module`
3. Restart All Services

---

## 🎯 Comandos Resumidos

```bash
# Navegar
cd C:\wamp64\www\email-management-system\auth-system

# Instalar
composer install
composer dump-autoload

# Criar admin
php setup.php

# Acessar
# http://localhost/email-management-system/auth-system/public/
```

---

## 📸 Como Deve Ficar

Após login, você verá:

```
┌────────────────────────────────────────┐
│ Dashboard                        Sair  │
└────────────────────────────────────────┘

Bem-vindo, Admin Sistema!
Você está logado como Administrador

┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│   Gerar     │ │  Analisar   │ │  Gerenciar  │
│   Emails    │ │   Emails    │ │  Usuários   │
└─────────────┘ └─────────────┘ └─────────────┘
```

---

## ✅ Pronto!

Agora você tem:
- ✅ Sistema de login funcionando
- ✅ Dashboard com controle de acesso
- ✅ Tela de gerenciamento de usuários
- ✅ 3 níveis de permissão (Admin, Analyst, Generator)

**Próximo passo:** Integrar com os outros sistemas (gerador e analisador de emails)

Precisa de ajuda? Me mande o erro completo que aparecer! 🚀
