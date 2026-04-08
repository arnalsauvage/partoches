<?php
/**
 * Liste des Strums (Django Style)
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";

$db = $_SESSION['mysql'];

// Récupération des paramètres de tri et filtre
// Par défaut, on trie par date (plus récents en premier)
$tri = $_GET['sort'] ?? 'date';
$mesure = $_GET['mesure'] ?? '';

$strums = Strum::chargeStrumsBdd($tri, $mesure);
$nbStrums = count($strums);

// --- RENDU HTML ---
$headHtml = envoieHead("Répertoire des Strums", "../../css/strum_liste.css");
echo $headHtml;
echo $MENU_HTML;

// Fonctions helper pour les classes CSS actives
$activeNom = ($tri == 'nom') ? 'background-color: #5d4037; color: white;' : 'background-color: #fdfaf5; color: #5d4037; border: 1px solid #D2B48C;';
$activeDate = ($tri == 'date') ? 'background-color: #5d4037; color: white;' : 'background-color: #fdfaf5; color: #5d4037; border: 1px solid #D2B48C;';
$activePop = ($tri == 'pop') ? 'background-color: #5d4037; color: white;' : 'background-color: #fdfaf5; color: #5d4037; border: 1px solid #D2B48C;';

// Préparation des options du select
$opt44 = ($mesure == '4/4') ? 'selected' : '';
$opt3t = ($mesure == '3t') ? 'selected' : '';

$html = <<<HTML
<div class="container strum-container" style="padding-top: 80px;">
    <div class="row">
        <div class="col-xs-12 text-center" style="margin-bottom: 20px;">
            <h1 style="font-weight: 900; letter-spacing: 5px; margin-bottom: 5px; color: #5d4037;">
                <span class="glyphicon glyphicon-music"></span> RÉPERTOIRE DES STRUMS
            </h1>
            <p class="text-muted" style="text-transform: uppercase; font-weight: bold; color: #8d6e63;">$nbStrums RYTHMES DISPONIBLES</p>
        </div>
    </div>

    <!-- Barre de filtres et tri -->
    <div class="row" style="margin-bottom: 30px; background: #fdfaf5; padding: 20px; border-radius: 12px; border: 2px solid #D2B48C; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
        <div class="col-sm-7 text-center-xs" style="margin-bottom: 10px;">
            <span style="font-weight: bold; color: #8B4513; margin-right: 15px; text-transform: uppercase; font-size: 13px;">Classer par :</span>
            <div class="btn-group" role="group">
                <a href="?sort=nom&amp;mesure=$mesure" class="btn btn-sm" style="$activeNom margin-right: 5px; border-radius: 4px;">Nom</a>
                <a href="?sort=date&amp;mesure=$mesure" class="btn btn-sm" style="$activeDate margin-right: 5px; border-radius: 4px;">Plus récents</a>
                <a href="?sort=pop&amp;mesure=$mesure" class="btn btn-sm" style="$activePop border-radius: 4px;">Plus utilisés</a>
            </div>
        </div>
        <div class="col-sm-5 text-right text-center-xs">
            <form class="form-inline" method="get">
                <input type="hidden" name="sort" value="$tri">
                <label style="font-weight: bold; color: #8B4513; margin-right: 10px; text-transform: uppercase; font-size: 13px;">Mesure :</label>
                <select name="mesure" class="form-control input-sm" onchange="this.form.submit()" style="border-radius: 15px; border: 1px solid #D2B48C; background: white; color: #5d4037; font-weight: bold;">
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
            <div class="col-xs-12 text-center" style="margin-bottom: 40px;">
                <a href="strum_form.php" class="btn btn-lg" style="background-color: #8B4513; color: white; border-radius: 30px; padding: 12px 35px; font-weight: bold; box-shadow: 0 6px 15px rgba(0,0,0,0.2); border: none; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="glyphicon glyphicon-plus"></i> AJOUTER UN STRUM
                </a>
            </div>
HTML;
}

foreach ($strums as $s) {
    $html .= $s->afficheCarteStrum();
}

$html .= <<<HTML
    </div>
</div>

<!-- MODALE POUR VOIR LES CHANSONS LIÉES -->
<div class="modal fade modal-strum" id="modalChansonsStrum" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; border: 4px solid var(--c-marron-clair);">
            <div class="modal-header" style="background-color: var(--c-beige); border-bottom: 2px solid var(--c-marron-clair);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" style="font-weight: bold; color: var(--c-marron-fonce);">
                    <i class="glyphicon glyphicon-music"></i> Chansons utilisant <span id="modalStrumNom"></span>
                </h4>
            </div>
            <div class="modal-body" id="modalChansonsBody" style="max-height: 400px; overflow-y: auto; padding: 0;">
                <!-- Contenu chargé via AJAX -->
            </div>
            <div class="modal-footer" style="background-color: var(--c-beige); border-top: 1px solid var(--c-marron-clair);">
                <button type="button" class="btn btn-dj btn-dj-default" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- INCLUSION DU JS POUR LES STRUMS -->
<script src="../../js/strum_liste.js?v=2026-04-05"></script>
HTML;

$html .= envoieFooter();

echo $html;
