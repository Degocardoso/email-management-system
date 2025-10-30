# 🚀 Guia de Instalação Rápida - WAMP

Este guia vai te ajudar a configurar o sistema de autenticação no WAMP passo a passo.

## ✅ Checklist Pré-Instalação

- [ ] WAMP instalado e rodando
- [ ] Apache rodando (ícone verde no WAMP)
- [ ] MySQL rodando (ícone verde no WAMP)
- [ ] PHP 7.4+ (verifique em phpinfo())
- [ ] Composer instalado ([Download aqui](https://getcomposer.org/))

## 📦 Passo 1: Instalar Dependências

Abra o **CMD** ou **PowerShell** como Administrador:

```bash
cd C:\wamp64\www\email-management-system\auth-system
composer install
```

**Resultado esperado:** Pasta `vendor` criada com as bibliotecas.

## ⚙️ Passo 2: Configurar o Ambiente

### 2.1 Copiar arquivo de configuração

```bash
copy .env.example .env
```

### 2.2 Editar o arquivo `.env`

Abra o arquivo `.env` com um editor de texto e ajuste:

```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/email-management-system/auth-system/public

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=auth_system
DB_USERNAME=root
DB_PASSWORD=
```

**Nota:** Se seu MySQL tem senha, coloque em `DB_PASSWORD`.

## 🗄️ Passo 3: Criar o Banco de Dados

### Opção A: Via phpMyAdmin (Recomendado)

1. Abra `http://localhost/phpmyadmin`
2. Clique em **"Importar"** no menu superior
3. Clique em **"Escolher arquivo"**
4. Selecione: `C:\wamp64\www\email-management-system\auth-system\database\schema.sql`
5. Clique em **"Executar"**

### Opção B: Via MySQL CLI

```bash
cd C:\wamp64\www\email-management-system\auth-system
mysql -u root -p < database\schema.sql
```

(Digite a senha do MySQL se solicitado)

## 🌐 Passo 4: Configurar o Apache

### 4.1 Verificar mod_rewrite

1. Clique no ícone do WAMP na bandeja
2. Vá em **Apache > Apache modules**
3. Certifique-se de que **rewrite_module** está marcado

### 4.2 Testar o .htaccess

O arquivo `.htaccess` já foi criado em `public/.htaccess`. Verifique se existe.

## 🎉 Passo 5: Testar a Instalação

### 5.1 Acessar o sistema

Abra seu navegador e vá para:

```
http://localhost/email-management-system/auth-system/public
```

Você deve ver a tela de login!

### 5.2 Fazer login com o admin padrão

**Credenciais:**
- Email: `admin@sistema.com`
- Senha: `password`

## 🐛 Problemas Comuns

### ❌ Erro: "Class not found"

**Solução:**
```bash
cd C:\wamp64\www\email-management-system\auth-system
composer dump-autoload
```

### ❌ Erro: "Connection refused" (Banco de dados)

**Solução:**
1. Verifique se o MySQL está rodando (ícone WAMP deve estar verde)
2. Confirme as credenciais no arquivo `.env`
3. Teste a conexão via phpMyAdmin

### ❌ Erro: "Permission denied" ao criar logs

**Solução:**
1. Vá até a pasta `auth-system`
2. Clique com botão direito em `logs` > Propriedades > Segurança
3. Dê permissão total para seu usuário
4. Repita para as pastas `storage/cache` e `storage/sessions`

### ❌ Página em branco

**Solução:**
1. Ative debug no `.env`: `APP_DEBUG=true`
2. Verifique o arquivo `logs/app.log` para ver os erros
3. Confirme que o Composer instalou todas as dependências

### ❌ Erro 404 em todas as páginas

**Solução:**
1. Verifique se o `mod_rewrite` está habilitado no Apache
2. Confirme que o arquivo `.htaccess` existe em `public/`
3. Verifique se o `AllowOverride All` está configurado no httpd.conf

## 🔒 Pós-Instalação

### Alterar senha do admin

1. Faça login com as credenciais padrão
2. **IMPORTANTE:** Altere a senha do admin imediatamente!
   - Conecte ao MySQL e execute:
   ```sql
   UPDATE auth_system.users
   SET password = '$2y$10$SEU_HASH_AQUI'
   WHERE email = 'admin@sistema.com';
   ```

   Ou crie um novo admin pelo formulário de registro e delete o padrão.

### Desabilitar debug em produção

No arquivo `.env`:
```env
APP_ENV=production
APP_DEBUG=false
```

## 📚 Próximos Passos

Agora que o sistema está rodando:

1. ✅ Crie novos usuários pelo formulário de registro
2. ✅ Explore o dashboard
3. ✅ Teste o sistema de logout
4. ✅ Verifique os logs em `logs/app.log`
5. ✅ Consulte a tabela `audit_logs` para ver o histórico de ações

## 📞 Precisa de Ajuda?

- Verifique o arquivo `README.md` para mais detalhes
- Consulte os logs em `logs/app.log`
- Verifique a documentação do código-fonte

---

**Dica:** Guarde as credenciais do admin em um local seguro! 🔐
