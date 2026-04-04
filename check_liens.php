<?php
require_once 'src/autoload.php';
$db = $_SESSION['mysql'];

$res = $db->query("SELECT * FROM lienchansonplaylist");
echo "Nombre de lignes : " . $res->num_rows . "\n";
while($row = $res->fetch_assoc()) {
    print_r($row);
}
