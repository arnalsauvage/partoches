<?php
// On utilise la librairie fpdf http://www.fpdf.org/
// ainsi que la librairie fpdi pour importer des pdf existants https://www.setasign.com/products/fpdi/manual/

const ARIAL = 'Arial';
const IMAGES_ICONES_TOP_5_PNG = '../../images/icones/top5.png';
require_once('fpdf/fpdf.php');
require_once('fpdi/autoload.php');
require_once('fpdi/Fpdi.php');
use setasign\Fpdi\Fpdi;

const DOSSIER_CHANSONS = "../data/chansons/";

class SongBookPDF extends FPDI
{
    const ARIAL = 'Arial';
    public $_nombrePages;

// Page header
    function Header()
    {
/*
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,'Songbook ',1,0,'C');
        // Line break
        $this->Ln(20);
*/
    }

// Page footer
    function Footer()
    {
        if ($this->PageNo() >2) {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont(self::ARIAL, 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }
}

// Ajoute le fichier pdf $file à notre $pdf
function ajouteFichier($pdf, $file)
{
    $nbPage = $pdf->setSourceFile($file);
    for ($pageEnCours = 1; $pageEnCours <= $nbPage; $pageEnCours++) {
        $tplidx = $pdf->ImportPage($pageEnCours);
        $size = $pdf->getTemplatesize($tplidx);
        // echo "Size du fichier :" . $pdf->name . "size :" ;
        // print_r($size);
        $pdf->AddPage('P', array($size['width'], $size['height']));
        $pdf->useTemplate($tplidx);
    }
}

// Teste la génération de pdf
function testePdf()
{
    $pdf = new FPDI();
    $pdf->AddPage();
    $pdf->Image("songbook-Madelon-v2.png", 5, 5, 200, 287);
    $pdf->AddPage();
    $pdf->SetFont(ARIAL, 'B', 16);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->Cell(0, 10, 'Sommaire', 1, 1, 'C'); // Centré
    $pdf->Cell(10, 10, " ", 0, 1, "L");
    $pdf->Cell(10, 10, "Chanson 1", 0, 1, "L");
    $pdf->Cell(10, 10, "Chanson 2", 0, 1, "L");
    ajouteFichier($pdf, "germaine.pdf");
    ajouteFichier($pdf, "laJument.pdf");
    $pdf->Output('compile.pdf', 'F');
    echo "Fichier <a href='compile.pdf'>compile.pdf</a> généré à partir de Germaine et La Jument de Michao";
}

function pdfCreeSongbook($idSongBook, $version, $intitule, $imageCouverture, $listeNomsChanson, $listeNomsFichiers, $listeIdChanson, $listeVersionsDoc)
{
    $pdf = new SongBookPDF();

    $version = pageDeCouverture($pdf, $version, $idSongBook, $imageCouverture, $intitule);

    foreach ($listeNomsFichiers as $nomFichier) {
        $idChanson = array_shift($listeIdChanson); // Pour récupérer l'id de la chanson
        $versionDoc = array_shift($listeVersionsDoc);
        $nomFichier = composeNomVersion($nomFichier, $versionDoc);
        //echo ("Tentative d'ajout du fichier : ".$nomFichier . "\n<br>");
        try {
            ajouteFichier($pdf, "../" . DOSSIER_CHANSONS . $idChanson . "/" . $nomFichier);
        } catch (Exception $e) {
            echo "Le fichier $nomFichier n'a pas été traité. <br> Exception reçue : ", $e->getMessage(), "\n<br>";
        }
    }
    ajouteSommaire($pdf, $listeNomsChanson,  IMAGES_ICONES_TOP_5_PNG );
    $intitule = make_alias ($intitule);
    $intitule = str_replace("'","",$intitule);
    $nom_pdf_songbook = "songbook_".$intitule . ".pdf";
    $pdf->Output(DATA_SONGBOOKS . $idSongBook . "/" . $nom_pdf_songbook, 'F');
    // Enregistrement du document en base de données
    $taille = filesize(DATA_SONGBOOKS . $idSongBook . "/" . $nom_pdf_songbook);
    $version = creeModifieDocument($nom_pdf_songbook, $taille, "songbook", $idSongBook);
    $nouveauNom = composeNomVersion($nom_pdf_songbook, $version);
    rename(DATA_SONGBOOKS . $idSongBook . "/" . $nom_pdf_songbook, DATA_SONGBOOKS . $idSongBook . "/" . $nouveauNom);
    echo "Fichier <a href='../../data/songbooks/$idSongBook/$nouveauNom' target='_blank''>$nouveauNom</a> généré à partir de la liste des partoches";
}

/**
 * @param SongBookPDF $pdf
 * @param $version
 * @param $idSongBook
 * @param $imageCouverture
 * @param $intitule
 * @return int
 */
function pageDeCouverture(SongBookPDF $pdf, $version, $idSongBook, $imageCouverture, $intitule): int
{
// On fait une couverture avec l'image
    $pdf->AddPage();
    $version++;
    $dateDuJour = date("d/m/Y");
    // Position at 1.5 cm from bottom
    $pdf->Image(DATA_SONGBOOKS . $idSongBook . "/" . $imageCouverture, 5, 5, 190, 250);
    $pdf->SetY(260);
    $pdf->SetFont(ARIAL, 'B', 10);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->Cell(0, 12, $intitule . " - v" . $version . " du " . $dateDuJour, 0, 0, "C");
    return $version;
    // TODO : ici on pourrait déterminer le ratio de l'image pour ne pas avoir d'image trop étirée
}

/**
 * @param SongBookPDF $pdf
 * @param $listeNomsChanson
 * @param $str
 */
function ajouteSommaire(SongBookPDF $pdf, $listeNomsChanson, $str): void
{
// On crée un sommaire
    $pdf->AddPage();
    // Logo
    $pdf->SetFont(ARIAL, 'B', 20);
    $pdf->SetTextColor(50, 50, 50);

    $pdf->Cell(30, 10, ' ', 0, 0, "C"); // Centré
    $pdf->Cell(150, 10, 'Sommaire', 1, 1, "C"); // Centré
    // $pdf->Cell(10, 10, " ", 0, 1, "L");
    $pdf->Image( $str , 10, 6, 20);

    // On a une hauteur de 240 à répartir sur la feuille
    $hauteur_ligne = 240 / (count($listeNomsChanson) + 1);
    if ($hauteur_ligne > 40) {
        $hauteur_ligne = 40;
    }

    $pdf->SetFont(ARIAL, 'B', $hauteur_ligne);
    // On met une petite ligne vide pour faire de la place
    $pdf->cell(10, $hauteur_ligne/2, " ", 0, 1, "L");
    $numeroChanson = 2;
    foreach ($listeNomsChanson as $nomChanson) {
        $pdf->Cell(10, $hauteur_ligne, $numeroChanson++ . " - " . utf8_decode($nomChanson), 0, 1, "L");
    }
    /// *** FIN SOMMAIRE /////
}

function make_alias($name)
{
    $alias = mb_strtolower($name, 'UTF-8');
    $alias = mb_strtolower(trim($alias));
    $search = array(utf8_decode('@[ÈÉÊËèéêë]@i'), utf8_decode('@[ÀÁÂÃÄÅàáâãäå]@i'), utf8_decode('@[ÌÍÎÏìíîï]@i'), utf8_decode('@[ÙÚÛÜùúûü]@i'), utf8_decode('@[ÒÓÔÕÖðòóôõö]@i'), utf8_decode('@[çÇ]@i'), utf8_decode('@[Ýýÿ]@i'), utf8_decode('@[,;:!§/.?*°+\'\-]@i'), utf8_decode('@[\s]@'));
    $replace = array('e', 'a', 'i', 'u', 'o', 'c', 'y', '', '-');
    $alias = preg_replace($search, $replace, utf8_decode($alias));
    $search = array('.', ',', '?', ';', ':', '/', '!', '§', '%', 'ù', '*', 'µ', '¨', '^', '$', '£', 'ø', '=', '+', '}', ')', '°', ']', '@', '^', '\\', '|', '[', '{', '#', '~', '}', ']', '&', '²');
    $alias = str_replace($search, '', $alias);
    $search = array('@-{2,}@i');
    $alias = preg_replace($search, '-', $alias);
    $alias = utf8_encode($alias);
    return $alias;
}

function testeCreeSongBook()
{
/*    $listeNomsChanson = ["Carmen", "Carmen Tab"];
    $listeNomsFichiers = ["Habanera-v1.pdf", "Habanera-Tablature-v1.pdf"];

    pdfCreeSongbook(28, "songbook-LesFacesA-v1.jpg", $listeNomsChanson, $listeNomsFichiers);*/
    $listeNomsChanson = ["Chanson 1"];
    $listeNomsFichiers = ["AfficheTop5-Rentree2019.pdf"];
    $listeIdChanson = [154];
    $listeVersionsDoc = [4];

    pdfCreeSongbook(45, "2","Songbook test", "AuBonheurDesDames-v1.jpg", $listeNomsChanson, $listeNomsFichiers, $listeIdChanson , $listeVersionsDoc);
}

//testePdf();
//testeCreeSongbook();
