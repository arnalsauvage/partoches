<?php
require_once __DIR__ . '/src/public/php/lib/configMysql.php';
$res = $_SESSION['mysql']->query('SELECT nom FROM chanson LIMIT 30');
while($r=$res->fetch_row()) echo $r[0]."\n";
