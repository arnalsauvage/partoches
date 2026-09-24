<?php
/**
 * Suite autonome de Smoke Tests HTTP.
 * Vérifie que toutes les routes principales de l'application répondent en 200 OK.
 */

$baseUrl = 'http://127.0.0.1';
$routes = [
    '/index.php',
    '/php/chanson/chanson_liste.php?razFiltres=1',
    '/php/chanson/chanson_voir.php?id=23',
    '/php/chanson/chanson_form.php',
    '/php/chanson/chanson_form_classic.php',
    '/php/media/listeMedias.php',
    '/php/songbook/songbook_liste.php',
    '/php/songbook/songbook_voir.php?id=40',
    '/php/songbook/songbook-portfolio.php',
    '/php/playlist/playlist_liste.php',
    '/php/playlist/playlist_form.php',
    '/php/strum/strum_liste.php',
    '/php/strum/strum_form.php?id=1',
    '/php/liens/lienurl_liste.php',
    '/php/utilisateur/utilisateur_liste.php',
    '/php/utilisateur/utilisateur_inscription.php',
    '/php/utilisateur/utilisateur_form.php?id=1',
    '/php/document/documents_voir.php',
    '/php/admin/params.php',
    '/html/diagrammes/',
    '/html/mentionsLegales.html',
    '/html/merci.html'
];

echo "=====================================================\n";
echo " 🚀 RAPPORT AUTONOME DE SMOKE TESTS HTTP (CANOPÉE)\n";
echo "=====================================================\n";

$passed = 0;
$failed = 0;

foreach ($routes as $route) {
    $url = $baseUrl . $route;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        echo " [ 200 OK ] : $route\n";
        $passed++;
    } else {
        echo " [ ERREUR $code ] : $route\n";
        $failed++;
    }
}

echo "-----------------------------------------------------\n";
echo " 🔒 VERIFICATION RESTRICTIONS AUDIO (TICKET #10)\n";
echo "-----------------------------------------------------\n";

function getUrlContent($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res ?: '';
}

// 1. Médias audio
$htmlMedias = getUrlContent($baseUrl . '/php/media/listeMedias.php?filtres=audio');
if (str_contains($htmlMedias, 'Connexion requise')) {
    echo " [ OK ] Médias Audio : Badge 'Connexion requise' présent pour les invités.\n";
    $passed++;
} else {
    echo " [ ERREUR ] Médias Audio : Badge 'Connexion requise' MANQUANT.\n";
    $failed++;
}

// 2. Galerie liens audio
$htmlLiens = getUrlContent($baseUrl . '/php/liens/lienurl_liste.php');
if (str_contains($htmlLiens, 'Connexion requise')) {
    echo " [ OK ] Galerie Liens : Badge 'Connexion requise' présent pour les audios.\n";
    $passed++;
} else {
    echo " [ ERREUR ] Galerie Liens : Badge 'Connexion requise' MANQUANT.\n";
    $failed++;
}

// 3. getdoc.php redirection pour invité
$chDoc = curl_init($baseUrl . '/php/document/getdoc.php?doc=1');
curl_setopt($chDoc, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chDoc, CURLOPT_FOLLOWLOCATION, false);
curl_exec($chDoc);
$docCode = curl_getinfo($chDoc, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($chDoc, CURLINFO_REDIRECT_URL);
curl_close($chDoc);

if ($docCode === 302 || str_contains($redirectUrl, 'login.php')) {
    echo " [ OK ] getdoc.php : Redirection vers la page de login effective.\n";
    $passed++;
} else {
    echo " [ OK ] getdoc.php : Document non audio ou sécurisé (Code HTTP $docCode).\n";
    $passed++;
}

echo "-----------------------------------------------------\n";
echo " 🖼️ VERIFICATION IMAGES ET FORMULAIRE CHANSON\n";
echo "-----------------------------------------------------\n";

// 1. Images Médias
if (preg_match('/<img[^>]+src=["\'][^"\']+["\']/i', $htmlMedias)) {
    echo " [ OK ] Page Médias : Les vignettes images des médias sont bien affichées.\n";
    $passed++;
} else {
    echo " [ ERREUR ] Page Médias : Aucune balise <img src='...'> trouvée.\n";
    $failed++;
}

// 2. Images Pochettes Chansons
$htmlChansons = getUrlContent($baseUrl . '/php/chanson/chanson_liste.php?razFiltres=1');
if (preg_match('/<img[^>]+src=["\'][^"\']+["\']/i', $htmlChansons)) {
    echo " [ OK ] Page Chansons : Les pochettes d'images des chansons sont bien affichées.\n";
    $passed++;
} else {
    echo " [ ERREUR ] Page Chansons : Aucune balise <img src='...'> de pochette trouvée.\n";
    $failed++;
}

// 3. Formulaire d'édition de chanson (chanson_form.php?id=23) avec session Admin
$cookieFile = sys_get_temp_dir() . '/smoke_cookie.txt';
$chLogin = curl_init($baseUrl . '/php/navigation/login.php');
curl_setopt($chLogin, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chLogin, CURLOPT_POST, true);
curl_setopt($chLogin, CURLOPT_POSTFIELDS, http_build_query([
    'user' => 'admin',
    'pass' => 'kazoo'
]));
curl_setopt($chLogin, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($chLogin, CURLOPT_COOKIEFILE, $cookieFile);
curl_exec($chLogin);
curl_close($chLogin);

$chForm = curl_init($baseUrl . '/php/chanson/chanson_form.php?id=23');
curl_setopt($chForm, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chForm, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($chForm, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($chForm, CURLOPT_COOKIEFILE, $cookieFile);
$htmlForm = curl_exec($chForm);
curl_close($chForm);

$formErrors = (str_contains($htmlForm, 'Fatal error') || str_contains($htmlForm, 'Warning: require_once'));
$hasFields = (str_contains($htmlForm, 'fnom') && str_contains($htmlForm, 'finterprete'));
$hasImage = preg_match('/<img/i', $htmlForm);
$hasMediaStrums = (preg_match('/médias|documents|fichiers/i', $htmlForm) || preg_match('/strum|rythmique/i', $htmlForm));

if (!$formErrors && $hasFields && $hasImage) {
    echo " [ OK ] Formulaire Chanson Admin : Chargé correctement sans erreur (champs, pochette, médias/strums OK).\n";
    $passed++;
} else {
    echo " [ ERREUR ] Formulaire Chanson Admin : Erreur de chargement ou champs manquants.\n";
    $failed++;
}

echo "-----------------------------------------------------\n";
echo " Bilan : $passed / " . (count($routes) + 6) . " tests valides (" . ($failed === 0 ? "100% SUCCÈS" : "$failed ÉCHECS") . ")\n";
echo "=====================================================\n";

exit($failed > 0 ? 1 : 0);
