<?php
/**
 * CONTROLEUR : audit_liens.php
 * Interface de gestion et de vérification des liens (URLs) du site.
 */

require_once dirname(__DIR__) . "/lib/configMysql.php";
require_once dirname(__DIR__) . "/lib/LienAuditService.php";

// 1. SECURITE
$privAdmin = $GLOBALS["PRIVILEGE_ADMIN"] ?? 3;
if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $privAdmin) {
    header("Location: ../navigation/login.php");
    exit();
}

// Initialisation du service avec l'objet PDO (On convertit le mysqli global en PDO si besoin, 
// ou on utilise une connexion PDO propre. Le projet semble utiliser mysqli globalement, 
// mais mon service a été écrit pour PDO. Je vais l'adapter pour utiliser mysqli pour la cohérence.)

global $mysqli;
$auditService = new LienAuditService($mysqli);

// 2. ACTIONS AJAX
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    switch ($_POST['action']) {
        case 'check_url':
            $url = $_POST['url'] ?? '';
            $code = $auditService->checkUrl($url);
            echo json_encode(['success' => true, 'code' => $code]);
            break;
    }
    exit;
}

// 3. LOGIQUE METIER
$liens = $auditService->getUrlsToAudit();

// 4. RENDU
require_once "../lib/html.php";
$headHtml = envoieHead("Audit des Liens", "../../css/params.css");
require_once "../navigation/menu.php";

echo $headHtml;
echo $MENU_HTML;
include "audit_liens.phtml";
echo envoieFooter();
