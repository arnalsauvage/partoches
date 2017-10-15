<?php
if (! isset ( $ClasseFormulaire )) {
	$ClasseFormulaire = 1;
	
	// Classe gérant les formulaires

	class Formulaire{
		// ----   Partie privée : les variables

		var $entetes, $champs, $nbChamps, $nbLignes;
		var $htmlGenere = "";
		
		// ---- Partie privée : les méthodes
		
		// Constructeur de la classe
		function Formulaire($pMethode, $pAction, &$retour, $pTransfertFichier = FALSE, $pNom = "Form") {
			
			// Mettre un attribut ENCTYPE si on transfère un fichier
			if ($pTransfertFichier)
				$encType = "ENCTYPE='multipart/form-data'";
			else
				$encType = "";
			
			// Ouverture de la balise
			$this->htmlGenere .= "<CENTER><FORM  METHOD='$pMethode' " . $encType . "ACTION='$pAction' NAME='$pNom'>\n";
		}
		
		// Méthode pour créer un champ INPUT général
		private function champINPUT($pType, $pNom, $pVal, $pTaille, $pTailleMax) {
			// Création de la balise
			return "<INPUT TYPE='$pType' NAME='$pNom' " . 'VALUE="' . htmlentities ( $pVal ) . '" SIZE="$pTaille" MAXLENGTH="' . $pTailleMax . '">';
			// Renvoi de la chaîne de caractères
		}
		
		// Champ de type texte
		private function champTEXTAREA($pNom, $pVal, $pLig, $pCol) {
			return "<TEXTAREA NAME='$pNom' ROWS='$pLig' " . "COLS='$pCol'>$pVal</TEXTAREA>\n";
		}
		
		// Champ pour sélectionner dans une liste
		private function champSELECT($pNom, $pListe, $pDefaut, $pTaille = 1) {
			$s = "<SELECT NAME='$pNom' SIZE='$pTaille'>\n";
			while ( list ( $val, $libelle ) = each ( $pListe ) ) {
				if ($val != $pDefaut)
					$s .= "<OPTION VALUE='$val'>" . $libelle . "</OPTION>\n";
				else
					$s .= "<OPTION VALUE='$val' SELECTED>$libelle</OPTION>\n";
			}
			return $s ."</SELECT>\n";
		}
		
		// Champ pour sélectionner dans une liste
		private function champSELECTImages($pNom, $pListe, $pDefaut, $pTaille = 1) {
			global $cheminVignettes;
			$s = "<SELECT NAME='$pNom' id='listeImage' SIZE='$pTaille' onchange='changeListeImage(this.form)'>\n";
			while ( list ( $val, $libelle ) = each ( $pListe ) ) {
				if ($val != $pDefaut)
					$s .= "<OPTION VALUE='$val'>" . $libelle . "</OPTION>\n";
				else
					$s .= "<OPTION VALUE='$val' SELECTED>$libelle</OPTION>\n";
			}
			$s .= "</SELECT>\n";
			$s .= "<img src='" . $cheminVignettes . "upload.png' onclick='miseAjourListeImages(this.form,vignette)'>";
			$s .= "<img id='vignette' src='$cheminVignettes$pDefaut'>";
			return $s;
		}
		
		// Champ CHECKBOX ou RADIO
		private function champBUTTONS($pType, $pNom, $pListe, $pDefaut) {
			// Toujours afficher dans une table
			while ( list ( $val, $libelle ) = each ( $pListe ) ) {
				$libelles .= "<TD><B>$libelle</B></TD>";
				if ($val == $pDefaut)
					$checked = "CHECKED";
				else
					$checked = " ";
				$champs .= "<TD><INPUT TYPE='$pType' NAME='$pNom' VALUE='$val' " . " $checked> </TD>\n";
			}
			return "<TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2><TR>\n" . $libelles . "</TR>\n<TR>" . $champs . "</TR></TABLE>";
		}
		
		// Champ de formulaire
		private function champForm($pType, $pNom, $pVal, $params, $pListe = array()) {
			$taille = "";
			$champ = "vide";
			switch ($pType) {
				case "TEXT" :
				case "PASSWORD" :
				case "SUBMIT" :
				case "RESET" :
				case "FILE" :
					if (isset ( $params ["SIZE"] ))
						$taille = $params ["SIZE"];
					if (isset ( $params ["MAXLENGHT"] ))
						$tailleMax = $params ["MAXLENGTH"];
					if (! isset ( $tailleMax ))
						$tailleMax = 128;
					if ($tailleMax == 0)
						$tailleMax = $taille;
					// Appel de la méthode champINPUT de l'objet courant
					$champ = $this->champINPUT ( $pType, $pNom, $pVal, $taille, $tailleMax );
					break;
				
				case "TEXTAREA" :
					$lig = $params ["ROWS"];
					$col = $params ["COLS"];
					// Appel de la méthode champTEXTAREA de l'objet courant
					$champ = $this->champTEXTAREA ( $pNom, $pVal, $lig, $col );
					break;
				
				case "SELECT" :
					$taille = $params ["SIZE"];
					// Appel de la méthode champSELECT de l'objet courant
					$champ = $this->champSELECT ( $pNom, $pListe, $pVal, $taille );
					break;
				
				case "SELECT-images" :
					$taille = $params ["SIZE"];
					// Appel de la méthode champSELECT de l'objet courant
					$champ = $this->champSELECTImages ( $pNom, $pListe, $pVal, $taille );
					break;
				
				case "CHECKBOX" :
				case "RADIO" :
					// Appel de la méthode champBUTTONS de l'objet courant
					$champ = $this->champBUTTONS ( $pType, $pNom, $pListe, $pVal );
					break;
				
				default :
					$champ = "<B>ERREUR: $pType est un type inconnu</B>\n";
					break;
			}
		return $champ;
		}
		
		// Affichage d'un champ avec son libellé
		private function champLibelle($pLibelle, $pNom, $pVal, $pType = "TEXT", $params = array(), $pListe = array()) {
			// Création du champ
			$retour = "";
			$champHTML = $this->champForm ( $pType, $pNom, $pVal, $params, $pListe );
				$retour .= "<B>" . $pLibelle . "</B>";
				$retour .= $champHTML . "<br>";
			$this->htmlGenere .= $retour;
		}
		
		// Partie publique
		function champTexte($pLibelle, $pNom, $pVal, $pTaille, $pTailleMax = 0) {
			$this->champLibelle ( $pLibelle, $pNom, $pVal, "TEXT", array (
					"SIZE" => $pTaille,
					"MAXLENGTH" => $pTailleMax 
			) );
		}
		function champMotDePasse($pLibelle, $pNom, $pVal, $pTaille, $pTailleMax = 0) {
			$this->champLibelle ( $pLibelle, $pNom, $pVal, "PASSWORD", array (
					"SIZE" => $pTaille,
					"MAXLENGTH" => $pTailleMax 
			) );
		}
		function champRadio($pLibelle, $pNom, $pVal, $pListe) {
			$this->champLibelle ( $pLibelle, $pNom, $pVal, "RADIO", array (), $pListe );
		}
		function champListe($pLibelle, $pNom, $pVal, $pTaille, $pListe) {
			$this->champLibelle ( $pLibelle, $pNom, $pVal, "SELECT", array (
					"SIZE" => $pTaille 
			), $pListe );
		}
		function champListeImages($pLibelle, $pNom, $pVal, $pTaille, $pListe) {
			$this->champLibelle ( $pLibelle, $pNom, $pVal, "SELECT-images", array (
					"SIZE" => $pTaille 
			), $pListe );
		}
		function champFenetre($pLibelle, $pNom, $pVal, $pLig, $pCol) {
			$this->champLibelle ( $pLibelle, $pNom, $pVal, "TEXTAREA", array (
					"ROWS" => $pLig,
					"COLS" => $pCol 
			) );
		}
		function champValider($pLibelle, $pNom) {
			$this->champLibelle ( " ", $pNom, $pLibelle, "SUBMIT" );
		}
		function champFichier($pLibelle, $pNom, $pTaille) {
			$this->champLibelle ( $pLibelle, $pNom, "", "FILE", array (
					"SIZE" => $pTaille 
			) );
		}
		function champCache($pNom, $pValeur) {
			$this->htmlGenere .= "<INPUT TYPE=HIDDEN NAME='$pNom' VALUE=\"$pValeur\">\n";
		}
		
		// Fin du formulaire
		public function fin() {
			$retour = "";
			$retour .= "</FORM></CENTER>\n";
			$this->htmlGenere .= $retour;
			return $this->htmlGenere;
		}
		
		public function getHtml() {
			return $this->htmlGenere;
		}
	}
}
?>