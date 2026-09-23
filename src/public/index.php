<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header('Location: php/chanson/chanson_liste.php');
} else {
    header('Location: php/media/listeMedias.php');
}
exit;
