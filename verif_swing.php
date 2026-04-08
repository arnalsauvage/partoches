<?php
require_once __DIR__ . '/src/public/php/lib/configMysql.php';
$res = $_SESSION['mysql']->query('SELECT id, strum, swing FROM strum WHERE swing = 1 OR strum LIKE "%B-BHB-B%"');
echo "--- STRUMS SWING EN BDD ---\n";
while($r=$res->fetch_assoc()) {
    echo "ID: " . $r['id'] . " | Rythme: [" . $r['strum'] . "] | Swing: " . $r['swing'] . "\n";
}
