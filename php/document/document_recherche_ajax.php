<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/document/document.php";

header('Content-Type: application/json');

if (($_SESSION['privilege'] ?? 0) < ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
    echo json_encode(['error' => 'Accès refusé']);
    exit();
}

$chansonId = $_GET['chansonId'] ?? 0;
if ($chansonId == 0) {
    echo json_encode([]);
    exit();
}

$db = $_SESSION['mysql'];
$maRequete = "SELECT id, nom, version FROM document WHERE nomTable = 'chanson' AND idTable = '$chansonId' AND nom LIKE '%.pdf' ORDER BY version DESC";
$result = $db->query($maRequete);

$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = [
        'id' => $row['id'],
        'nom' => $row['nom'],
        'nomVersion' => composeNomVersion($row['nom'], $row['version']),
        'version' => $row['version']
    ];
}

echo json_encode($documents);
