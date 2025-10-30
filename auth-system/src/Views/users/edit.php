<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="<?= url('users/create.php') ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .header-content { max-width: 800px; margin: 0 auto; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 10px; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-secondary { background: #6c757d; margin-left: 10px; }
        .help-text { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Editar Usuário</h1>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <form method="POST" action="<?= url('users/update') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">

                <div class="form-group">
                    <label for="name">Nome Completo *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="role">Função *</label>
                    <select id="role" name="role" required>
                        <?php foreach ($roles as $roleKey => $roleName): ?>
                            <option value="<?= $roleKey ?>" <?= $user['role'] === $roleKey ? 'selected' : '' ?>>
                                <?= htmlspecialchars($roleName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Nova Senha</label>
                    <input type="password" id="password" name="password" minlength="6">
                    <div class="help-text">Deixe em branco para manter a senha atual</div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nova Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="active" name="active" <?= $user['active'] ? 'checked' : '' ?>>
                        <label for="active" style="margin-bottom: 0;">Usuário ativo</label>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn">Salvar Alterações</button>
                    <a href="<?= url('users') ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
