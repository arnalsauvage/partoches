<?php
if(!isset ($FichierCompteur)){
	$FichierCompteur = 1;	
	function ajouteHit ($nomtable, $idItem, $connexion){
		$DEBUG_COMPTEUR = false;
		//$DEBUG_COMPTEUR = true;
		// Connexion � la base de donn�es d�ja pass� en param�tre
		// R�cup�ration du nombre de Hits pour cet item
		$marequete = "select hits from $nomtable WHERE id = '$idItem'";
		if($DEBUG_COMPTEUR)
		echo " Requ�te lanc�e : $marequete <BR>";			
		$resultat = ExecRequete ( $marequete, $connexion);
		if($ligne = lignesuivante($resultat)){
			$nombreHits = $ligne[0];
			if($DEBUG_COMPTEUR)
			echo " Nombre de hits : $nombreHits <BR>";			
			if($nombreHits == "")
			$nombreHits = 0;
			// Augmentation du nombre et inscription dans la base de donn�es
			$nombreHits += 1;
			$marequete = "UPDATE $nomtable SET hits='$nombreHits' WHERE id = '$idItem'";
			if($DEBUG_COMPTEUR)
			echo " Requ�te lanc�e : $marequete <BR>";			
			$resultat = ExecRequete ( $marequete, $connexion);					
			return ($nombreHits);
		}
		else{
			if($DEBUG_COMPTEUR)
			echo " Pas d'identifiant id =$iditem trouv� dans la table $nomtable. <BR>";			
			return (0);
		}
	}
}
?>