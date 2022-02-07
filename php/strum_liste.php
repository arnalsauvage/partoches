<?php
require_once("lib/utilssi.php");
require_once("menu.php");

global $iconeAttention;
global $cheminImages;
global $iconePoubelle;
global $iconeCreer;
global $iconeEdit;

$_strumForm = "strum_form.php";
$_strumPost = "strum_post.php";

$_table = "strum";
$_renduHtml = "";
$_renduHtml .= entreBalise("Strums", "H1");

$_renduHtml .= "Pour écouter le strum, copie le et colle le dans la fenêtre de la Boîte à Strum ! <a href='../html/boiteAstrum/index.html'>lien</a>
<iframe src='../html/boiteAstrum/index.html'    title='Inline Frame Example'
    width='800'
    height='600'></iframe>
";

// Chargement de la liste des strums
$marequete = "select * from $_table ORDER BY 'dateDernierLogin' DESC";
$_listeDesStrums = $_SESSION ['mysql']->query($marequete);
if (!$_listeDesStrums) {
    die ("Problème strumsListe #1 : " . $_SESSION ['mysql']->error);
}
$_numLigneParcourue = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $_renduHtml .= "<BR>" . Ancre("$_strumForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouveau strum");
}
// //////////////////////////////////////////////////////////////////////ADMIN

while ($_strumParcouru = $_listeDesStrums->fetch_row()) {
    $_numLigneParcourue++;
        $_renduHtml .= entreBalise(str_replace(" ", "-",$_strumParcouru [3]), "H2"); // Login

        if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) ) {
            $_image = Image($cheminImages . $iconeEdit, 32, 32);
            $_ancre = Ancre("$_strumForm?id=" . $_strumParcouru [0], $_image,-1, -1, "modifier le strum" );
            $_renduHtml .= TblCellule($_ancre);
        }

        $_renduHtml .= $_strumParcouru [2] . " / " . $_strumParcouru [1]; //  longueur / unité
        $_renduHtml .= " - "  . $_strumParcouru [4]; // description

        // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
        if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
            $_renduHtml .= boutonSuppression($_strumPost . "?id=$_strumParcouru[0]&mode=SUPPR", $iconePoubelle, $cheminImages);
        // //////////////////////////////////////////////////////////////////////ADMIN
        }
}
///////////////////////////////  DIV EDITION DE STRUM /////////////////////////////////

// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $_renduHtml .= "<BR>" . Ancre("$_strumForm", Image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouveau strum");
}

// //////////////////////////////////////////////////////////////////////ADMIN

$_renduHtml .= envoieFooter();
echo $_renduHtml;
