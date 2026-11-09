-- Migration 006 : Création de la table auth_token pour la connexion persistante (14 jours)
CREATE TABLE IF NOT EXISTS `auth_token` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `selector` VARCHAR(32) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL,
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_expiration` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_selector` (`selector`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_auth_token_utilisateur` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
