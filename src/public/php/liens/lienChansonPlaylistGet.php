<?php
/**
 * Created by PhpStorm.
 * User: medin
 * Date: 07/12/2017
 * Time: 15:26
 * Ce script permet de changer l'ordre des morceaux dans une playlist
 *
 * paramètre en entrée, par GET :
 * idPlaylist : identifiant de la playlist
 * dir : "down" (augemnter l'ordre) "pit" (dernier de la liste)
 *          "up" (baisser l'ordre) "top" (premier de la liste)
 * ordre : n° de la chanson visée
 *
 * Pour le moment, on redirige vers le formulaire, mais cette instruction pourra être
 * enlevée pour une utilisation par un composant javascript / Ajax
 *
 */
require_once("../lib/utilssi.php");
require("../liens/lienChansonPlaylist.php");

// Un non-admin ne peut changer l'ordre
if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_MEMBRE"]) {
    if (is_numeric($_GET['idPlaylist'] > 0)) {
        if ($_GET['dir'] == "down") {
            echo "remonteTitre " . $_GET['ordre'] + 1 . "  1";
            remonteTitre($_GET['idPlaylist'], $_GET['ordre'] + 1, 1);
        }
        if ($_GET['dir'] == "pit") {
            $ordre = $_GET['ordre'];
            $nb = nombreDeLiensDuPlaylist($_GET['idPlaylist']);

            while ($ordre++ < $nb)
                remonteTitre($_GET['idPlaylist'], $ordre, 1);
        }
        if ($_GET['ordre'] > 1) {
            if ($_GET['dir'] == "up") {
                remonteTitre($_GET['idPlaylist'], $_GET['ordre'], 1);
            }
            if ($_GET['dir'] == "top") {
                $nb = nombreDeLiensDuPlaylist($_GET['idPlaylist']);
                remonteTitre($_GET['idPlaylist'], $_GET['ordre'], $_GET['ordre'] - 1);
            }
        }
    }
}
header('Location: ./playlist_form.php?id=' . $_GET['idPlaylist']);
