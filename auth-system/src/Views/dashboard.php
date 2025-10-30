<?php
// Garantir que as variáveis existam
$user = $user ?? ['name' => 'Usuário', 'email' => '', 'role' => 'user'];
$success = $success ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Autenticação</title>
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
            background-color: var(--cor-fundo);
            color: var(--cor-texto);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
        }

        .navbar {
            background: linear-gradient(135deg, var(--cor-principal) 0%, #1cd09b 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 0.75rem;
            font-size: 1.8rem;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 1rem;
        }

        .user-role {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .btn-logout {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-logout i {
            margin-right: 0.5rem;
        }

        .btn-logout:hover {
            background-color: white;
            color: var(--cor-principal);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--cor-principal) 0%, #1cd09b 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(26, 169, 127, 0.3);
        }

        .header-gradient h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .header-gradient p {
            opacity: 0.95;
            font-size: 1.1rem;
        }

        .alert {
            padding: 1.2rem;
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

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--cor-principal) 0%, #1cd09b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(26, 169, 127, 0.3);
        }

        .card-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .card-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .info-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--cor-principal);
            font-weight: 600;
            margin: 0 0 1.5rem 0;
            display: flex;
            align-items: center;
            font-size: 1.35rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.75rem;
        }

        .section-title i {
            font-size: 1.3rem;
            margin-right: 0.75rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: var(--cor-principal);
            font-weight: 500;
        }

        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge-admin {
            background-color: #dc3545;
            color: white;
        }

        .badge-user {
            background-color: #17a2b8;
            color: white;
        }

        .badge-gerador {
            background-color: #28a745;
            color: white;
        }

        .badge-report {
            background-color: #ffc107;
            color: #333;
        }

        .badge-permission {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.75rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .card.disabled {
            opacity: 0.5;
            filter: grayscale(100%);
            cursor: not-allowed;
        }

        .card.disabled:hover {
            transform: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .navbar-user {
                flex-direction: column;
                gap: 0.75rem;
            }

            .container {
                padding: 1rem;
            }

            .header-gradient h1 {
                font-size: 1.8rem;
            }

            .card-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-shield-alt"></i>
            Sistema de Autenticação
        </div>
        <div class="navbar-user">
            <div class="user-info">
                <div class="user-name">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="user-role">
                    <?php
                    $roleLabel = $user['role'] === 'admin' ? 'Administrador' : 'Usuário';
                    echo htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8');
                    ?>
                </div>
            </div>
            <a href="/logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Sair
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="header-gradient">
            <h1><i class="fas fa-home"></i> Dashboard</h1>
            <p>Bem-vindo ao painel de controle do sistema</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        <?php endif; ?>

        <div class="info-section">
            <h2 class="section-title">
                <i class="fas fa-id-card"></i>
                Informações da Conta
            </h2>

            <div class="info-item">
                <span class="info-label">Nome:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="info-item">
                <span class="info-label">E-mail:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="info-item">
                <span class="info-label">Perfil:</span>
                <span class="info-value">
                    <?php
                    $roleLabel = $user['description'] ?? 'Usuário';
                    $roleBadge = 'badge-user';
                    $roleIcon = 'fa-user';

                    if ($user['role'] === 'admin') {
                        $roleBadge = 'badge-admin';
                        $roleIcon = 'fa-crown';
                    } elseif ($user['role'] === 'gerador') {
                        $roleBadge = 'badge-gerador';
                        $roleIcon = 'fa-envelope';
                    } elseif ($user['role'] === 'report') {
                        $roleBadge = 'badge-report';
                        $roleIcon = 'fa-chart-line';
                    }
                    ?>
                    <span class="badge <?php echo $roleBadge; ?>">
                        <i class="fas <?php echo $roleIcon; ?>"></i>
                        <?php echo htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </span>
            </div>

            <div class="info-item">
                <span class="info-label">Permissões:</span>
                <span class="info-value">
                    <?php
                    $permissions = $user['permissions'] ?? [];
                    if (empty($permissions)):
                    ?>
                        <span class="badge-permission" style="background-color: #dc3545; color: white;">
                            <i class="fas fa-ban"></i> Sem Permissões
                        </span>
                    <?php else: ?>
                        <?php if (in_array('gerador', $permissions)): ?>
                            <span class="badge-permission badge-gerador">
                                <i class="fas fa-envelope"></i> Gerador
                            </span>
                        <?php endif; ?>
                        <?php if (in_array('report', $permissions)): ?>
                            <span class="badge-permission badge-report">
                                <i class="fas fa-chart-line"></i> Report
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </span>
            </div>

            <div class="info-item">
                <span class="info-label">Status da Sessão:</span>
                <span class="info-value">
                    <i class="fas fa-circle" style="color: #28a745; font-size: 0.7rem;"></i>
                    Ativo
                </span>
            </div>
        </div>

        <h2 class="section-title" style="background: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <i class="fas fa-th-large"></i>
            Recursos Disponíveis
        </h2>

        <div class="card-grid">
            <?php
            $permissions = $user['permissions'] ?? [];
            $hasGerador = in_array('gerador', $permissions);
            $hasReport = in_array('report', $permissions);
            ?>

            <!-- Card Gerador de E-mails -->
            <?php if ($hasGerador): ?>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h3 class="card-title">Gerador de E-mails</h3>
                <p class="card-description">
                    Crie e envie campanhas de e-mail personalizadas usando o sistema gerador.
                </p>
                <span class="badge-permission badge-gerador" style="margin-top: 1rem;">
                    <i class="fas fa-check"></i> Acesso Liberado
                </span>
            </div>
            <?php endif; ?>

            <!-- Card Relatórios de E-mail -->
            <?php if ($hasReport): ?>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="card-title">Relatórios de E-mail</h3>
                <p class="card-description">
                    Acesse o sistema de relatórios de e-mails do Dynamics 365 para análise de campanhas.
                </p>
                <span class="badge-permission badge-report" style="margin-top: 1rem;">
                    <i class="fas fa-check"></i> Acesso Liberado
                </span>
            </div>
            <?php endif; ?>

            <!-- Card Configurações (todos têm acesso) -->
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h3 class="card-title">Configurações</h3>
                <p class="card-description">
                    Gerencie suas preferências e configurações de conta de forma segura.
                </p>
            </div>

            <!-- Card Admin (apenas para admin) -->
            <?php if ($user['role'] === 'admin'): ?>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3 class="card-title">Gerenciar Usuários</h3>
                <p class="card-description">
                    Administre usuários do sistema, permissões e acessos.
                </p>
                <span class="badge-permission badge-admin" style="margin-top: 1rem;">
                    <i class="fas fa-crown"></i> Admin
                </span>
            </div>
            <?php endif; ?>

            <!-- Mensagem se não tiver nenhuma permissão -->
            <?php if (empty($permissions)): ?>
            <div class="card disabled">
                <div class="card-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <h3 class="card-title">Sem Permissões</h3>
                <p class="card-description">
                    Você ainda não possui permissões para acessar os sistemas. Entre em contato com o administrador.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
