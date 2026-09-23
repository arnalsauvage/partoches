-- Migration 004 : Ajout de la colonne ordre dans la table lienstrumchanson
ALTER TABLE `lienstrumchanson` ADD COLUMN IF NOT EXISTS `ordre` int(11) DEFAULT 0;
