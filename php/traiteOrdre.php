<?php
include_once("lib/utilssi.php");
include_once("lienDocSongbook.php");

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < 2) {
    exit();
    // TODO : ajouter un log ?
}

// Traitement des paramètres de changement d'ordre
if (isset ($_POST ['idSongbook']) && (isset ($_POST ['positions']))) {
    $idSongbook = $_POST ['idSongbook'];
    $positions = $_POST ['positions'];
    $rang = 1;
    // Les enregistrements arrivent dans le nouvel ordre voulu,
    // avec pour chaque entrée l'identifiant linDocSongbbok et la position dans l'ordre existant en BDD
    foreach ($positions as $position) {
        $idLienDS = $position[0];
        $position = $position[1];
        // Si l'enregistrement a changé de place
        if ($rang <> $position) {
            // On modifie l'ordre de BDD pour mettre le nouveau souhaité
            modifieOrdreLienDocSongbook($idLienDS, $idSongbook, $rang);
            // echo "Modification du doc $idLienDS du rang $position vers le rang $rang pour le songbook $idSongbook <br> \n";
        }
        $rang++;
    }
}