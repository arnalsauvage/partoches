<?php
/**
 * Formulaire de gestion d'une chanson (Django Style)
 * Contrôleur principal (Logique et aiguillage)
 */

const CHANSON = "chanson";
const RETOUR_RACINE = "../";
const CHEMIN_CHANSON_VOIR_PHP = "chanson_voir.php";
const CHANSON_POST_PHP = "chanson_post.php";
const CHANSON_CHERCHER = "chanson_chercher";
const CHANSON_UPLOAD = "chanson_upload.php";
const CHEMIN_LIEN_URL_POST_PHP = RETOUR_RACINE . "liens/lienurlPost.php";
const LIENS_LIEN_STRUM_CHANSON_POST_PHP = RETOUR_RACINE . "liens/lienStrumChanson_post.php";
const CHEMIN_SONGBOOK_FORM = RETOUR_RACINE . "/songbook/songbook_form.php";
const JS_CHANSON_FORM_JS = RETOUR_RACINE . RETOUR_RACINE . "js/chansonForm.js?v=" . 260308;

require_once "../../../autoload.php";
require_once '../lib/utilssi.php';
require_once "../utilisateur/Utilisateur.php";

// --- INITIALISATION ---
$table = CHANSON;
global $iconePoubelle, $cheminImages, $_DOSSIER_CHANSONS;

$listeSongbooks = Songbook::listeSongbooks();

// Sécurité : Droits d'édition requis
if ($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $url = $table . "_voir.php" . (isset($_GET['id']) ? "?id=" . (int)$_GET['id'] : "");
    redirection($url);
}

$_chanson = new Chanson();
$id = 0;

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $_chanson->chercheChanson($id);
    $mode = "MAJ";
} else {
    $mode = "INS";
    $_chanson->setIdUser($_SESSION['id']);
}

$titrePage = ($mode == "MAJ") ? "Mise à jour - " . $_chanson->getNom() : "Création chanson";

// --- GESTION DES MESSAGES (TOASTR) ---
$msgScript = "";
if (isset($_GET['msg'])) {
    $m = $_GET['msg'];
    $count = $_GET['count'] ?? 0;
    $msgScript = "<script>$(function() { ";
    switch($m) {
        case 'OK_REGEN': $msgScript .= "toastr.success('Vignettes régénérées avec succès ($count fichier(s) traité(s)).');"; break;
        case 'OK_UPLOAD': $msgScript .= "toastr.success('Fichier envoyé avec succès !');"; break;
    }
    $msgScript .= " });</script>";
}

// --- RENDU HTML ---
$headHtml = envoieHead($titrePage, "../../css/chansonform.css?v=" . date('His'));
$pasDeMenu = true;
require_once "../navigation/menu.php";

$htmlForm = ChansonFormRenderer::renderForm($_chanson, $mode);
$htmlRecherches = ChansonFormRenderer::renderExternalLinks($_chanson);

$titreH1 = ($mode == "MAJ") ? "Mise à jour - " . $table : "Création - " . $table;
$jsFile = JS_CHANSON_FORM_JS;

$html = <<<HTML
<div class="container sb-form-container" style="padding-top: 70px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">$titreH1</h1>
HTML;

if ($mode == "MAJ") {
    $urlVoir = "chanson_voir.php?id=$id";
    $html .= " <a href='$urlVoir' class='btn btn-sm btn-info'><i class='glyphicon glyphicon-eye-open'></i> VOIR LA FICHE PUBLIQUE</a>";    
}

$html .= <<<HTML
    </div>

    <div id="tabs">
        <ul>
            <li><a href="#tabs-1">Chanson</a></li>
            <li><a href="#tabs-2">Fichiers</a></li>
            <li><a href="#tabs-3">Strums</a></li>
            <li><a href="#tabs-4">Liens</a></li>
        </ul>

        <!-- ONGLET 1 : INFOS GÉNÉRALES -->
        <div id="tabs-1" class="col-lg-12 centrer">
            <div style="margin-bottom: 10px;">
                <a href="chanson_form_new.php?id=$id" class="btn btn-xs btn-default">Essayer la version expérimentale</a>
            </div>
            $htmlForm
            $htmlRecherches
        </div>

        <!-- ONGLET 2 : FICHIERS ET DOCUMENTS -->
        <div id="tabs-2" class="col-lg-12 centrer">
HTML;

if ($mode == "MAJ") {
    $html .= ChansonFormRenderer::renderFiles($id, $_DOSSIER_CHANSONS, $iconePoubelle, $cheminImages, $listeSongbooks, $_chanson);
    $html .= ChansonFormRenderer::renderTrash($id, $_DOSSIER_CHANSONS, $iconePoubelle, $cheminImages, $_chanson);
} else {
    $html .= "<div class='alert alert-info'>Enregistrez d'abord la chanson pour pouvoir y ajouter des fichiers.</div>";
}

$html .= <<<HTML
        </div>

        <!-- ONGLET 3 : RYTHMIQUES (STRUMS) -->
        <div id="tabs-3" class="col-lg-12 centrer">
HTML;
$html .= ChansonFormRenderer::renderStrums($_chanson);
$html .= <<<HTML
        </div>

        <!-- ONGLET 4 : LIENS EXTERNES -->
        <div id="tabs-4" class="col-lg-12 centrer">
HTML;
$html .= ChansonFormRenderer::renderLinks($id);
$html .= <<<HTML
        </div>
    </div> <!-- Fin #tabs -->
</div> <!-- Fin .container -->

<!-- INCLUSION DU JAVASCRIPT CENTRALISÉ -->
<script src="$jsFile"></script>
HTML;

$html .= envoieFooter();

// --- AFFICHAGE FINAL ---
echo $headHtml;
echo $MENU_HTML;
echo $msgScript;
echo $html;
