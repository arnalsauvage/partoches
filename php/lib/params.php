<?php
	$fichier = "../conf/params.ini";
	
	// On lit les données dans le fichier ini
	$ini_objet = new ini ();
	$ini_objet->m_fichier ( $fichier );
	
	$_SESSION ['urlSite'] = $ini_objet->m_valeur ( "urlSite", "general" );
	$_SESSION ['emailAdmin'] = $ini_objet->m_valeur ( "EmailAdmin", "general" );
	$_SESSION ['loginParam'] = $ini_objet->m_valeur ( "loginParam", "general" );

