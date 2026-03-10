<?php

require_once '../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('monLoggerA');
$dateHeureMinute = date('Y-m-d') . '.log';
$logger->pushHandler(new StreamHandler('../tmp/' . $dateHeureMinute, Logger::WARNING));

echo "Lancement du logger <br>";
$logger->debug('Ce message ne sera pas stocké dans le fichier car debug');
$logger->warning('Ce message sera stocké dans le fichier');
$logger->error('Celui-ci aussi');
echo "Consulter le fichier <a href='../tmp/$dateHeureMinute'>../tmp/monfichier.log</a>";