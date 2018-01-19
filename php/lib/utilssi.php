<?php
$a = session_id();
if(empty($a)) session_start();
unset ($a);
//	function pc_process_dir ($nom_rep, $profondeur_max = 10, $profondeur = 0)
//	function affichePlayer($mp3="vide")
//	function ecritFichierLog($fichier, $log)
//	function insereJavaScript ($source)
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
	include_once("lib/table.php");
	require_once ("lib/vignette.php");
        
	if(!isset ($_SESSION["privilege"])){
		// session_register("privilege");	 
		$_SESSION["privilege"] = 0;
	}

	// Cette fonction, pompée dans "PHP en action", p547, éditions O'Reilly
	// parcourt un sous-répertoire et exporte la liste des fichiers dans un tableau	
	function pc_process_dir ($nom_rep, $profondeur_max = 10, $profondeur = 0){
		if($profondeur >= $profondeur_max){
			error_log("Profondeur maximum $profondeur_max atteinte dans $nom_rep.");
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

	function insereJavaScript ($source){
		return "<script type='text/javascript' src='$source'></script>\n";
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

	// Cette fonction renvoie l'image vignette relative à une table et son id
	function imageTableId($table, $id)
	{
		$maRequete = "SELECT * FROM document WHERE document.idTable = '$id' AND document.nomTable='$table' ";
		$maRequete .= " AND ( document.nom LIKE '%.png' OR document.nom LIKE '%.jpg')";
		$result = $_SESSION ['mysql']->query($maRequete) or die ("Problème imageSongbook #1 : " . $_SESSION ['mysql']->error);
		if (empty($result)) {
			return ("");
		}
		// TODO : Choisit une vignette au hasard parmi les images
		// renvoie la ligne sélectionnée : id, nom, description, date , image, hits
		if (($ligne = $result->fetch_row()))
			return (composeNomVersion($ligne[1], $ligne[4]));
		else
			return ("");
	}
}
?>