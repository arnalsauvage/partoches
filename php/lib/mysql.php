<?php

// Cette fonction transforme une date au format Mysql et la traduit en notation
// - JJ/MM/AAAA si mode == 0 (par defaut)
// - JJ/MM si mode = 1
function dateMysqlVersTexte($dateMysql, $mode = 0)
{
    $an = substr($dateMysql, 0, 4);
    $mois = substr($dateMysql, 5, 2);
    $jour = substr($dateMysql, 8, 2);
    if ($mode == 0)
        $retour = $jour . "/" . $mois . "/" . "$an";
    if ($mode == 1)
        $retour = $jour . "/" . $mois;
//	       echo "<P> Année : $an , mois : $mois, Jour : $jour. $retour </P>"; // Pour test
    return ($retour);
}

// Cette fonction transforme une date-heure au format Mysql et la traduit en notation
// - JJ/MM/AA si mode == 0 (par defaut)
// - JJ/MM si mode = 1

function dateHeureMysqlVersTexte($date, $mode = 0)
{
    $an = substr($date, 0, 4);
    $mois = substr($date, 5, 2);
    $jour = substr($date, 8, 2);
    $heure = substr($date, 11, 2);
    $minutes = substr($date, 14, 2);
    $secondes = substr($date, 17, 2);
    if ($mode == 0)
        $retour = $jour . "/" . $mois . "/" . "$an" . " " . $heure . "h" . $minutes;
    if ($mode == 1)
        $retour = $jour . "/" . $mois;
    //       echo "<P> Année : $an , mois : $mois, Jour : $jour. $retour </P>";
    return ($retour);
}

// Cette fonction traduit une date du format JJ/MM/AAAA vers le format mySql
function dateTexteVersMysql($date)
{
    $compteur = 0;
    for ($i = 0; $i < strlen($date); $i++) {
        if ($date[$i] == "/") {
            $marqueur[$compteur] = $i;
            $compteur++;
        }
    }
    // S'il n'y a pas de slash ou plus de 2, la date est erronée
    if ($compteur == 0 || $compteur > 2)
        return ("0000-00-00");
    // S'il n'y a qu'un slash, l'année est l'année courante
    if ($compteur == 1)
        $an = date("Y");
    else {
        // On prend l'année saisie par l'utilisateur
        $an = substr($date, $marqueur[1] + 1, 4);
        // On complète si elle n'est que sur un ou deux caractères
        if (strlen($an) == 2)
            $an = "20" . $an;
        if (strlen($an) == 4)
            $an = "" . $an;
    }
    $mois = substr($date, $marqueur[0] + 1, 2);
    if ($mois[1] == "/")
        $mois[1] = 0;
    $jour = substr($date, 0, $marqueur[0]);
    if (checkdate($mois, $jour, $an) == 0)
        return ("0000-00-00");
    $retour = $an . "-" . $mois . "-" . $jour;
    return ($retour);
}

// Renvoie la date du jour au format MySql
function dateDuJourMysql()
{
    return (date("Y") . "-" . date("m") . "-" . date("d"));
}

// Chargement de la liste des libelles
// ex : chargeLibelles($conn, "auteurs", "contenuFiltrer") donne la liste des noms dans un tab[id]=contenuFiltrer trié par contenuFiltrer
function chargeLibelles($table, $libelle)
{

    $marequete = "select id, $libelle  from $table order by $libelle";
    $resultat = $_SESSION ['mysql']->query($marequete);

    while ($ligne = $resultat->fetch_row()) {
        $listeLibelles[$ligne[0]] = $ligne[1];
    }
//	$_SESSION ['mysql']->free_result($resultat);
    return ($listeLibelles);
}

// Exemple de fonction métier à recopier
// Cette fonction renvoie false si une chanson n'existe pas en enregistrement,
// Sinon, elle renvoie le dernier enregistrement dispo
function chansonEstEnregistree($idChanson, $connexion)
{
    $marequete = "select id, idchanson from enregistrement where idchanson = '$idChanson'";
    $resultat = ExecRequete($marequete, $connexion);
    $nbReponses = mysqli_num_rows($resultat);

    if ($nbReponses > 0) {
        //echo "ok";
        while ($ligne = lignesuivante($resultat))
            $id = $ligne[0];
        return ($id);
    } else {
        //echo "Chanson $idChanson non enregistrée... <BR>";
        return false;
    }
}

function augmenteHits($nomTable, $id)
{
    $marequete = "select id, hits from $nomTable where id = '$id'";
    $result = $_SESSION ['mysql']->query($marequete) or die ("Problème augmenteHits #1 : table $nomTable, id : $id " . $_SESSION ['mysql']->error);
    $ligne = $result->fetch_row();
    $nbHits = $ligne[1] + 1;
    $marequete = "UPDATE $nomTable SET hits = '$nbHits' where id = '$id'";
    $result = $_SESSION ['mysql']->query($marequete) or die ("Problème augmenteHits #2 : table $nomTable, id : $id " . $_SESSION ['mysql']->error);
}
