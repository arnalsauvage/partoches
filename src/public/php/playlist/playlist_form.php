<?php
/**
 * PAGE : playlist_form.php
 * Formulaire de gestion des playlists (Refactorisé SOLID / Canopée).
 */

require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/PlaylistFormService.php";
require_once __DIR__ . "/PlaylistFormRenderer.php";
require_once __DIR__ . "/playlist.php";
require_once __DIR__ . "/../liens/lienChansonPlaylist.php";
require_once __DIR__ . "/../chanson/Chanson.php";

$table = "playlist";

// Sécurité : Droits d'édition requis
if ($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $urlRedirection = $table . "_voir.php" . (isset($_GET['id']) ? "?id=" . (int)$_GET['id'] : "");
    redirection($urlRedirection);
}

// Récupération de l'ID
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$message = "";

// --- GESTION DES ACTIONS (AJAX/GET) ---
if ($id > 0) {
    // Import massif depuis un Songbook
    if (isset($_POST['action']) && $_POST['action'] === 'import_songbook' && isset($_POST['id_songbook'])) {
        $message = PlaylistFormService::importFromSongbook($id, (int)$_POST['id_songbook']);
    }
    // Ajout d'un morceau unitaire
    elseif (isset($_POST['chanson']) && is_numeric($_POST['chanson'])) {
        $message = PlaylistFormService::addSong($id, (int)$_POST['chanson']);
    }
    // Actions sur les morceaux (up/down/del)
    PlaylistFormService::handleActions($id, $_GET);
}

// --- PRÉPARATION DES DONNÉES ---
$formData = PlaylistFormService::prepareData($id);

// --- RENDU HTML ---
echo PlaylistFormRenderer::render($formData, $message);
