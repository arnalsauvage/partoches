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
	function Image ($urlImage, $largeur = -1, $hauteur = -1,  $alt = "image deco", $class = ""){
		$attrLargeur = "";
		$attrHauteur = "";
		if($largeur != -1)
		$attrLargeur = " width = '$largeur' ";
		if($hauteur != -1)
		$attrHauteur = " height = '$hauteur' ";
		return "<img src='$urlImage' " . $attrLargeur . $attrHauteur . "  alt='$alt' class ='$class'>\n";
	}
	// Fin de la fonction Image____________________________________________

	// Fonction créant un champ SELECT
	// Liste contient toutes les valeurs duchamp select
	function ChampSelect ($liste, $numero, $nom){
		$champSelect = "";
		$champSelect .= "<select name = $nom size=\"1\">";
		$choix = 0;
		while($ligne = $liste->fetch_row()){
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
		<meta charset='UTF-8' >";

		// Pour BootStrap
		$retour .= "<meta http-equiv='X-UA-Compatible' content='IE=edge'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
    	<link href='../css/bootstrap.min.css' rel='stylesheet'>
    	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
 		<script src='https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js'></script>
  		<script src='https://oss.maxcdn.com/respond/1.4.2/respond.min.js'></script>
		<![endif]-->
    	";

		$retour .= "<link rel='stylesheet' media='screen' type='text/css' title='resolution' href='$feuilleCss' />
		<script src='./lib/javascript.js'></script>
		<link href='../css/toastr.min.css' rel='stylesheet' type='text/css'>
		<title>$titrePage</title>
		</head>";
		return $retour;
	}
	
	function envoieFooter(){
		$retour = file_get_contents('../html/footer.html', FILE_USE_INCLUDE_PATH);
		return $retour;
	}
	
}
