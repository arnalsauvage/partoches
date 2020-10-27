<?php

require_once '../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('monLoggerA');
$logger->pushHandler(new StreamHandler('../tmp/monfichier.log', Logger::WARNING));

$logger->debug('Ce message ne sera pas stocké dans le fichier car debug');
$logger->warning('Ce message sera stocké dans le fichier');
$logger->error('Celui-ci aussi');