<?php
$fichier = "../conf/params.ini";

// On lit les données dans le fichier ini
$ini_objet = new FichierIni ();
$ini_objet->m_fichier($fichier);

$_SESSION ['urlSite'] = $ini_objet->m_valeur("urlSite", "general");
$_SESSION ['emailAdmin'] = $ini_objet->m_valeur("EmailAdmin", "general");
$_SESSION ['loginParam'] = $ini_objet->m_valeur("loginParam", "general");
// Niveau de log : on écrit à partir de DEBUG / INFO / WARNING / ERROR
$niveauDeLog = $ini_objet->m_valeur("niveauDeLog", "general");
// echo "Niveau de log : " . $niveauDeLog;

$_DOSSIER_DATA = "data/";
$_DOSSIER_CHANSONS = $_DOSSIER_DATA . "chansons/";
$_DOSSIER_SONGBOOKS = $_DOSSIER_DATA . "songbooks/";