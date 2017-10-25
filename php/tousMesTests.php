<?php
include_once ("lib/utilssi.php");
include_once "lib/configMysql.php";

include "chanson.php";
include "document.php";
include "songbook.php";
include "utilisateur.php";



if ($_SESSION ['privilege'] > 2) {
 testeChanson ();
 testeDocument ();
 testeSongbook ();
 testeUtilisateur ();
}

else
	redirection ("chanson_liste.php");
?>