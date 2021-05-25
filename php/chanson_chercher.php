<?php
const CHANSON = "chanson";
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("chanson.php");
require_once("document.php");
require_once("songbook.php");
require_once("lib/formulaire.php");
$table =  CHANSON;
$sortie = "";

// On lit les données dans le fichier ini
$fichier = "../conf/params.ini";
$ini_objet = new FichierIni ();
$ini_objet->m_fichier($fichier);
$cle = $ini_objet->m_valeur("cleGetSongBpm", "general");

$urlRecherche = "https://api.getsongbpm.com/search/?api_key=CLE&type=both&lookup=song:CHANSONartist:ARTISTE";
$urlRecherche = str_replace("CLE",$cle, $urlRecherche);
if (isset ($_GET ['chanson']) ) {
    $urlRecherche = str_replace("CHANSON", urlEncode($_GET ['chanson']), $urlRecherche);
}
if (isset ($_GET ['artiste']) ) {
    $urlRecherche = str_replace("ARTISTE", urlEncode($_GET ['artiste']), $urlRecherche);
}
echo ("url appelée : " . $urlRecherche);
$retour = file_get_contents($urlRecherche);

$tableau = json_decode($retour);
if (strlen($retour)>32) {
    $numero = 1;
    foreach ((array)$tableau as $value) {
        foreach ((array)$value as $enr) {
            // print_r($enr);

            echo "<br></br>#$numero-----------------------------------------------------<br>";
            echo "Tempo :" . $enr->tempo . "<br>";
            echo"Mesure :" . $enr->time_sig . "<br>";
            echo"Tonalité :" . $enr->key_of . "<br>";
            $album = $enr->album;
            echo "Année :" . $album->year . "<br>";
            echo "image : <img src='" . $album->img . "'>";
            echo "<a href='chanson_post.php?id=" . $_GET ['idChanson'] . "&tempo=" . urlencode($enr->tempo);
            echo "&mesure=".urlencode($enr->time_sig)."&tonalite=" . urlencode($enr->key_of) . "&annee= ". urlencode($album->year);
            echo "&image=".urlencode($album->img);
            echo "&mode=MAJ_SONGBPM'>enregistrer ces données</a>";
            //echo simplifieNomFichier("S'assoir.jpg");
        $numero++;
        }
    }
}
else{
    echo "Pas de résultat pour “" . $_GET ['chanson'] . "“ par “" . $_GET ['artiste'] . "“ 😒🤷‍♂️";
    echo "<br> <a href='https://getsongbpm.com'>Faire une recherche sur getsongbpm.com pour vérifier le nom de la chanson ou de l'interprête.</a>";
}