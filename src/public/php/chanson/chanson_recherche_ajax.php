<?php
// Inclusion de l'autoloader (Django Style)
require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';

header('Content-Type: application/json');

if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
    echo json_encode(['error' => 'Accès refusé']);
    exit();
}

$query = trim($_GET['q'] ?? '');
if (mb_strlen($query) < 4) {
    echo json_encode([]);
    exit();
}

$db = $_SESSION['mysql'] ?? ($GLOBALS['mysqli'] ?? null);
if (!$db) {
    echo json_encode(['error' => 'Base de données indisponible']);
    exit();
}

$searchTerm = '%' . $query . '%';

// Requête préparée sécurisée (Constitution GEMINI.MD 3.3 & 5)
$stmt = $db->prepare("SELECT id, nom, interprete, tonalite FROM chanson WHERE nom LIKE ? OR interprete LIKE ? ORDER BY nom ASC LIMIT 20");
if (!$stmt) {
    echo json_encode(['error' => 'Erreur de préparation SQL']);
    exit();
}

$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$chansons = [];
while ($row = $result->fetch_assoc()) {
    $chansons[] = [
        'id' => (int)$row['id'],
        'nom' => $row['nom'],
        'interprete' => $row['interprete'] ?? '',
        'tonalite' => $row['tonalite'] ?? ''
    ];
}
$stmt->close();

echo json_encode($chansons);
