<?php
require_once 'src/autoload.php';
$res = $_SESSION['mysql']->query('SHOW TABLES');
while ($row = $res->fetch_row()) {
    echo $row[0] . PHP_EOL;
}
