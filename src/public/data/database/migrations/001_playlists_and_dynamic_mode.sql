-- 001_playlists_and_dynamic_mode.sql
-- (Version alignée sur la Prod)

-- Création de la table playlist si elle n'existe pas
CREATE TABLE IF NOT EXISTS `playlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_utilisateur` int(11) NOT NULL,
  `type` varchar(50) DEFAULT 'manuelle',
  `criteres` text COLLATE utf8_unicode_ci,
  `image` varchar(255) COLLATE utf8_unicode_ci,
  `hits` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Création de la table lienchansonplaylist si elle n'existe pas
CREATE TABLE IF NOT EXISTS `lienchansonplaylist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_chanson` int(11) NOT NULL,
  `id_playlist` int(11) NOT NULL,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_playlist` (`id_playlist`),
  KEY `id_chanson` (`id_chanson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
