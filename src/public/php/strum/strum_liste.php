<?php
// Inclusion de l'autoloader (Django Style)
require_once dirname(__DIR__, 3) . "/autoload.php";

require_once PHP_DIR . "/navigation/menu.php";

// Palette Canopée
$c_marron_fonce = "#2b1d1a";
$c_marron_clair = "#D2B48C"; 
$c_accent = "#8B4513";
$c_ivoire = "#fcfaf2";
$c_orange = "#e67e22";

// Gestion du tri
$tri = $_GET['tri'] ?? 'pop'; 
$strums = Strum::chargeStrumsBdd($tri);
$nbStrums = count($strums);

// Classes actives pour les boutons
$activePop = ($tri == 'pop') ? 'active' : '';
$activeNom = ($tri == 'nom') ? 'active' : '';
$activeDate = ($tri == 'date') ? 'active' : '';

// --- RENDU ---

$html = <<<HTML
<!-- CSS Spécifique -->
<link rel="stylesheet" href="../../css/strum_liste.css">
<style>
    /* Fix pour éviter que le backdrop ne passe devant la modale */
    .modal { z-index: 2050 !important; }
    .modal-backdrop { z-index: 2040 !important; }
</style>

<div class="container strum-container">
    
    <div class="row">
        <div class="col-xs-12 text-center" style="margin-bottom: 20px;">
            <h1 style="color: $c_marron_fonce; font-weight: 900; font-size: 42px; text-transform: uppercase; letter-spacing: 5px; margin-bottom: 10px;">
                <span class="glyphicon glyphicon-option-vertical"></span> STRUMS
            </h1>
            <p style="color: $c_marron_clair; font-size: 14px; letter-spacing: 3px; font-weight: bold;">DICTIONNAIRE DE $nbStrums RYTHMIQUES</p>
            <div style="width: 80px; height: 4px; background: $c_orange; margin: 15px auto; border-radius: 2px;"></div>
        </div>
    </div>

    <!-- CONSOLE DE TRI -->
    <div class="row" style="margin-bottom: 30px;">
        <div class="col-xs-12 text-center">
            <div class="btn-group" role="group">
                <a href="?tri=pop" class="btn btn-default $activePop" style="border-radius: 20px 0 0 20px; font-weight: bold; color: $c_marron_fonce;">
                    <i class="glyphicon glyphicon-star"></i> LES PLUS JOUÉS
                </a>
                <a href="?tri=nom" class="btn btn-default $activeNom" style="font-weight: bold; color: $c_marron_fonce;">
                    <i class="glyphicon glyphicon-sort-by-alphabet"></i> ALPHABÉTIQUE
                </a>
                <a href="?tri=date" class="btn btn-default $activeDate" style="border-radius: 0 20px 20px 0; font-weight: bold; color: $c_marron_fonce;">
                    <i class="glyphicon glyphicon-time"></i> RÉCENTS
                </a>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="strum_form.php" class="btn btn-primary" style="background-color: $c_accent; border: none; font-weight: bold; padding: 10px 25px; border-radius: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                    <i class="glyphicon glyphicon-plus-sign"></i> CRÉER UNE RYTHMIQUE
                </a>
            </div>
        </div>
    </div>

    <div class="row" id="strumGrid">
HTML;

foreach ($strums as $s) {
    $html .= $s->afficheCarteStrum();
}

$html .= "</div></div>"; // Fin Grid et Container

// --- MODALES ET SCRIPTS (EN DEHORS DU CONTAINER PRINCIPAL) ---
$html .= <<<HTML
<div class="modal fade" id="modalChansonsStrum" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
            <div class="modal-header" style="background: $c_marron_fonce; color: white; border: none;">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; font-size: 30px;">&times;</button>
                <h4 class="modal-title" style="font-weight: bold; letter-spacing: 1px;">
                    <i class="glyphicon glyphicon-music"></i> CHANSONS UTILISANT <span id="modalStrumNom"></span>
                </h4>
            </div>
            <div class="modal-body" id="modalChansonsBody" style="background: $c_ivoire; padding: 20px; max-height: 450px; overflow-y: auto;">
                <div class="text-center"><i class="glyphicon glyphicon-refresh spin"></i> Chargement...</div>
            </div>
            <div class="modal-footer" style="background: #eee; border: none;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold; border-radius: 20px; padding: 8px 25px;">FERMER</button>
            </div>
        </div>
    </div>
</div>

<script src="../../js/strum_liste.js"></script>
HTML;

echo $html;
echo envoieFooter();
