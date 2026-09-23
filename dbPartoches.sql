-- dbPartoches.sql
-- Structure de base complète pour le projet Partoches

CREATE TABLE IF NOT EXISTS `chanson` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `interprete` varchar(255) DEFAULT NULL,
  `annee` int(11) DEFAULT NULL,
  `idUser` int(11) DEFAULT 1,
  `tempo` int(11) DEFAULT NULL,
  `mesure` varchar(50) DEFAULT NULL,
  `pulsation` varchar(50) DEFAULT NULL,
  `datePub` date DEFAULT NULL,
  `hits` int(11) DEFAULT 0,
  `tonalite` varchar(50) DEFAULT NULL,
  `tonalite_originale` varchar(3) DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `publication` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `songbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text,
  `date` date,
  `image` varchar(255),
  `hits` int(11) DEFAULT 0,
  `idUser` int(11),
  `type` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `liendocsongbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idDocument` int(11) NOT NULL,
  `idSongbook` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `tailleKo` int(11) DEFAULT 0,
  `date` date DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `nomTable` varchar(255),
  `idTable` int(11),
  `idUser` int(11),
  `hits` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `parametres` (
  `nom` varchar(255) NOT NULL,
  `valeur` text,
  PRIMARY KEY (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'utilisateur/defaut.png',
  `site` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `dateDernierLogin` date DEFAULT NULL,
  `nbreLogins` int(11) DEFAULT 0,
  `privilege` int(11) DEFAULT 1,
  `token_activation` varchar(255) DEFAULT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `noteUtilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `nomObjet` varchar(255) NOT NULL,
  `idObjet` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text,
  `auteur` int(11) DEFAULT 1,
  `datePub` datetime DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(50) DEFAULT 'partoche',
  `tags` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `lien` varchar(255) DEFAULT NULL,
  `hits` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `strum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unite` int(11) DEFAULT 4,
  `longueur` int(11) DEFAULT 4,
  `strum` varchar(255) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `description` text,
  `swing` varchar(50) DEFAULT 'non',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lienstrumchanson` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idChanson` int(11) NOT NULL,
  `idStrum` int(11) DEFAULT NULL,
  `strum` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lienurl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` text,
  `date` date DEFAULT NULL,
  `idUser` int(11) DEFAULT 1,
  `nomTable` varchar(255) DEFAULT 'chanson',
  `idTable` int(11) DEFAULT NULL,
  `hits` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
