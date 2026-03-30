<?php
if (!defined('IMAGES')) {
    define('IMAGES', "../../images");
}
if (!defined('ICONES')) {
    define('ICONES', IMAGES . "/icones/");
}

$a = session_id();
if (empty ($a)) {
    session_start();
}

unset ($a);

if (!isset ($FichierUtilsSi)) {
    // Déclaration des variables globales
    $FichierUtilsSi = 1;

    // Inclusion des différentes librairies
    require_once("FichierIni.php");
    require_once("compteur.php");
    require_once("configMysql.php");
    include_once "config-images.php";
    require_once("formulaire.php");
    require_once("html.php");
    require_once("mysql.php");
    include_once("params.php");
    include_once("tableHtml.php");
    require_once("Chiffrement.php");
    if (!isset ($_SESSION ["privilege"])) {
        $_SESSION ["privilege"] = 0;
    }

    // MODE SMOKE TEST : On force l'admin si on est en local et qu'on le demande
    if (isset($_GET['smoke_test']) && $_GET['smoke_test'] == '1') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip == '127.0.0.1' || $ip == '::1' || strpos($ip, '172.') === 0) { // Localhost ou réseau Docker
            $_SESSION['privilege'] = 3; // ADMIN
            $_SESSION['id'] = 1;        // Utilisateur par défaut
            $_SESSION['user'] = 'SmokeTest';
        }
    }

    /**
     * Limite la longueur d'une chaine à x caractères
     * @param string $chaine
     * @param int $tailleMax
     * @return string
     */
    function limiteLongueur($chaine, $tailleMax)
    {
        if (strlen((string)$chaine) > $tailleMax) {
            return mb_substr((string)$chaine, 0, $tailleMax - 4) . "...";
        } else {
            return (string)$chaine;
        }
    }

    // Cette fonction retourne une liste des images disponibles sur le site, éventuellement dans un sous-dossier
    function listeImages($subDir = "")
    {
        // On adapte le chemin pour le dossier utilisateurs déplacé dans data/
        $cheminRecherche = IMAGES . $subDir;
        if ($subDir === "/utilisateur") {
            $cheminRecherche = __DIR__ . "/../../data/utilisateurs";
        }

        $tableau = [];
        if (is_dir($cheminRecherche)) {
            $d = dir($cheminRecherche);
            while (false !== ($entry = $d->read())) {
                if (($entry != ".") && ($entry != "..")) {
                    $tableau [$entry] = $entry;
                }
            }
            $d->close();
            asort($tableau);
        }
        return $tableau;
    }

    // Cette fonction retourne un bouton de suppression avec message de confirmation
    function boutonSuppression($lien, $iconePoubelle = "", $cheminImages = "") :string
    {
        $msg = "Voulez-vous vraiment supprimer cet élément ?";
        return "<button type='button' class='btn btn-xs btn-danger' title='Supprimer' onclick=\"confirmeSuppr('$lien', '$msg');\"><i class='glyphicon glyphicon-trash'></i></button>";
    }
    /**
     * Vérifie si l'utilisateur a au moins le privilège demandé
     */
    function aDroits(int $privilegeMin): bool
    {
        if (!isset($_SESSION['privilege'])) return false;
        return (int)$_SESSION['privilege'] >= $privilegeMin;
    }

    /**
     * Raccourci pour vérifier si l'utilisateur est Admin
     */
    function estAdmin(): bool
    {
        return aDroits($GLOBALS["PRIVILEGE_ADMIN"]);
    }

    // Vérifie qu'une date, selon un format donné, est bien valide - repris sur un exemple dans la doc php
    function validateDate($date, $format = 'd/m/Y') :bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Reconstruit la table des médias (utilisé après ajout/modif de fichiers ou liens)
     */
    function actualiseMedias(): void
    {
        // On évite les inclusions multiples si on est déjà dans un processus complexe
        require_once PHP_DIR . "/media/Media.php";
        $media = new Media();
        $media->resetMediaTable();
    }

    // Fonction pour filtrer les données venant de POST et GET
    function filtreGetPost($source, $cle, $type = 'string', $options = []) {
        $valeur = null;

        if (!isset($source[$cle])) {
            return null;
        }

        switch ($type) {
            case 'int':
                $valeur = filter_var($source[$cle], FILTER_VALIDATE_INT);
                break;

            case 'float':
                $valeur = filter_var($source[$cle], FILTER_VALIDATE_FLOAT);
                break;

            case 'bool':
                $valeur = filter_var($source[$cle], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;

            case 'email':
                $valeur = filter_var($source[$cle], FILTER_VALIDATE_EMAIL);
                break;

            case 'url':
                $valeur = filter_var($source[$cle], FILTER_VALIDATE_URL);
                break;

            case 'string':
            default:
                $valeur = strip_tags($source[$cle]);
                $valeur = trim($valeur);
                if (isset($options['max_length'])) {
                    $valeur = substr($valeur, 0, (int)$options['max_length']);
                }
                break;
        }

        return $valeur;
    }

    function generateQRCode($url, $size): string
    {

        $apiUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($url) . "&amp;size=".$size."x"."$size";
        return "<img src='$apiUrl' alt='QR Code'>";
    }

}
