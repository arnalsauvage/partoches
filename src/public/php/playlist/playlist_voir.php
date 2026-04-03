<?php
require_once __DIR__ . "/../lib/utilssi.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";
require_once("playlist.php");
require_once __DIR__ . "/../liens/lienChansonPlaylist.php";
require_once __DIR__ . "/../chanson/Chanson.php";
require_once __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../utilisateur/Utilisateur.php";

echo envoieHead("Voir Playlist", "styles-communs.css");
echo $MENU_HTML;
$table = "playlist";
$sortie = "";
$monImage = "";
global $_DOSSIER_CHANSONS;


if (isset($_GET ['id']) && is_numeric($_GET ['id'])) {
    $idPlaylist = $_GET ['id'];
} else {
    echo "erreur #1 dans playlist_voir.php";
    return;
}

// On augmente le compteur de vues de la playlist
augmenteHits($table, $idPlaylist);

// On charge le tableau des utilisateurs
$tabUsers = portraitDesUtilisateurs();

$donnee = chercheplaylist($idPlaylist);
$nomPlaylist = htmlspecialchars($donnee[1]);
$description = $donnee[2];
$datePub = dateMysqlVersTexte($donnee[3]);
$hits = $donnee[5];

$sortie .= "<div class='container'>";
$sortie .= "  <div class='starter-template'>";
$sortie .= "    <div class='row'>";
$sortie .= "      <div class='col-xs-12 text-left' style='margin-bottom: 15px;'>";
$sortie .= "        <a href='playlist_liste.php' class='btn btn-default btn-sm'><i class='glyphicon glyphicon-arrow-left'></i> Retour aux playlists</a>";
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    global $playlistForm, $cheminImages, $iconeEdit;
    $sortie .= "        <a href='$playlistForm?id=$idPlaylist' class='btn btn-primary btn-sm'><i class='glyphicon glyphicon-pencil'></i> Modifier la playlist</a>";
}
$sortie .= "      </div>";
$sortie .= "    </div>";

$sortie .= "    <div class='well' style='background-color: #2b1d1a; color: #F5F5DC; border: 1px solid #D2B48C;'>";
$sortie .= "      <div class='row'>";
$sortie .= "        <div class='col-md-3 text-center'>";
// Image de la playlist
$imagePochette = imagePlaylist($idPlaylist);
if ($imagePochette != "") {
    $sortie .= "<img src='../data/playlists/$imagePochette' class='img-responsive img-thumbnail' style='max-height: 200px; border: 2px solid #D2B48C;' alt='Pochette'>";
} else {
    $sortie .= "<div style='height: 150px; background: #D2B48C; display: flex; align-items: center; justify-content: center; border-radius: 8px;'><i class='glyphicon glyphicon-music' style='font-size: 64px; color: #2b1d1a;'></i></div>";
}
$sortie .= "        </div>";
$sortie .= "        <div class='col-md-9'>";
$sortie .= "          <h1 style='color: #D2B48C; margin-top: 0;'>$nomPlaylist</h1>";
$sortie .= "          <p style='font-size: 1.2em; font-style: italic;'>$description</p>";
$sortie .= "          <hr style='border-top: 1px solid #D2B48C;'>";
$sortie .= "          <p><i class='glyphicon glyphicon-calendar'></i> Créée le $datePub | <i class='glyphicon glyphicon-eye-open'></i> $hits vue(s)</p>";
$sortie .= "        </div>";
$sortie .= "      </div>";
$sortie .= "    </div>";

$sortie .= "    <h2 style='margin-bottom: 30px; border-bottom: 2px solid #8B4513; padding-bottom: 10px;'>Morceaux de la playlist</h2>";

$lignes = getMorceauxPlaylist($idPlaylist);

if ($lignes->num_rows > 0) {
    $sortie .= "    <div class='row'>";
    while ($ligne = $lignes->fetch_assoc()) {
        $idChanson = $ligne['id'];
        $_chanson = new Chanson($idChanson);
        
        // On récupère la carte et on adapte les liens (car on est dans /php/playlist/)
        $card = $_chanson->afficheCarteChanson();
        $card = str_replace("href='chanson_voir.php", "href='../chanson/chanson_voir.php", $card);
        $card = str_replace("href='?filtre", "href='../chanson/chanson_liste.php?filtre", $card);
        
        $sortie .= $card;
    }
    $sortie .= "    </div>";
} else {
    $sortie .= "    <div class='alert alert-info'>Cette playlist ne contient aucun morceau pour le moment.</div>";
}

$sortie .= "  </div>"; // starter-template
$sortie .= "</div>"; // container

$sortie .= envoieFooter();
echo $sortie;
// TODO ajouter un bouton : supprimer fichiers
