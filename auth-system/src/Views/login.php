<?php
// Garantir que as variáveis existam
$error = $error ?? null;
$success = $success ?? null;
$email = $email ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Autenticação</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --cor-principal: #1aa97f;
            --cor-principal-hover: #168a68;
            --cor-fundo: #f4f7f6;
            --cor-texto: #333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding: 20px;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
        }

        .header-card {
            background: white;
            padding: 2rem;
            border-radius: 12px 12px 0 0;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-card .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--cor-principal) 0%, #1cd09b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 15px rgba(26, 169, 127, 0.3);
        }

        .header-card .icon i {
            font-size: 2.5rem;
            color: white;
        }

        .header-card h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .header-card p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-card {
            background: white;
            padding: 2.5rem;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid;
            display: flex;
            align-items: start;
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: #555;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--cor-principal);
            box-shadow: 0 0 0 3px rgba(26, 169, 127, 0.15);
        }

        .btn {
            font-weight: 600;
            padding: 1rem 2rem;
            font-size: 1.05rem;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            width: 100%;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-login {
            background-color: var(--cor-principal);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 169, 127, 0.3);
        }

        .btn-login:hover:not(:disabled) {
            background-color: var(--cor-principal-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 169, 127, 0.4);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .form-footer a {
            color: var(--cor-principal);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: var(--cor-principal-hover);
            text-decoration: underline;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle input {
            padding-right: 3rem;
        }

        .password-toggle-btn {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .password-toggle-btn:hover {
            color: var(--cor-principal);
        }

        @media (max-width: 768px) {
            .header-card h1 {
                font-size: 1.5rem;
            }

            .form-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header-card">
            <div class="icon">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Bem-vindo de volta!</h1>
            <p>Faça login para acessar o sistema</p>
        </div>

        <div class="form-card">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" id="loginForm">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> E-mail
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="seu@email.com"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i> Senha
                    </label>
                    <div class="password-toggle">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Digite sua senha"
                            required
                        >
                        <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-login" id="btnLogin">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>
            </form>

            <div class="form-footer">
                <p>Não tem uma conta? <a href="/register">Cadastre-se aqui</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btnLogin = document.getElementById('btnLogin');
            btnLogin.disabled = true;
            btnLogin.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';
        });
    </script>
</body>
</html>
