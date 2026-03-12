<?php

const DOC_TRI = 'docTri';
const DOC_ORDRE_ASC = 'docOrdreAsc';
const CHANSON = "chanson";
const DOC_PAGE = 'docPage';
const C_RACINE = "../../";
require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once __DIR__ . "/../lib/Pagination.php";
require_once __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../liens/lienDocSongbook.php";
require_once __DIR__ . "/../navigation/menu.php";
require_once __DIR__ . "/../songbook/Songbook.php";
$table = "songbook";
$sortie = "";
$monImage = "";

global $_DOSSIER_CHANSONS;

$nombreDocumentsParPage = 50;
if (!isset ($_SESSION['user']) || $_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_MEMBRE"]) {
    // Affichage du formulaire de login
    $sortie = "pas de contenu...";
    echo $sortie;
    exit();
}
$sortie .= "<h2>Documents publiés pour des chansons</h2> \n"; // Titre

// Gestion du paramètre de tri
if (!isset($_SESSION[DOC_TRI])) {
    $_SESSION[DOC_TRI] = "date";
    $_SESSION[DOC_ORDRE_ASC] = false;
}
if (isset ($_POST ['tri'])) {
    $_SESSION[DOC_TRI] = $_POST ['tri'];
    $_SESSION[DOC_ORDRE_ASC] = true;
}
if (isset ($_GET ['triDesc'])) {
    $_SESSION[DOC_TRI] = $_GET ['triDesc'];
    $_SESSION[DOC_ORDRE_ASC] = false;
}
if (isset ($_POST['filtre'])) {
    $contenuFiltrer = htmlspecialchars($_POST['filtre'], ENT_QUOTES);
} else {
    $contenuFiltrer = "";
}

if ($contenuFiltrer == "") {
    $lignes = chercheDocuments("nomTable", CHANSON, $_SESSION[DOC_TRI], $_SESSION[DOC_ORDRE_ASC]);
} else {
    $lignes = chercheDocuments("nom", "%" . addslashes($contenuFiltrer) . "%", $_SESSION[DOC_TRI], $_SESSION[DOC_ORDRE_ASC]);
}

// Gestion de la pagination
$pagination = new Pagination ($lignes->num_rows, $nombreDocumentsParPage);
if (isset ($_GET['page']) && is_numeric($_GET['page'])) {
    $_SESSION[DOC_PAGE] = $_GET['page'];
} else if (!isset ($_SESSION[DOC_PAGE])) {
    $_SESSION[DOC_PAGE] = 1;
}
$pagination->setPageEnCours($_SESSION[DOC_PAGE]);

// On charge le tableau des utilisateurs
$tabUsers = Utilisateur::portraitDesUtilisateurs();

$sortie .= <<<HTML
<form METHOD="POST" ACTION="documents_voir.php" NAME="formfiltre" class="well form-inline">
    <div class="form-group">
        <input type="text" list="typesFichier" value="$contenuFiltrer" name="filtre" class="form-control" placeholder="Filtrer par nom...">
    </div>
    <button type="submit" name="filtrer" class="btn btn-primary">
        <span class="glyphicon glyphicon-filter"></span> Filtrer
    </button>
</form>
<datalist id="typesFichier">
   <option value="*">tous</option>
   <option value="doc">document</option>
   <option value="son">son</option>
   <option value="img">image</option>
</datalist>
HTML;


// Entête de la table
$titrePublicateur = titreColonne("Publicateur", "idUser");
$titreTaille = titreColonne("Taille", "tailleKo");
$titreDate = titreColonne("Date", "date");
$titreHits = titreColonne("Nbre vues", "hits");

$sortie .= <<<HTML
<table class="table table-striped table-hover">
    <thead>
        <tr>
            $titrePublicateur
            <th></th>
            <th>Fichier</th>
            $titreTaille
            $titreDate
            $titreHits
        </tr>
    </thead>
    <tbody>
HTML;

$numligne = 0;
while ($ligneDoc = $lignes->fetch_row()) {
    $numligne++;
    if (($numligne < $pagination->getItemDebut()) || $numligne > $pagination->getItemFin()) {
        continue;
    }

    $fichierCourt = composeNomVersion($ligneDoc[1], $ligneDoc[4]);
    $urlFichier = C_RACINE . $_DOSSIER_CHANSONS . $ligneDoc[6] . "/" . urlencode($fichierCourt);
    $extension = substr(strrchr($ligneDoc[1], '.'), 1);

    if ($contenuFiltrer) {
        if (($contenuFiltrer == "son") && ($extension != "mp3")) continue;
        if (($contenuFiltrer == "pdf") && ($extension != "pdf")) continue;
        if (($contenuFiltrer == "doc") && ($extension != "doc")) continue;
    }

    $iconePath = ICONES . $extension . ".png";
    if (!file_exists($iconePath)) {
        $iconePath = __DIR__ . "/../images/icones/fichier.png";
    }
    $icone = image($iconePath, 32, 32, "icone");

    $idUserDoc = $ligneDoc[7] ?? 0;
    $userPseudo = htmlspecialchars($tabUsers[$idUserDoc][1] ?? 'Inconnu', ENT_QUOTES);
    $userImage = $tabUsers[$idUserDoc][5] ?? 'defaut.png';
    $userNom = htmlspecialchars($tabUsers[$idUserDoc][0] ?? 'Inconnu', ENT_QUOTES);

    require_once PHP_DIR . "/lib/Image.php";
    $avatarUrl = Image::getThumbnailUrl($idUserDoc . "/" . $userImage, 'mini', 'utilisateurs');
    $vignettePublicateur = "<img src='$avatarUrl' width='48' height='48' class='img-circle' style='object-fit:cover;' alt='$userNom' title='$userPseudo'>";
    
    $idChansonDoc = $ligneDoc[6] ?? 0;
    $vignetteChanson = "";
    if ($idChansonDoc > 0) {
        $vignetteChanson = image(C_RACINE . $_DOSSIER_CHANSONS . $idChansonDoc . "/" . rawurlencode(imageTableId(CHANSON, $idChansonDoc)), 128, 128, CHANSON);
    }

    $lienChanson = "";
    if ($vignetteChanson != "") {
        $lienChanson = "<a href='../chanson/chanson_voir.php?id=$idChansonDoc'>$vignetteChanson</a>";
    }

    $tailleMo = number_format($ligneDoc[2] / 1024, 0, ',', ' ');
    $dateTexte = dateMysqlVersTexte($ligneDoc[3]);
    $lienTelechargement = ancre("getdoc.php?doc=" . $ligneDoc[0], $icone, "", true);

    $sortie .= <<<HTML
        <tr>
            <td>$vignettePublicateur</td>
            <td>$lienChanson</td>
            <td>$lienTelechargement <a href="$urlFichier" target="_blank">$fichierCourt</a></td>
            <td>$tailleMo ko</td>
            <td>publié le $dateTexte</td>
            <td>$ligneDoc[8] vues</td>
        </tr>
HTML;
}

$sortie .= <<<HTML
    </tbody>
</table>
HTML;

$sortie .= $pagination->barrePagination() . "   ";
$sortie .= envoieFooter();
echo $sortie;

function titreColonne($libelle, $nomRubrique): string
{
    return TblCellule(ancre("?tri=$nomRubrique", "<span class='glyphicon glyphicon-chevron-up'> </span>")
        . "  $libelle   " . ancre("?triDesc=$nomRubrique", "  <span class='glyphicon glyphicon-chevron-down'></span> "));
}
