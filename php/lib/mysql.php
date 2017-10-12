<?php

// Fonction Connexion _________________________________________________
function Connexion ($pNom, $pMotDePasse, $pBase, $pServeur){
	// Connexion au serveur
	$connexion = mysql_connect($pServeur, $pNom, $pMotDePasse);
	if(!$connexion){
		echo "Désolé, connexion au serveur $pServeur impossible\n";
		echo " pour $pNom";
		exit;
	}
	// Connexion à la base
	if(!mysql_select_db($pBase, $connexion)){
		echo "Désolé, accès à la base $pBase impossible\n";
		echo "<B>Message de MySQL :</B>" . mysql_error($connexion);
		exit;
	}
	// On renvoie la variable de connexion
	return $connexion;
}        // Fin de la fonction Connexion _______________________________

// Exécution d'une requête avec MySql _________________________________
function ExecRequete ($requete, $connexion){
	$resultat = mysql_query($requete, $connexion);
	if($resultat)
		return ($resultat);
	else{
		echo "<B>Erreur dans l'éxécution de la requête '$requete.</B><BR>";
		echo "<B>Message de MySql : </B> " . mysql_error($connexion);
		exit;
	}
}        // Fin de la fonction ExecRequete _____________________________

// Récupération d'une ligne de résultat avecv MySql____________________
function LigneSuivante ($resultat){
	return (mysql_fetch_row($resultat));
}        // Fin de la fonction LigneSuivante ___________________________

// Récupération d'une ligne de résultat avecv MySql__________________
function RenvoieLigneN ($resultat, $nbligne){
	if(!mysql_data_seek($resultat, $nbligne)){
		echo "Impossible d'atteindre la ligne $nbligne: " . mysql_error() . "\n";
		continue;
	}     
	return (mysql_fetch_row ($resultat));
}        // Fin de la fonction RenvoieLigneN ___________________________

// Récupération du nombre de ligne de résultat avecv MySql____________________
function NombreLignes ($resultat){
	return (mysql_num_rows($resultat));
}        // Fin de la fonction NombreLignes ___________________________

// Libération de la variable resultat _________________________________
function LibereResultat ($resultat){
	mysql_free_result($resultat);
}        // Fin de la fonction Resultat ___________________________

function DernierIdInsere (){
	return (mysql_insert_id());
}

// Cette fonction transforme une date au format Mysql et la traduit en notation
// - JJ/MM/AAAA si mode == 0 (par defaut)
// - JJ/MM si mode = 1
function dateMysqlVersTexte ($dateMysql, $mode = 0){
	$an = substr ($dateMysql, 0, 4);
	$mois = substr ($dateMysql, 5, 2);
	$jour = substr ($dateMysql, 8, 2);
	if($mode == 0)
		$retour = $jour . "/" . $mois . "/" . "$an";
	if($mode == 1)
		$retour = $jour . "/" . $mois;
//	       echo "<P> Année : $an , mois : $mois, Jour : $jour. $retour </P>"; // Pour test
	return ($retour);
}
// Cette fonction transforme une date-heure au format Mysql et la traduit en notation
// - JJ/MM/AA si mode == 0 (par defaut)
// - JJ/MM si mode = 1

function dateHeureMysqlVersTexte ($date, $mode = 0){
	$an = substr ($date, 0, 4);
	$mois = substr ($date, 5, 2);
	$jour = substr ($date, 8, 2);
	$heure = substr ($date,11,2);
	$minutes = substr ($date,14,2);
	$secondes = substr ($date,17,2);
	if( $mode == 0)
	$retour = $jour . "/" . $mois . "/" . "$an" . " " . $heure . "h" . $minutes;
	if( $mode == 1)
	$retour = $jour . "/" . $mois;
	//       echo "<P> Année : $an , mois : $mois, Jour : $jour. $retour </P>";
	return ($retour);
}

// Cette fonction traduit une date du format JJ/MM/AAAA vers le format mySql
function dateTexteVersMysql ($date){
	$compteur = 0;
	for($i = 0; $i < strlen ($date); $i++)
	{
		if($date[$i] == "/"){
			$marqueur[$compteur] = $i;
			$compteur++;
		}
	}
	// S'il n'y a pas de slash ou plus de 2, la date est erronée
	if($compteur == 0 || $compteur > 2)
		return("0000-00-00");
	// S'il n'y a qu'un slash, l'année est l'année courante
	if($compteur==1)
		$an  = date("Y");
	else{
		// On prend l'année saisie par l'utilisateur
		$an = substr ($date, $marqueur[1]+1,4);
		// On complète si elle n'est que sur un ou deux caractères
		if(strlen($an) == 2)
			$an = "20" . $an;
		if(strlen($an) == 4)
			$an = "" . $an;
	}
	$mois = substr ($date, $marqueur[0]+1, 2);
	if($mois[1] == "/")
		$mois[1] =  0;
	$jour = substr ($date, 0, $marqueur[0]);
	if(checkdate($mois,$jour,$an)==0)
		return("0000-00-00");
	$retour = $an . "-" . $mois . "-" . $jour;
	return ($retour);
}

// Renvoie la date du jour au format MySql
function dateDuJourMysql(){
	return( date("Y") . "-" . date("m") . "-" . date("d"));
}

// Libere les ressources
function libereRessources($resultat){
	mysql_free_result($resultat);
	
}

// Chargement de la liste des libelles
// ex : chargeLibelles($conn, "auteurs", "nom") donne la liste des noms dans un tab[id]=nom trié par nom
function chargeLibelles($connexion, $table, $libelle){
	$marequete = "select id, $libelle,  from $table order by $libelle";
	$resultat = ExecRequete ($marequete, $connexion);
	while($ligne=LigneSuivante($resultat)){
		$listeLibelles[$ligne[0]]= $ligne[1];
	}
	mysql_free_result($resultat);
	return($listeLibelles);
}

// Exemple de fonction métier à recopier
// Cette fonction renvoie false si une chanson n'existe pas en enregistrement,
// Sinon, elle renvoie le dernier enregistrement dispo
function chansonEstEnregistree ($idChanson,$connexion){
	$marequete = "select id, idchanson from enregistrement where idchanson = '$idChanson'";
	$resultat = ExecRequete ( $marequete, $connexion);
	$nbReponses = mysql_num_rows($resultat);
	
	if($nbReponses > 0){
		//echo "ok";
		while($ligne = lignesuivante($resultat))
			$id = $ligne[0];
			return ($id);
	}
	else{
		//echo "Chanson $idChanson non enregistrée... <BR>";
		return false;
	}
}

?>