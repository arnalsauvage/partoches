<?php
const CHANSON = "chanson";
require_once("../chanson/Chanson.php");
require_once("../document/Document.php");
require_once("../lib/formulaire.php");
require_once("../lib/utilssi.php");
require_once("../navigation/menu.php");
require_once("../songbook/Songbook.php");
$table =  CHANSON;
$sortie = "";

// On lit les données dans le fichier ini
$fichier = "../../../conf/params.ini";
$ini_objet = new FichierIni ();
$ini_objet->m_load_fichier($fichier);
$cle = $ini_objet->m_valeur("cleGetSongBpm", "general") ?? '';

$urlRecherche = "https://api.getsongbpm.com/search/?api_key=CLE&type=both&lookup=song:CHANSONartist:ARTISTE";
$urlRecherche = str_replace("CLE", (string)$cle, $urlRecherche);
if (isset ($_GET ['chanson']) ) {
    $urlRecherche = str_replace("CHANSON", urlEncode($_GET ['chanson']), $urlRecherche);
}
if (isset ($_GET ['artiste']) ) {
    $urlRecherche = str_replace("ARTISTE", urlEncode($_GET ['artiste']), $urlRecherche);
}

$retour = "";
if ($cle) {
    echo "url appelée : " . $urlRecherche;
    $retour = @file_get_contents($urlRecherche);
}

$tableau = json_decode((string)$retour);
if ($retour && strlen($retour)>32) {
    $numero = 1;
    foreach ((array)$tableau as $value) {
        foreach ((array)$value as $enr) {
            echo "<br>-#$numero-----------------------------------------------------<br>";
            echo "Tempo :" . ($enr->tempo ?? 'N/A') . "<br>";
            echo "Mesure :" . ($enr->time_sig ?? 'N/A') . "<br>";
            echo "Tonalité :" . ($enr->key_of ?? 'N/A') . "<br>";
            $album = $enr->album ?? null;
            echo "Année :" . ($album->year ?? 'N/A') . "<br>";
            if (isset($album->img)) echo "image : <img src='" . $album->img . "'>";
            
            if (isset($_GET['idChanson'])) {
                echo "<a href='chanson_post.php?id=" . $_GET ['idChanson'] . "&tempo=" . urlencode($enr->tempo ?? '');
                echo "&mesure=".urlencode($enr->time_sig ?? "")."&tonalite=" . urlencode($enr->key_of ?? "") . "&annee= ". urlencode($album->year ?? "");
                if (isset($album->img)) echo "&image=".urlencode($album->img);
                echo "&mode=MAJ_SONGBPM'>enregistrer ces données</a>";
            }
        $numero++;
        }
    }
}
else{
    $chanson = htmlspecialchars($_GET['chanson'] ?? '');
    $artiste = htmlspecialchars($_GET['artiste'] ?? '');
    echo "Pas de résultat pour “" . $chanson . "“ par “" . $artiste . "“ 😒🤷‍♂️";
    echo "<br> <a href='https://getsongbpm.com'>Faire une recherche sur getsongbpm.com pour vérifier le nom de la chanson ou de l'interprète.</a>";
}