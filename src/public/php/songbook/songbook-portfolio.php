<?php
require_once dirname(__DIR__) . "/lib/utilssi.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";
require_once "Songbook.php";
require_once __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../chanson/Chanson.php";
require_once __DIR__ . "/../lib/Image.php";

/**
 * Portfolio des Songbooks - Vue moderne type galerie "Canopée"
 */

global $_DOSSIER_CHANSONS;

// 1. Logique de récupération des données
$titresChansons = chargeLibelles("chanson", "nom");
$listeSongbooks = chercheSongbooks("nom", "%", "date", false);

$html = "<div class='container'>";
$html .= "  <h1 class='text-center' style='margin-bottom: 40px;'><i class='glyphicon glyphicon-book'></i> Galerie des Songbooks</h1>";
$html .= "  <div class='portfolio-grid'>";

if ($listeSongbooks) {
    while ($songbook = $listeSongbooks->fetch_row()) {
        // Songbook : [0]id [1]nom [2]description [3]date [4]image [5]hits [6]idUser
        $idSb = $songbook[0];
        $nomSb = htmlspecialchars($songbook[1]);
        $dateSb = dateMysqlVersTexte($songbook[3]);
        $imageSb = imageSongbook($idSb);
        
        // Utilisation de la vignette moderne via Image.php
        $srcVignette = Image::getThumbnailUrl($idSb . "/" . $imageSb, 'sd', 'songbooks');
        $vignetteSb = "<img src='$srcVignette' class='img-responsive center-block' style='height:200px; object-fit:cover; border-radius:10px;' alt='vignette'>";

        // Récupération du PDF principal du songbook
        $pdfSb = "vide";
        $docsSb = chercheDocumentsTableId("songbook", $idSb);
        if ($docsSb) {
            while ($doc = $docsSb->fetch_row()) {
                if (strstr(strtolower($doc[1]), "pdf")) {
                    $pdfSb = composeNomVersion($doc[1], $doc[4]);
                }
            }
        }

        // Construction du lien pochette (soit PDF, soit fiche détail)
        $urlPochette = ($pdfSb !== "vide")
            ? "../../data/songbooks/" . myUrlEncode($idSb) . "/" . $pdfSb
            : "./songbook_voir.php?id=$idSb";
        $target = ($pdfSb !== "vide") ? "target='_blank'" : "";

        // Récupération des chansons liées
        $trackListHtml = "";
        $liensChansons = LienDocSongbook::chercheLiensDocSongbook('idSongbook', $idSb, "ordre");
        if ($liensChansons) {
            while ($lien = $liensChansons->fetch_row()) {
                $docInfo = chercheDocument($lien[1]); // [6] est l'idChanson
                $idChanson = $docInfo[6];
                if (isset($titresChansons[$idChanson])) {
                    $nomChanson = htmlspecialchars(limiteLongueur($titresChansons[$idChanson], 25));
                    $trackListHtml .= <<<HTML
                    <li class="songbook-track-item">
                        <a href="../chanson/chanson_voir.php?id=$idChanson" title="Voir la fiche">
                            <i class="glyphicon glyphicon-music" style="color: #D2B48C; margin-right: 5px;"></i> $nomChanson
                        </a>
                    </li>
HTML;
                }
            }
        }

        // Assemblage de la carte
        $html .= <<<HTML
        <div class="songbook-card">
            <div class="songbook-card-header">
                <h3><a href="./songbook_voir.php?id=$idSb">$nomSb</a></h3>
            </div>
            <div class="songbook-card-pochette">
                <a href="$urlPochette" $target title="Ouvrir le Songbook">
                    $vignetteSb
                </a>
            </div>
            <div class="songbook-card-body">
                <ul class="songbook-track-list">
                    $trackListHtml
                </ul>
            </div>
            <div class="songbook-card-footer">
                <span><i class="glyphicon glyphicon-calendar"></i> $dateSb</span>
                <span><i class="glyphicon glyphicon-eye-open"></i> $songbook[5] vues</span>
            </div>
        </div>
HTML;
    }
} else {
    $html .= "<p class='lead'>Aucun songbook trouvé.</p>";
}

$html .= "  </div>"; // .portfolio-grid
$html .= "</div>"; // .container

$headHtml = envoieHead("Galerie des Songbooks", "../../css/index.css");
$html .= envoieFooter();

echo $headHtml;
echo $MENU_HTML;
echo $html;
