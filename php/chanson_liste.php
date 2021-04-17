<?php
const PRIVILEGE = 'privilege';
const CHANSON = "chanson";
const ORDRE_ASC = 'ordreAsc';
const TRI = 'tri';
const DATE_PUB = "datePub";
const CHERCHE = 'cherche';
const CENTRER = "centrer";
const VAL_FILTRE = "valFiltre";
const FILTRE = "filtre";
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("chanson.php");
require_once("document.php");
require_once("Pagination.php");
require_once("UtilisateurNote.php");

$chansonForm = "chanson_form.php";
$chansonPost = "chanson_post.php";
$chansonVoir = "chanson_voir.php";
$table = CHANSON;
$nombreChansonsParPage = 20;
$valeur_filtre = "";

global $cheminImages;
global $iconeEdit;
global $iconePoubelle;
global $contenuHtmlCompCherche;

$contenuHtml = "<div class='container'> \n
  <div class='starter-template'> \n";

$contenuHtml .= entreBalise("Chansons", "H1");

if (isset($_GET['filtre'])){
    $contenuHtml .= "<p class='filtres'> filtre présent : " . $_GET['filtre'] . " = " . $_GET['valFiltre'];
    $contenuHtml .= " " . Ancre($_SERVER['PHP_SELF'],"effacer le filtre") . "</p>";
}

if (isset($_GET['raz-recherche'])){
    $_SESSION[CHERCHE] = "";
}


// Gestion du paramètre de tri
// On prend en compte une demande de tri ascendant
if (isset ($_GET [TRI]) ) {
    $_SESSION[TRI] = $_GET [TRI];
    $_SESSION[ORDRE_ASC] = true;
    // echo "session tri = get tro = " . $_SESSION['tri'] = $_GET ['tri'];
} // On prend en compte une demande de tri descendant
else {
    if (isset ($_GET ['triDesc'])) {
        $_SESSION[TRI] = $_GET ['triDesc'];
        $_SESSION[ORDRE_ASC] = false;
        // echo "session tri desc = get tro = " . $_SESSION['tri'] = $_GET ['triDesc'];
    } // Sinon, on installe le tri par date dégressif
    else {
        if (!isset ($_SESSION[TRI])) {
            $_SESSION[TRI] = DATE_PUB;
            $_SESSION[ORDRE_ASC] = false;
            // echo "tri par défaut ";
        }
    }
}

// Gestion du parametre nonVote
if (isset ($_GET ['nonVote'])) {
    $critere_cherche = "%";
}

// Gestion du parametre filtre
        $filtre = "";
if (isset ($_GET [FILTRE])) {
    if ($_GET [FILTRE]=='contributeur'){
        $filtre = "contributeur";
        $valeur_filtre  = $_GET [VAL_FILTRE];
    }
    if ($_GET [FILTRE]=='tempo'||$_GET [FILTRE]=='mesure'||$_GET [FILTRE]=='tonalite'||$_GET [FILTRE]=='pulsation'||$_GET [FILTRE]=='annee'||$_GET [FILTRE]=='interprete'){
        $filtre = $_GET [FILTRE];
        $valeur_filtre  = $_GET [VAL_FILTRE];
    }
}

// Gestion paramètres de recherche
if (isset ($_POST [CHERCHE])) {

    $_SESSION[CHERCHE] = $_POST[CHERCHE];
} else {
    if (!isset($_SESSION[CHERCHE])) {
        $_SESSION[CHERCHE] = "";
    }
}

if ($_SESSION[CHERCHE] != "") {
    $critere_cherche = "%" . $_SESSION[CHERCHE] . "%";
} else {
    $critere_cherche = "%";
}

// Gestion razFiltres
if (isset ($_GET ['razFiltres'])) {
    $_SESSION[TRI] = DATE_PUB;
    $_SESSION[ORDRE_ASC] = false;
    $_SESSION[CHERCHE] = "";
    $critere_cherche = "%";
}

// echo " Recherche = " . $critere_cherche;

// Chargement de la liste des chansons
$resultat = Chanson::chercheChansons($critere_cherche, $_SESSION[TRI], $_SESSION[ORDRE_ASC], $filtre, $valeur_filtre);
$nbreChansons = count($resultat);
$numligne = 0;

// Gestion de la pagination
$pagination = new Pagination ($nbreChansons, $nombreChansonsParPage);
if (isset ($_GET['page']) && is_numeric($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}
$pagination->setPageEnCours($page);

// Affichage de la liste
$largeur_ecran  = $_SESSION['largeur-fenetre'];

// $contenuHtml .= "Largeur d'écran : " . $largeur_ecran;
// >1200 on affiche tout
// >740 on retire tempo mesure pulsation tonalité
// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION [PRIVILEGE] > 1) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'></span> Ajouter une chanson</a>\n";
}
// //////////////////////////////////////////////////////////////////////ADMIN

$contenuHtml .= TblDebut();
$contenuHtml .= TblEnteteDebut() . TblDebutLigne();
$contenuHtml .= TblEntete("  -  ");
$contenuHtml .= TblEntete("  Pochette  ");
$contenuHtml .= titreColonne("Nom", "nom");
$contenuHtml .= titreColonne("Interprète", "interprete");
if ($largeur_ecran > 700) {
    $contenuHtml .= titreColonne("  Votes  ", "votes");
    $contenuHtml .= titreColonne("Année", "annee");
}
if ($largeur_ecran >1200) {
    $contenuHtml .= titreColonne("Tempo", "tempo");
    $contenuHtml .= titreColonne("Mesure", "mesure");
    $contenuHtml .= titreColonne("Pulsation", "pulsation");
    $contenuHtml .= titreColonne("Tonalité", "tonalite");
}
if ($largeur_ecran > 700) {
    $contenuHtml .= titreColonne("Date pub.", DATE_PUB);
    $contenuHtml .= titreColonne("Publié par", "idUser");
}
if ($largeur_ecran >1200) {

    $contenuHtml .= titreColonne("Vues", "hits");
}
// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
if ($_SESSION [PRIVILEGE] > 1) {
    $contenuHtml .= titreColonne("", "");

}
// //////////////////////////////////////////////////////////////////////ADMIN
$contenuHtml .= TblFinLigne() . TblEnteteFin();
$contenuHtml .= TblCorpsDebut();

global $_DOSSIER_CHANSONS;

$cheminImagesChanson = "../".$_DOSSIER_CHANSONS;
$_chanson = new Chanson();
$maNote = new UtilisateurNote(0, 1, 1, 1);

/** @noinspection PhpUndefinedMethodInspection */
foreach ($resultat as $ligne) {
    $numligne++;
    if (($numligne < $pagination->getItemDebut()) || $numligne > $pagination->getItemFin()) {
        continue;
    }

    $contenuHtml .= TblDebutLigne();

    $_chanson->chercheChanson($ligne);
    $_id = $_chanson->getId();
    if ((isset ($_GET ['nonVote'])) && is_numeric($_GET ['nonVote']) && ($maNote->chercheNoteUtilisateur($_SESSION['id'], CHANSON, $_id) == 1)) {
        echo "session id: " . $_SESSION['id'];
        $nbreChansons--;
        continue;
    }

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton modifier
    if ($_SESSION [PRIVILEGE] > 1)
    {
        $contenuHtml .= TblCellule(Ancre("$chansonForm?id=" . $_id, Image($cheminImages . $iconeEdit, 32, 32)));
    }
    else
    {
        $contenuHtml .= TblCellule(" ");
    }
    // TODO Supprimer les parametres filtres existant dans l'url pour les liens avec filtre !
    $imagePochette = Image(($cheminImagesChanson . $_id . "/" . rawurlencode(imageTableId(CHANSON, $_id))), 48, 48, "couverture");
    $contenuHtml .= TblCellule(Ancre("$chansonVoir?id=$_id", $imagePochette));
    $contenuHtml .= TblCellule(Ancre("$chansonVoir?id=$_id", entreBalise(limiteLongueur($_chanson->getNom(), 21), "EM"))); // Nom
    $url = $_SERVER['REQUEST_URI'];
    $contenuHtml .= TblCellule(Ancre($pagination->urlAjouteParam($url, FILTRE ."=interprete&" .  VAL_FILTRE . "=" .urlencode($_chanson->getInterprete())),limiteLongueur($_chanson->getInterprete(),21))); // interprete
    if ($largeur_ecran >700) {
        if ($_SESSION [PRIVILEGE] > 0) {
            $contenuHtml .= TblCellule(UtilisateurNote::starBarUtilisateur(CHANSON, $_id, 5, 25), 1, 1, CENTRER);
        } else {
            $contenuHtml .= TblCellule(UtilisateurNote::starBar(CHANSON, $_id, 5, 25), 1, 1, CENTRER);
        }
    }
    if ($largeur_ecran >700) {
        $contenuHtml .= TblCellule(Ancre($pagination->urlAjouteParam($url, FILTRE ."=annee&" .  VAL_FILTRE . "=" .$_chanson->getAnnee()), $_chanson->getAnnee()),1, 1, CENTRER); // annee
    }

    if ($largeur_ecran >1200) {
        $contenuHtml .= TblCellule(Ancre($pagination->urlAjouteParam($url, FILTRE ."=tempo&" .  VAL_FILTRE . "=" .$_chanson->getTempo()), $_chanson->getTempo()), 1, 1, "alignerAdroite"); // tempo
        $contenuHtml .= TblCellule(Ancre($pagination->urlAjouteParam($url, FILTRE ."=mesure&" .  VAL_FILTRE . "=" .$_chanson->getMesure()), $_chanson->getMesure()), 1, 1, CENTRER); // mesure
        $contenuHtml .= TblCellule(Ancre($pagination->urlAjouteParam($url, FILTRE ."=pulsation&" .  VAL_FILTRE . "=" .$_chanson->getPulsation()), $_chanson->getPulsation()), 1, 1, CENTRER); // pulsation
        $contenuHtml .= TblCellule(Ancre($pagination->urlAjouteParam($url, FILTRE ."=tonalite&" .  VAL_FILTRE . "=" .$_chanson->getTonalite()), $_chanson->getTonalite()), 1, 1, CENTRER); // tonalité
    }
    if ($largeur_ecran >700) {
        $contenuHtml .= TblCellule(dateMysqlVersTexte($_chanson->getDatePub())); // Date Pub
        $nomAuteur = chercheUtilisateur($_chanson->getIdUser());
        $nomAuteur = $nomAuteur[3];
        $contenuHtml .= TblCellule(Ancre($pagination->urlAjouteParam($url,FILTRE . "=contributeur&" . VAL_FILTRE . "=" . $_chanson->getIdUser()), $nomAuteur), 1, 1,CENTRER); // auteur
        $contenuHtml .= TblCellule($_chanson->getHits(), 1, 1, "alignerAdroite"); // hits
    }
    // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
    if ($_SESSION [PRIVILEGE] > 1) {
        $contenuHtml .= TblCellule(boutonSuppression($chansonPost . "?id=$_id&mode=SUPPR", $iconePoubelle, $cheminImages));
        // //////////////////////////////////////////////////////////////////////ADMIN
    }
    $contenuHtml .= TblFinLigne();
}
$contenuHtml .= TblCorpsFin();
$contenuHtml .= TblFin();
$contenuHtml .= $pagination->barrePagination() . "   ";
$contenuHtml .= $nbreChansons . " chanson(s) dans la liste.<br>\n";
if ($nbreChansons == 0) {
    $contenuHtml .= "Pas de résultat ... <BR><a href='?razFiltres' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Supprimer les filtres et tris</a>\n";
}
// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter
if ($_SESSION [PRIVILEGE] > 1) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Ajouter une chanson</a>\n";
}
// //////////////////////////////////////////////////////////////////////ADMIN

// Affichage de la recherche
require_once("chanson-v-comp-cherche.php");
$contenuHtml .= $contenuHtmlCompCherche;
$contenuHtml .= "
</div>\n
</div><!-- /.container -->\n";
$contenuHtml .= envoieFooter();
echo $contenuHtml;

function titreColonne($libelle, $nomRubrique)
{
    $lienCroissant = Ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> </span>");
    $lienDecroissant = Ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'> </span>");
    return TblEntete($lienCroissant . "  $libelle " . $lienDecroissant);
}