# 📝 Notas de Instalação

## ⚠️ Importante: Configuração do Banco de Dados

O sistema foi desenvolvido para usar **SQLite** por padrão, mas o ambiente atual não possui a extensão `pdo_sqlite` instalada.

### Opções disponíveis:

#### Opção 1: Usar MySQL (Recomendado para este ambiente)

O sistema pode ser facilmente adaptado para MySQL. Para isso:

1. **Crie um banco de dados MySQL:**
```sql
CREATE DATABASE auth_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'auth_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON auth_system.* TO 'auth_user'@'localhost';
FLUSH PRIVILEGES;
```

2. **Atualize o arquivo `src/Services/DatabaseService.php`:**

Substitua a linha de conexão (linha ~43):
```php
// DE:
$this->connection = new PDO(
    'sqlite:' . $this->dbPath,
    null,
    null,
    [...]
);

// PARA:
$this->connection = new PDO(
    'mysql:host=localhost;dbname=auth_system;charset=utf8mb4',
    'auth_user',
    'senha_segura',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
```

3. **Atualize as queries SQL para MySQL:**

No método `initializeTables()` do mesmo arquivo, ajuste as queries:

```sql
-- Substitua AUTOINCREMENT por AUTO_INCREMENT
-- Substitua INTEGER por INT
-- Substitua TEXT por VARCHAR
-- Substitua DATETIME DEFAULT CURRENT_TIMESTAMP (funciona em ambos)
```

Exemplo:
```sql
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL CHECK(role IN ('admin', 'analyst', 'generator')),
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Opção 2: Instalar extensão SQLite

Se preferir usar SQLite (mais simples para desenvolvimento):

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install php-sqlite3 php-pdo-sqlite
sudo service apache2 restart  # ou nginx + php-fpm
```

**CentOS/RHEL:**
```bash
sudo yum install php-pdo php-sqlite3
sudo systemctl restart httpd
```

Depois execute:
```bash
php setup.php
```

## 📦 Arquivos Criados

### Estrutura completa do sistema:

```
auth-system/
├── .env                          # Configurações de ambiente
├── .env.example                  # Template de configuração
├── .gitignore                    # Arquivos ignorados pelo git
├── .htaccess                     # Configuração Apache (na public/)
├── README.md                     # Documentação principal
├── INSTALL_NOTES.md             # Este arquivo
├── composer.json                 # Dependências
├── setup.php                     # Script de instalação
│
├── config/                       # Configurações (reservado para expansão)
│
├── database/                     # Banco de dados
│   └── auth.db                  # Criado automaticamente pelo setup
│
├── logs/                         # Logs da aplicação
│   └── app.log                  # Log principal
│
├── public/                       # Pasta pública (DocumentRoot)
│   ├── .htaccess                # Rewrite rules
│   └── index.php                # Front controller (roteamento)
│
├── src/                          # Código fonte
│   ├── Bootstrap.php            # Inicialização da aplicação
│   │
│   ├── Controllers/             # Controllers
│   │   ├── AuthController.php  # Login/Logout/Dashboard
│   │   └── UserController.php  # CRUD de usuários
│   │
│   ├── Middleware/              # Middlewares
│   │   └── AuthMiddleware.php  # Proteção de rotas
│   │
│   ├── Models/                  # Modelos
│   │   └── User.php            # Modelo de usuário
│   │
│   ├── Services/                # Serviços
│   │   ├── AuthService.php     # Lógica de autenticação
│   │   └── DatabaseService.php # Conexão com banco
│   │
│   └── Views/                   # Views (templates HTML)
│       ├── login.php            # Tela de login
│       ├── dashboard.php        # Dashboard principal
│       ├── unauthorized.php     # Acesso negado
│       │
│       └── users/               # Gerenciamento de usuários
│           ├── index.php        # Listar usuários
│           ├── create.php       # Criar usuário
│           └── edit.php         # Editar usuário
│
├── storage/                      # Storage
│   ├── cache/                   # Cache da aplicação
│   └── sessions/                # Sessões PHP
│
└── vendor/                       # Dependências do Composer
```

## 🎯 Funcionalidades Implementadas

### ✅ Sistema de Autenticação
- Login com email/senha
- Logout seguro
- Sessões com timeout (2 horas padrão)
- Proteção CSRF
- Senhas criptografadas (bcrypt cost 12)

### ✅ Controle de Acesso (RBAC)
- **Admin:** Acesso total
- **Analyst:** Apenas análise de emails
- **Generator:** Apenas geração de emails

### ✅ Gerenciamento de Usuários
- Criar usuários
- Editar usuários
- Inativar usuários
- Listar usuários
- Visualizar sessões ativas
- Forçar logout
- Logs de auditoria

### ✅ Segurança
- Proteção XSS
- Proteção SQL Injection (prepared statements)
- Proteção CSRF
- Rate limiting preparado
- Audit logs completos
- Registro de IP e User-Agent

## 🔧 Próximos Passos

1. **Escolha o banco de dados** (MySQL ou SQLite)
2. **Execute o setup** para criar usuário admin
3. **Configure o servidor web** apontando para `auth-system/public/`
4. **Teste o login** acessando: `http://localhost/auth-system/public/`
5. **Integre com os outros sistemas** (gerador-de-emails e dynamics-email-report)

## 🔗 Integração com Outros Sistemas

Para proteger os outros sistemas com autenticação:

```php
// No início de cada arquivo que precisa autenticação:

require_once __DIR__ . '/../auth-system/vendor/autoload.php';

use Auth\Bootstrap;
use Auth\Services\AuthService;

Bootstrap::getInstance();
$auth = new AuthService();

// Verifica se está logado
if (!$auth->check()) {
    header('Location: /auth-system/public/login');
    exit;
}

// Obtém usuário
$user = $auth->user();

// Verifica permissão
if (!$auth->can('analyze_emails')) {
    die('Acesso negado');
}
```

## 📊 Estrutura do Banco de Dados

### Tabela: users
```sql
id              INT/INTEGER PRIMARY KEY
name            VARCHAR(255)/TEXT NOT NULL
email           VARCHAR(255)/TEXT NOT NULL UNIQUE
password        VARCHAR(255)/TEXT NOT NULL (bcrypt hash)
role            VARCHAR(50)/TEXT NOT NULL (admin, analyst, generator)
active          TINYINT(1)/INTEGER DEFAULT 1
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP [ON UPDATE...]
```

### Tabela: user_sessions
```sql
id              INT/INTEGER PRIMARY KEY
user_id         INT/INTEGER NOT NULL (FK -> users.id)
session_token   VARCHAR(255)/TEXT NOT NULL UNIQUE
ip_address      VARCHAR(45)/TEXT
user_agent      TEXT
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
expires_at      DATETIME NOT NULL
```

### Tabela: audit_log
```sql
id              INT/INTEGER PRIMARY KEY
user_id         INT/INTEGER (FK -> users.id, NULL on delete)
action          VARCHAR(100)/TEXT NOT NULL
description     TEXT
ip_address      VARCHAR(45)/TEXT
created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
```

## ⚡ Performance

- Sessões em arquivos (pode migrar para Redis)
- Prepared statements (previne SQL injection e melhora cache)
- Índices no banco de dados
- Bcrypt cost 12 (balanceado entre segurança e performance)

## 🐛 Troubleshooting

**Erro: "could not find driver"**
- Instale a extensão PDO SQLite ou configure MySQL

**Erro: "Permission denied"**
```bash
chmod -R 755 auth-system/
chmod -R 777 auth-system/storage/ auth-system/logs/ auth-system/database/
```

**Erro: "Class not found"**
```bash
cd auth-system && composer dump-autoload
```

## 📞 Suporte

Para dúvidas ou problemas, consulte o README.md principal.
