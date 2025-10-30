# 🔐 Sistema de Autenticação e Controle de Acesso

Sistema centralizado de autenticação e gerenciamento de permissões para o projeto de gerenciamento de emails.

## 📋 Visão Geral

Este sistema fornece autenticação segura e controle de acesso baseado em roles (funções) para os seguintes módulos:
- **Gerador de Emails** (`gerador-de-emails-master`)
- **Análise de Emails** (`dynamics-email-report`)

## 🎯 Funcionalidades

### Autenticação
- ✅ Login seguro com email e senha
- ✅ Senhas criptografadas com bcrypt (cost 12)
- ✅ Proteção CSRF em todos os formulários
- ✅ Gerenciamento de sessões com timeout configurável
- ✅ Logout com limpeza de sessão

### Controle de Acesso (RBAC)
Três níveis de permissão:

| Role | Descrição | Permissões |
|------|-----------|------------|
| **Admin** | Administrador | Acesso total: gerar emails, analisar emails e gerenciar usuários |
| **Analyst** | Analista | Apenas visualizar e analisar relatórios de emails |
| **Generator** | Gerador | Apenas gerar e enviar emails |

### Gerenciamento de Usuários (Admin)
- ✅ Criar, editar e inativar usuários
- ✅ Definir roles e permissões
- ✅ Visualizar sessões ativas
- ✅ Forçar logout de usuários
- ✅ Logs de auditoria completos

### Auditoria e Segurança
- ✅ Log de todas as ações (login, logout, criação de usuários, etc.)
- ✅ Registro de IP e user-agent
- ✅ Histórico de ações por usuário
- ✅ Sessões rastreáveis no banco de dados

## 🚀 Instalação

### Requisitos
- PHP >= 7.4
- Extensões PHP: PDO, JSON
- Composer
- Servidor web (Apache/Nginx)

### Passo a passo

1. **Clone o repositório e acesse a pasta:**
```bash
cd auth-system
```

2. **Instale as dependências:**
```bash
composer install
```

3. **Configure o ambiente:**
```bash
cp .env.example .env
```

Edite o `.env` conforme necessário:
```env
APP_ENV=production          # development ou production
APP_DEBUG=false             # true apenas em desenvolvimento
SESSION_LIFETIME=7200       # 2 horas em segundos
LOG_LEVEL=info              # debug, info, warning, error
```

4. **Execute o setup (cria banco e usuário admin):**
```bash
php setup.php
```

5. **Configure o servidor web:**

**Apache (.htaccess já incluído):**
```apache
DocumentRoot "/caminho/para/auth-system/public"

<Directory "/caminho/para/auth-system/public">
    AllowOverride All
    Require all granted
</Directory>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name auth.seudominio.com;
    root /caminho/para/auth-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

6. **Defina permissões:**
```bash
chmod -R 755 auth-system/
chmod -R 777 storage/ logs/ database/
```

7. **Acesse o sistema:**
```
http://localhost/auth-system/public/
```

## 📁 Estrutura do Projeto

```
auth-system/
├── config/                 # Arquivos de configuração (reservado)
├── database/              # Banco de dados SQLite
│   └── auth.db           # Gerado automaticamente
├── logs/                  # Logs da aplicação
│   └── app.log
├── public/               # Pasta pública (DocumentRoot)
│   └── index.php        # Front controller
├── src/                  # Código fonte
│   ├── Bootstrap.php    # Inicialização da aplicação
│   ├── Controllers/     # Controllers
│   │   ├── AuthController.php
│   │   └── UserController.php
│   ├── Middleware/      # Middlewares
│   │   └── AuthMiddleware.php
│   ├── Models/          # Modelos
│   │   └── User.php
│   ├── Services/        # Serviços
│   │   ├── AuthService.php
│   │   └── DatabaseService.php
│   └── Views/           # Views (templates HTML)
│       ├── login.php
│       ├── dashboard.php
│       ├── unauthorized.php
│       └── users/       # Views de gerenciamento
│           ├── index.php
│           ├── create.php
│           └── edit.php
├── storage/              # Storage de sessões e cache
│   ├── cache/
│   └── sessions/
├── vendor/               # Dependências do Composer
├── .env                  # Configurações de ambiente
├── .env.example          # Template de configuração
├── .gitignore
├── composer.json
└── setup.php            # Script de instalação
```

## 🗄️ Banco de Dados

O sistema usa **SQLite** para simplicidade. O banco é criado automaticamente em `database/auth.db`.

### Tabelas

**users** - Usuários do sistema
```sql
id, name, email, password, role, active, created_at, updated_at
```

**user_sessions** - Sessões ativas
```sql
id, user_id, session_token, ip_address, user_agent, created_at, expires_at
```

**audit_log** - Logs de auditoria
```sql
id, user_id, action, description, ip_address, created_at
```

## 🔧 Uso

### Criando usuários manualmente (via código)

```php
use Auth\Models\User;

$userModel = new User();
$userId = $userModel->create([
    'name' => 'João Silva',
    'email' => 'joao@example.com',
    'password' => 'senha123',
    'role' => 'analyst',  // admin, analyst, generator
    'active' => 1
]);
```

### Verificando permissões

```php
use Auth\Services\AuthService;

$auth = new AuthService();

// Verifica se está logado
if ($auth->check()) {
    $user = $auth->user();
}

// Verifica permissão específica
if ($auth->can('generate_emails')) {
    // Usuário pode gerar emails
}

if ($auth->can('analyze_emails')) {
    // Usuário pode analisar emails
}

if ($auth->can('manage_users')) {
    // Usuário pode gerenciar usuários (admin)
}
```

### Protegendo rotas

```php
use Auth\Middleware\AuthMiddleware;

$middleware = new AuthMiddleware();

// Requer autenticação
$middleware->requireAuth();

// Requer role específica
$middleware->requireRole('admin');

// Requer permissão para gerar emails
$middleware->requireGeneratorAccess();

// Requer permissão para analisar emails
$middleware->requireAnalystAccess();

// Requer acesso de admin
$middleware->requireUserManagement();
```

## 🔗 Integração com Outros Sistemas

Para integrar o sistema de autenticação com os outros módulos:

1. **Inclua o autoloader no início do arquivo:**
```php
require_once __DIR__ . '/../auth-system/vendor/autoload.php';
```

2. **Inicialize o Bootstrap:**
```php
use Auth\Bootstrap;
Bootstrap::getInstance();
```

3. **Verifique autenticação:**
```php
use Auth\Services\AuthService;

$auth = new AuthService();

if (!$auth->check()) {
    header('Location: /auth-system/public/login');
    exit;
}

$user = $auth->user();

// Verifica permissão específica
if (!$auth->can('analyze_emails')) {
    die('Acesso negado');
}
```

## 📊 Logs de Auditoria

Todas as ações importantes são registradas:
- Login bem-sucedido
- Tentativa de login falha
- Logout
- Criação de usuário
- Atualização de usuário
- Inativação de usuário
- Logout forçado

Acesse em: `/users/audit` (apenas admin)

## 🔒 Segurança

- **Senhas:** Bcrypt com cost 12
- **Sessões:** HTTPOnly cookies, regeneração de ID
- **CSRF:** Tokens em todos os formulários POST
- **SQL Injection:** Prepared statements (PDO)
- **XSS:** htmlspecialchars() em todas as outputs
- **Auditoria:** Log completo de ações

## 🛠️ Manutenção

### Limpar sessões expiradas
```php
use Auth\Services\AuthService;

$auth = new AuthService();
$deleted = $auth->cleanExpiredSessions();
echo "$deleted sessões expiradas foram removidas";
```

### Forçar logout de um usuário
```php
$auth->forceLogout($userId);
```

### Backup do banco de dados
```bash
cp database/auth.db database/auth_backup_$(date +%Y%m%d).db
```

## 📝 Credenciais Padrão

Após executar `setup.php`, as credenciais são definidas por você.

**Exemplo:**
- Email: admin@example.com
- Senha: (definida durante o setup)

⚠️ **IMPORTANTE:** Altere as credenciais padrão em produção!

## 🐛 Troubleshooting

**Erro: "Database initialization failed"**
- Verifique permissões da pasta `database/` (777)

**Erro: "Session path not writable"**
- Verifique permissões da pasta `storage/sessions/` (777)

**Erro: "404 Not Found"**
- Verifique configuração do servidor web
- Confirme que o DocumentRoot aponta para `public/`
- Verifique se mod_rewrite está habilitado (Apache)

**Esqueci a senha do admin**
- Execute `php setup.php` novamente para criar novo admin

## 📄 Licença

MIT License - Veja LICENSE para detalhes

## 👥 Suporte

Para problemas ou dúvidas, abra uma issue no repositório.
