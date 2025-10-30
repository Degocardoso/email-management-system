<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Autenticação</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .user-info { display: flex; align-items: center; gap: 20px; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .card h2 { margin-bottom: 10px; color: #333; }
        .card p { color: #666; margin-bottom: 20px; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .feature-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; transition: transform 0.3s; cursor: pointer; }
        .feature-card:hover { transform: translateY(-5px); }
        .feature-card h3 { font-size: 20px; margin-bottom: 10px; }
        .feature-card p { font-size: 14px; opacity: 0.9; }
        .badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: #ff6b6b; color: white; }
        .badge-analyst { background: #4ecdc4; color: white; }
        .badge-generator { background: #95e1d3; color: white; }
        .btn { padding: 10px 20px; background: white; color: #667eea; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #f5f7fa; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Dashboard</h1>
            <div class="user-info">
                <div>
                    <strong><?= htmlspecialchars($user['name']) ?></strong><br>
                    <span class="badge badge-<?= $user['role'] ?>">
                        <?= \Auth\Models\User::getRoleName($user['role']) ?>
                    </span>
                </div>
                <a href="/logout" class="btn">Sair</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Bem-vindo, <?= htmlspecialchars($user['name']) ?>!</h2>
            <p>Você está logado como <strong><?= \Auth\Models\User::getRoleName($user['role']) ?></strong></p>
        </div>

        <div class="features">
            <?php if ($user['role'] === 'admin' || $user['role'] === 'generator'): ?>
                <div class="feature-card" onclick="window.location.href='/../gerador-de-emails-master/public/'">
                    <h3>Gerar Emails</h3>
                    <p>Acesse o sistema de geração de emails</p>
                </div>
            <?php endif; ?>

            <?php if ($user['role'] === 'admin' || $user['role'] === 'analyst'): ?>
                <div class="feature-card" onclick="window.location.href='/../dynamics-email-report/public/'">
                    <h3>Analisar Emails</h3>
                    <p>Visualize relatórios e análises de emails</p>
                </div>
            <?php endif; ?>

            <?php if ($user['role'] === 'admin'): ?>
                <div class="feature-card" onclick="window.location.href='/users'">
                    <h3>Gerenciar Usuários</h3>
                    <p>Crie e gerencie usuários do sistema</p>
                </div>

                <div class="feature-card" onclick="window.location.href='/users/sessions'">
                    <h3>Sessões Ativas</h3>
                    <p>Visualize usuários conectados</p>
                </div>

                <div class="feature-card" onclick="window.location.href='/users/audit'">
                    <h3>Logs de Auditoria</h3>
                    <p>Visualize histórico de ações</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
