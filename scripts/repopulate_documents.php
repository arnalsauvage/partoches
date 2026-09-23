<?php
require_once __DIR__ . '/../src/public/php/lib/configMysql.php';

$db = $_SESSION['mysql'];
$dir = __DIR__ . '/../src/public/data/chansons';

if (!is_dir($dir)) {
    echo "Dossier chansons introuvable.\n";
    exit;
}

$count = 0;
$folders = glob($dir . '/*', GLOB_ONLYDIR);

foreach ($folders as $folder) {
    $idChanson = (int)basename($folder);
    if ($idChanson <= 0) continue;

    $pdfs = glob($folder . '/*.pdf');
    foreach ($pdfs as $pdf) {
        $filename = basename($pdf);
        $filesizeKo = (int)ceil(filesize($pdf) / 1024);

        // Vérifier si le document existe déjà
        $check = $db->query("SELECT id FROM document WHERE nomTable = 'chanson' AND idTable = $idChanson AND nom = '" . $db->real_escape_string($filename) . "'");
        if ($check && $check->num_rows > 0) {
            continue;
        }

        $sql = "INSERT INTO document (nom, tailleKo, date, version, nomTable, idTable, idUser, hits)
                VALUES ('" . $db->real_escape_string($filename) . "', $filesizeKo, CURDATE(), 1, 'chanson', $idChanson, 1, 0)";
        if ($db->query($sql)) {
            $count++;
        }
    }
}

echo "Documents synchronisés : $count nouveau(x) document(s) ajouté(s).\n";
