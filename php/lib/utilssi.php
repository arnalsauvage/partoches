<?php
$a = session_id();
if(empty($a)) session_start();
unset ($a);
//	function pc_process_dir ($nom_rep, $profondeur_max = 10, $profondeur = 0)
//	function chansonEstEnregistree ($idChanson,$connexion)
//	function vignette ($image,$largeur)
//	function affichePlayer($mp3="vide")
//	function ecritFichierLog($fichier, $log)
//	function ListeIdLabel ($table, $nomId)
//	function imagePortraitRandom ()
//	function chargeChansons($connexion)
//	function insereJavaScript ($source)
//	function insereLienLightbox($image,$largeur='')
//	function EnTete ($titre, $texte, $menu, $soustitre, $imagetitreGauche, $imagetitreDroite)
//	function PiedDePage ()
//	function listeImages ()
//	function boutonSuppression($lien,$iconePoubelle)

// Ce code remplace la récupération automatique des variables post et GET en php4
// TODO : sécurité
extract($_GET, EXTR_OVERWRITE);
extract($_POST, EXTR_OVERWRITE);

if(!isset ($FichierUtilsSi)){
	// Déclaration des variables globales
	$FichierUtilsSi = 1;

	// Inclusion des différentes librairies
	require_once ("lib/class.ini.php");
	require_once ("lib/compteur.php");
	require_once ("lib/configMysql.php");
	include_once "lib/config-images.php";
	require_once ("lib/formulaire.php");
	require_once ("lib/html.php");
	require_once ("lib/mysql.php");
	include_once("lib/params.php");
	require_once ("lib/vignette.php");
        
	if(!isset ($_SESSION["privilege"])){
		// session_register("privilege");	 
		$_SESSION["privilege"] = 0;
	}

	// Cette fonction, pompée dans "PHP en action", p547, éditions O'Reilly
	// parcourt un sous-répertoire et exporte la liste des fichiers dans un tableau	
	function pc_process_dir ($nom_rep, $profondeur_max = 10, $profondeur = 0){
		if($profondeur >= $profondeur_max){
			error_log("Profondeur maximum $profondeur_max atteinte dans $nomrep.");
			return false;
		}
		$sous_repertoires = array();
		$fichiers = array();
		if(is_dir($nom_rep) && is_readable($nom_rep)){
			$d = dir($nom_rep);
			while(false !== ($f = $d->read())){
				// évite . et ..
				if(('.' == $f) || ('..' == $f)){
					continue;
				}
				if(is_dir("$nom_rep/$f")){
					array_push($sous_repertoires,"$nom_rep/$f");
					//					echo $f . "<BR>";					
				}
				else{
					array_push($fichiers,"$nom_rep/$f");
					//					echo $f . "<BR>";
				}
			}
			$d -> close();
			foreach($sous_repertoires as $sous_repertoire){
				$fichiers = array_merge ($fichiers, pc_process_dir($sous_repertoire, $profondeur_max, $profondeur+1));
				//				echo "$sous_repertoire <BR>";
			}
		}
		return $fichiers;
	}	

	// Cette fonction affiche un player mp3 ou un lien vers le fichier en mode popup   
	function affichePlayer($mp3="vide"){
		if(is_file ("mp3/".$mp3) ){
			if(substr($mp3,-3,3)=="mp3"){		
				$texte = ' <audio controls="controls" preload="none"> ' .'\n';
				$texte .= '<!-- On ouvre la balise audio, on affiche les controles et sans pre-charger les titres. -->\n';
				$texte .=  '<source src="mp3/'.$mp3.'" type="audio/mpeg"/>\n';
				//<!-- Apres avoir indique les chemins des deux fichiers, on charge le lecteur dewplayer.-->
				//<!-- Ce code ne sera execute que si le navigateur de votre visiteur ne prends pas en charge la balise audio. -->
				//        <object type="application/x-shockwave-flash" data="/dew/dewplayer.swf" width="200" height="20" id="dewplayer" name="dewplayer">
				//            <param name="wmode" value="transparent"/>
				//            <param name="movie" value="/dew/dewplayer.swf"/>
				//            <param name="flashvars" value="mp3=musique/titre.mp3&amp;autostart=1&amp;showtime=1"/>
				//        </object>
				//</audio>	
				$texte .= "<object type='application/x-shockwave-flash' data='mp3/dewplayer.swf?son=mp3/$mp3' width='200' height='20'> " ;
				$texte .= "<param name='movie' value='mp3/dewplayer.swf?son=mp3/$mp3' /> </object>";	
				$texte .= "</audio>";
				return $texte;
			}
			else
			return (Ancre ("mp3/".$mp3,"ouvrir",-1,1));
		}
		else 
		//               echo "fichier $mp3 non trouvé !!";
		return "";
	}	

	// Cette fonction écrit le $log dans le $fichier
	function ecritFichierLog($fichier, $log){
		$time = date("l, j F Y [h:i a]"); 
		$ip = $_SERVER['REMOTE_ADDR']; 
		$fp = fopen("$fichier","a"); 
		fputs($fp, "\n"); 
		fputs($fp, " 
			<table border=0 cellspacing=0 cellpadding=0> 
			<tr> 
			<td valign=top><font size=1>$ip</font></td> <td valign=top width=10></td> 
			<td valign=top><font size=1>$time</font></td><td valign=top></td> 
			<td valign=top><font size=2>$log</font></td><td valign=top></td> 		
			</tr></table><br>"); 
		//		echo "TRUC !!! Fichier : $fichier <br>\n";
		fclose($fp); 
	}

	// Cette fonction retourne une image en portrait au parmi les images dans la base avec le tag aLaUne
	function imagePortraitRandom (){
		global $LOGIN, $MOTDEPASSE, $mabase, $monserveur;
		$connexion = Connexion($LOGIN,$MOTDEPASSE,$mabase,$monserveur);
		$requete = "SELECT nomFichier, hauteur, largeur, repertoire, poids from image where tags LIKE '%aLaUne%'";
		$resultat = ExecRequete ($requete, $connexion);
		$nombreResultats = Mysql_num_rows($resultat);
		$parcours = 0;
		mt_srand (time());
		$numero = mt_rand(0,$nombreResultats);
		//echo ("Tirage de $numero / $nombreResultats");
		while($parcours++<=$numero)
		$ligne = lignesuivante($resultat);
		return ($ligne[3].$ligne[0]);
	}	

	function insereJavaScript ($source){
		return "<script type='text/javascript' src='$source'></script>\n";
	}


	// Cette fonction crée l'en-tête du HTML de réponse
	function EnTete ($titre, $texte, $menu, $soustitre, $imagetitreGauche, $imagetitreDroite){
		$enTete = "";
		$enTete .= "<!doctype html>\n";
		$enTete .= "<html lang='fr'>";
		$enTete .= "<head>";

//		$enTete .= "<meta http-equiv=\"Content-Type\" content=\"text/html\"; \"charset=iso-8859-1\" />";
		$enTete .= "<meta http-equiv=\"Content-Type\" content=\"text/html\"; charset=\"UTF-8\" />";
		$enTete .= "<TITLE>$titre</TITLE>\n";
		$enTete .= "<LINK REL=\"stylesheet\" HREF=\"pages/si.css\" TYPE=\"text/css\">\n";
		$enTete .= insereJavaScript ("pages/include/javascript.js");

		// Utilisation du nuage de mots tagcanvas
		$enTete .= insereJavaScript("pages/include/tagcanvas.min.js");
		include("pages/include/tagcanvas.param.php");

		$enTete .= "</HEAD>\n";
		$enTete .= "<BODY>\n";
		$enTete .= "<div align='center'>";
		TblDebut (1,"800",3,3,"page"); 
		TblDebutLigne ("MENU"); TblDebutCellule ();
		TblDebut (0,"800",3,3);
		TblDebutLigne ();
		// mt_srand (time());
		//		numero = mt_rand(10,99);
		//		  $image1 = "/images/flrtr0" . $numero . ".jpg";
		$image1 = "images/$imagetitreGauche";
		// $numero = mt_rand(10,99);
		//		  $image2 = "/images/flrtr0" . $numero . ".jpg";
		//$image2 = "images/$imagetitreDroite";
		$image2 = imagePortraitRandom();
		TblCellule( Image($image1,240, 180));

		TblCellule ("<FONT SIZE=+7>$texte</FONT><BR><BR> $soustitre ",1,1,"TITRE");
		//		TblCellule ("<DIV align='center'>".Image ($image2,240)."</DIV>");
		$enTete .= ("<TD align='center'>".insereLienLightbox($image2,240)."</TD>");
		TblFinLigne(); TblFin();		
		// Affichage du menu
		// Premier tableau d'une case pour obtenir le fond rouge
		TblDebut (0, "800", -1, -1, "MENU");
		TblDebutLigne ("MENU"); TblDebutCellule();
		// Deuxième tableau imbriqué pour contenir les éléments du menu
		TblDebut (0, "800",-1, -1, "MENU"); TblDebutLigne("MENU");
		// Choix des menus
		while( list ($libelle, $ancre) = each($menu))
		TblCellule (Ancre($ancre, $libelle,"MENU"), 1, 1, "MENU");
		TblFin(); TblFin();
		TblDebut (0,"800",1,1,"page"); TblDebutLigne();TblDebutCellule	();		
		$enTete .= "</BODY></HTML>";
		return $enTete	;		 
	}

	// Cette fonction retourne une liste des images disponibles sur le site, eventuellement dans un sous-dossier
	function listeImages ($subDir=""){
		$d = dir("../images".$subDir);
		$compteur = 0;
		while(false !== ($entry = $d->read())){
			if(($entry!=".")AND($entry!="..")){
				//				echo "<option " . "value=$entry> echo $ligne[1]" . "</option>";
				//		  echo "tableau[$compteur]=$entry <br>";
				$tableau[$entry]=$entry;
			}
		}
		$d->close();
		asort ($tableau);
		return $tableau;
	}
        
	// Cette fonction retourne un bouton de suppression avec message de confirmation
	function boutonSuppression($lien,$iconePoubelle,$cheminImages){//<img src="x.png" onclick="getattrs(this);">
		return "<img src='$cheminImages$iconePoubelle' width='16' alt='supprimer la fiche' onclick =\"confirmeSuppr('".$lien."','Voulez-vous vraiment supprimer cet élément ?');\" border='0'>";
	}

}
?>