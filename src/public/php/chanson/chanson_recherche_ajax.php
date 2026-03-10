<?php
// Inclusion de l'autoloader (Django Style)
require_once dirname(__DIR__, 3) . "/autoload.php";

header('Content-Type: application/json');

if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
    echo json_encode(['error' => 'Accès refusé']);
    exit();
}

$query = $_GET['q'] ?? '';
if (strlen($query) < 4) {
    echo json_encode([]);
    exit();
}

$db = $_SESSION['mysql'];
$qEscaped = $db->real_escape_string($query);

$maRequete = "SELECT id, nom, interprete FROM chanson WHERE nom LIKE '%$qEscaped%' ORDER BY nom LIMIT 10";
$result = $db->query($maRequete);

$chansons = [];
while ($row = $result->fetch_assoc()) {
    $chansons[] = [
        'id' => $row['id'],
        'nom' => $row['nom'],
        'interprete' => $row['interprete']
    ];
}

echo json_encode($chansons);
