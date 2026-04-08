<?php
require_once __DIR__ . '/src/public/php/lib/configMysql.php';

$query = "SELECT Strum, COUNT(*) as nb, GROUP_CONCAT(id) as ids 
          FROM strum 
          GROUP BY Strum 
          HAVING nb > 1 
          ORDER BY nb DESC";

$result = $_SESSION['mysql']->query($query);

echo "--- STRUMS EN DOUBLONS ---\n";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Rythme : [" . $row['Strum'] . "] - Apparaît " . $row['nb'] . " fois (IDs: " . $row['ids'] . ")\n";
    }
} else {
    echo "Aucun doublon trouvé ! La base est propre comme un sou neuf. ✨\n";
}
