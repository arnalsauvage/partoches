-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Jeu 12 Octobre 2017 à 14:49
-- Version du serveur :  5.7.14
-- Version de PHP :  5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `fuck`
--

-- --------------------------------------------------------

--
-- Structure de la table `chanson`
--

CREATE TABLE `chanson` (
  `id` int(11) NOT NULL,
  `nom` tinytext NOT NULL,
  `interprete` tinytext NOT NULL,
  `annee` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `chanson`
--

INSERT INTO `chanson` (`id`, `nom`, `interprete`, `annee`) VALUES
(26, 'La nuit je mens', 'Bashung', 1998),
(23, 'VoilÃ  l\'Ã©tÃ©', 'N\'egresses vertes', 1990),
(25, 'Le poinÃ§onneur', 'Serge Gainsbourg', 1958),
(27, 'La javanaise remake', 'Gainsbarre', 1979);

-- --------------------------------------------------------

--
-- Structure de la table `document`
--

CREATE TABLE `document` (
  `id` int(11) NOT NULL,
  `nom` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `tailleKo` int(11) NOT NULL,
  `date` date NOT NULL,
  `version` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `liendocsongbook`
--

CREATE TABLE `liendocsongbook` (
  `id` int(11) NOT NULL,
  `idDocument` int(11) NOT NULL,
  `idSongbook` int(11) NOT NULL,
  `ordre` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `songbook`
--

CREATE TABLE `songbook` (
  `id` int(11) NOT NULL,
  `nom` text COLLATE utf8_unicode_ci NOT NULL,
  `description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `image` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `songbook`
--

INSERT INTO `songbook` (`id`, `nom`, `description`, `date`, `image`, `hits`) VALUES
(23, 'Songbook 1', 'les chansons retro', '2017-04-10', 'songbook1.png', 112),
(24, 'Songbook 2', 'les chansons swing', '2017-04-11', 'songbook2.png', 51);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `login` char(64) NOT NULL,
  `mdp` char(64) DEFAULT NULL,
  `prenom` char(64) DEFAULT NULL,
  `nom` char(64) DEFAULT NULL,
  `image` tinytext,
  `site` tinytext,
  `email` tinytext,
  `signature` tinytext,
  `dateDernierLogin` date DEFAULT NULL,
  `nbreLogins` int(11) NOT NULL DEFAULT '0',
  `privilege` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `login`, `mdp`, `prenom`, `nom`, `image`, `site`, `email`, `signature`, `dateDernierLogin`, `nbreLogins`, `privilege`) VALUES
(1, 'arnaud', 'h95dkHItKF3DKIaTS+GD/g==', 'Arnaud', 'Medina', '/utilisateur/user11.jpg', 'http://top5.re', 'arnaud@test.com', 'Internet accÃ©lÃ¨re l\'avÃ¨nement de la sociÃ©tÃ© de marchÃ©, avec une poussÃ©e violente de concurrence et de compÃ©tition.', '2017-10-02', 3, 2),
(56, 'alainminc', 'wVwy4AFAtlhXY0nXa4aA8w==', 'Alain', 'Minc', '/utilisateur/user02.jpg', 'http://samere', 'truc@bidule.com', 'Si une once de vertu ajoute Ã  l\'efficacitÃ© nÃ©e de la compÃ©tition, il faut beaucoup de compÃ©tition pour mettre fin Ã  l\'efficacitÃ© de la seule vertu.', '2017-10-02', 2, 1),
(55, 'admin', 'h95dkHItKF3DKIaTS+GD/g==', 'Jacques', 'Attali', '/utilisateur/user06.jpg', 'http://www.attali.com/', 'truc@bidule.com', 'Dans un monde oÃ¹ l\'information est une arme et oÃ¹ elle constitue mÃªme le code de la vie, la rumeur agit comme un virus, le pire de tous car il dÃ©truit les dÃ©fenses immunitaires de sa victime.', '2017-10-03', 26, 3),
(58, 'doudou', 'wVwy4AFAtlhXY0nXa4aA8w==', 'loulou', 'liuliu', '/utilisateur/user05.jpg', 'http://site.fr', 'rer@ratp.fr', 'Devise ou citation...', '2017-10-02', 3, 0);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `chanson`
--
ALTER TABLE `chanson`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `document`
--
ALTER TABLE `document`
  ADD KEY `id` (`id`);

--
-- Index pour la table `liendocsongbook`
--
ALTER TABLE `liendocsongbook`
  ADD KEY `id` (`id`);

--
-- Index pour la table `songbook`
--
ALTER TABLE `songbook`
  ADD KEY `id` (`id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `chanson`
--
ALTER TABLE `chanson`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT pour la table `document`
--
ALTER TABLE `document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `liendocsongbook`
--
ALTER TABLE `liendocsongbook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `songbook`
--
ALTER TABLE `songbook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
