<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/php/lib/FichierIni.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/php/lib/mysql.php';
if (FALSE == isset($configMysql)) {
    $configMysql = TRUE;

    $fichier = $_SERVER['DOCUMENT_ROOT'] . "/conf/params.ini";

    // On lit les données dans le fichier ini
    $ini_objet = new FichierIni ();
    $ini_objet->m_load_fichier($fichier);

    $monserveur = $ini_objet->m_valeur("monServeur", "mysql");
    $mabase = $ini_objet->m_valeur("maBase", "mysql");
    $LOGIN = $ini_objet->m_valeur("login", "mysql");
    $MOTDEPASSE = $ini_objet->m_valeur("motDePasse", "mysql");

    $mysqli = new mysqli($monserveur, $LOGIN, $MOTDEPASSE, $mabase);
    if ($mysqli->connect_error) {
        die(' Erreur #1 configMysql : Impossible de créer une connexion persistante ! ' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
    }
    if ($mysqli->select_db($mabase) == false) {
        $error = "Erreur #2 configMysql : Impossible de selectionner la base !";
        return (0);
    }
    $_SESSION ['mysql'] = $mysqli;
}
//	echo "connexion : $idconnect";
//	return($idconnect);

function convertitDateJJMMAAAAversMySql($date)
{
    // On convertit la date au format mysql : "JJ/MM/AAAA" devient "AAAA-MM-JJ"
    // echo "Ancienne date : $date ";
    $date = explode('/', $date);
    $new_date = $date[2] . '-' . $date[1] . '-' . $date[0];
    // echo " , New date : " . $new_date . "<br>";
    return $new_date;
}

function convertitDateMySqlVersJJMMAAAA($date)
{
    // On vérifie que la date est au format MySQL : "AAAA-MM-JJ"
    $date = explode('-', $date);

    // On s'assure qu'on a bien trois éléments
    if (count($date) === 3) {
        $new_date = $date[2] . '/' . $date[1] . '/' . $date[0];
        return $new_date;
    } else {
        // Gérer le cas où la date n'est pas au format attendu
        return null; // ou une exception, selon vos besoins
    }
}
