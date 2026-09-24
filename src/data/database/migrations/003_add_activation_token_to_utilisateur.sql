-- Migration : 003_add_activation_token_to_utilisateur.sql
-- Ajout des colonnes pour la confirmation de compte par email et la prévention anti-bot

ALTER TABLE `utilisateur`
  ADD COLUMN IF NOT EXISTS `token_activation` VARCHAR(255) NULL DEFAULT NULL AFTER `privilege`,
  ADD COLUMN IF NOT EXISTS `est_actif` TINYINT(1) NOT NULL DEFAULT 1 AFTER `token_activation`;
