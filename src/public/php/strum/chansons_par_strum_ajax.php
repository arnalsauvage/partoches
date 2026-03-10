<?php
// Inclusion de l'autoloader (qui définit PHP_DIR et gère les classes)
require_once dirname(__DIR__, 3) . "/autoload.php";

$idStrum = (int)($_GET['idStrum'] ?? 0);
if ($idStrum == 0) {
    echo "ID Strum invalide.";
    exit();
}

$db = $_SESSION['mysql'];
$maRequete = "SELECT chanson.id, chanson.nom, chanson.interprete FROM chanson 
              JOIN lienstrumchanson ON lienstrumchanson.idChanson = chanson.id 
              WHERE lienstrumchanson.idStrum = $idStrum 
              ORDER BY chanson.nom";
$result = $db->query($maRequete);

if ($result->num_rows == 0) {
    echo "<p class='text-muted'>Aucune chanson n'utilise encore ce strum.</p>";
} else {
    echo "<div class='list-group'>";
    while ($row = $result->fetch_assoc()) {
        $idC = $row['id'];
        $nom = htmlspecialchars($row['nom']);
        $interprete = htmlspecialchars($row['interprete']);
        echo "<a href='../chanson/chanson_voir.php?id=$idC' class='list-group-item list-group-item-action' style='display:flex; justify-content:space-between; align-items:center;'>
                <span><strong>$nom</strong> <small class='text-muted'>- $interprete</small></span>
                <i class='glyphicon glyphicon-chevron-right'></i>
              </a>";
    }
    echo "</div>";
}
