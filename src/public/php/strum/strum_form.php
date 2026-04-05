<?php
/**
 * Formulaire de création/édition d'un Strum (Django Style)
 */

// Inclusion de l'autoloader
require_once dirname(__DIR__, 3) . "/autoload.php";

// 1. SÉCURITÉ
if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_MEMBRE"] ?? 1)) {
    header("Location: strum_liste.php");
    exit();
}

// 2. CHARGEMENT DU STRUM
$id = (int)($_GET['id'] ?? 0);
$strumObj = new Strum($id);
$mode = ($id > 0) ? "MAJ" : "INS";

// Pour aider la saisie, on remplace les espaces par des tirets
$strumPattern = str_replace(" ", "-", $strumObj->getStrum());
$description = $strumObj->getDescription();
$unite = $strumObj->getUnite();
$longueur = $strumObj->getLongueur();
$swing = $strumObj->getSwing();

// Options pour l'unité
$optUnite4 = ($unite == 4) ? 'selected' : '';
$optUnite8 = ($unite == 8) ? 'selected' : '';
$optUnite16 = ($unite == 16) ? 'selected' : '';

// Checkbox Swing
$swingChecked = ($swing == 1) ? 'checked' : '';

// Préparation des variables pour le rendu
$pageTitle = ($mode === 'MAJ') ? 'Modifier le Strum' : 'Nouveau Strum';
$descAffiche = htmlspecialchars($description);

// --- RENDU HTML ---
$headHtml = envoieHead($pageTitle, "../../css/strum_form.css");
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";

echo $headHtml;
echo $MENU_HTML;

$html = <<<HTML
<div class="container strum-form-container">
    <div class="row">
        <div class="col-xs-12">
            
            <header class="strum-form-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h1 style="margin:0;">
                    <i class="glyphicon glyphicon-music"></i>
                    $pageTitle
                </h1>
                <button id="btnAide" class="btn btn-link" title="Aide au formatage" style="color: #D2B48C; font-size: 24px; padding: 0;">
                    <i class="glyphicon glyphicon-question-sign"></i>
                </button>
            </header>

            <!-- ZONE D'AIDE (MASQUÉE PAR DÉFAUT) -->
            ... [rest of aideBox code] ...

            <!-- FORMULAIRE -->
            <div id="editionStrum">
                <input id="id" type="hidden" value="$id">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="strum">Motif de la rythmique (Strum) :</label>
                            <input id="strum" type="text" class="form-control input-lg" style="font-family: monospace; letter-spacing: 2px;" placeholder="Ex: B-BH-HBH" value="$strumPattern">
                            
                            <!-- SWING OPTION INTEGRATED -->
                            <div class="checkbox" style="margin-top: 10px;">
                                <label style="font-weight:bold; color:#e67e22; cursor:pointer; font-size: 15px; display: flex; align-items: center; padding-left: 0;">
                                    <input type="checkbox" id="swing" $swingChecked style="position: relative; margin: 0 12px 0 0; width: 20px; height: 20px;"> 🎷 CE RYTHME EST "SWING" (TERNAIRE)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="unite">Division du temps (Unité) :</label>
                            <select id="unite" class="form-control">
                                <option value="4" $optUnite4>Noires (4)</option>
                                <option value="8" $optUnite8>Croches (8)</option>
                                <option value="16" $optUnite16>Double-croches (16)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="longueur">Nombre de divisions (Longueur) :</label>
                            <input id="longueur" type="number" class="form-control" value="$longueur" min="1" max="64">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="description">Description ou Nom :</label>
                            <input id="description" type="text" class="form-control" placeholder="Ex: Feu de camp, Reggae, Valse..." value="$descAffiche">
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 30px; text-align: center;">
                    <button class="btn btn-default strum-btn-action" onclick="window.history.back();" style="margin-right: 15px;">
                        <i class="glyphicon glyphicon-arrow-left"></i> Retour
                    </button>
HTML;

if ($mode == "INS") {
    $html .= '<button class="btn btn-success strum-btn-action" name="creer" style="background-color: #2e7d32; border:none;"><i class="glyphicon glyphicon-plus-sign"></i> Créer</button>';
} else {
    $html .= '<button class="btn btn-warning strum-btn-action" name="modifier" style="background-color: #e67e22; border:none;"><i class="glyphicon glyphicon-save"></i> Enregistrer</button>';
}

$html .= <<<HTML
                </div>
            </div>

            <div id="retour" style="display:none; margin-top: 20px;" class="alert alert-danger"></div>

        </div>
    </div>
</div>

<script src="../../js/strum_form.js"></script>
HTML;

$html .= envoieFooter();

echo $html;
