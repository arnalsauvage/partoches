<?php
/**
 * PAGE : playlist_liste.php
 * Affichage moderne des playlists sous forme de cartes Canopée.
 */

require_once __DIR__ . "/../lib/utilssi.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";
require_once __DIR__ . "/../playlist/playlist.php";

echo envoieHead("Playlists", "../../css/styles-communs.css");
echo $MENU_HTML;

// --- PARAMÈTRES ET FILTRES ---
$tri = $_GET['tri'] ?? 'date';
$ordreAsc = isset($_GET['tri']); // tri = ASC, triDesc = DESC
if (isset($_GET['triDesc'])) {
    $tri = $_GET['triDesc'];
    $ordreAsc = false;
}

$recherche = $_GET['recherche'] ?? '';
$critereRecherche = ($recherche != "") ? "%$recherche%" : "%";

// --- RÉCUPÉRATION DES DONNÉES ---
$resultat = cherchePlaylists("nom", $critereRecherche, $tri, $ordreAsc);

// --- CONSTRUCTION DU CONTENU HTML ---
$html = "
<div class='container'>
    <div class='starter-template'>
        <h1 class='playlist-list-header'>
            <i class='glyphicon glyphicon-list-alt'></i> Playlists
        </h1>

        <!-- BARRE D'OUTILS -->
        <div class='well playlist-toolbar-well'>
            <div class='row'>
                <form method='GET' action='playlist_liste.php' class='form-inline'>
                    <div class='col-md-6 col-sm-12'>
                        <div class='input-group full-width'>
                            <input type='text' name='recherche' class='form-control' placeholder='Rechercher une playlist...' value='" . htmlspecialchars($recherche) . "'>
                            <span class='input-group-btn'>
                                <button class='btn btn-accent' type='submit'>
                                    <i class='glyphicon glyphicon-search'></i>
                                </button>
                                " . ($recherche != "" ? "<a href='playlist_liste.php' class='btn btn-default' title='Réinitialiser'><i class='glyphicon glyphicon-remove'></i></a>" : "") . "
                            </span>
                        </div>
                    </div>
                    <div class='col-md-6 col-sm-12 text-right mt-5'>
                        <div class='btn-group'>
                            <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                <i class='glyphicon glyphicon-sort'></i> Trier par : " . ucfirst($tri) . " <span class='caret'></span>
                            </button>
                            <ul class='dropdown-menu dropdown-menu-right'>
                                <li><a href='?tri=nom&amp;recherche=$recherche'>Nom (A-Z)</a></li>
                                <li><a href='?triDesc=nom&amp;recherche=$recherche'>Nom (Z-A)</a></li>
                                <li class='divider' aria-hidden='true'></li>
                                <li><a href='?tri=date&amp;recherche=$recherche'>Date (Ancienne)</a></li>
                                <li><a href='?triDesc=date&amp;recherche=$recherche'>Date (Récente)</a></li>
                                <li class='divider' aria-hidden='true'></li>
                                <li><a href='?triDesc=hits&amp;recherche=$recherche'>Popularité (Vues)</a></li>
                            </ul>
                        </div>
                        ";
if ($_SESSION['privilege'] >= $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $html .= "          <a href='playlist_form.php' class='btn btn-marron-fonce'>
                            <i class='glyphicon glyphicon-plus'></i> Nouvelle Playlist
                        </a>";
}
$html .= "          </div>
                </form>
            </div>
        </div>

        <!-- AFFICHAGE DES CARTES -->
        <div class='row'>";

if ($resultat->num_rows > 0) {
    while ($ligne = $resultat->fetch_row()) {
        $html .= afficheCartePlaylist($ligne);
    }
} else {
    $html .= "
        <div class='col-xs-12'>
            <div class='alert playlist-empty-alert text-center'>
                <i class='glyphicon glyphicon-info-sign playlist-empty-icon'></i>
                <p style='font-size: 1.2em;'>Aucune playlist trouvée pour cette recherche.</p>
                <a href='playlist_liste.php' class='btn btn-link'>Afficher toutes les playlists</a>
            </div>
        </div>";
}

$html .= "
        </div> <!-- Fin row -->
    </div> <!-- Fin starter-template -->
</div> <!-- Fin container -->
";

$html .= envoieFooter();
echo $html;
