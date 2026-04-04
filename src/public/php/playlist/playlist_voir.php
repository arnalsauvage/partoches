<?php
require_once __DIR__ . "/../lib/utilssi.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";
require_once("playlist.php");
require_once __DIR__ . "/../liens/lienChansonPlaylist.php";
require_once __DIR__ . "/../chanson/Chanson.php";
require_once __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../utilisateur/Utilisateur.php";

echo envoieHead("Voir Playlist", "../../css/styles-communs.css");
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
$nomPlaylist = htmlspecialchars($donnee['nom'] ?? '');
$description = $donnee['description'] ?? '';
$datePub = dateMysqlVersTexte($donnee['date_creation'] ?? $donnee['date'] ?? '');
$hits = $donnee['hits'] ?? 0;
$typePl = $donnee['type'] ?? 0;

$tri = $_GET['tri'] ?? ($typePl == 1 ? 'nom' : 'ordre');

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

// BARRE DE TRI
$labels = [
    'ordre' => 'Ordre manuel',
    'nom' => 'Ordre Alpha',
    'date' => 'Date publication',
    'hits' => 'Nombre de vues',
    'tona' => 'Tonalité',
    'annee' => 'Année chanson',
    'bpm' => 'Tempo (BPM)'
];

$sortie .= "    <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #8B4513; padding-bottom: 10px;'>";
$sortie .= "        <h2 style='margin: 0;'>Morceaux</h2>";
$sortie .= "        <div class='btn-group'>";
$sortie .= "            <button type='button' class='btn btn-sm btn-default dropdown-toggle' data-toggle='dropdown'>";
$sortie .= "                <i class='glyphicon glyphicon-sort'></i> Trier par : " . ($labels[$tri] ?? $tri) . " <span class='caret'></span>";
$sortie .= "            </button>";
$sortie .= "            <ul class='dropdown-menu dropdown-menu-right'>";
if ($typePl == 0) $sortie .= "                <li><a href='?id=$idPlaylist&tri=ordre'>Ordre manuel</a></li>";
$sortie .= "                <li><a href='?id=$idPlaylist&tri=nom'>Ordre Alpha (Titre)</a></li>";
$sortie .= "                <li><a href='?id=$idPlaylist&tri=date'>Date de publication</a></li>";
$sortie .= "                <li><a href='?id=$idPlaylist&tri=hits'>Nombre de vues</a></li>";
$sortie .= "                <li><a href='?id=$idPlaylist&tri=tona'>Tonalité</a></li>";
$sortie .= "                <li><a href='?id=$idPlaylist&tri=annee'>Année de la chanson</a></li>";
$sortie .= "                <li><a href='?id=$idPlaylist&tri=bpm'>BPM (Tempo)</a></li>";
$sortie .= "            </ul>";
$sortie .= "        </div>";
$sortie .= "    </div>";

$lignes = getMorceauxPlaylist($idPlaylist, $tri);

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
