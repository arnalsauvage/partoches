<?php
/**
 * Traitement des liens externes (AJAX / POST)
 */

// Inclusion de l'autoloader (définit PHP_DIR, Session et MySQL)
require_once dirname(__DIR__, 3) . "/autoload.php";

// Un non-admin non éditeur ne peut modifier les liens
if (($_SESSION['privilege'] ?? 0) > ($GLOBALS["PRIVILEGE_MEMBRE"] ?? 1)) {
    
    $mode = $_POST['mode'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    // Suppression
    if ($mode == "DEL") {
        LienUrl::supprimeLienurl($id);
        echo "ok suppression";
        exit();
    }

    $db = $_SESSION['mysql'];
    $url = $db->real_escape_string($_POST['url'] ?? '');
    $type = $db->real_escape_string($_POST['type'] ?? '');
    $description = $db->real_escape_string($_POST['description'] ?? '');
    $nomtable = $db->real_escape_string($_POST['nomtable'] ?? '');
    $idtable = (int)($_POST['idtable'] ?? 0);
    $date = $db->real_escape_string($_POST['date'] ?? '');
    $idUser = (int)($_POST['idUser'] ?? $_SESSION['id']);
    $hits = (int)($_POST['hits'] ?? 0);

    // Contrôle de la date
    if (!validateDate($date)) {
        $date = date("d/m/Y");
    }

    // Création
    if ($mode == "NEW") {
        LienUrl::creeLienurl($url, $type, $description, $nomtable, $idtable, $date, $idUser, $hits);
        echo "ok creation";
    }

    // Modification
    if ($mode == "UPDATE" && $id > 0) {
        LienUrl::modifieLienurl($id, $url, $type, $description, $nomtable, $idtable, $date, $idUser, $hits);
        echo "ok modification";
    }

    // On actualise la table des médias automatiquement
    actualiseMedias();
} else {
    echo "n'a pas été traité. (Droits insuffisants)";
}
