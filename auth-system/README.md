# Sistema de Autenticação

Sistema completo de autenticação desenvolvido em PHP com arquitetura MVC, MySQL e recursos avançados de segurança.

## 🚀 Características

- ✅ Arquitetura MVC bem estruturada
- ✅ Autenticação segura com hash bcrypt
- ✅ Proteção contra força bruta (bloqueio após tentativas falhas)
- ✅ Validação robusta de senhas
- ✅ Logging com Monolog
- ✅ Sistema de cache (File ou Redis)
- ✅ Interface moderna e responsiva
- ✅ Auditoria de ações dos usuários
- ✅ Middleware de autenticação
- ✅ Gerenciamento de sessões

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Composer
- WAMP/XAMPP (ou qualquer servidor com PHP e MySQL)

## 🔧 Instalação

### 1. Clone ou copie o projeto

```bash
cd /caminho/do/wamp/www
# O projeto já deve estar na pasta auth-system
```

### 2. Instale as dependências com Composer

```bash
cd auth-system
composer install
```

### 3. Configure o arquivo .env

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite o arquivo .env com suas configurações
# Especialmente DB_DATABASE, DB_USERNAME e DB_PASSWORD
```

### 4. Crie o banco de dados

No phpMyAdmin ou MySQL CLI:

```bash
# Via MySQL CLI
mysql -u root -p < database/schema.sql

# OU importe manualmente via phpMyAdmin
# Vá em phpMyAdmin > Importar > Selecione database/schema.sql
```

O script irá:
- Criar o banco de dados `auth_system`
- Criar as tabelas necessárias
- Inserir um usuário admin padrão

### 5. Configure o Apache (WAMP)

Certifique-se de que o `mod_rewrite` está habilitado no Apache.

Crie um arquivo `.htaccess` na pasta `public`:

```apache
RewriteEngine On

# Redireciona tudo para index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 6. Permissões de pasta

```bash
# Linux/Mac
chmod -R 775 storage/
chmod -R 775 logs/

# Windows (WAMP)
# Certifique-se que as pastas storage/ e logs/ têm permissão de escrita
```

## 🌐 Acesso

Após a instalação, acesse:

```
http://localhost/auth-system/public
```

### Credenciais padrão do Admin:

- **Email:** admin@sistema.com
- **Senha:** password

**⚠️ IMPORTANTE:** Altere a senha do admin após o primeiro login!

## 📁 Estrutura do Projeto

```
auth-system/
├── config/              # Arquivos de configuração
│   ├── app.php         # Configurações gerais
│   └── database.php    # Configurações do banco
├── database/           # Schemas SQL
│   └── schema.sql      # Estrutura do banco de dados
├── logs/               # Arquivos de log
├── public/             # Pasta pública (Document Root)
│   └── index.php       # Front Controller
├── src/                # Código-fonte da aplicação
│   ├── Bootstrap.php   # Inicialização da aplicação
│   ├── Controllers/    # Controllers MVC
│   ├── Middleware/     # Middlewares
│   ├── Models/         # Models (Entidades)
│   ├── Services/       # Serviços (Database, etc)
│   ├── Validators/     # Validadores
│   └── Views/          # Views (Templates)
├── storage/            # Armazenamento temporário
│   ├── cache/          # Cache de arquivos
│   └── sessions/       # Sessões
├── .env.example        # Exemplo de configuração
├── composer.json       # Dependências do Composer
└── README.md          # Este arquivo
```

## 🛡️ Recursos de Segurança

### Proteção contra Força Bruta
- Máximo de 5 tentativas de login
- Bloqueio da conta por 15 minutos após exceder tentativas
- Desbloqueio automático após o período

### Validação de Senhas
- Mínimo 8 caracteres
- Pelo menos 1 letra maiúscula
- Pelo menos 1 letra minúscula
- Pelo menos 1 número
- Pelo menos 1 caractere especial

### Outras Medidas
- Hash de senhas com bcrypt
- Regeneração de ID de sessão após login
- Cookies HttpOnly
- Logging de todas as ações importantes
- Auditoria de login/logout

## 🔐 Sistema de Permissões

### Roles (Perfis)
- **admin**: Acesso total ao sistema
- **user**: Acesso padrão

Para verificar permissões em controllers:

```php
use App\Middleware\AuthMiddleware;

// Requer autenticação
AuthMiddleware::require();

// Requer role específica
AuthMiddleware::requireRole('admin');
```

## 📊 Log de Auditoria

Todas as ações importantes são registradas na tabela `audit_logs`:
- Login/Logout
- Criação de usuários
- Bloqueios de conta
- Alterações de dados

## 🔄 Rotas Disponíveis

| Rota | Método | Descrição |
|------|--------|-----------|
| `/` ou `/login` | GET | Exibe formulário de login |
| `/login` | POST | Processa login |
| `/register` | GET | Exibe formulário de registro |
| `/register` | POST | Processa registro |
| `/dashboard` | GET | Dashboard (requer autenticação) |
| `/logout` | GET | Realiza logout |

## 🐛 Troubleshooting

### Erro de conexão com banco de dados
- Verifique se o MySQL está rodando no WAMP
- Confirme as credenciais no arquivo `.env`
- Verifique se o banco `auth_system` foi criado

### Erro de permissão nas pastas
```bash
# Linux/Mac
chmod -R 775 storage/ logs/

# Windows
# Dê permissão total para as pastas storage/ e logs/
```

### Página em branco
- Verifique os logs em `logs/app.log`
- Ative debug no `.env`: `APP_DEBUG=true`
- Verifique se o Composer instalou as dependências

### Erro 404 em todas as páginas
- Verifique se o `mod_rewrite` do Apache está habilitado
- Confirme se existe o arquivo `.htaccess` na pasta `public/`

## 📝 Licença

Este projeto é de código aberto sob a licença MIT.

## 🤝 Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou pull requests.

## 📧 Suporte

Para dúvidas ou problemas, abra uma issue no repositório do projeto.

---

**Desenvolvido com ❤️ usando PHP, MySQL e arquitetura MVC**
