<?php
$fichier = __DIR__ . "/../../../data/conf/params.ini";

// On lit les données dans le fichier ini
$ini_objet = new FichierIni ();
$ini_objet->m_load_fichier($fichier);

$_SESSION ['urlSite'] = $ini_objet->m_valeur("urlSite", "general");
$_SESSION ['emailAdmin'] = $ini_objet->m_valeur("EmailAdmin", "general");
$_SESSION ['loginParam'] = $ini_objet->m_valeur("loginParam", "general");
$_SESSION ['mailOubliMotDePasse'] = $ini_objet->m_valeur("mailOubliMotDePasse", "general");
$_SESSION ['nomEmailOubliMotDePasse'] = $ini_objet->m_valeur("nomEmailOubliMotDePasse", "general");
$_SESSION ['logoSite'] = $ini_objet->m_valeur("logoSite", "general");

$titreSite = $ini_objet->m_valeur("titreSite", "general");
if (empty($titreSite)) {
    $titreSite = "Partoches Canopée";
}
if (!mb_check_encoding($titreSite, 'UTF-8')) {
    $titreSite = mb_convert_encoding($titreSite, 'UTF-8', 'ISO-8859-1');
}
$_SESSION ['titreSite'] = $titreSite;

$sousTitreSite = $ini_objet->m_valeur("sousTitreSite", "general");
if (empty($sousTitreSite)) {
    $sousTitreSite = "Le site de partage de partitions collaboratif !";
}
if (!mb_check_encoding($sousTitreSite, 'UTF-8')) {
    $sousTitreSite = mb_convert_encoding($sousTitreSite, 'UTF-8', 'ISO-8859-1');
}
$_SESSION ['sousTitreSite'] = $sousTitreSite;

// Niveau de log : on écrit à partir de DEBUG / INFO / WARNING / ERROR
$niveauDeLog = $ini_objet->m_valeur("niveauDeLog", "general");

// On utilise les chemins globaux absolus définis dans autoload.php si disponibles
$_DOSSIER_DATA = defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR . '/' : __DIR__ . "/../../data/";
$_DOSSIER_CHANSONS = defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR . '/chansons/' : $_DOSSIER_DATA . "chansons/";
$_DOSSIER_SONGBOOKS = defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR . '/songbooks/' : $_DOSSIER_DATA . "songbooks/";
$_DOSSIER_UTILISATEURS = defined('PUBLIC_DATA_DIR') ? PUBLIC_DATA_DIR . '/utilisateurs/' : $_DOSSIER_DATA . "utilisateurs/";
