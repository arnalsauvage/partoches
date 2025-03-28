<?php
include_once("../lib/utilssi.php");
include_once("../note/UtilisateurNote.php");

echo envoieHead("Partoches", "../../css/index.css?v=25.3.28") . PHP_EOL;

// Display 4 star bar system for 4 different IDs
echo UtilisateurNote::starBar( "chanson", 5, 10, 25); // 5 stars, Media ID 201, 25px star image
echo UtilisateurNote::starBar("chanson", 1, 5, 25); // 3 stars, Media ID 200, 25px star image
echo UtilisateurNote::starBar("chanson",  6,5, 25); // 10 stars, Media ID 202, 25px star image
echo UtilisateurNote::starBar( "chanson", 7,10, 25); // 30 stars, Media ID 203, 25px star image

echo envoieFooter();