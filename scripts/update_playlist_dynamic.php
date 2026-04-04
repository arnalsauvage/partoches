<?php
require_once __DIR__ . "/../src/public/php/lib/utilssi.php";
require_once __DIR__ . "/../src/public/php/lib/configMysql.php";

$db = $_SESSION['mysql'];

echo "--- Mise à jour de la table playlist pour le mode Dynamique ---\n";

// 1. Ajout de la colonne 'type' (0 = Manuel, 1 = Dynamique)
$sqlCheckType = "SHOW COLUMNS FROM playlist LIKE 'type'";
$resType = $db->query($sqlCheckType);
if ($resType->num_rows == 0) {
    $db->query("ALTER TABLE playlist ADD COLUMN type INT DEFAULT 0 AFTER idUser");
    echo "✅ Colonne 'type' ajoutée.\n";
} else {
    echo "ℹ️ Colonne 'type' déjà présente.\n";
}

// 2. Ajout de la colonne 'criteres' (pour stocker les filtres JSON)
$sqlCheckCriteres = "SHOW COLUMNS FROM playlist LIKE 'criteres'";
$resCriteres = $db->query($sqlCheckCriteres);
if ($resCriteres->num_rows == 0) {
    $db->query("ALTER TABLE playlist ADD COLUMN criteres TEXT AFTER type");
    echo "✅ Colonne 'criteres' ajoutée.\n";
} else {
    echo "ℹ️ Colonne 'criteres' déjà présente.\n";
}

echo "--- Opération terminée avec succès ! 🎸 ---\n";
?>