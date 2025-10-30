<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-content { max-width: 800px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 10px; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; font-size: 16px; }
        .btn:hover { background: #5568d3; }
        .btn-secondary { background: #6c757d; margin-left: 10px; text-decoration: none; display: inline-block; }
        .btn-secondary:hover { background: #5a6268; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .help-text { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Criar Novo Usuário</h1>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= url('users/store') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label for="name">Nome Completo *</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="role">Função *</label>
                    <select id="role" name="role" required>
                        <option value="">Selecione uma função</option>
                        <?php foreach ($roles as $roleKey => $roleName): ?>
                            <option value="<?= $roleKey ?>"><?= htmlspecialchars($roleName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text">
                        <strong>Admin:</strong> Acesso total ao sistema<br>
                        <strong>Analista:</strong> Pode apenas analisar emails<br>
                        <strong>Gerador:</strong> Pode apenas gerar emails
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Senha *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <div class="help-text">Mínimo de 6 caracteres</div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Senha *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="active" name="active" checked>
                        <label for="active" style="margin-bottom: 0;">Usuário ativo</label>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn">Criar Usuário</button>
                    <a href="<?= url('users') ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
