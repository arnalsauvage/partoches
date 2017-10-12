<?php
if(!isset ($FichierHtml)){
	$FichierHtml = 1;
	// Fonction retournant le code HTML pour un lien hypertexte____________

	function Ancre ($url, $libelle, $classe=-1, $nouvellefenetre=-1){
		$optionClasse = "";
		if($nouvellefenetre==-1)
		$nouvellefenetre="";
		else
		$nouvellefenetre = 'target="_blank"';
		if($classe != -1)
		$optionClasse = " class='$classe'";
		return "<a href='$url'" . "$nouvellefenetre $optionClasse>$libelle</A>";
	}
	// Fin de la fonction Ancre____________________________________________
        
	function titre ($texte,$niveau){
		return "<h$niveau>$texte</h$niveau>";
	}


	// Fonction retournant le code HTML pour une image ____________________
	function Image ($urlImage, $largeur = -1, $hauteur = -1, $bordure = 0, $alt = ""){
		$attrLargeur = "";
		$attrHauteur = "";
		if($largeur != -1)
		$attrLargeur = " width = '$largeur' ";
		if($hauteur != -1)
		$attrHauteur = " height = '$hauteur' ";
		return "<img src='$urlImage' " . $attrLargeur . $attrHauteur . " border='$bordure' alt='$alt'>\n";
	}
	// Fin de la fonction Image____________________________________________

	// Fonction créant un champ SELECT
	// Liste contient toutes les valeurs duchamp select
	function ChampSelect ($liste, $numero, $nom){
		$champSelect = "";
		$champSelect .= "<select name = $nom size=\"1\">";
		while($ligne = LigneSuivante($liste)){
			$choix++;
			$champSelect .= "<option ";
			if($numero==$choix)
			$champSelect .= "selected ";
			$champSelect .= "value=$choix>";
			$champSelect .= $ligne[1] . "</option>";
		}
		$champSelect .= " </select>";
		return $champSelect;
	}

	function ecritHtml($texte){
		return ($texte);
	}

	function entreBalise($texte,$balise){
		return ("<".$balise."> ". htmlentities($texte) . "</" . $balise . ">");
	}

	// Cette fonction donne l'instruction au navigateur de se rediriger
	// vers une autre adresse (aucun caractère n'a du être transmis,
	// pas m?me un espace ou  un retour de ligne
	function redirection($url){
		if(headers_sent())
			print('<meta http-equiv="refresh" content="0;URL='.$url.'">');
		else
			header("Location: $url");
		exit;
	}

	// Cette fonction remplace une adresse url dans un texte par un lien cliquable
	function lienCliquable($texte){
		$texte = preg_replace('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@', '<a href="$1" target="blank">$1</a>', $texte);

		//because you want the url to be an external link the href needs to start with 'http://'
		//simply replace any occurance of 'href="www.' into 'href="http://www."

		$texte = str_replace("href=\"www.","href=\"http://www.",$texte);
		return $texte;
	}

	// Cette fonction remplacera dans le $texte les éléments de type http://www.bidule.com/machin en lien html 
	function ajouteLiens($texte){
		// On place d'abord le texte en tableaux où l'on sépare le texte pur du texte formaté html
		// parcours la chaine caractère par acaractère
		// Quand la balise < est rencontrée, on augmente le niveau : il peut y a voir des < imbriqués
		// L'indice indique l'élément du tableau dans lequel le bout sera rangé
		$indice = 0;
		$niveau = 0;
		$tableau = array();
		$tableau[0] = "";
		$longueur = strlen($texte);
		for( $i = 0 ; $i < $longueur ; $i++ ){
			// si un nouvel ouvrant est découvert, on augmente l'indice et le niveau
			if($texte[$i]=="<"){
				if(($i>0)&&($niveau==0)){
					$indice++;            
					$tableau[$indice] = "";
				}
				// Si l'ouvrant est un ouvrant imbriqué, on ajoute 1 à la variable niveau
				$niveau++;
			}
			// si un fermant est découvert, on diminue le niveau, et on le copie
			if($texte[$i]==">"){
				$niveau--;
				$tableau[$indice] .= $texte[$i];
				// Si le niveau est à nouveau à zéro, on est sorti du code, on peut créer une nouvelle ligne dans tableau
				if($niveau==0){
					$indice++;
					$tableau[$indice] = "";
				}
			}
			else
			// on copie le caractère dans le tableau[indice]
			$tableau[$indice] .= $texte[$i];
		}
		// Pour chaque élément du tableau non HTML, on applique une expression régulière
		// transformant les adresses en liens
		$indice_max = $indice;
		$indice = 0;
		for( $indice = 0 ; $indice <= $indice_max ; $indice++ ){
			if(isset($debug_fonc))
			echo "tableau[$indice] : $tableau[$indice]\n";

			if(strstr($tableau[$indice],"<")==FALSE){
				$chaine = $tableau[$indice];
				$tableau[$indice] = lienCliquable($tableau[$indice]);
				//$tableau[$indice]  = preg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]",
				//    "<a href=\"\\0\">\\0</a>", $tableau[$indice]);
				if(isset($debug_fonc))
				echo "<br>chaine  remplacée : $chaine <br>\n";
				if(isset($debug_fonc))    
				echo "<br>chaine  de remplacement : $tableau[$indice] <br>\n";
			}
		}
		$chaine = implode ($tableau);    
		return $chaine;
	}
	// Fin de la function ajouteLiens($texte)
	
	function envoieHead($titrePage, $feuilleCss){
		$retour = 	
		"<!doctype html>
		<html lang='fr'>
		<head>
		<meta http-equiv='Content-Type' content='text/html; charset='utf-8'  />
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
    	<link href='../css/bootstrap.min.css' rel='stylesheet'>
    	<script src= 'https://code.jquery.com/jquery-3.2.1.slim.min.js ' integrity= 'sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN ' crossorigin= 'anonymous '></script>
		<script src= 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js ' integrity= 'sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4 ' crossorigin= 'anonymous '></script>
		<script src= 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js ' integrity= 'sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1 ' crossorigin= 'anonymous '></script>
    	
		<link rel='stylesheet' media='screen' type='text/css' title='resolution' href='$feuilleCss' /> 
		<script type='text/javascript' src='./lib/javascript.js'></script>
		<title>$titrePage</title>
		</head>";
		return $retour;
	}
	
	function envoieFooter($contenu){
		$retour = 	
		"<footer>
		$contenu | 
		<a href='http://www.facebook.com/top5.asso' target='_blank'>Facebook</a> | 
		<a href='http://www.top5.re' target='_blank'>Site web</a> | 
		<a href='https://www.youtube.com/channel/UCFKyqYcs5cnML-EgPgYmwdg' target='_blank'>Chaîne Youtube</a>
		</footer>";
		$retour .= "
		<!-- Bootstrap core JavaScript
	    ================================================== -->
	    <!-- Placed at the end of the document so the pages load faster -->
	    <script src= 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js '></script>
	    <script>window.jQuery || document.write('<script src= \"../../assets/js/vendor/jquery.min.js\"><\/script>')</script>
	    <script src= '../js/bootstrap.min.js '></script>
	    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	    <script src= '../js/ie10-viewport-bug-workaround.js '></script>";
		return $retour;
	}
	
}
?>