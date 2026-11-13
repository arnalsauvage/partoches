<?php
/**
 * API Endpoint : Publication de Chanson — Partoches Canopée
 * 
 * Déposé dans : domains/partoches.canopee-musique.fr/public_html/api/publish_song.php
 * Conforme aux règles d'architecture Hostinger (REX-07) :
 * - Zéro session_start()
 * - Authentification Bearer token
 * - Écriture du fichier .crd versionné dans data/chansons/{id}/
 * - Mise à jour tables chanson & document
 */

header('Content-Type: application/json; charset=utf-8');

// Configuration du Token Secret
$SECRET_API_TOKEN = 'canopee_secret_2026';

// 1. Vérification méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée. POST requis.']);
    exit;
}

// 2. Vérification Token Bearer
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$token = '';
if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
    $token = $matches[1];
}

if ($token !== $SECRET_API_TOKEN) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentification invalide ou manquante.']);
    exit;
}

// 3. Lecture du payload JSON
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data || empty($data['title']) || empty($data['crd_content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Champs obligatoires manquants (title, crd_content).']);
    exit;
}

$title = trim($data['title']);
$artist = trim($data['artist'] ?? 'Inconnu');
$key = trim($data['key'] ?? 'C');
$crdContent = trim($data['crd_content']);

// 4. Chargement de l'environnement Canopée
require_once __DIR__ . '/../php/lib/configMysql.php';
require_once __DIR__ . '/../php/lib/utilssi.php';
if (file_exists(__DIR__ . '/../php/lib/html.php')) {
    require_once __DIR__ . '/../php/lib/html.php';
}
require_once __DIR__ . '/../php/chanson/Chanson.php';
require_once __DIR__ . '/../php/document/Document.php';

if (!function_exists('simplifieNomFichier')) {
    function simplifieNomFichier($nomOriginal) {
        $info = pathinfo($nomOriginal);
        $baseName = $info['filename'] ?? $nomOriginal;
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
        );
        $clean = strtr($baseName, $table);
        $clean = str_replace('#', 'diese', $clean);
        $clean = preg_replace('/[^a-zA-Z0-9]+/', '-', $clean);
        $clean = preg_replace('/-+/', '-', $clean);
        $clean = trim($clean, '-');
        return $clean !== '' ? $clean : 'chanson';
    }
}

global $mysqli;
if (!$mysqli && isset($_SESSION['mysql'])) {
    $mysqli = $_SESSION['mysql'];
}

if (!$mysqli) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Connexion MySQL indisponible.']);
    exit;
}

$_SESSION = $_SESSION ?? [];
$_SESSION['mysql'] = $mysqli;
$_SESSION['id'] = 1;

try {
    // Vérifier si la chanson existe déjà par son titre
    $nomEsc = $mysqli->real_escape_string($title);
    $artEsc = $mysqli->real_escape_string($artist);
    $sqlCheck = "SELECT id FROM chanson WHERE nom = '$nomEsc' AND (interprete = '$artEsc' OR interprete = '' OR '$artEsc' = 'Inconnu') LIMIT 1";
    $resCheck = $mysqli->query($sqlCheck);

    if ($resCheck && $row = $resCheck->fetch_assoc()) {
        $songId = (int)$row['id'];
        $chanson = Chanson::load($songId);
        $chanson->setTonalite($key);
        $chanson->setTonaliteOriginale($key);
        $chanson->setPublication(1);
        $chanson->save();
        $action = 'updated';
    } else {
        $chanson = new Chanson();
        $chanson->setNom($title);
        $chanson->setInterprete($artist);
        $chanson->setTonalite($key);
        $chanson->setTonaliteOriginale($key);
        $chanson->setIdUser(1);
        $chanson->setPublication(1);
        $songId = $chanson->save();
        $action = 'created';
    }

    // Gestion du dossier physique data/chansons/{id}/
    $dossierChanson = __DIR__ . '/../data/chansons/' . $songId . '/';
    if (!file_exists($dossierChanson)) {
        mkdir($dossierChanson, 0755, true);
    }

    $baseName = simplifieNomFichier($title);
    $crdFileName = $baseName . '-v1.crd';
    $crdFilePath = $dossierChanson . $crdFileName;

    file_put_contents($crdFilePath, $crdContent);
    chmod($crdFilePath, 0644);

    // Enregistrement dans la table document
    $docName = $baseName . '.crd';
    $docNameEsc = $mysqli->real_escape_string($docName);
    $tailleKo = max(1, (int)ceil(strlen($crdContent) / 1024));
    $dateJour = date("Y-m-d");

    $sqlDoc = "SELECT id, version FROM document WHERE nom = '$docNameEsc' AND nomTable = 'chanson' AND idTable = $songId LIMIT 1";
    $resDoc = $mysqli->query($sqlDoc);
    if ($resDoc && $docRow = $resDoc->fetch_assoc()) {
        $docId = (int)$docRow['id'];
        $v = ((int)($docRow['version'] ?? 1)) + 1;
        $mysqli->query("UPDATE document SET tailleKo = $tailleKo, date = '$dateJour', version = $v WHERE id = $docId");
    } else {
        $mysqli->query("INSERT INTO document (nom, tailleKo, date, version, nomTable, idTable, idUser, hits) VALUES ('$docNameEsc', $tailleKo, '$dateJour', 1, 'chanson', $songId, 1, 0)");
    }

    echo json_encode([
        'success' => true,
        'action' => $action,
        'song_id' => $songId,
        'title' => $title,
        'artist' => $artist,
        'url' => "https://partoches.canopee-musique.fr/php/chanson/chanson_voir.php?id=" . $songId,
        'message' => "Partition publiée avec succès sur Partoches Canopée !"
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
