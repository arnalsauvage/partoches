<?php
/**
 * Fichier de secours pour la redirection vers le dossier public.
 * Ce fichier n'est utilisé que si le serveur web ne supporte pas la réécriture d'URL via .htaccess.
 */
header('Location: public/index.php');
exit();
