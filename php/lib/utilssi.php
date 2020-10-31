<?php
require_once '../vendor/autoload.php';
$a = session_id();
if (empty ($a)) {
    session_start();
}

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

unset ($a);
// function pc_process_dir ($nom_rep, $profondeur_max = 10, $profondeur = 0)
// function affichePlayer($mp3="vide")
// function ecritFichierLog($fichier, $log)
// function insereJavaScript ($source)
// function listeImages ()
// function boutonSuppression($lien,$iconePoubelle)

if (!isset ($FichierUtilsSi)) {
    // Déclaration des variables globales
    $FichierUtilsSi = 1;


    // Inclusion des différentes librairies
    require_once("FichierIni.php");
    require_once("compteur.php");
    require_once("configMysql.php");
    include_once "config-images.php";
    require_once("formulaire.php");
    require_once("html.php");
    require_once("mysql.php");
    include_once("params.php");
    include_once("table.php");
    require_once("vignette.php");
    require_once("Chiffrement.php");

    if (!isset ($_SESSION ["privilege"])) {
        $_SESSION ["privilege"] = 0;
    }

    // Cette fonction, pompée dans "PHP en action", p547, éditions O'Reilly
    // parcourt un sous-répertoire et exporte la liste des fichiers dans un tableau
    function pc_process_dir($nom_rep, $profondeur_max = 10, $profondeur = 0)
    {
        if ($profondeur >= $profondeur_max) {
            error_log("Profondeur maximum $profondeur_max atteinte dans $nom_rep.");
            return false;
        }
        $sous_repertoires = array();
        $fichiers = array();
        if (is_dir($nom_rep) && is_readable($nom_rep)) {
            $d = dir($nom_rep);
            while (false !== ($f = $d->read())) {
                // évite . et ..
                if (('.' == $f) || ('..' == $f)) {
                    continue;
                }
                if (is_dir("$nom_rep/$f")) {
                    array_push($sous_repertoires, "$nom_rep/$f");
                    // echo $f . "<BR>";
                } else {
                    array_push($fichiers, "$nom_rep/$f");
                    // echo $f . "<BR>";
                }
            }
            $d->close();
            foreach ($sous_repertoires as $sous_repertoire) {
                $fichiers = array_merge($fichiers, pc_process_dir($sous_repertoire, $profondeur_max, $profondeur + 1));
                // echo "$sous_repertoire <BR>";
            }
        }
        return $fichiers;
    }

    // Cette fonction affiche un player mp3 ou un lien vers le fichier en mode popup
    function affichePlayer($mp3 = "vide")
    {
        if (is_file("mp3/" . $mp3)) {
            if (substr($mp3, -3, 3) == "mp3") {
                $texte = ' <audio controls="controls" preload="none"> ' . '\n';
                $texte .= '<!-- On ouvre la balise audio, on affiche les controles et sans pre-charger les titres. -->\n';
                $texte .= '<source src="mp3/' . $mp3 . '" type="audio/mpeg"/>\n';
                // <!-- Apres avoir indique les chemins des deux fichiers, on charge le lecteur dewplayer.-->
                // <!-- Ce code ne sera execute que si le navigateur de votre visiteur ne prends pas en charge la balise audio. -->
                // <object type="application/x-shockwave-flash" data="/dew/dewplayer.swf" width="200" height="20" id="dewplayer" name="dewplayer">
                // <param name="wmode" value="transparent"/>
                // <param name="movie" value="/dew/dewplayer.swf"/>
                // <param name="flashvars" value="mp3=musique/titre.mp3&amp;autostart=1&amp;showtime=1"/>
                // </object>
                // </audio>
                $texte .= "<object type='application/x-shockwave-flash' data='mp3/dewplayer.swf?son=mp3/$mp3' width='200' height='20'> ";
                $texte .= "<param name='movie' value='mp3/dewplayer.swf?son=mp3/$mp3' /> </object>";
                $texte .= "</audio>";
                return $texte;
            } else {
                return (Ancre("mp3/" . $mp3, "ouvrir", -1, 1));
            }
        } else {
            // echo "fichier $mp3 non trouvé !!";
            return "";
        }
    }

    // Cette fonction écrit le $log dans le $fichier
    // TODO    ancien fichier de log à supprimer
    function ecritFichierLog($fichier, $log)
    {
        $time = date("l, j F Y [h:i a]");
        $ip = $_SERVER ['REMOTE_ADDR'];
        $fp = fopen("$fichier", "a");
        fputs($fp, "\n");
        fputs($fp, " 
			<table  
			<tr> 
			<td valign=top>< size=1>$ip</></td> <td valign=top width=10></td> 
			<td valign=top><t size=1>$time</t></td><td valign=top></td> 
			<td valign=top>< size=2>$log</></td><td valign=top></td> 		
			</tr></table><br>");
        // echo "Fichier : $fichier <br>\n";
        fclose($fp);
    }

    function insereJavaScript($source)
    {
        return "<script type='text/javascript' src='$source'></script>\n";
    }

    // Cette fonction retourne une liste des images disponibles sur le site, eventuellement dans un sous-dossier
    function listeImages($subDir = "")
    {
        $d = dir("../images" . $subDir);
        while (false !== ($entry = $d->read())) {
            if (($entry != ".") && ($entry != "..")) {
                // echo "<option " . "value=$entry> echo $ligne[1]" . "</option>";
                // echo "tableau[$compteur]=$entry <br>";
                $tableau [$entry] = $entry;
            }
        }
        $d->close();
        asort($tableau);
        return $tableau;
    }

    // Cette fonction retourne un bouton de suppression avec message de confirmation
    function boutonSuppression($lien, $iconePoubelle, $cheminImages)
    { // <img src="x.png" onclick="getattrs(this);">
        return "<img src='$cheminImages$iconePoubelle' width='16' alt='supprimer' onclick =\"confirmeSuppr('" . $lien . "','Voulez-vous vraiment supprimer cet élément ?');\" >";
    }

    /**
     * @return Logger
     */
    function init_logger()
    {
        $logger = new Logger('monLoggerA');
        $dateHeureMinute = date('Y-m-d') . '.log';
        // Niveaux de log dans
        switch ($GLOBALS['niveauDeLog']) {
            case "debug" :
                $GLOBALS['niveauDeLog'] = Logger::DEBUG;
            case "info" :
                $GLOBALS['niveauDeLog'] = Logger::INFO;
            case "warning" :
                $GLOBALS['niveauDeLog'] = Logger::WARNING;
            case "error" :
                $GLOBALS['niveauDeLog'] = Logger::ERROR;
        }
        $logger->pushHandler(new StreamHandler('../../logs/' . $dateHeureMinute, $GLOBALS['niveauDeLog']));
        echo "log dans le fichier " . '../../logs/' . $dateHeureMinute;
        return $logger;
    }
}