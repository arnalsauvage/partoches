<?php
require_once("../lib/Pagination.php");
require_once("../lib/utilssi.php");

require_once("lienurl.php");
require_once("lienurl_voir.php");

require_once("../navigation/menu.php");

global $iconeAttention;
global $cheminImages;
global $iconePoubelle;
global $iconeCreer;
global $iconeEdit;

const LIENSURL_PAGE = 'liensurlPage';
$nombreLiensParPage = 15;
$_lienurlForm = "../chanson/chanson_form.php";
$_lienurlPost = "lienurl_post.php";
$_lienurlPost = "lienurl.php";

$_table_lien_url = "lienurl";
$_renduHtml = "";
$_renduHtml .= entreBalise("Liens", "H1");

// Chargement de la liste des lienurls
$_listeDeslienurls = chargeLiensurls("date", false);

if (!$_listeDeslienurls) {
    die ("Problème lienurlsListe #1 : " . $_SESSION ['mysql']->error);
}
$_numLigneParcourue = 0;

// Gestion de la pagination
$pagination = new Pagination ($_listeDeslienurls->num_rows, $nombreLiensParPage);
if (isset ($_GET['page']) && is_numeric($_GET['page'])) {
    $_SESSION[LIENSURL_PAGE] = $_GET['page'];
} else
    if (!isset ($_SESSION[$_table_lien_url."_page"])) {
        $_SESSION[LIENSURL_PAGE] = 1;
    }
$pagination->setPageEnCours($_SESSION[LIENSURL_PAGE]);

// Affichage de la liste

while ($_lienurlParcouru = $_listeDeslienurls->fetch_row()) {
    $_numLigneParcourue++;
    if (($_numLigneParcourue < $pagination->getItemDebut()) || $_numLigneParcourue > $pagination->getItemFin()) {
        continue;
    }
        $_renduHtml .= "<div class=\"col-xs-8 col-sm-6 col-md-4 \">" . entreBalise(str_replace(" ", "-",$_lienurlParcouru [4]), "H2"); // Login

        if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"] ) {
            $_image = Image($cheminImages . $iconeEdit, 32, 32);
            $_image = str_replace("'>","' loading='lazy'>", $_image);
            $table_concernee = $_lienurlParcouru[1];
            $id_concerne = $_lienurlParcouru[2];
            if ($table_concernee == "chanson"){
                $_ancre = Ancre("$_lienurlForm?id=" . $id_concerne, $_image,-1, -1, "modifier le lienurl" );
            }
            $_renduHtml .= $_ancre;
        }

        $_renduHtml .= "<a href='". $_lienurlParcouru[3]."' > lien</a>"; //  url
        $_renduHtml .= " - "  . $_lienurlParcouru [5]; // description
        $_renduHtml .= afficheVignetteSiVideoYoutube( $_lienurlParcouru[4], $_lienurlParcouru[3]) ;
        // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
        if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
            $_renduHtml .= boutonSuppression($_lienurlPost . "?id=$_lienurlParcouru[0]&mode=SUPPR", $iconePoubelle, $cheminImages);
        // //////////////////////////////////////////////////////////////////////ADMIN
        }
        $_renduHtml .= "</div>";
}
    $_renduHtml .= "<BR>" . $pagination->barrePagination() . "   ";
///////////////////////////////  DIV EDITION DE lienurl /////////////////////////////////

// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $_renduHtml .= "<BR>" . Ancre("$_lienurlForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouveau lienurl");
}

// //////////////////////////////////////////////////////////////////////ADMIN

$_renduHtml .= envoieFooter();
echo $_renduHtml;
