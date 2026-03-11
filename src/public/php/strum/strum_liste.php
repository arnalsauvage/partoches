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
HTML;

if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
    $html .= <<<HTML
            <div style="margin-top: 20px;">
                <a href="strum_form.php" class="btn btn-primary btn-lg" style="border-radius: 30px; padding: 10px 25px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                    <i class="glyphicon glyphicon-plus"></i> AJOUTER UN STRUM
                </a>
            </div>
HTML;
}

$html .= <<<HTML
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
