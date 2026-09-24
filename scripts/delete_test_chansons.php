<?php
/**
 * Script CLI / Web : Suppression des chansons de test (ID > 764)
 */
$hosts = [getenv('DATABASE_HOST') ?: 'db', '172.19.0.4', 'my-mariadb', '127.0.0.1'];
$pdo = null;

foreach ($hosts as $host) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=dbPartoches;charset=utf8", "root", "root");
        if ($pdo) break;
    } catch (Exception $e) {}
}

if (!$pdo) {
    die("Impossible de se connecter à MariaDB\n");
}

$sql = "
DELETE FROM `lienstrumchanson` WHERE `idChanson` > 764 AND `idChanson` != 1955;
DELETE FROM `lienchansonplaylist` WHERE `id_chanson` > 764 AND `id_chanson` != 1955;
DELETE FROM `lienurl` WHERE `nomTable` = 'chanson' AND `idTable` > 764 AND `idTable` != 1955;
DELETE FROM `document` WHERE `nomTable` = 'chanson' AND `idTable` > 764 AND `idTable` != 1955;
DELETE FROM `chanson` WHERE `id` > 764 AND `id` != 1955;
";

$countBefore = (int)$pdo->query("SELECT COUNT(*) FROM chanson WHERE id > 764 AND id != 1955")->fetchColumn();
$pdo->exec($sql);
$countAfter = (int)$pdo->query("SELECT COUNT(*) FROM chanson WHERE id > 764 AND id != 1955")->fetchColumn();

echo "Nettoyage des chansons de test terminé.\n";
echo "Chansons supprimées (ID > 764) : " . ($countBefore - $countAfter) . "\n";
