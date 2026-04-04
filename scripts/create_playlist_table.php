<?php
require_once __DIR__ . '/../src/autoload.php';
$db = $_SESSION['mysql'];

// Création de la table playlist
$sqlPlaylist = "CREATE TABLE IF NOT EXISTS `playlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text,
  `date` date NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `hits` int(11) DEFAULT '0',
  `idUser` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($db->query($sqlPlaylist)) {
    echo "Table 'playlist' vérifiée/créée avec succès !<br>";
} else {
    echo "Erreur lors de la création de 'playlist' : " . $db->error . "<br>";
}

// Création de la table de liens
$sqlLien = "CREATE TABLE IF NOT EXISTS `lienchansonplaylist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idChanson` int(11) NOT NULL,
  `idPlaylist` int(11) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($db->query($sqlLien)) {
    echo "Table 'lienchansonplaylist' vérifiée/créée avec succès !<br>";
} else {
    echo "Erreur lors de la création de 'lienchansonplaylist' : " . $db->error . "<br>";
}
