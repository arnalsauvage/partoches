<?php
include_once ("lib/utilssi.php");
include_once "lib/configMysql.php";

include_once "chanson.php";
include_once "document.php";
include_once "songbook.php";
include_once "utilisateur.php";



if ($_SESSION ['privilege'] > 2) {
 testeChanson ();
 testeDocument ();
 testeSongbook ();
 testeUtilisateur ();
}

else
	redirection ("chanson_liste.php");
