<?php
include_once("lib/utilssi.php");
include_once "lib/configMysql.php";
include_once("lib/pdf.php");
include_once("lienDocSongbook.php");
include_once("document.php");

$songbookForm = "songbook_form.php";
$songbookGet = "songbook_get.php";
$songbookVoir = "songbook_voir.php";
$songbookListe = "songbook_liste.php";
$cheminImagesSongbook = "../data/songbooks/";

// Fonctions de gestion du songbook

// Cherche les songbooks correspondant à un critère
function chercheSongbooks($critere, $valeur, $critereTri = 'contenuFiltrer', $bTriAscendant = true)
{
    $maRequete = "SELECT * FROM songbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    if ($bTriAscendant == false)
        $maRequete .= " DESC";
    else
        $maRequete .= " ASC";
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchesongbook #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche un songbook et le renvoie s'il existe
function chercheSongbook($id)
{
    $maRequete = "SELECT * FROM songbook WHERE songbook.id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchesongbook #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, contenuFiltrer, description, date , image, hits
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Cherche un songbook et la renvoie si elle existe
function chercheSongbookParLeNom($nom)
{
    $maRequete = "SELECT * FROM songbook WHERE songbook.contenuFiltrer = '$nom'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchesongbookParLeNom #1 : " . $_SESSION ['mysql']->error);
    // renvoie la lisgne sélectionnée : id, contenuFiltrer, description, date , image, hits
    if (($ligne = $result->fetch_row()))
        return ($ligne);
    else
        return (0);
}

// Crée un songbook
function creeSongbook($nom, $description, $date, $image, $hits)
{
    $date = convertitDateJJMMAAAA($date);
    $idUSer = $_SESSION ['id'];
    $maRequete = "INSERT INTO songbook VALUES (NULL, '$nom', '$description', '$date', '$image', '$hits', '$idUSer')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creesongbook#1 : " . $_SESSION ['mysql']->error);
}

// Modifie en base la songbook
function modifiesSongbook($id, $nom, $description, $date, $image, $hits)
{
    $date = convertitDateJJMMAAAA($date);
    $maRequete = "UPDATE  songbook
	SET contenuFiltrer = '$nom', description = '$description', date = '$date' , image = '$image', hits = '$hits'
	WHERE id='$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifiesongbook #1 : " . $_SESSION ['mysql']->error);
}

// Cette fonction supprime un songbook si il existe
function supprimeSongbook($idsongbook)
{
    // On supprime les enregistrements dans songbook
    $maRequete = "DELETE FROM songbook
	WHERE id='$idsongbook'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimesongbook : " . $_SESSION ['mysql']->error);
    supprimeliensDocSongbookDuSongbook($idsongbook);

    // TODO : supprimer également le dossier et les fichiers
}

// Cette fonction duplique un songbook si il existe
function dupliqueSongbook($idSongbook)
{
    //   echo " Duplication du songbook $idSongbook";

    // On charge le songbook demandé
    $songbookModele = chercheSongbook($idSongbook);
    if ($songbookModele == 0) {
        return (0);
    }

    // On duplique les enregistrements dans songbook
    $nomModele = $_SESSION ['mysql']->real_escape_string($songbookModele[1]);

    // On crée un nouveau songbook nommé "copie de $nomModele"
    creeSongbook("copie de " . $nomModele, "songbook créé par copie", date("d/m/Y"), "", 0);
    $idDoublon = $_SESSION ['mysql']->insert_id;

    // En suite, on va recopier tous les liens BDD songbook-document
    $result = chercheLiensDocSongbook("idSongbook", $idSongbook, "ordre", true);
    $tabIdDocs = array();
    $indice = 0;
    while ($ligne = mysqli_fetch_assoc($result)) {
        $tabIdDocs[$indice] = $ligne["idDocument"]; // idDocument
        $indice++;
        //     echo "Ajout de l'iddoc " . var_dump($ligne) . " à l'indice $indice";
    }

    // Boucle, insérer tout $tabIddoc dans le nouvel IDSongbook !
    $parcours = 0;
    while ($parcours < $indice) {
        creelienDocSongbook($tabIdDocs[$parcours], $idDoublon);
        $parcours++;
    }
    return (1);
}

// Cette fonction modifie ou crée un songbook si besoin
function creeModifieSongbook($id, $nom, $description, $date, $image, $hits)
{
    if (chercheSongbook($id))
        modifiesSongbook($id, $nom, $description, $date, $image, $hits);
    else
        creeSongbook($nom, $description, $date, $image, $hits);
}

// Cette fonction renvoie l'image vignette d'un songbook
function imageSongbook($idSongbook)
{
    $maRequete = "SELECT * FROM document WHERE document.idTable = '$idSongbook' AND document.nomTable='songbook' ";
    $maRequete .= " AND ( document.contenuFiltrer LIKE '%.png' OR document.contenuFiltrer LIKE '%.jpg')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème imageSongbook #1 : " . $_SESSION ['mysql']->error);
    if (empty($result)) {
        return ("");
    }

    // Choisit une vignette au hasard parmi les images
    // renvoie la ligne sélectionnée : id, contenuFiltrer, description, date , image, hits
    if (($ligne = $result->fetch_row())) {
        $nom = composeNomVersion($ligne[1], $ligne[4]);
        return ($nom);
    } else
        return ("");
}

// Cette fonction renvoie une chaine de description de la songbook
function infosSongbook($id)
{
    $enr = chercheSongbook($id);
    // id_journee id_joueur poste statut
    $retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Description : " . $enr [2] . " Date : " . $enr [3] . " image : " . $enr [4] . " Hits : " . $enr [5];
    return $retour . "<BR>\n";
}

// Cette fonction renvoie la liste des fichiers présents dans le répertoire du songbook
// Sous forme de tableau [0] répertoire [1] nomFichier [2] extension

function fichiersSongbook($id)
{
    $retour = array(); // repertoire, contenuFiltrer, extension
    $repertoire = "../data/songbooks/$id/";
    if (is_dir($repertoire)) {
        foreach (new DirectoryIterator ($repertoire) as $fileInfo) {
            if ($fileInfo->isDot() || strpos($fileInfo->getFilename(), ".") == 0)
                continue;
            else
                array_push($retour, array($repertoire, $fileInfo->getFilename(), $fileInfo->getextension()));
        }
    }
    return $retour;
}

function CreeSongBookPdf($id)
{
    // construit la liste des chansons, des fichiers, des id et des versions pour pouvoir générer un pdf
    $listeNomsChanson = [];
    $listeNomsFichier = [];
    $listeIdChanson = [];
    $listeVersionsDoc = [];

    $maRequete = "SELECT document.contenuFiltrer as NomFichier, chanson.contenuFiltrer as NomChanson, chanson.id as IdChanson, document.version as VersionDoc from document LEFT JOIN liendocsongbook ON liendocsongbook.idDocument = document.id LEFT JOIN chanson ON document.idTable = chanson.id
WHERE liendocsongbook.idSongbook =  '$id' ORDER BY liendocsongbook.ordre ASC";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème CreeSongBookPdf #1 : requete" . $_SESSION ['mysql']->error);
    if (empty($result)) {
        $listeNomsFichier = [];
    } else {
        while ($ligne = mysqli_fetch_assoc($result)) {

            //print_r($ligne);
            array_push($listeNomsFichier, $ligne["NomFichier"]);
            array_push($listeNomsChanson, $ligne["NomChanson"]);
            array_push($listeIdChanson, $ligne["IdChanson"]);
            array_push($listeVersionsDoc, $ligne["VersionDoc"]);
        }
    }
    $imageSongBook = imageSongBook($id);
    $ligneSongbook = chercheSongbook($id);

    pdfCreeSongbook($id, $ligneSongbook[1], $imageSongBook, $listeNomsChanson, $listeNomsFichier, $listeIdChanson, $listeVersionsDoc);
}

// Fonction de test
function testeSongbook()
{
    creeSongbook("Songbook #1", "Chansons d été", "31/07/2017", "cover.jpg", 0);
    $id = chercheSongbookParLeNom("Songbook #1");
    $id = $id [0];
    echo infosSongbook($id);

    chercheSongbook($id);
    $id = $id [0];
    echo infosSongbook($id);

    creeSongbook("Songbook #2", "Chansons d automne", "30/11/2017", "cover.jpg", 0);
    $id = chercheSongbookParLeNom("Songbook #2");
    $id = $id [0];
    echo infosSongbook($id);

    creeModifieSongbook($id, "Songbook #2", "Chansons d automne !", "28/11/2017", "cover.jpg", 0);
    $id = chercheSongbookParLeNom("Songbook #2");
    $id = $id [0];
    echo infosSongbook($id);

    $id = chercheSongbookParLeNom("Songbook #2");
    $id = $id [0];
    // supprimesongbook($id);
    echo infosSongbook($id);

    $id = chercheSongbookParLeNom("Songbook #1");
    supprimeSongbook($id[0]);
    $id = chercheSongbookParLeNom("Songbook #2");
    supprimeSongbook($id[0]);

}

// testesongbook ();
// TODO ajouter des logs pour tracer l'activité du site
