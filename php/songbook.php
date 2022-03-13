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
/**
 * @param $critere
 * @param $valeur
 * @param string $critereTri
 * @param bool $bTriAscendant
 * @return mixed
 */
function chercheSongbooks($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) :object
{
    $maRequete = "SELECT * FROM songbook WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    if (! $bTriAscendant) {
        $maRequete .= " DESC";
    }
    else
    {
        $maRequete .= " ASC";
    }
//    echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchesongbook #1 : " . $_SESSION ['mysql']->error);
  //  echo "mon resultat : " . var_dump($result);
    return $result;
}

// Cherche un songbook et le renvoie s'il existe
function chercheSongbook($id) :array
{
    $maRequete = "SELECT * FROM songbook WHERE songbook.id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchesongbook #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, description, date , image, hits
    $ligne = $result->fetch_row();
    return ($ligne);
}

// Cherche un songbook et la renvoie si elle existe
function chercheSongbookParLeNom($nom) :array
{
    $maRequete = "SELECT * FROM songbook WHERE songbook.nom = '$nom'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème cherchesongbookParLeNom #1 : " . $_SESSION ['mysql']->error);
    // renvoie la ligne sélectionnée : id, nom, description, date , image, hits
    $ligne = $result->fetch_row();
    return ($ligne);
}

// Crée un songbook
function creeSongbook($nom, $description, $date, $image, $hits, $type)
{
    $date = convertitDateJJMMAAAAversMySql($date);
    $idUSer = $_SESSION ['id'];
    $maRequete = "INSERT INTO songbook VALUES (NULL, '$nom', '$description', '$date', '$image', '$hits', '$idUSer', '$type')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creesongbook#1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Modifie en base la songbook
function modifiesSongbook($id, $nom, $description, $date, $image, $hits, $type)
{
    $date = convertitDateJJMMAAAAversMySql($date);
    $maRequete = "UPDATE  songbook
	SET nom = '$nom', description = '$description', date = '$date' , image = '$image', hits = '$hits', type = '$type'
	WHERE id='$id'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modifiesongbook #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cette fonction supprime un songbook si il existe
function supprimeSongbook($idsongbook)
{
    // On supprime les enregistrements dans songbook
    $maRequete = "DELETE FROM songbook
	WHERE id='$idsongbook'";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimesongbook : " . $_SESSION ['mysql']->error);
    supprimeliensDocSongbookDuSongbook($idsongbook);
    return $result;
    // TODO : supprimer également le dossier et les fichiers
}

// Cette fonction duplique un songbook si il existe
function dupliqueSongbook($idSongbook) :bool
{
    //   echo " Duplication du songbook $idSongbook";

    // On charge le songbook demandé
    $songbookModele = chercheSongbook($idSongbook);
    if ($songbookModele == 0) {
        return (false);
    }

    // On duplique les enregistrements dans songbook
    $nomModele = $_SESSION ['mysql']->real_escape_string($songbookModele[1]);

    // On crée un nouveau songbook nommé "copie de $nomModele"
    creeSongbook("copie de " . $nomModele, "songbook créé par copie", date("d/m/Y"), "", 0, $songbookModele[7]);
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
    return (true);
}

// Cette fonction modifie ou crée un songbook si besoin
function creeModifieSongbook($id, $nom, $description, $date, $image, $hits, $type)
{
    if (chercheSongbook($id)) {
        modifiesSongbook($id, $nom, $description, $date, $image, $hits, $type);
    }
    else {
        creeSongbook($nom, $description, $date, $image, $hits, $type);
    }
}

// Cette fonction renvoie l'image vignette d'un songbook
function imageSongbook($idSongbook) :string
{
    $maRequete = "SELECT * FROM document WHERE document.idTable = '$idSongbook' AND document.nomTable='songbook' ";
    $maRequete .= " AND ( document.nom LIKE '%.png' OR document.nom LIKE '%.jpg')";
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème imageSongbook #1 : " . $_SESSION ['mysql']->error);
    if (empty($result)) {
        return ("");
    }

    // Choisit une vignette au hasard parmi les images
    // renvoie la ligne sélectionnée : id, nom, description, date , image, hits
    if ($ligne = $result->fetch_row()) {
        $nom = composeNomVersion($ligne[1], $ligne[4]);
        return ($nom);
    } else {
        return ("");
    }
}

// Cette fonction renvoie une chaine de description du songbook
function infosSongbook($id) :string
{
    $enr = chercheSongbook($id);
    // id_journée id_joueur poste statut
    $retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Description : " . $enr [2] . " Date : " . $enr [3] . " image : " . $enr [4] . " Hits : " . $enr [5] . " type = " . $enr [7];
    return $retour . "<BR>\n";
}

// Cette fonction renvoie la liste des fichiers présents dans le répertoire du songbook
// Sous forme de tableau [0] répertoire [1] nomFichier [2] extension

function fichiersSongbook($id) :array
{
    $retour = array(); // repertoire, nom, extension
    $repertoire = "../data/songbooks/$id/";
    if (is_dir($repertoire)) {
        foreach (new DirectoryIterator ($repertoire) as $fileInfo) {
            if (! $fileInfo->isDot() && strpos($fileInfo->getFilename(), ".") != 0) {
                array_push($retour, array($repertoire, $fileInfo->getFilename(), $fileInfo->getextension()));
            }
        }
    }
    return $retour;
}

function CreeSongBookPdf($idSongbook)
{
    // construit la liste des chansons, des fichiers, des id et des versions pour pouvoir générer un pdf
    $listeNomsChanson = [];
    $listeNomsFichier = [];
    $listeIdChanson = [];
    $listeVersionsDoc = [];

    $maRequete = "SELECT document.nom as NomFichier, chanson.nom as NomChanson, chanson.id as IdChanson, document.version as VersionDoc from document LEFT JOIN liendocsongbook ON liendocsongbook.idDocument = document.id LEFT JOIN chanson ON document.idTable = chanson.id
WHERE liendocsongbook.idSongbook =  '$idSongbook' ORDER BY liendocsongbook.ordre ASC";
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
    $imageSongBook = imageSongBook($idSongbook);
    $ligneSongbook = chercheSongbook($idSongbook);
    $nom_songbookgenere = make_alias("songbook_".$ligneSongbook[1]) .'.pdf';
    // echo '$nom de document cherche pour la version : ' . $nom_songbookgenere;
    $document = chercheDocumentNomTableId($nom_songbookgenere, "songbook", $idSongbook);
    // renvoie la ligne sélectionnée : id, nom, taille, date, version, nomTable, idTable, idUser
    $version = $document[4];
    pdfCreeSongbook($idSongbook, $version, $ligneSongbook[1], $imageSongBook, $listeNomsChanson, $listeNomsFichier, $listeIdChanson, $listeVersionsDoc);
//     function pdfCreeSongbook($idSongBook, $version, $intitule, $imageCouverture, $listeNomsChanson, $listeNomsFichiers, $listeIdChanson, $listeVersionsDoc)
}

// Renvoie la liste des songbooks en base
function listeSongbooks($type = 0) :array
{
    // Cas de demande de liste des songbooks filtrée par type = 1, 2, 3, ou pas (0)
    if ($type==0){
        $maListeSongbooks = chercheSongbooks("nom", "%",'id', false);
    }
    else{
        $maListeSongbooks = chercheSongbooks("type", "$type", 'id', false);
    }
    $index = 0;
    $liste = [];
    while ($ligne = $maListeSongbooks->fetch_row()) {
        // id
        $liste[$index][0] = $ligne[0];
        // nom
        $liste[$index][1] = $ligne[1];
        $index++;
    }
    return $liste;
}

// Fonction de test
function testeSongbook()
{
    $SONGBOOK_1 = "Songbook #1";
    $cover = "cover.jpg";
    creeSongbook($SONGBOOK_1, "Chansons d été", "31/07/2017", $cover, 0, 1);
    $id = chercheSongbookParLeNom($SONGBOOK_1);
    $id = $id [0];
    echo infosSongbook($id);

    chercheSongbook($id);
    $id = $id [0];
    echo infosSongbook($id);
    $SONGBOOK_2 = "Songbook #2";
    creeSongbook($SONGBOOK_2, "Chansons d automne", "30/11/2017", $cover, 0, 1);
    $id = chercheSongbookParLeNom($SONGBOOK_2);
    $id = $id [0];
    echo infosSongbook($id);

    creeModifieSongbook($id, $SONGBOOK_2, "Chansons d automne !", "28/11/2017", $cover, 0,1);
    $id = chercheSongbookParLeNom($SONGBOOK_2);
    $id = $id [0];
    echo infosSongbook($id);

    $id = chercheSongbookParLeNom($SONGBOOK_2);
    $id = $id [0];
    // supprimesongbook($id);
    echo infosSongbook($id);

    $id = chercheSongbookParLeNom($SONGBOOK_2);
    supprimeSongbook($id[0]);
    $id = chercheSongbookParLeNom($SONGBOOK_2);
    supprimeSongbook($id[0]);

}

// testeSongbook ();
// TODO ajouter des logs pour tracer l'activité du site
