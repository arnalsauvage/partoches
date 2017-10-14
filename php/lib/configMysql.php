<?php
if(FALSE == isset($configMysql) )
{
	$configMysql = TRUE;
	
	$fichier = "../conf/params.ini";
	
	// On lit les données dans le fichier ini
	$ini_objet = new ini ();
	$ini_objet->m_fichier ( $fichier );
	
	$monserveur = $ini_objet->m_valeur ( "monServeur", "mysql" );
	$mabase = $ini_objet->m_valeur ( "maBase", "mysql" );
	$LOGIN = $ini_objet->m_valeur ( "login", "mysql" );
	$MOTDEPASSE = $ini_objet->m_valeur ( "motDePasse", "mysql" );
	
	if(($idconnect=@mysql_connect($monserveur,$LOGIN,$MOTDEPASSE))==false){
		$error="Erreur #1 configMysql : Impossible de creer une connexion persistante !";
		return(0);
	}
	
	if(@mysql_select_db($mabase,$idconnect)==false){
		$error="Erreur #2 configMysql : Impossible de selectionner la base !";
		return(0);
	}
	
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
