<?php
require_once "php/lib/configMysql.php";
session_start();
$_SESSION['mysql'] = $conn; // Simulation de la session pour les scripts qui en dépendent

$sql = "ALTER TABLE chanson ADD COLUMN publication TINYINT(1) DEFAULT 1 AFTER cover";
if ($conn->query($sql) === TRUE) {
    echo "Colonne 'publication' ajoutée avec succès !";
} else {
    echo "Erreur lors de l'ajout de la colonne : " . $conn->error;
}
$conn->close();
?>
