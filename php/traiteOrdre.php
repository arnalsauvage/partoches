<?php
include_once("lib/utilssi.php");
include_once("lienDocSongbook.php");

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < 2) {
    exit();
    // TODO : ajouter un log ?
}

// Traitement de l'ajout de document
if (isset ($_POST ['idSongbook']) && (isset ($_POST ['positions']))) {
    $idSongbook = $_POST ['idSongbook'];
    $positions = $_POST ['positions'];
    //var_dump($positions);
    $rang = 1;

    foreach ($positions as $position) {
        $index = $position[0];
        $position = $position[1];
        if ($rang <> $position) {
            modifieOrdreLienDocSongbook($index, $idSongbook, $rang);
            echo "Modification du doc $index du rang $position vers le rang $rang pour le songbook $idSongbook <br> \n";
        }
        $rang++;
    }
}