<?php
/**
 * Liste des Strums (Django Style)
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";

$db = $_SESSION['mysql'];

// Récupération des paramètres de tri et filtre
$tri = $_GET['sort'] ?? 'nom';
$mesure = $_GET['mesure'] ?? '';

$strums = Strum::chargeStrumsBdd($tri, $mesure);
$nbStrums = count($strums);

// --- RENDU HTML ---
$headHtml = envoieHead("Répertoire des Strums", "../../css/strum_liste.css");
echo $headHtml;
echo $MENU_HTML;

// Fonctions helper pour les classes CSS actives
$activeNom = ($tri == 'nom') ? 'btn-primary' : 'btn-default';
$activeDate = ($tri == 'date') ? 'btn-primary' : 'btn-default';
$activePop = ($tri == 'pop') ? 'btn-primary' : 'btn-default';

// Préparation des options du select
$opt44 = ($mesure == '4/4') ? 'selected' : '';
$opt3t = ($mesure == '3t') ? 'selected' : '';

$html = <<<HTML
<div class="container strum-container">
    <div class="row">
        <div class="col-xs-12 text-center" style="margin-bottom: 20px;">
            <h1 style="font-weight: 900; letter-spacing: 5px; margin-bottom: 5px;">
                <span class="glyphicon glyphicon-music"></span> RÉPERTOIRE DES STRUMS
            </h1>
            <p class="text-muted" style="text-transform: uppercase;">$nbStrums RYTHMES DISPONIBLES</p>
        </div>
    </div>

    <!-- Barre de filtres et tri -->
    <div class="row" style="margin-bottom: 30px; background: #fdfaf5; padding: 15px; border-radius: 8px; border: 1px solid #D2B48C;">
        <div class="col-sm-7 text-center-xs" style="margin-bottom: 10px;">
            <span style="font-weight: bold; color: #8B4513; margin-right: 10px; text-transform: uppercase; font-size: 12px;">Classer par :</span>
            <div class="btn-group" role="group">
                <a href="?sort=nom&amp;mesure=$mesure" class="btn btn-sm $activeNom">Nom</a>
                <a href="?sort=date&amp;mesure=$mesure" class="btn btn-sm $activeDate">Plus récents</a>
                <a href="?sort=pop&amp;mesure=$mesure" class="btn btn-sm $activePop">Plus utilisés</a>
            </div>
        </div>
        <div class="col-sm-5 text-right text-center-xs">
            <form class="form-inline" method="get">
                <input type="hidden" name="sort" value="$tri">
                <label style="font-weight: bold; color: #8B4513; margin-right: 10px; text-transform: uppercase; font-size: 12px;">Mesure :</label>
                <select name="mesure" class="form-control input-sm" onchange="this.form.submit()" style="border-radius: 15px;">
                    <option value="">Toutes</option>
                    <option value="4/4" $opt44>4 temps</option>
                    <option value="3t" $opt3t>3 temps</option>
                </select>
            </form>
        </div>
    </div>

    <div class="row">
HTML;

if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
    $html .= <<<HTML
            <div class="col-xs-12 text-center" style="margin-bottom: 30px;">
                <a href="strum_form.php" class="btn btn-primary btn-lg" style="border-radius: 30px; padding: 10px 25px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                    <i class="glyphicon glyphicon-plus"></i> AJOUTER UN STRUM
                </a>
            </div>
HTML;
}

foreach ($strums as $s) {
    $html .= $s->afficheCarteStrum();
}

$html .= "</div></div>";
$html .= envoieFooter();

echo $html;
