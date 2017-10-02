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

extract($_GET, EXTR_OVERWRITE);
extract($_POST, EXTR_OVERWRITE);

if(!isset ($FichierUtilsSi)){
	// Déclaration des variables globales
	$FichierUtilsSi = 1;

	// Inclusion des différentes librairies
	require_once ("lib/html.php");
	require_once ("lib/mysql.php");
	require_once ("lib/configMysql.php");
	require_once ("lib/formulaire.php");
	require_once ("lib/compteur.php");
	include_once "lib/images-config.php";
	require_once ("lib/vignette.php");
	require_once ("lib/class.ini.php");
	include_once("lib/params.php");
        
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

	// Cette fonction retourne des couples id/nom_de_champ d'une table
	function ListeIdLabel ($table, $nomId){
		$requete = "SELECT ID , $nomId FROM $table ORDER BY $nomId";
		$connexion = Connexion ($LOGIN, $MOTDEPASSE,"si", $SERVEUR);
		$resultat = ExecRequete ($requete, $connexion);
		return $resultat;
	}

	// Cette fonction retourne une image en portrait au hasard dans la base
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

	// Chargement de la liste des chansons
	function chargeChansons($connexion){
		$marequete = "select id, nom, image from chanson order by nom";
		$resultat = ExecRequete ($marequete, $connexion);
		while($ligne=LigneSuivante($resultat)){
			$listeChansons[$ligne[0]][1] = $ligne[1];
			$listeChansons[$ligne[0]][2] = $ligne[2];
		}
		mysql_free_result($resultat);
		return($listeChansons);
	}

	function insereJavaScript ($source){
		return "<script type='text/javascript' src='$source'></script>\n";
	}

	function insereLienLightbox($image,$largeur=''){
		$lien = "<a href=\"$image\" rel=\"lightbox\"> <img src='$image'";
		if($largeur <> '')
		$lien .=  "width='$largeur'";
		$lien .= "></a>";
		return ($lien);
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
		$enTete .= '<link rel="stylesheet" href="pages/include/lightbox/css/lightbox.css" type="text/css" media="screen" />';
		$enTete .= "<link rel='stylesheet' href='pages/include/videobox/css/videobox.css' type='text/css' />";  		
		$enTete .= insereJavaScript ("pages/include/javascript.js");

		// On insère ici le composant videobox pour afficher les vidéos	
		//		insereJavaScript ('pages/include/videobox/js/mootools.js');
		//		insereJavaScript ('pages/include/videobox/js/swfobject.js');
		//		insereJavaScript ('pages/include/videobox/js/videobox.js');


		// Utilisation de lightbox
		$enTete .= insereJavaScript ("pages/include/lightbox/js/prototype.js");
		$enTete .= insereJavaScript ("pages/include/lightbox/js/scriptaculous.js?load=effects,builder");
		$enTete .= insereJavaScript ("pages/include/lightbox/js/lightbox.js");

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
	// Menu affiché sur la barre horizontale
	$menu = array (
		// "actualité" => "index.php?page=$PAGEnews",
		"home" => "index.php?page=accueil.php",
		"articles" => "index.php?page=",
		"commentaires" => "index.php?page=commentairesliste.php",
		"albums" => "index.php?page=",
		"chansons" => "index.php?page=",
		//	"atelier accords" => "index.php?page=",
		//	"contacts" => "index.php?page=",
		"s'identifier" => "index.php?page=",
		"<img src='images/iconeRss.png'>" => "pages/rss.php");

	// Cette fonction affiche un pied de page
	function PiedDePage (){
		TblFinCellule();
		TblFinLigne ();
		TblFin();
		TblFinCellule();
		TblFinLigne ();		
		TblDebutLigne ();
		TblDebutCellule();
		$imgbarre = "images/barre.jpg";
		$enTete .= "<footer>" . Image ($imgbarre,"800",15) . "<br><DIV align ='center'>";
		$enTete .= Ancre ("index.php?page=articlesvoir.php&article=Contacts",Image ("images/icone_mail.png"),-1);
		$enTete .= Ancre ("http://www.myspace.com/arnal",Image ("images/myspace.png"),-1,1);
		$enTete .= Ancre ("http://youtube.com/arnalsauvage",Image ("images/youtube.png"),-1,1);
		$enTete .= Ancre ("http://www.arnalsauvage.com",Image ("images/icone_arnal.png"),-1,1);
		$enTete .= Ancre ("http://www.facebook.com/arnaud.medina",Image ("images/facebook.png"),-1,1);
		$enTete .= Ancre ("http://enavantlazizique.free.fr/wiki/index.php5?title=Accueil",Image ("images/wikipedia.png"),-1,1);		
		$enTete .= Ancre ("http://www.delicious.com/arnalsauvage",Image ("images/delicious.png"),-1,1);		
		$enTete .= Ancre ("http://www.goodreads.com/user/show/979367-arnalsauvage",Image ("images/icone_livre.png"),-1,1);
		$enTete .= Ancre ("http://fr.audiofanzine.com/membres/a.play,u.123722.html",Image ("images/audiofanzine.png"),-1,1);		
		$enTete .= Ancre ("https://sites.google.com/site/glashband/","*g*",-1,1);
		$enTete .= "</div></footer>";
		TblFinCellule();
		TblFinligne();
		TblFin();

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
	// Cette fonction retourne la vignette d'une image
	/*	function vignette ($image,$largeur)
	{
	global $iconePuce;
	global $cheminImages;
	if (file_exists($cheminImages."/".$image)==FALSE)
	$image = $iconePuce;
	// Si l'image est un PNG, appel à bouton PNG
	if ( stristr($image, '.png') != FALSE)
	return "pages/include/boutonpng.php?string=$image&largeur=$largeur";
	// Si l'image est un JPG, appel à bouton PNG
	if ( stristr($image, '.jpg') != FALSE)
	return "pages/include/boutonjpg.php?string=$image&largeur=$largeur";
	// Sinon affichage de la puce par défaut
	return "pages/include/boutonjpg.php?string=$iconePuce&largeur=$largeur'";
	}
	*/
}
?>