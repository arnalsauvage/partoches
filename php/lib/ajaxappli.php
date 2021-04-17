<?php
session_start();

// Si l'utilisateur n'a pas de session, on ne répond pas
if (!isset($_SESSION ['privilege']) ) {
    return(0);
}
// On récupère les paramètre par POST
if (isset ($_POST ['largeur_fenetre']))
{
    // TODO Remplacer 800 par une constante / variable largeur_fenetre_min
    $_largeur_fenetre = 800;
    echo "largeur fenetre reçue : " . $_largeur_fenetre;
    if ( $_POST ['largeur_fenetre'] != "" && $_POST ['largeur_fenetre'] >100 )
    {
        $_largeur_fenetre = $_POST ['largeur_fenetre'];
    }
    $_SESSION['largeur-fenetre'] = $_largeur_fenetre;
}
