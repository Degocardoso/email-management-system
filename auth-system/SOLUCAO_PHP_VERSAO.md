# ⚡ SOLUÇÃO RÁPIDA - Problema de Versão PHP

## 🔴 Problema Identificado

Seu WAMP tem PHP 8.4 instalado, mas o terminal está usando PHP 7.4!

```
✅ PHP 8.4.0  usado no Apache (site funciona)
❌ PHP 7.4.33 usado no terminal (composer não funciona)
```

---

## ✅ SOLUÇÃO 1: Usar PHP 8.4 no Terminal (RECOMENDADO)

### Passo 1: Mudar PHP do CLI para 8.4

No WAMP:

1. **Clique com botão direito** no ícone do WAMP (bandeja)
2. **Tools** → **Change CLI PHP version**
3. Selecione: **PHP 8.4.0**
4. Clique **OK**

### Passo 2: Verificar mudança

Abra um **NOVO** terminal (CMD/PowerShell) e execute:

```bash
php -v
```

Deve mostrar: **PHP 8.4.0**

### Passo 3: Reinstalar dependências

```bash
cd C:\wamp64\www\email-management-system\auth-system

# Remove dependências antigas
rmdir /S /Q vendor
del composer.lock

# Instala novamente
composer install

# Se der erro ainda, tente:
composer update
```

### Passo 4: Executar setup

```bash
php setup.php
```

---

## ✅ SOLUÇÃO 2: Ajustar para PHP 7.4 (Alternativa)

Se não conseguir mudar o PHP CLI, já ajustei o código!

### Passo 1: Puxar as últimas alterações

```bash
cd C:\wamp64\www\email-management-system

git pull
```

### Passo 2: Limpar e reinstalar

```bash
cd auth-system

# Remove dependências antigas
rmdir /S /Q vendor
del composer.lock

# Instala com versões compatíveis com PHP 7.4
composer install --no-dev
```

O `--no-dev` pula o PHPUnit que pode dar conflito.

### Passo 3: Executar setup

```bash
php setup.php
```

---

## 🎯 Comandos Resumidos (Escolha UMA solução)

### 👍 OPÇÃO A: Usar PHP 8.4 (Melhor)

```bash
# 1. Mudar PHP CLI no WAMP (via interface)
# 2. Abrir NOVO terminal
php -v  # Verificar se mostra 8.4

cd C:\wamp64\www\email-management-system\auth-system
rmdir /S /Q vendor
del composer.lock
composer install
php setup.php
```

### 👌 OPÇÃO B: Manter PHP 7.4

```bash
cd C:\wamp64\www\email-management-system
git pull

cd auth-system
rmdir /S /Q vendor
del composer.lock
composer install --no-dev
php setup.php
```

---

## 🔧 Se ainda der erro

### Erro: "Command not found" ao remover vendor

Use o Windows Explorer:
1. Navegue até `C:\wamp64\www\email-management-system\auth-system`
2. **Delete** as pastas: `vendor` e o arquivo `composer.lock`
3. Volte ao terminal e execute: `composer install`

### Erro: "composer: command not found"

Instale o Composer: https://getcomposer.org/download/

Ou use o caminho completo:
```bash
php C:\ProgramData\ComposerSetup\bin\composer.phar install
```

---

## ✅ Após resolver, teste:

```bash
php setup.php
```

Deve funcionar e criar o usuário admin! 🎉

---

## 📸 Como Verificar Versão PHP no WAMP

**Interface gráfica:**

```
WAMP (ícone) → Tools → Change CLI PHP version → Selecione 8.4.0
```

**Via terminal:**

```bash
php -v
```

Deve mostrar:
```
PHP 8.4.0 (cli) (built: Nov 19 2024 15:02:24) (NTS Visual C++ 2019 x64)
```

---

## 🆘 Precisa de Ajuda?

Me mande:
1. Resultado de `php -v`
2. Qual solução você escolheu (A ou B)
3. O erro completo se aparecer

Vou te ajudar! 🚀
