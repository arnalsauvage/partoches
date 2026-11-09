<?php
/**
 * CONTROLEUR : params.php (Anciennement paramsEdit.php)
 * Gere l'administration du site.
 */

require_once dirname(__DIR__) . "/lib/configMysql.php";
require_once "AdminService.php";

// 1. SECURITE
if (!estAdmin()) {
    header("Location: ../navigation/login.php");
    exit();
}

global $mysqli; 
$adminService = new AdminService($mysqli);

// 2. ACTIONS AJAX
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'lecture_log':
            echo $adminService->getLogContent($_POST['fichier'] ?? '');
            break;
        case 'execute_sql':
            echo $adminService->executeRawSql($_POST['sql'] ?? '');
            break;
        case 'diagnostic_systeme':
            require_once __DIR__ . "/params_ajax.php";
            handleDiagnostic($adminService, $mysqli);
            break;
        case 'run_migrations':
            try {
                $count = $adminService->runPendingMigrations();
                echo "✅ $count migration(s) appliquee(s) avec succes !";
            } catch (Exception $e) {
                echo "❌ " . $e->getMessage();
            }
            break;
        case 'regenere_medias':
            MediaService::resetMediaTable(); 
            echo "✅ Catalogue regenere avec succes !";
            break;
        case 'regenere_vignettes_all':
            try {
                $res = $adminService->batchRegenerateThumbnails();
                echo "✅ {$res['total']} image(s) vérifiée(s). {$res['generated']} miniature(s) générée(s).";
            } catch (Exception $e) {
                echo "❌ " . $e->getMessage();
            }
            break;
        case 'export_db':
            $filePath = $adminService->exportDatabase();
            if ($filePath && file_exists($filePath)) {
                if (ob_get_level()) ob_end_clean();
                
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            } else {
                http_response_code(500);
                echo "❌ Erreur lors de l'export de la base de données. Verifiez les permissions du dossier data/backups et la presence de mysqldump.";
            }
            break;
    }
    exit;
}

// 3. LOGIQUE METIER (Chargement des donnees)
$fichierIni = class_exists('FichierIni') ? FichierIni::getParamsIniPath() : (defined('CONF_DIR') ? CONF_DIR . "/params.ini" : dirname(__DIR__, 2) . "/data/conf/params.ini");
$ini_objet = new FichierIni();
$ini_objet->m_load_fichier($fichierIni);
$alerts = "";

// Sauvegarde si POST normal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bModif = false;
    $footer = new Footer();
    $allKeys = ["loginParam", "urlSite", "EmailAdmin", "titreSite", "sousTitreSite", "mailOubliMotDePasse", "nomEmailOubliMotDePasse", "largeurMaxImageChanson", "hauteurMaxImageChanson", "cleGetSongBpm", "GEMINI_API_KEY", "MAMMOUTH_API_KEY", "monServeur", "maBase", "login", "motDePasse", "log_level"];
    foreach ($allKeys as $key) {
        if (isset($_POST[$key])) {
            $group = (in_array($key, ["monServeur", "maBase", "login", "motDePasse"])) ? "mysql" : (in_array($key, ["log_level"]) ? "admin" : "general");
            $ini_objet->m_put($_POST[$key], $key, $group);
            $bModif = true;
        }
    }

    // Gestion du switch display_errors (case à cocher)
    if (isset($_POST['display_errors_submitted'])) {
        $displayErrorsNew = (!empty($_POST['display_errors'])) ? "1" : "0";
        $ini_objet->m_put($displayErrorsNew, "display_errors", "admin");
        $bModif = true;
    }

    if (isset($_POST['footerHtml'])) {
        $footerHtmlRaw = strip_tags($_POST['footerHtml'], '<a><br><img><strong><em><p>');
        $ini_objet->m_put($footerHtmlRaw, 'footerHtml', 'footer');
        $footer->setHtml($footerHtmlRaw);
        $bModif = true;
    }
    if ($bModif) { 
        $footer->sauveBdd(); 
        $ini_objet->save(); 
        $alerts = "<div class='alert alert-success'><span class='glyphicon glyphicon-ok'></span> Configuration enregistrée avec succès !</div>"; 
    }
}

// Preparation des variables pour la vue
$logoActuel = $ini_objet->m_valeur('logoSite', 'general');
$footer = new Footer();
$footerHtml = htmlspecialchars($footer->getHtml());

$displayErrorsVal = $ini_objet->m_valeur('display_errors', 'admin');
$displayErrorsChecked = ($displayErrorsVal === "1" || $displayErrorsVal === 1) ? 'checked' : '';

function champInputView($ini, $name, $label, $type, $groupe) {
    $val = $ini->m_valeur($name, $groupe) ?? '';
    if (!empty($val) && !mb_check_encoding($val, 'UTF-8')) $val = mb_convert_encoding($val, 'UTF-8', 'ISO-8859-1');
    $val = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
    $isPwd = (str_contains(strtolower($name), 'key') || str_contains(strtolower($name), 'passe') || $name === 'cleGetSongBpm');
    
    $out = "<div class='form-group-django'><label class='label-django'>$label</label><div class='input-group-django'>";
    $out .= "<input type='" . ($isPwd ? "password" : $type) . "' class='input-django' name='$name' id='$name' value='$val'>";
    if ($isPwd) $out .= "<button type='button' class='btn-toggle-pwd' data-target='$name'><span class='glyphicon glyphicon-eye-open'></span></button>";
    $out .= "</div></div>";
    return $out;
}

$genFields1 = champInputView($ini_objet, "titreSite", "Nom du site", "text", "general") . champInputView($ini_objet, "sousTitreSite", "Slogan", "text", "general") . champInputView($ini_objet, "urlSite", "URL racine", "text", "general");
$genFields2 = champInputView($ini_objet, "EmailAdmin", "Email admin", "email", "general") . champInputView($ini_objet, "cleGetSongBpm", "Cle GetSongBpm", "text", "general") . champInputView($ini_objet, "GEMINI_API_KEY", "Cle Gemini", "text", "general") . champInputView($ini_objet, "MAMMOUTH_API_KEY", "Cle Mammouth", "text", "general");
$mysqlFields = champInputView($ini_objet, "monServeur", "Serveur MySQL", "text", "mysql") . champInputView($ini_objet, "maBase", "Base MySQL", "text", "mysql") . champInputView($ini_objet, "login", "Login MySQL", "text", "mysql") . champInputView($ini_objet, "motDePasse", "Mot de passe MySQL", "password", "mysql");

$logLinks = "";
$logsPattern = (defined('DATA_DIR') ? DATA_DIR : dirname(__DIR__, 2) . '/data') . "/logs/*.{txt,htm,log,html}";
foreach (glob($logsPattern, GLOB_BRACE) as $l) {
    $b = basename($l); 
    $logLinks .= "<a href='#' class='list-group-item item-log-dj' data-file='$b'>$b</a>";
}

// 4. RENDU
$headHtml = envoieHead("Parametrage", "../../css/params.css");
$pasDeMenu = true;
require_once "../navigation/menu.php";

echo $headHtml;
echo $MENU_HTML;
include "params_view.phtml";
echo envoieFooter();
