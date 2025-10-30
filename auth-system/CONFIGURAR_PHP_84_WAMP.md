# 🚀 Como Configurar PHP 8.4 no WAMP (CLI)

## ✅ PASSO A PASSO DEFINITIVO

Seu WAMP **JÁ TEM** PHP 8.4 instalado! Só precisa ativar no terminal.

---

## 📋 SOLUÇÃO: Mudar PHP CLI no WAMP

### Método 1: Via Interface do WAMP (MAIS FÁCIL)

1. **Clique com botão direito** no ícone do WAMP (barra de tarefas/bandeja)

2. **Tools** → **Change CLI PHP version**

3. Selecione: **PHP 8.4.0**

4. Clique **OK**

5. **FECHE** todos os terminais abertos

6. **ABRA UM NOVO** terminal (CMD ou PowerShell)

7. Teste:
```bash
php -v
```

Deve aparecer:
```
PHP 8.4.0 (cli) (built: Nov 19 2024...)
```

---

### Método 2: Via Linha de Comando (Alternativo)

Se o Método 1 não funcionar:

#### No CMD (como Administrador):

```cmd
setx PATH "C:\wamp64\bin\php\php8.4.0;%PATH%"
```

#### Feche e abra novo terminal, teste:

```bash
php -v
```

---

### Método 3: Definir Manualmente no PATH (Garantido)

1. **Pesquise no Windows**: "Variáveis de Ambiente"

2. Clique em **"Editar as variáveis de ambiente do sistema"**

3. Clique em **"Variáveis de Ambiente"**

4. Em **"Variáveis do sistema"**, encontre **"Path"**

5. Clique em **"Editar"**

6. Procure por linhas contendo `C:\wamp64\bin\php\`

7. **Mova para o TOPO** a linha:
   ```
   C:\wamp64\bin\php\php8.4.0
   ```

8. **Remova** ou mova para baixo qualquer outra versão PHP (como php7.4.33)

9. Clique **OK** em todas as janelas

10. **FECHE todos os terminais**

11. **ABRA NOVO** terminal

12. Teste:
```bash
php -v
```

---

## ⚡ INSTALAÇÃO COMPLETA (Após configurar PHP 8.4)

### Passo 1: Verificar PHP

```bash
php -v
```

✅ Deve mostrar: **PHP 8.4.0**

### Passo 2: Navegar para pasta

```bash
cd C:\wamp64\www\email-management-system\auth-system
```

### Passo 3: Limpar instalação antiga

**Via CMD:**
```bash
rmdir /S /Q vendor
del composer.lock
```

**OU via Windows Explorer:**
- Delete a pasta `vendor`
- Delete o arquivo `composer.lock`

### Passo 4: Instalar dependências

```bash
composer install
```

Agora deve funcionar sem erros! 🎉

### Passo 5: Criar banco de dados

Acesse: **http://localhost/phpmyadmin**

Execute:
```sql
CREATE DATABASE auth_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Passo 6: Configurar .env

Edite `auth-system\.env`:

```env
APP_ENV=development
APP_DEBUG=true
SESSION_LIFETIME=7200
LOG_LEVEL=info

DB_HOST=localhost
DB_DATABASE=auth_system
DB_USERNAME=root
DB_PASSWORD=
```

### Passo 7: Atualizar DatabaseService.php

**Substitua o arquivo** `src\Services\DatabaseService.php` pelo código completo que está no arquivo `INSTALACAO_WAMP.md` (seção "Atalho: Arquivo DatabaseService.php Completo")

### Passo 8: Executar setup

```bash
php setup.php
```

Preencha:
```
Nome: Admin Sistema
Email: admin@teste.com
Senha: admin123
Confirmar: admin123
```

### Passo 9: Acessar

```
http://localhost/email-management-system/auth-system/public/
```

Login:
- Email: `admin@teste.com`
- Senha: `admin123`

---

## ✅ Checklist Final

```
✅ php -v mostra PHP 8.4.0
✅ composer.lock deletado
✅ pasta vendor deletada
✅ composer install funcionou
✅ Banco auth_system criado
✅ .env configurado
✅ DatabaseService.php atualizado para MySQL
✅ php setup.php executado com sucesso
✅ Login funciona!
```

---

## ⚠️ Problemas Comuns

### ❌ "php -v" ainda mostra PHP 7.4

**Solução:**
1. Feche **TODOS** os terminais
2. Abra um **NOVO** terminal
3. Teste novamente

Se ainda mostrar 7.4, use o **Método 3** (PATH manual)

### ❌ Composer ainda reclama de PHP 7.4

**Solução:**
```bash
# Limpe tudo
rmdir /S /Q vendor
del composer.lock

# Force a reinstalação
composer clear-cache
composer install
```

### ❌ "composer: command not found"

**Instale o Composer:**
https://getcomposer.org/download/

---

## 🎯 Comandos Resumidos (COPIE E COLE)

```bash
# 1. Verificar versão
php -v

# Se mostrar 7.4, feche terminal e abra novo
# Se ainda mostrar 7.4, use Método 3 (PATH)

# 2. Navegar
cd C:\wamp64\www\email-management-system\auth-system

# 3. Limpar
rmdir /S /Q vendor
del composer.lock

# 4. Instalar
composer install

# 5. Criar admin
php setup.php

# 6. Acessar
# http://localhost/email-management-system/auth-system/public/
```

---

## 💡 Dica Extra

Se você usa **VSCode** ou outro editor, pode ter um terminal integrado com PATH antigo.

**Solução:**
- Feche completamente o VSCode
- Abra novamente
- O terminal vai pegar o novo PATH

---

## 🆘 Ainda Com Problemas?

Me mande:

1. **Resultado de** `php -v`
2. **Resultado de** `echo %PATH%` (CMD) ou `$env:PATH` (PowerShell)
3. **Screenshot** do erro do Composer

Vou resolver! 🚀

---

## 📸 Como Deve Ficar

**Terminal mostrando PHP 8.4:**
```
C:\>php -v
PHP 8.4.0 (cli) (built: Nov 19 2024 15:02:24) (NTS Visual C++ 2019 x64)
Copyright (c) The PHP Group
Zend Engine v4.4.0, Copyright (c) Zend Technologies
```

**Composer install funcionando:**
```
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Package operations: 36 installs, 0 updates, 0 removals
  - Installing psr/log (3.0.2): Extracting archive
  - Installing monolog/monolog (2.10.0): Extracting archive
  ...
Generating autoload files
```

**Setup concluído:**
```
=== Sistema de Autenticação - Setup ===

Inicializando banco de dados...
✓ Banco de dados inicializado com sucesso

Criando usuário administrador...
✓ Usuário administrador criado com sucesso!

✓ Setup concluído com sucesso!
```

---

🎉 **Pronto! Sistema funcionando com PHP 8.4!**
