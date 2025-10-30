<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; background: white; padding: 60px; border-radius: 10px; box-shadow: 0 2px 20px rgba(0,0,0,0.1); max-width: 500px; }
        .error-code { font-size: 72px; font-weight: bold; color: #dc3545; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 15px; }
        p { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 5px; text-decoration: none; display: inline-block; font-weight: 600; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">403</div>
        <h1>Acesso Negado</h1>
        <p><?= isset($message) ? htmlspecialchars($message) : 'Você não tem permissão para acessar esta página.' ?></p>
        <a href="/dashboard" class="btn">Voltar ao Dashboard</a>
    </div>
</body>
</html>
