# 🔐 Sistema de Permissões

## 📋 Visão Geral

O sistema possui **3 níveis de permissão**:

1. **🟢 Gerador** - Acesso ao Gerador de E-mails
2. **🟡 Report** - Acesso aos Relatórios
3. **🔴 Admin** - Acesso Total (Gerador + Report)

## 📊 Configurando Permissões

### Passo 1: Executar a Migration

Abra o **phpMyAdmin** e execute o arquivo:

```
auth-system/database/migration_permissions.sql
```

**Isso irá:**
- ✅ Adicionar coluna `permissions` na tabela `users`
- ✅ Adicionar coluna `description` na tabela `users`
- ✅ Criar 3 usuários de exemplo:
  - `admin@sistema.com` - Admin (acesso total)
  - `gerador@sistema.com` - Apenas Gerador
  - `report@sistema.com` - Apenas Report
- ✅ Criar view `user_permissions_view` para facilitar consultas

**Senha padrão para todos:** `password`

### Passo 2: Testar o Sistema

Faça login com cada usuário e veja as diferenças:

| Usuário | Email | Permissões | O que vê no Dashboard |
|---------|-------|------------|----------------------|
| Admin | `admin@sistema.com` | Gerador + Report | Todos os cards + Gerenciar Usuários |
| Gerador | `gerador@sistema.com` | Gerador | Apenas card do Gerador |
| Report | `report@sistema.com` | Report | Apenas card de Relatórios |

## 🔧 Tipos de Role

### Admin
```php
role = 'admin'
permissions = ["gerador", "report"]
description = "Administrador - Acesso Total"
```

### Gerador
```php
role = 'gerador'
permissions = ["gerador"]
description = "Gerador de E-mails"
```

### Report
```php
role = 'report'
permissions = ["report"]
description = "Relatórios"
```

## 💻 Como Usar no Código

### 1. Verificar Permissão no Controller

```php
use App\Middleware\PermissionMiddleware;

// Requer permissão específica
PermissionMiddleware::requireGerador();

// Ou
PermissionMiddleware::requireReport();

// Ou verificar sem redirecionar
if (PermissionMiddleware::canAccessGerador()) {
    // Usuário tem acesso
}
```

### 2. Verificar na View

```php
<?php
$permissions = $_SESSION['user_permissions'] ?? [];
$hasGerador = in_array('gerador', $permissions);
$hasReport = in_array('report', $permissions);
?>

<?php if ($hasGerador): ?>
    <!-- Mostrar conteúdo apenas para quem tem acesso ao Gerador -->
    <a href="/gerador">Acessar Gerador</a>
<?php endif; ?>
```

### 3. Usar no Model User

```php
$user = $userModel->findByEmail('usuario@exemplo.com');

if ($user->hasGeradorAccess()) {
    // Usuário pode acessar o Gerador
}

if ($user->hasReportAccess()) {
    // Usuário pode acessar Relatórios
}

// Ou verificação genérica
if ($user->hasPermission('gerador')) {
    // ...
}
```

## 🗄️ Consultas SQL Úteis

### Ver Permissões de Todos os Usuários

```sql
SELECT
    name,
    email,
    role,
    permissions,
    has_gerador_access,
    has_report_access
FROM user_permissions_view;
```

### Adicionar Permissão a um Usuário

```sql
-- Adicionar acesso ao Gerador
UPDATE users
SET permissions = JSON_ARRAY('gerador')
WHERE email = 'usuario@exemplo.com';

-- Adicionar ambas as permissões
UPDATE users
SET permissions = JSON_ARRAY('gerador', 'report')
WHERE email = 'usuario@exemplo.com';
```

### Remover Permissão

```sql
-- Remover todas as permissões
UPDATE users
SET permissions = JSON_ARRAY()
WHERE email = 'usuario@exemplo.com';
```

### Criar Usuário com Permissões

```sql
INSERT INTO users (name, email, password, role, permissions, description, status)
VALUES (
    'Novo Usuário',
    'novo@exemplo.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'gerador',
    JSON_ARRAY('gerador'),
    'Acesso ao Gerador',
    'active'
);
```

## 🎯 Exemplos Práticos

### Exemplo 1: Proteger Página do Gerador

```php
// Em gerador-de-emails-master/index.php

require_once '../auth-system/vendor/autoload.php';
use App\Middleware\PermissionMiddleware;

session_start();

// Verifica se tem permissão
PermissionMiddleware::requireGerador();

// Se chegou aqui, tem permissão!
// Continua com o código...
```

### Exemplo 2: Proteger Página de Relatórios

```php
// Em dynamics-email-report/public/index.php

require_once '../../auth-system/vendor/autoload.php';
use App\Middleware\PermissionMiddleware;

session_start();

// Verifica se tem permissão
PermissionMiddleware::requireReport();

// Se chegou aqui, tem permissão!
// Continua com o código...
```

### Exemplo 3: Menu Condicional

```php
<?php
use App\Middleware\PermissionMiddleware;

$canAccessGerador = PermissionMiddleware::canAccessGerador();
$canAccessReport = PermissionMiddleware::canAccessReport();
$isAdmin = PermissionMiddleware::isAdmin();
?>

<nav>
    <a href="/dashboard">Dashboard</a>

    <?php if ($canAccessGerador): ?>
        <a href="/gerador">Gerador de E-mails</a>
    <?php endif; ?>

    <?php if ($canAccessReport): ?>
        <a href="/report">Relatórios</a>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <a href="/admin">Administração</a>
    <?php endif; ?>
</nav>
```

## 🔒 Boas Práticas de Segurança

### ✅ Sempre Verifique no Servidor

```php
// ❌ ERRADO - Apenas esconder no HTML
<?php if ($hasPermission): ?>
    <form action="/gerador/enviar">...</form>
<?php endif; ?>

// ✅ CERTO - Verificar também no servidor
// Na página de processamento:
PermissionMiddleware::requireGerador();
```

### ✅ Use Middleware Adequado

```php
// Para rotas que exigem autenticação
AuthMiddleware::require();

// Para rotas que exigem permissão específica
PermissionMiddleware::requireGerador();

// Para rotas apenas de admin
PermissionMiddleware::requireAdmin();
```

### ✅ Registre Ações Importantes

O sistema já registra automaticamente no `audit_logs`:
- Login/Logout
- Criação de usuários
- Bloqueios de conta

Para registrar ações customizadas:

```sql
INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent)
VALUES (1, 'email_sent', 'Enviou campanha para 1000 destinatários', '127.0.0.1', 'Mozilla...');
```

## 📞 Troubleshooting

### Permissões não aparecem no dashboard

1. Verifique se executou a migration:
   ```sql
   SHOW COLUMNS FROM users LIKE 'permissions';
   ```

2. Verifique se o usuário tem permissões:
   ```sql
   SELECT email, permissions FROM users WHERE email = 'seu@email.com';
   ```

3. Faça logout e login novamente para atualizar a sessão

### Erro "Column permissions not found"

Execute a migration `migration_permissions.sql` no phpMyAdmin.

### Permissões não funcionam

1. Verifique se está usando a sessão correta
2. Limpe a sessão: logout e login
3. Verifique os logs em `logs/app.log`

---

**Desenvolvido com 🔐 para segurança máxima**
