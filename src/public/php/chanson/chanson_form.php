<?php
/**
 * Formulaire Chanson (Wrapper vers chanson_form_new.php)
 */

require_once dirname(__DIR__) . "/lib/utilssi.php";
$pasDeMenu = true;
require_once "../navigation/menu.php";

$mode = isset($_GET['id']) ? "MAJ" : "INS";

// --- RENDU HTML ---
$headHtml = envoieHead("Chanson - " . $mode, "../../css/chansonform.css");
echo $headHtml;
echo $MENU_HTML;

// Rediriger vers le nouveau formulaire moderne
require_once "chanson_form_new.php";
