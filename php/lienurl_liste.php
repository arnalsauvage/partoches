<?php
require_once("lib/utilssi.php");
require_once("menu.php");
require_once("lienurl_voir.php");

global $iconeAttention;
global $cheminImages;
global $iconePoubelle;
global $iconeCreer;
global $iconeEdit;

$_lienurlForm = "lienurl_form.php";
$_lienurlPost = "lienurl_post.php";

$_table = "lienurl";
$_renduHtml = "";
$_renduHtml .= entreBalise("Liens", "H1");

// Chargement de la liste des lienurls
$marequete = "select * from $_table ORDER BY 'dateDernierLogin' DESC";
$_listeDeslienurls = $_SESSION ['mysql']->query($marequete);
if (!$_listeDeslienurls) {
    die ("Problème lienurlsListe #1 : " . $_SESSION ['mysql']->error);
}
$_numLigneParcourue = 0;

// Affichage de la liste

while ($_lienurlParcouru = $_listeDeslienurls->fetch_row()) {
    $_numLigneParcourue++;
        $_renduHtml .= "<div class=\"col-xs-8 col-sm-6 col-md-4 \">" . entreBalise(str_replace(" ", "-",$_lienurlParcouru [4]), "H2"); // Login

        if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) ) {
            $_image = Image($cheminImages . $iconeEdit, 32, 32);
            $_image = str_replace("'>","' loading='lazy'>", $_image);
            $_ancre = Ancre("$_lienurlForm?id=" . $_lienurlParcouru [0], $_image,-1, -1, "modifier le lienurl" );
            $_renduHtml .= $_ancre;
        }

        $_renduHtml .= "<a href='". $_lienurlParcouru[3]."' > $_lienurlParcouru[3]</a>"; //  url
        $_renduHtml .= " - "  . $_lienurlParcouru [5]; // description
        $_renduHtml .= afficheVignetteSiVideoYoutube( $_lienurlParcouru[4], $_lienurlParcouru[3]) ;
        // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
        if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
            $_renduHtml .= boutonSuppression($_lienurlPost . "?id=$_lienurlParcouru[0]&mode=SUPPR", $iconePoubelle, $cheminImages);
        // //////////////////////////////////////////////////////////////////////ADMIN
        }
        $_renduHtml .= "</div>";
}
///////////////////////////////  DIV EDITION DE lienurl /////////////////////////////////

// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $_renduHtml .= "<BR>" . Ancre("$_lienurlForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouveau lienurl");
}

// //////////////////////////////////////////////////////////////////////ADMIN

$_renduHtml .= envoieFooter();
echo $_renduHtml;
