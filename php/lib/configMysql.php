<?php
	
	$configMysql = TRUE;

	$monserveur="mysql.hostinger.fr";
	$mabase="u528022398_fuck";
	$LOGIN="u528022398_arnal";
	$MOTDEPASSE="8adpt7s4";

	$monserveur="localhost";
	$mabase="fuck";
	$LOGIN="fuck";
	$MOTDEPASSE="fuck";

	

	if(($idconnect=@mysql_connect($monserveur,$LOGIN,$MOTDEPASSE))==false){
		$error="Erreur #1 configMysql : Impossible de creer une connexion persistante !";
		return(0);
	}
	
	if(@mysql_select_db($mabase,$idconnect)==false){
		$error="Erreur #2 configMysql : Impossible de selectionner la base !";
		return(0);
	}
//	echo "connexion : $idconnect";
//	return($idconnect);

	function convertitDateJJMMAAAA($date){
		// On convertit la date au format mysql
		$date = explode('/', $date);
		$new_date = $date[2].'-'.$date[1].'-'.$date[0];
		//  echo "New date : " . $new_date . "<br>";
		return $new_date;
	}

?>
