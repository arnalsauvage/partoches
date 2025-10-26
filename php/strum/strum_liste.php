<?php
require_once("../lib/utilssi.php");
require_once("../navigation/menu.php");
require_once("strum.php");

global $iconeAttention;
global $cheminImages;
global $iconePoubelle;
global $iconeCreer;
global $iconeEdit;

$_strumForm = "strum_form.php";
$_strumPost = "strum_post.php";

$urlBoiteAstrum = "../../html/boiteAstrum/index.html";
$imageBoiteAstrum = "../../html/boiteAstrum/medias/img/boiteAstrum.png";
$_table = "strum";
$_renduHtml = "";
$_renduHtml .= entreBalise("Strums", "H1");

// Chargement de la liste des strums
    $marequete = "SELECT strum.id, strum.strum as lestrum , strum.longueur , strum.unite, strum.description  , count(lienstrumchanson.strum) as compte
FROM  strum 
left join lienstrumchanson on binary lienstrumchanson.strum  = strum.strum 
group by lestrum
order by compte desc;";
$_listeDesStrums = $_SESSION ['mysql']->query($marequete);
if (!$_listeDesStrums) {
    die ("Problème strumsListe #1 : " . $_SESSION ['mysql']->error);
}
$_numLigneParcourue = 0;

// Affichage de la liste

// //////////////////////////////////////////////////////////////////////ADMIN : bouton nouveau
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $_renduHtml .= "<BR>" . ancre("$_strumForm", image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouveau strum");
}
// //////////////////////////////////////////////////////////////////////ADMIN

$_monStrum = new Strum();

while ($_strumParcouru = $_listeDesStrums->fetch_row()) {
    $_numLigneParcourue++;
        $_renduHtml .= entreBalise(str_replace(" ", "-",$_strumParcouru [1]), "H2"); // Strum
        $_renduHtml .= "<a title='ouvrir dans la boîte à strum' href='$urlBoiteAstrum?strum=" . str_replace(" ", "-",$_strumParcouru [1]) ."'>
            <img src='$imageBoiteAstrum' alt='Ouvrir dans la boite a strum' height='50vw'> </a>";
        if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
            $_image = image($cheminImages . $iconeEdit, 32, 32);
            $_ancre = ancre("$_strumForm?id=" . $_strumParcouru [0], $_image,-1, -1, "modifier le strum" ); //id
            $_renduHtml .= TblCellule($_ancre);
        }
        switch ($_strumParcouru [3]) {
            case 4 :
                $_uniteStrum = "noires";
                break;
            case 8 :
                $_uniteStrum = "croches";
                break;
            case 16 :
                $_uniteStrum = "double-croches";
                break;
            default : $_uniteStrum = sprintf("%s", $_strumParcouru[3]);
        }


        $_renduHtml .= $_strumParcouru [2] . "  " .$_uniteStrum; //  longueur / unité
        $chansons_liste = Strum::chansonsDuStrumChaine($_strumParcouru[1]);
        $_renduHtml .= " - "  . $_strumParcouru [4] . " (utilisé dans " .$_strumParcouru [5]  . " chansons : $chansons_liste)"; // description

        // //////////////////////////////////////////////////////////////////////ADMIN : bouton supprimer
        if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
            $_renduHtml .= boutonSuppression($_strumPost . "?id=$_strumParcouru[0]&mode=SUPPR", $iconePoubelle, $cheminImages);
        // //////////////////////////////////////////////////////////////////////ADMIN
        }
}
///////////////////////////////  DIV EDITION DE STRUM /////////////////////////////////

// //////////////////////////////////////////////////////////////////////ADMIN : bouton ajouter

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {
    $_renduHtml .= "<BR>" . ancre("$_strumForm", image($cheminImages . $iconeCreer, 32, 32) . "Créer un nouveau strum");
}

// //////////////////////////////////////////////////////////////////////ADMIN

$_renduHtml .= envoieFooter();
echo $_renduHtml;
