<?php
/** @noinspection PhpMethodParametersCountMismatchInspection */
include_once "../lib/utilssi.php";
include_once("../navigation/menu.php");
$fichier = "../../conf/params.ini";
$sortie = "<table><tr><td>";

// Vérification droits admin
if (!isset($_SESSION['user']) || $_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    include "../html/menuLogin.html";
    exit();
}

// Chargement fichier ini
$ini_objet = new FichierIni();
$sortie .= "<h1>Fichier params.ini</h1>";
$ini_objet->m_load_fichier($fichier);

// Traitement formulaire de modif fichier ini (fonction interne)
$bModif = false;

/**
 * @param FichierIni $ini_objet
 * @param string $fichier
 * @param string $rubrique
 * @param string $item
 * @return bool
 */
function traiteModif(FichierIni &$ini_objet, string $fichier, string $rubrique, string $item): bool
{
    $_modifie = false;
    if (isset($_POST[$item])) {
        $ini_objet->m_put($_POST[$item], $item, $rubrique, $fichier);
        $_modifie = true;
    }
    return $_modifie;
}

$bModif = traiteModif($ini_objet, $fichier, "general", "loginParam") || $bModif;
$bModif = traiteModif($ini_objet, $fichier, "general", "urlSite") || $bModif;
$bModif = traiteModif($ini_objet, $fichier, "general", "EmailAdmin") || $bModif;
$bModif = traiteModif($ini_objet, $fichier, "general", "niveauDeLog") || $bModif;
$bModif = traiteModif($ini_objet, $fichier, "general", "cleGetSongBpm") || $bModif;

if ($bModif) {
    $ini_objet->save();
}

// --- GESTION AJAX pour reset partoches ---
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'reset_partoches') {
    include_once("../classes/Media.php");
    $media = new Media();
    $nb = 50;
    $success = $media->resetAvecDernieresPartoches($nb);
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode(['status' => 'ok', 'message' => "Réinitialisation des $nb dernières partoches réussie."]);
    } else {
        // On peut récupérer l'erreur SQL ou autre message d'erreur dans la classe Media si possible
        $errorMessage = method_exists($media, 'getLastError') ? $media->getLastError() : "Échec lors de la réinitialisation des partoches.";
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    }
    exit();
}

// Fonction ajout champs formulaire
function ajouteChampFormulaire(FichierIni $ini_objet, string $_element, string $_description, $_type, string $_groupe): string
{
    return "
<br> <label for='$_element'> $_description :</label>
<input value='" . htmlspecialchars($ini_objet->m_valeur($_element, $_groupe), ENT_QUOTES) . "' name='$_element' id='$_element' type='$_type'>
";
}

// Construction formulaire
$sortie .= "
<form action='paramsEdit.php' method='post' ENCTYPE='application/x-www-form-urlencoded'>
<fieldset>
 <legend> Attributs modifiables : </legend>";

$sortie .= ajouteChampFormulaire($ini_objet, "urlSite", "Url du site", "text", "general");
$sortie .= ajouteChampFormulaire($ini_objet, "loginParam", "login de parametrage", "text", "general");
$sortie .= ajouteChampFormulaire($ini_objet, "EmailAdmin", "Email de l'admin", "email", "general");
$sortie .= ajouteChampFormulaire($ini_objet, "niveauDeLog", "niveau de log", "text", "general");
$sortie .= ajouteChampFormulaire($ini_objet, "cleGetSongBpm", "cle GetSongBpm", "text", "general");

$sortie .= "
<button type='submit' value='Valider' name='valider'>Valider</button>
</fieldset>
</form>
";

// --- BOUTON REINITIALISER LES 50 DERNIERES PARTOCHES via AJAX ---
$sortie .= "
<div style='margin-top: 30px;'>
    <button id='btnResetPartoches'>Réinitialiser les 50 dernières partoches</button>
    <div id='resetResult' style='margin-top:10px; font-weight: bold;'></div>
</div>
";

$sortie .= "</td></tr></table>";

// Formulaire test SQL (inchangé)
if (isset($_POST['testTexte'])) {
    $result = "";
    $compteur = 0;
    $resultat = $_SESSION['mysql']->query($_POST['testTexte']) or die("Problème #1 dans paramsEdit : " . $_SESSION['mysql']->error);
    while ($ligne = mysqli_fetch_row($resultat)) {
        $compteur++;
        $result .= $compteur . " - " . $ligne[7] . " - " . $ligne[1] . " " . "<br>\n";
    }
    $sortie .= "<div>" . $result . "</div>";
}

$sortie .= "
<form id='testeTexte' action='paramsEdit.php' method='post'>
    <input type='text' name='testTexte' id='testTexte'>
    <button type='submit' value='Valider' name='valider'>Valider</button>
</form>
";

$sortie .= envoieFooter();
echo $sortie;
?>

<script>
    document.getElementById('btnResetPartoches').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Traitement en cours...';

        fetch('paramsEdit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'ajax_action=reset_partoches'
        })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('resetResult');
                if (data.status === 'ok') {
                    resultDiv.style.color = 'green';
                    resultDiv.textContent = data.message;
                } else {
                    resultDiv.style.color = 'red';
                    resultDiv.textContent = data.message;
                }
            })
            .catch(() => {
                const resultDiv = document.getElementById('resetResult');
                resultDiv.style.color = 'red';
                resultDiv.textContent = 'Erreur lors de la requête : ' + error.message;
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Réinitialiser les 50 dernières partoches';
            });
    });
</script>

