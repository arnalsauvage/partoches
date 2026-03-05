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

// 1. Initialisation des sessions par défaut
if (!isset($_SESSION[TRI])) $_SESSION[TRI] = DATE_PUB;
if (!isset($_SESSION[ORDRE_ASC])) $_SESSION[ORDRE_ASC] = false;
if (!isset($_SESSION[CHERCHE])) $_SESSION[CHERCHE] = '';

// 2. Gestion du tri (Bascule automatique)
if (isset($_GET[TRI])) {
    $nouveauTri = filtreGetPost($_GET, TRI);
    if ($_SESSION[TRI] === $nouveauTri) {
        $_SESSION[ORDRE_ASC] = !$_SESSION[ORDRE_ASC];
    } else {
        $_SESSION[TRI] = $nouveauTri;
        $_SESSION[ORDRE_ASC] = true;
    }
} elseif (isset($_GET[TRIDESC])) {
    $_SESSION[TRI] = filtreGetPost($_GET, TRIDESC);
    $_SESSION[ORDRE_ASC] = false;
}

// 3. Gestion de la recherche (Globale)
if (isset($_POST[CHERCHE])) {
    $recherche = strip_tags(filtreGetPost($_POST, CHERCHE));
    if ($recherche !== null) {
        logueRecherche($recherche);
        $_SESSION[CHERCHE] = $recherche;
    }
}
if (isset($_GET['raz-recherche']) || isset($_GET['razFiltres'])) {
    $_SESSION[CHERCHE] = "";
}

$critere_cherche = ($_SESSION[CHERCHE] != "") ? "%" . $_SESSION[CHERCHE] . "%" : "%";

// 4. Gestion des filtres spécifiques (Interprète, Année, etc.)
if (!isset($_SESSION[FILTRE])) $_SESSION[FILTRE] = "";
if (!isset($_SESSION[VAL_FILTRE])) $_SESSION[VAL_FILTRE] = "";

if (isset($_GET[FILTRE])) {
    $filtreGet = filtreGetPost($_GET, FILTRE);
    $valeurGet = filtreGetPost($_GET, VAL_FILTRE);
    $filtres_valides = ['contributeur', 'tempo', 'tempo_famille', 'mesure', 'tonalite', 'pulsation', 'annee', 'interprete'];

    if ($filtreGet !== null && in_array($filtreGet, $filtres_valides, true)) {
        $_SESSION[FILTRE] = $filtreGet;
        $_SESSION[VAL_FILTRE] = $valeurGet;
    }
}

// Récupération des filtres depuis la session (pour persistance)
$filtre = $_SESSION[FILTRE];
$valeur_filtre = $_SESSION[VAL_FILTRE];

if ($filtre <> "" && $valeur_filtre <> "") {
    $contenuHtml .= "<div class='alert alert-info alert-dismissible' role='alert'>
        <a href='?razFiltres' class='close' aria-label='Close' style='text-decoration: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 24px;'>&times;</a>
        Filtre actif : <strong>" . htmlspecialchars($filtre) . "</strong> = <strong>" . htmlspecialchars($valeur_filtre) . "</strong>
    </div>";
}

// 5. Remise à zéro totale
if (isset($_GET['razFiltres'])) {
    $_SESSION[TRI] = DATE_PUB;
    $_SESSION[ORDRE_ASC] = false;
    $_SESSION[CHERCHE] = "";
    $_SESSION[FILTRE] = "";
    $_SESSION[VAL_FILTRE] = "";
    $critere_cherche = "%";
    $filtre = "";
    $valeur_filtre = "";
}

// 6. Pagination et Chargement des données
if (isset($_GET['debug']) && estAdmin()) {
    $maRequeteDebug = "SELECT chanson.id FROM chanson";
    // Simulation du début de la requete comme dans Chanson::chercheChansons
    $nbreChansonsTotalDebug = Chanson::compteChansons($critere_cherche, $filtre, $valeur_filtre);
    $contenuHtml .= "<div class='alert alert-warning'>DEBUG : critere='$critere_cherche', filtre='$filtre', valeur='$valeur_filtre' -> $nbreChansonsTotalDebug résultats.</div>";
}

$nbreChansonsTotal = Chanson::compteChansons($critere_cherche, $filtre, $valeur_filtre);
$pagination = new Pagination($nbreChansonsTotal, $nombreChansonsParPage);
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
$pagination->setPageEnCours($page);

$offset = ($page - 1) * $nombreChansonsParPage;
$resultatIds = Chanson::chercheChansons($critere_cherche, $_SESSION[TRI], $_SESSION[ORDRE_ASC], $filtre, $valeur_filtre, $nombreChansonsParPage, $offset);

// --- AFFICHAGE ---

require_once "chanson-v-comp-cherche.php";
$contenuHtml .= $contenuHtmlCompCherche;

$largeur_ecran = $_SESSION['largeur-fenetre'];

if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'></span> Ajouter une chanson</a>\n";
}

$contenuHtml .= TblDebut();
$contenuHtml .= TblEnteteDebut() . TblDebutLigne();
$contenuHtml .= TblEntete("  -  ") . TblEntete("  Pochette  ");
$contenuHtml .= titreColonne("Nom", "nom");
$contenuHtml .= titreColonne("Interprète", "interprete");

if ($largeur_ecran > 700) {
    if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) $contenuHtml .= titreColonne("  Votes  ", "votes");
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
if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) $contenuHtml .= tblEntete("action");

$contenuHtml .= TblFinLigne() . TblEnteteFin() . TblCorpsDebut();

$_RACINE = "../../";
$cheminImagesChanson = $_DOSSIER_CHANSONS;
$_chanson = new Chanson();

function celluleFiltrable($libelle, $cle, $valeur, $alignement = '', $longueurMax = null)
{
    global $pagination;
    $url = $_SERVER['REQUEST_URI'];
    $texte = $longueurMax ? limiteLongueur($libelle, $longueurMax) : $libelle;
    
    // On repart à la page 1 quand on change de filtre
    $urlSansPage = $pagination->retirerParametreUrl("page");
    $urlFiltre = $pagination->urlAjouteParam($urlSansPage, FILTRE . "=$cle&" . VAL_FILTRE . "=" . urlencode($valeur));
    
    return TblCellule(ancre($urlFiltre, $texte), 1, 1, $alignement);
}

foreach ($resultatIds as $idChanson) {
    $contenuHtml .= TblDebutLigne();
    $_chanson->chercheChanson($idChanson);
    $_id = $_chanson->getId();

    if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
        $_image = image($cheminImages . $iconeEdit, 32, 32);
        $contenuHtml .= TblCellule(ancre("$chansonForm?id=" . $_id, $_image, -1, -1, "modifier la chanson"));
    } else {
        $contenuHtml .= TblCellule(" ");
    }
    
    $nomImage = imageTableId(CHANSON, $_id);
    $imagePochette = affichePochette($nomImage, $_id, 48, 48);
    $contenuHtml .= TblCellule(ancre("$chansonVoir?id=$_id", $imagePochette));
    $contenuHtml .= TblCellule(ancre("$chansonVoir?id=$_id", entreBalise(limiteLongueur($_chanson->getNom(), 21), "EM"), -1, -1, $_chanson->getNom()));
    $contenuHtml .= celluleFiltrable($_chanson->getInterprete(), "interprete", $_chanson->getInterprete(), '', 21);

    if ($largeur_ecran > 700) {
        if (estAdmin()) {
            $contenuHtml .= TblCellule(UtilisateurNote::starBar(CHANSON, $_id, 5, 25), 1, 1, CENTRER);
        } elseif (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
            $contenuHtml .= TblCellule(UtilisateurNote::starBarUtilisateur(CHANSON, $_id, 5, 25), 1, 1, CENTRER);
        }
    }

    if ($largeur_ecran > 700) $contenuHtml .= celluleFiltrable($_chanson->getAnnee(), "annee", $_chanson->getAnnee(), CENTRER);

    if ($largeur_ecran > 1200) {
        $contenuHtml .= celluleFiltrable($_chanson->getTempo(), "tempo", $_chanson->getTempo(), "alignerAdroite");
        $contenuHtml .= celluleFiltrable($_chanson->getMesure(), "mesure", $_chanson->getMesure(), CENTRER);
        $contenuHtml .= celluleFiltrable($_chanson->getPulsation(), "pulsation", $_chanson->getPulsation(), CENTRER);
        $contenuHtml .= celluleFiltrable($_chanson->getTonalite(), "tonalite", $_chanson->getTonalite(), CENTRER);
    }

    if ($largeur_ecran > 700) {
        $contenuHtml .= TblCellule(dateMysqlVersTexte($_chanson->getDatePub()));
        $nomAuteur = chercheUtilisateur($_chanson->getIdUser());
        $contenuHtml .= celluleFiltrable($nomAuteur[3], "contributeur", $_chanson->getIdUser(), CENTRER);
        $contenuHtml .= TblCellule($_chanson->getHits(), 1, 1, "alignerAdroite");
    }

    if (estAdmin()) {
        $contenuHtml .= TblCellule(boutonSuppression($chansonPost . "?id=$_id&mode=SUPPR", $iconePoubelle, $cheminImages));
    }
    $contenuHtml .= TblFinLigne();
}

$contenuHtml .= TblCorpsFin() . TblFin();

// Affichage du compteur et de la pagination sur la même ligne
$contenuHtml .= "<div style='margin: 20px 0;'>";
$contenuHtml .= "<strong>" . $nbreChansonsTotal . " chanson(s) dans la liste.</strong> ";
$contenuHtml .= $pagination->barrePagination();
$contenuHtml .= "</div>";

if ($nbreChansonsTotal == 0) {
    $contenuHtml .= "Pas de résultat ... <BR><a href='?razFiltres' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Supprimer les filtres et tris</a>\n";
}

if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
    $contenuHtml .= "<BR><a href='$chansonForm' class='btn btn-lg btn-default'><span class='glyphicon glyphicon-plus'> </span> Ajouter une chanson</a>\n";
}

$contenuHtml .= "</div></div>\n";
$contenuHtml .= envoieFooter();
echo $contenuHtml;

function titreColonne($libelle, $nomRubrique): string
{
    $icone = "";
    if ($_SESSION[TRI] === $nomRubrique) {
        $icone = " <span class='glyphicon glyphicon-chevron-" . ($_SESSION[ORDRE_ASC] ? "up" : "down") . "'></span>";
    }
    return TblEntete(ancre("?tri=$nomRubrique", $libelle . $icone, -1, -1, "trier par $libelle"));
}
