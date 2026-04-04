-- Migration 001: Création de la table playlist et support du mode dynamique
-- Date: 2026-04-04

CREATE TABLE IF NOT EXISTS `playlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_utilisateur` int(11) NOT NULL,
  `type` varchar(50) DEFAULT 'manuelle',
  `criteres` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `hits` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lienchansonplaylist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_playlist` int(11) NOT NULL,
  `id_chanson` int(11) NOT NULL,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_playlist` (`id_playlist`),
  KEY `id_chanson` (`id_chanson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
