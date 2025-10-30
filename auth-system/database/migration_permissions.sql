-- ===================================================
-- MIGRATION: Adicionar Sistema de Permissões
-- ===================================================
-- Execute este SQL no phpMyAdmin para adicionar o sistema de permissões

USE auth_system;

-- 1. Adiciona coluna de permissões na tabela users
ALTER TABLE `users`
ADD COLUMN `permissions` JSON NULL DEFAULT NULL AFTER `role`,
ADD COLUMN `description` VARCHAR(255) NULL DEFAULT NULL AFTER `email`;

-- 2. Atualiza os valores ENUM da coluna role
ALTER TABLE `users`
MODIFY COLUMN `role` ENUM('admin', 'gerador', 'report', 'user') NOT NULL DEFAULT 'user';

-- 3. Atualiza usuários existentes com permissões padrão
UPDATE `users`
SET `permissions` = JSON_ARRAY('gerador', 'report')
WHERE `role` = 'admin';

-- 4. Cria índice para melhor performance
ALTER TABLE `users`
ADD INDEX `role_permissions_index` (`role`);

-- 5. Cria view para facilitar consulta de permissões
CREATE OR REPLACE VIEW `user_permissions_view` AS
SELECT
    u.id,
    u.name,
    u.email,
    u.role,
    u.permissions,
    u.status,
    CASE
        WHEN u.role = 'admin' THEN 'Administrador - Acesso Total'
        WHEN u.role = 'gerador' THEN 'Gerador de E-mails'
        WHEN u.role = 'report' THEN 'Relatórios'
        ELSE 'Usuário Padrão'
    END as role_description,
    CASE
        WHEN u.role = 'admin' OR JSON_CONTAINS(u.permissions, '"gerador"', '$') THEN 1
        ELSE 0
    END as has_gerador_access,
    CASE
        WHEN u.role = 'admin' OR JSON_CONTAINS(u.permissions, '"report"', '$') THEN 1
        ELSE 0
    END as has_report_access
FROM `users` u;

-- 6. Insere exemplos de usuários com diferentes permissões
-- ADMIN (acesso total)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `permissions`, `description`, `status`)
VALUES (
    'Administrador',
    'admin@sistema.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'admin',
    JSON_ARRAY('gerador', 'report'),
    'Acesso total ao sistema',
    'active'
) ON DUPLICATE KEY UPDATE
    permissions = JSON_ARRAY('gerador', 'report'),
    description = 'Acesso total ao sistema';

-- Usuário GERADOR (apenas gerador de emails)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `permissions`, `description`, `status`)
VALUES (
    'Usuário Gerador',
    'gerador@sistema.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'gerador',
    JSON_ARRAY('gerador'),
    'Acesso apenas ao Gerador de E-mails',
    'active'
) ON DUPLICATE KEY UPDATE
    permissions = JSON_ARRAY('gerador'),
    description = 'Acesso apenas ao Gerador de E-mails';

-- Usuário REPORT (apenas relatórios)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `permissions`, `description`, `status`)
VALUES (
    'Usuário Report',
    'report@sistema.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'report',
    JSON_ARRAY('report'),
    'Acesso apenas aos Relatórios',
    'active'
) ON DUPLICATE KEY UPDATE
    permissions = JSON_ARRAY('report'),
    description = 'Acesso apenas aos Relatórios';

-- ===================================================
-- VERIFICAÇÃO
-- ===================================================
-- Execute este SELECT para verificar se funcionou:
SELECT
    name,
    email,
    role,
    permissions,
    description,
    has_gerador_access,
    has_report_access
FROM user_permissions_view;

-- ===================================================
-- FIM DA MIGRATION
-- ===================================================
