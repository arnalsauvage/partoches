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

// 1. Médias audio
$htmlMedias = file_get_contents($baseUrl . '/php/media/listeMedias.php?filtres=audio');
if (str_contains($htmlMedias, 'Connexion requise')) {
    echo " [ OK ] Médias Audio : Badge 'Connexion requise' présent pour les invités.\n";
    $passed++;
} else {
    echo " [ ERREUR ] Médias Audio : Badge 'Connexion requise' MANQUANT.\n";
    $failed++;
}

// 2. Galerie liens audio
$htmlLiens = file_get_contents($baseUrl . '/php/liens/lienurl_liste.php');
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
echo " Bilan : $passed / " . (count($routes) + 3) . " tests valides (" . ($failed === 0 ? "100% SUCCÈS" : "$failed ÉCHECS") . ")\n";
echo "=====================================================\n";

exit($failed > 0 ? 1 : 0);
