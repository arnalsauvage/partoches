<?php
if (file_exists(dirname(__DIR__, 2) . '/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/autoload.php';
} elseif (file_exists(dirname(__DIR__, 4) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__, 4) . '/vendor/autoload.php';
}
