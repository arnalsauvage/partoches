<?php
if(!isset ($FichierCompteur)){
	$FichierCompteur = 1;
	function ajouteHit($nomtable, $idItem)
	{
		$DEBUG_COMPTEUR = false;
		//$DEBUG_COMPTEUR = true;
		// Connexion à la base de données déjà pass� en paramètre
		// Récupération du nombre de Hits pour cet item
		$maRequete = "select hits from $nomtable WHERE id = '$idItem'";
		if($DEBUG_COMPTEUR)
			echo " Requête lancée : $maRequete <BR>";
		$resultat = $_SESSION ['mysql']->query($maRequete) or die ("Problème ajouteHits #1 : " . $_SESSION ['mysql']->error);
		// renvoie la lisgne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
		$resultat = $resultat->fetch_row();
		if ($resultat) {
			$nombreHits = $resultat[0];
			if($DEBUG_COMPTEUR)
				echo " Nombre de hits : $nombreHits <BR>";			
			if($nombreHits == "")
				$nombreHits = 0;
			// Augmentation du nombre et inscription dans la base de donn�es
			$nombreHits += 1;
			$maRequete = "UPDATE $nomtable SET hits='$nombreHits' WHERE id = '$idItem'";
			if($DEBUG_COMPTEUR)
				echo " Requête lancée : $maRequete <BR>";
			$resultat = $_SESSION ['mysql']->query($maRequete) or die ("Problème ajouteHits #2 : " . $_SESSION ['mysql']->error);
			return ($nombreHits);
		}
		else{
			if($DEBUG_COMPTEUR)
				echo " Pas d'identifiant id =$idItem trouvé dans la table $nomtable. <BR>";
			return (0);
		}
	}
}
?>