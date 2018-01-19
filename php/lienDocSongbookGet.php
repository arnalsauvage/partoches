<?php
/**
 * Created by PhpStorm.
 * User: medin
 * Date: 07/12/2017
 * Time: 15:26
 */
include_once("lib/utilssi.php");
include("lienDocSongbook.php");

// Un non-admin ne peut changer l'ordre
if ($_SESSION ['privilege'] > 1) {
    if ($_GET['idSongbook'] > 0) {
        if ($_GET['dir'] == "down") {
            remonteTitre($_GET['idSongbook'], $_GET['ordre'], -1);
        }
        if ($_GET['dir'] == "pit") {
            $nb = nombreDeLiensDuSongbook($_GET['idSongbook']);
            remonteTitre($_GET['idSongbook'], $_GET['ordre'], $_GET['ordre'] - $nb);
        }
        if ($_GET['ordre'] > 1) {
            if ($_GET['dir'] == "up") {
                remonteTitre($_GET['idSongbook'], $_GET['ordre'], 1);
            }
            if ($_GET['dir'] == "top") {
                $nb = nombreDeLiensDuSongbook($_GET['idSongbook']);
                remonteTitre($_GET['idSongbook'], $_GET['ordre'], $_GET['ordre'] - 1);
            }
        }
    }
}
header('Location: ./songbook_form.php?id=' . $_GET['idSongbook']);
?>