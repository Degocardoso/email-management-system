-- ===================================================
-- SISTEMA DE AUTENTICAÇÃO - SCHEMA MySQL
-- ===================================================

-- Cria o banco de dados se não existir
CREATE DATABASE IF NOT EXISTS `auth_system`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `auth_system`;

-- ===================================================
-- TABELA DE USUÁRIOS
-- ===================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'inactive', 'blocked') NOT NULL DEFAULT 'active',
    `last_login` DATETIME NULL DEFAULT NULL,
    `login_attempts` INT(11) NOT NULL DEFAULT 0,
    `blocked_until` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email_unique` (`email`),
    KEY `status_index` (`status`),
    KEY `role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- TABELA DE SESSÕES (opcional, para gerenciar sessões)
-- ===================================================
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` INT(11) UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `payload` TEXT NOT NULL,
    `last_activity` INT(11) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id_index` (`user_id`),
    KEY `last_activity_index` (`last_activity`),
    CONSTRAINT `fk_sessions_users`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- TABELA DE LOGS DE AUDITORIA
-- ===================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NULL DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id_index` (`user_id`),
    KEY `action_index` (`action`),
    KEY `created_at_index` (`created_at`),
    CONSTRAINT `fk_audit_users`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- INSERIR USUÁRIO ADMIN PADRÃO
-- Senha: Admin@123 (use hash bcrypt em produção)
-- ===================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`)
VALUES (
    'Administrador',
    'admin@sistema.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'admin',
    'active'
) ON DUPLICATE KEY UPDATE `email` = `email`;

-- ===================================================
-- VIEWS ÚTEIS
-- ===================================================

-- View para estatísticas de usuários
CREATE OR REPLACE VIEW `user_statistics` AS
SELECT
    COUNT(*) as total_users,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_users,
    SUM(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_last_30_days
FROM `users`;

-- ===================================================
-- PROCEDURES ÚTEIS
-- ===================================================

-- Procedure para limpar sessões expiradas
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS `clean_expired_sessions`()
BEGIN
    DELETE FROM `sessions`
    WHERE `last_activity` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 HOUR));
END //
DELIMITER ;

-- Procedure para desbloquear usuários automaticamente
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS `unlock_users`()
BEGIN
    UPDATE `users`
    SET `status` = 'active',
        `login_attempts` = 0,
        `blocked_until` = NULL
    WHERE `status` = 'blocked'
      AND `blocked_until` IS NOT NULL
      AND `blocked_until` < NOW();
END //
DELIMITER ;

-- ===================================================
-- EVENTOS AGENDADOS (requer event_scheduler=ON)
-- ===================================================

-- Limpar sessões expiradas a cada hora
CREATE EVENT IF NOT EXISTS `evt_clean_sessions`
ON SCHEDULE EVERY 1 HOUR
DO CALL `clean_expired_sessions`();

-- Desbloquear usuários a cada 5 minutos
CREATE EVENT IF NOT EXISTS `evt_unlock_users`
ON SCHEDULE EVERY 5 MINUTE
DO CALL `unlock_users`();

-- ===================================================
-- FIM DO SCHEMA
-- ===================================================
