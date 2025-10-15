<?php

const PRIVILEGE = 'privilege';
const CHANSON = "chanson";
const ORDRE_ASC = 'ordreAsc';
const TRI = 'tri';
const TRIDESC = 'triDesc';
const DATE_PUB = "datePub";
const CHERCHE = 'cherche';
const CENTRER = "centrer";
const VAL_FILTRE = "valFiltre";
const FILTRE = "filtre";
require_once "../lib/utilssi.php";
require_once "../lib/Pagination.php";
require_once "chanson.php";
require_once "../document/document.php";
require_once "../navigation/menu.php";
require_once "../note/UtilisateurNote.php";

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

function logueRecherche($critereCherche): void
{
    $date = new DateTime();
    $myfile = fopen("logRecherche.txt", "a");
    $txt = $date->format('Y-m-d--H-i') . " : " . $critereCherche . "\n";
    fwrite($myfile, $txt);
    fclose($myfile);
}

$contenuHtml = "<div class='container'> \n
  <div class='starter-template'> \n";

$contenuHtml .= entreBalise("Chansons", "H1");

if (!isset($_SESSION[TRI])) {
    $_SESSION[TRI] = DATE_PUB;
}
if (!isset($_SESSION[ORDRE_ASC])) {
    $_SESSION[ORDRE_ASC] = false;
}
if (!isset($_SESSION[CHERCHE])) {
    $_SESSION[CHERCHE] = '';
}

if (isset($_GET['filtre'])) {
    $filtre = filtreGetPost($_GET, FILTRE);
    $valeur_filtre = filtreGetPost($_GET, VAL_FILTRE);
    $contenuHtml .= "<p class='filtres'> filtre présent : $filtre = $valeur_filtre";
    $contenuHtml .= " " . Ancre($_SERVER['PHP_SELF'], "effacer le filtre") . "</p>";
}

if (isset($_GET['raz-recherche'])) {
    $_SESSION[CHERCHE] = "";
}

// Gestion du paramètre de tri
// On prend en compte une demande de tri ascendant
if (isset ($_GET [TRI]) || isset ($_GET [TRIDESC])) {
    // Gestion du paramètre de tri
    $triAsc = filtreGetPost($_GET, 'tri');
    $triDesc = filtreGetPost($_GET, 'triDesc');

    if ($triAsc !== null) {
        // Tri ascendant explicite
        $_SESSION[TRI] = $triAsc;
        $_SESSION[ORDRE_ASC] = true;
    } elseif ($triDesc !== null) {
        // Tri descendant explicite
        $_SESSION[TRI] = $triDesc;
        $_SESSION[ORDRE_ASC] = false;
    } elseif (!isset($_SESSION[TRI])) {
        // Tri par défaut (date décroissante)
        $_SESSION[TRI] = DATE_PUB;
        $_SESSION[ORDRE_ASC] = false;
    }

}

// Gestion du parametre nonVote
if (isset ($_GET ['nonVote'])) {
    $critere_cherche = "%";
}

// Gestion du parametre filtre
$filtre = "";
if (isset ($_GET [FILTRE])) {
    // Gestion du paramètre de filtre
    $filtreGet = filtreGetPost($_GET, FILTRE);
    $valeurGet = filtreGetPost($_GET, VAL_FILTRE);

    $filtre = null;
    $valeur_filtre = null;

    $filtres_valides = ['contributeur', 'tempo', 'mesure', 'tonalite', 'pulsation', 'annee', 'interprete'];

    if ($filtreGet !== null && in_array($filtreGet, $filtres_valides, true)) {
        $filtre = $filtreGet;
        $valeur_filtre = $valeurGet;
    }
}

// Gestion paramètres de recherche
if (isset ($_POST [CHERCHE])) {
    // Gestion du champ de recherche
    $recherche = filtreGetPost($_POST, CHERCHE);

    // Nettoyage minimal pour le log
    $recherche_log = strip_tags($recherche); // Supprime tout code HTML

    if ($recherche !== null) {
        logueRecherche($recherche);
        $_SESSION[CHERCHE] = $recherche;
    } else {
        if (!isset($_SESSION[CHERCHE])) {
            $_SESSION[CHERCHE] = "";
        }
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

// Affichage de la recherche
require_once "chanson-v-comp-cherche.php";
$contenuHtml .= $contenuHtmlCompCherche;

// Affichage de la liste
$largeur_ecran = $_SESSION['largeur-fenetre'];

// $contenuHtml .= "Largeur d'écran : " . $largeur_ecran;
// >1200 on affiche tout
// >740 on retire tempo mesure pulsation tonalité
// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION [PRIVILEGE] > $GLOBALS["PRIVILEGE_INVITE"]) {
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
    // Pour les privileges > INVITE, on affiche les colonnes votes et annee
    if ($_SESSION [PRIVILEGE] > $GLOBALS["PRIVILEGE_INVITE"]) {
        $contenuHtml .= titreColonne("  Votes  ", "votes");
    }
    $contenuHtml .= titreColonne("Année", "annee");
}
if ($largeur_ecran > 1200) {
    $contenuHtml .= titreColonne("Tempo", "tempo");
    $contenuHtml .= titreColonne("Mesure", "mesure");
    $contenuHtml .= titreColonne("Pulsation", "pulsation");
    $contenuHtml .= titreColonne("Tonalité", "tonalite");
}
if ($largeur_ecran > 700) {
    $contenuHtml .= titreColonne("Date pub.", DATE_PUB);
    $contenuHtml .= titreColonne("Publié par", "idUser");
    $contenuHtml .= titreColonne("Vues", "hits");
}
// //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
if ($_SESSION [PRIVILEGE] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $contenuHtml .= tblEntete("action");
}
// //////////////////////////////////////////////////////////////////////ADMIN
$contenuHtml .= TblFinLigne() . TblEnteteFin();
$contenuHtml .= TblCorpsDebut();

global $_DOSSIER_CHANSONS;

$_RACINE = "../../";
$cheminImagesChanson = $_RACINE . $_DOSSIER_CHANSONS;
$_chanson = new Chanson();
$maNote = new UtilisateurNote(0, 1, 1, 1);


function celluleFiltrable($libelle, $cle, $valeur, $alignement = '', $longueurMax = null)
{
    global $pagination;
    $url = $_SERVER['REQUEST_URI'];
    $texte = $longueurMax ? limiteLongueur($libelle, $longueurMax) : $libelle;
    $urlFiltre = $pagination->urlAjouteParam($url, FILTRE . "=$cle&" . VAL_FILTRE . "=" . urlencode($valeur));
    return TblCellule(Ancre($urlFiltre, $texte), 1, 1, $alignement);
}

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
    if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
        $_image = Image($cheminImages . $iconeEdit, 32, 32);
        $_ancre = Ancre("$chansonForm?id=" . $_id, $_image, -1, -1, "modifier la chanson");
        $contenuHtml .= TblCellule($_ancre);
    } else {
        $contenuHtml .= TblCellule(" ");
    }
    // TODO Supprimer les parametres filtres existant dans l'url pour les liens avec filtre !
    $imagePochette = Image(($cheminImagesChanson . $_id . "/" . rawurlencode(imageTableId(CHANSON, $_id))), 48, 48, "couverture");
    $contenuHtml .= TblCellule(Ancre("$chansonVoir?id=$_id", $imagePochette));
    $contenuHtml .= TblCellule(Ancre("$chansonVoir?id=$_id", entreBalise(limiteLongueur($_chanson->getNom(), 21), "EM"), -1, -1, $_chanson->getNom())); // Nom
    $url = $_SERVER['REQUEST_URI'];

    $contenuHtml .= celluleFiltrable($_chanson->getInterprete(), "interprete", $_chanson->getInterprete(), '', 21); // interprete

    if ($largeur_ecran > 700) {
        if ($_SESSION[PRIVILEGE] >= $GLOBALS["PRIVILEGE_ADMIN"]) {
            $contenuHtml .= TblCellule(UtilisateurNote::starBar(CHANSON, $_id, 5, 25), 1, 1, CENTRER);
        } else {
            if ($_SESSION[PRIVILEGE] > $GLOBALS["PRIVILEGE_INVITE"]) {
                $contenuHtml .= TblCellule(UtilisateurNote::starBarUtilisateur(CHANSON, $_id, 5, 25), 1, 1, CENTRER);
            }
        }
    }

    if ($largeur_ecran > 700) {
        $contenuHtml .= celluleFiltrable($_chanson->getAnnee(), "annee", $_chanson->getAnnee(), CENTRER); // annee
    }

    if ($largeur_ecran > 1200) {
        $contenuHtml .= celluleFiltrable($_chanson->getTempo(), "tempo", $_chanson->getTempo(), "alignerAdroite"); // tempo
        $contenuHtml .= celluleFiltrable($_chanson->getMesure(), "mesure", $_chanson->getMesure(), CENTRER); // mesure
        $contenuHtml .= celluleFiltrable($_chanson->getPulsation(), "pulsation", $_chanson->getPulsation(), CENTRER); // pulsation
        $contenuHtml .= celluleFiltrable($_chanson->getTonalite(), "tonalite", $_chanson->getTonalite(), CENTRER); // tonalité
    }

    if ($largeur_ecran > 700) {
        $contenuHtml .= TblCellule(dateMysqlVersTexte($_chanson->getDatePub())); // Date Pub
        $nomAuteur = chercheUtilisateur($_chanson->getIdUser());
        $nomAuteur = $nomAuteur[3];
        $contenuHtml .= celluleFiltrable($nomAuteur, "contributeur", $_chanson->getIdUser(), CENTRER); // auteur
        $contenuHtml .= TblCellule($_chanson->getHits(), 1, 1, "alignerAdroite"); // hits
    }

    // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
    if ($_SESSION [PRIVILEGE] >= $GLOBALS["PRIVILEGE_ADMIN"]) {
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
if ($_SESSION [PRIVILEGE] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Ajouter une chanson</a>\n";
}
// //////////////////////////////////////////////////////////////////////ADMIN

$contenuHtml .= "
</div>\n
</div><!-- /.container -->\n";
$contenuHtml .= envoieFooter();
echo $contenuHtml;

function titreColonne($libelle, $nomRubrique): string
{
    $lienCroissant = "<button onclick=\"window.location.href='?tri=$nomRubrique'\" title='tri croissant par $nomRubrique'>
                        <span class='glyphicon glyphicon-chevron-up'> </span>
                        </button>";
    $lienDecroissant = "<button onclick=\"window.location.href='?triDesc=$nomRubrique'\" title='tri décroissant par $nomRubrique'>
                        <span class='glyphicon glyphicon-chevron-down'> </span>
                        </button>";
    return TblEntete($lienCroissant . "  $libelle " . $lienDecroissant);
}
