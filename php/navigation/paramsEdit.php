<?php /** @noinspection PhpMethodParametersCountMismatchInspection */
include_once "../lib/utilssi.php";
include_once("menu.php");
$fichier = "../../conf/params.ini";
$sortie = "";
$sortie .= "<table><tr><td>";

// Si l'utilisateur n'est pas logué
if (!isset ($_SESSION['user']) || $_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    // Affichage du formulaire de login
    $sortie .= $sortie;
    include "../../html/menuLogin.html";
    exit();
}
// On lit les données dans le fichier ini
$ini_objet = new FichierIni();

$sortie .= ("<h1>Fichier params.ini</h1>");
$ini_objet->m_load_fichier($fichier);

// Traitement du formulaire si besoin
$bModif = false;

/**
 * @param FichierIni $ini_objet
 * @param string $fichier
 * @param string $rubrique
 * @param string $item
 * @return void
 */
function traiteModif(FichierIni &$ini_objet, string $fichier, string $rubrique, string $item): bool
{
    $_modifie = false;
    // Pour debug echo "Traite fichier $fichier, rubrique $rubrique, item $item <br> \n";
    if (isset ($_POST[$item])) {
        $ini_objet->m_put($_POST[$item], $item, $rubrique , $fichier);
        // Pour debug echo "Traité fichier $fichier, rubrique $rubrique, item $item  valeur <br> $_POST[$item] \n";
        $_modifie = true;
    }
    return $_modifie;
}

    $bModif = traiteModif($ini_objet, $fichier, "general", "loginParam")|| $bModif;
    $bModif = traiteModif($ini_objet, $fichier, "general", "urlSite") || $bModif;
    $bModif = traiteModif($ini_objet, $fichier, "general", "EmailAdmin")|| $bModif;
    if ($bModif) {
        $ini_objet->save();
    }
    // On traite le reset des médias
    if (isset ($_GET['resetmedias'])) {
        require_once ("../media/Media.php");
        $medias = new Media();
        $nombreMedias =$_GET['resetmedias'];
        $medias->resetAvecDernieresPartoches($nombreMedias);
        $medias->resetAvecDernieresVideos($nombreMedias);
        $sortie .= "<p>Réinitialisation des médias effectuée</p>";
    }
    if (isset ($_POST['testTexte']))
    {
        $result = ""; $compteur =0;
        $resultat = $_SESSION ['mysql']->query($_POST['testTexte']) or die ("Problème #1 dans paramsEdit : " . $_SESSION ['mysql']->error);
        while ($ligne = mysqli_fetch_row($resultat)) {
            $compteur++;
            $result .= $compteur . " - " . $ligne[7] . " - " . $ligne[1] . " " . "<br>\n";
        }
    }
$sortie .= $ini_objet->print_fichier();

$sortie .= "
<form action='paramsEdit.php' method='post' ENCTYPE='application/x-www-form-urlencoded'>
<fieldset>
 <legend> Attributs modifiables : </legend>";

/**
 * @param FichierIni $ini_objet
 * @param string $sortie
 * @return string
 */
function ajouteChampFormulaire(FichierIni $ini_objet, string $_element, string $_description, $_type, string $_groupe ): string
{
    $sortie = "
<br> <label for='$_element'> $_description :</label>
<input value='" . $ini_objet->m_valeur($_element, $_groupe) . "' name='$_element' id='$_element' type='$_type'>
";
    return $sortie;
}

$sortie .= ajouteChampFormulaire($ini_objet, "urlSite", "Url du site","text","general");
$sortie .= ajouteChampFormulaire($ini_objet, "loginParam", "login de parametrage","text","general");
$sortie .= ajouteChampFormulaire($ini_objet, "EmailAdmin", "Email de l'admin","email","general");
$sortie .= ajouteChampFormulaire($ini_objet, "niveauDeLog", "niveau de log","text","general");
$sortie .= ajouteChampFormulaire($ini_objet, "cleGetSongBpm", "cle GetSongBpm","text","general");

$sortie .="
<button type='submit' value='Valider' name='valider'>Valider</button>
</fieldset></form> ";

$sortie .= "</td></tr></table>";

$sortie .= "<h2>Medias</h2>
<a href='../media/listeMedias.php'> Voir les medias</a>
<a href='paramsEdit.php?resetmedias=125'>Réinitialiser les médias</a>
<form id='testeTexte' action='paramsEdit.php' method='post'>
    <input type='text' name = 'testTexte' id='testTexte'>
    </input>
    
    <button type='submit' value='Valider' name='valider'>Valider</button>
</form>
";

if (isset($result)){
    $sortie .= "<div>" .$result . "</div>";
}

/** @noinspection PhpMethodParametersCountMismatchInspection */
$sortie .= envoieFooter();
echo $sortie;
