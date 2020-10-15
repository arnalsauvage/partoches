<?php

// Si l'utilisateur est logué
if (isset ($_SESSION ['user']))
    header('Location: php/chanson_liste.php');
else {
    header('Location: php/songbook-portfolio.php');
//	header ( 'Location: html/index.html' );
}
