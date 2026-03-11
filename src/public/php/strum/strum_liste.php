<?php
/**
 * Liste des Strums (Django Style)
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";

$db = $_SESSION['mysql'];
$strums = Strum::chargeStrumsBdd();
$nbStrums = count($strums);

// --- RENDU HTML ---
$headHtml = envoieHead("Répertoire des Strums", "../../css/strum_liste.css");
echo $headHtml;
echo $MENU_HTML;
$html = "";

$html .= <<<HTML
<div class="container strum-container">
    <div class="row">
        <div class="col-xs-12 text-center" style="margin-bottom: 30px;">
            <h1 style="font-weight: 900; letter-spacing: 5px;">
                <span class="glyphicon glyphicon-music"></span> RÉPERTOIRE DES STRUMS
            </h1>
            <p class="text-muted">$nbStrums RYTHMES DISPONIBLES</p>
        </div>
    </div>

    <div class="row">
HTML;

foreach ($strums as $s) {
    $html .= $s->afficheCarteStrum();
}

$html .= "</div></div>";
$html .= envoieFooter();

echo $html;
