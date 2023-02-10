<?php

// Si l'utilisateur est logué
if (isset ($_SESSION ['user']))
    header('Location: php/chanson/chanson_liste.php');
else {
    header('Location: php/songbook/songbook-portfolio.php');
}
