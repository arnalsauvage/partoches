<?php
include_once __DIR__ . "/../lib/utilssi.php";
include_once __DIR__ . "/../liens/LienDocSongbook.php";

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if (($_SESSION['privilege'] ?? 0) < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    exit();
}

// Traitement des paramètres de changement d'ordre
if (isset($_POST['idSongbook']) && isset($_POST['positions']) && is_array($_POST['positions'])) {
    $idSongbook = (int)$_POST['idSongbook'];
    $positions = $_POST['positions'];
    $rang = 1;
    
    // On met à jour TOUS les items pour garantir la cohérence de l'ordre en BDD
    foreach ($positions as $item) {
        if (!isset($item[0])) continue;
        
        $idDocument = (int)$item[0];
        // On utilise la méthode statique avec le bon casing
        LienDocSongbook::modifieOrdreLienDocSongbook($idDocument, $idSongbook, $rang);
        $rang++;
    }
    
    // Finalisation : on ré-indexe proprement 1, 2, 3... sans trous
    LienDocSongbook::ordonneLiensSongbook($idSongbook);
    
    echo "OK";
}