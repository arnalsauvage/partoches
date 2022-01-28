<?php
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("playlist.php");
require_once("document.php");
require_once("lienChansonPlaylist.php");
$table = "playlist";
$sortie = "";

// Cette page est dupliquée depuis songbook_form...

// Si l'utilisateur n'est pas authentifié (compte invité) ou n'a pas le droit de modif, on le redirige vers la page _voir
if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"] ) {
    $urlRedirection = $table . "_voir.php";
    if (isset ($_GET ['id']) && is_numeric($_GET['id']))
        $urlRedirection .= "?id=" . $_GET ['id'];
    redirection($urlRedirection);
}

// Traitement de l'ajout de chanson
if (isset ($_POST ['id']) && (isset ($_POST ['chanson']))) {

    $id = $_POST ['id'];
    ordonneLiensPlaylist($id);
    creelienChansonPlaylist($_POST ['chanson'], $_POST ['id']);
}

// Chargement des donnees de la playlist si l'identifiant est fourni
if (isset ($_GET ['id']) && is_numeric($_GET ['id'])) {
    if (isset ($_GET ['id']))
        $id = $_GET ['id'];
    $donnee = chercheplaylist($id);
    $donnee [1] = htmlspecialchars($donnee [1]);
    $donnee [2] = htmlspecialchars($donnee [2]);
    $donnee [3] = dateMysqlVersTexte($donnee [3]);
//	$donnee [4] = $donnee [4];
//	$donnee [5] = $donnee [5];
    $mode = "MAJ";
    ordonneLiensPlaylist($id);
} else {
    $mode = "INS";
    $donnee [0] = 0;
    $donnee [1] = "";
    $donnee [2] = "";
    $donnee [3] = "01/01/1970";
    $donnee [4] = "";
    $donnee [5] = 0;
}

if ($mode == "MAJ")
    $sortie .= "<H1> Mise à jour - " . $table . "</H1>";
if ($mode == "INS")
    $sortie .= "<H1> Création - " . $table . "</H1>";

$sortie .= "<div class = 'centrer'>";
// Création du formulaire
$f = new Formulaire ("POST", $table . "_get.php", $sortie);
$f->champCache("id", $donnee [0]);
// TODO : La longueur du champ n'est pas prise en compte dans formulaire!
$f->champTexte("Nom :", "fnom", $donnee [1], 64, 128);
$f->champTexte("Description :", "fdescription", $donnee [2], 64, 128);
$f->champTexte("Date :", "fdate", $donnee [3], 10, 10);
$f->champTexte("Image :", "fimage", $donnee [4], 64, 64);
$f->champTexte("Hits :", "fhits", $donnee [5], 10, 10);
$f->champCache("mode", $mode);
$f->champValider(" Valider ", "valider");
$sortie .= $f->fin();

if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    // On verrouille les champs hits, date publication
    $sortie = str_replace("NAME='fdate'", "NAME='fdate' disabled='disabled' ", $sortie);
    $sortie = str_replace("NAME='fhits'", "NAME='fhits' disabled='disabled' ", $sortie);
}

echo $sortie;
if ($mode == "MAJ") {
    ?>

    </div>
    <?php
}
echo envoieFooter();
?>