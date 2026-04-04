<?php
/**
 * CONTROLEUR : params.php (Anciennement paramsEdit.php)
 * Gere l'administration du site.
 */

require_once dirname(__DIR__) . "/lib/configMysql.php";
require_once "AdminService.php";

// 1. SECURITE (Test direct sur la session avec les vraies valeurs du projet)
$privAdmin = $GLOBALS["PRIVILEGE_ADMIN"] ?? 3; // L'admin est au niveau 3 dans ce projet

if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $privAdmin) {
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
            // Pour le diagnostic, on garde un peu de logique ici ou on injecte une vue partielle
            // On va appeler le service
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
    }
    exit;
}

// 3. LOGIQUE METIER (Chargement des donnees)
$fichierIni = __DIR__ . "/../../../data/conf/params.ini";
$ini_objet = new FichierIni();
$ini_objet->m_load_fichier($fichierIni);
$alerts = "";

// Sauvegarde si POST normal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... logique de sauvegarde ... (On simplifie pour l'instant en reprenant l'ancienne)
    $bModif = false;
    $footer = new Footer();
    // On boucle sur les champs (Logique identique a l'ancienne pour la compatibilite)
    $allKeys = ["loginParam", "urlSite", "EmailAdmin", "titreSite", "sousTitreSite", "mailOubliMotDePasse", "nomEmailOubliMotDePasse", "largeurMaxImageChanson", "hauteurMaxImageChanson", "cleGetSongBpm", "GEMINI_API_KEY", "MAMMOUTH_API_KEY", "monServeur", "maBase", "login", "motDePasse", "display_errors", "log_level"];
    foreach ($allKeys as $key) {
        if (isset($_POST[$key])) {
            $group = (in_array($key, ["monServeur", "maBase", "login", "motDePasse"])) ? "mysql" : (in_array($key, ["display_errors", "log_level"]) ? "admin" : "general");
            $ini_objet->m_put($_POST[$key], $key, $group);
            $bModif = true;
        }
    }
    if (isset($_POST['footerHtml'])) {
        $footerHtmlRaw = strip_tags($_POST['footerHtml'], '<a><br><img><strong><em><p>');
        $ini_objet->m_put($footerHtmlRaw, 'footerHtml', 'footer');
        $footer->setHtml($footerHtmlRaw);
        $bModif = true;
    }
    if ($bModif) { $footer->sauveBdd(); $ini_objet->save(); $alerts = "<div class='alert alert-success'>Enregistre !</div>"; }
}

// Preparation des variables pour la vue
$logoActuel = $ini_objet->m_valeur('logoSite', 'general');
$footer = new Footer();
$footerHtml = htmlspecialchars($footer->getHtml());

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
foreach (glob(__DIR__ . "/../../../data/logs/*.{txt,htm,log,html}", GLOB_BRACE) as $l) {
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
