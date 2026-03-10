<?php
require_once "../lib/configMysql.php";

$db = $_SESSION['mysql'];

echo "--- DÉBUT DE LA MIGRATION STRUM ID ---<br>";

// 1. Ajout de la colonne idStrum si elle n'existe pas
echo "Vérification de la colonne idStrum... ";
$checkCol = $db->query("SHOW COLUMNS FROM lienstrumchanson LIKE 'idStrum'");
if ($checkCol->num_rows == 0) {
    $db->query("ALTER TABLE lienstrumchanson ADD COLUMN idStrum INT(11) AFTER idChanson");
    echo "Colonne créée !<br>";
} else {
    echo "Déjà présente.<br>";
}

// 2. Migration des données
echo "Migration des liens... ";
$res = $db->query("SELECT * FROM strum");
$count = 0;
while ($strum = $res->fetch_assoc()) {
    $id = $strum['id'];
    $chaine = $db->real_escape_string($strum['strum']);
    
    // On met à jour tous les liens qui matchent cette chaîne
    $update = "UPDATE lienstrumchanson SET idStrum = $id WHERE BINARY strum = '$chaine'";
    $db->query($update);
    $count += $db->affected_rows;
}
echo "$count liens mis à jour avec l'ID !<br>";

echo "--- MIGRATION TERMINÉE ---<br>";
echo "<a href='strum_liste.php'>Retour à la liste</a>";
