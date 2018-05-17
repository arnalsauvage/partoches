<?php
// On utilise la librairue fpdf http://www.fpdf.org/
// qui s'appui sur la librairie fpdi https://www.setasign.com/products/fpdi/downloads

require_once('fpdf/fpdf.php');
require_once('fpdi/autoload.php');
require_once('fpdi/Fpdi.php');
use \setasign\Fpdi\Fpdi;


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
    $pdf->Image("songbook-Madelon-v2.png",5,5,200, 287);
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->SetTextColor(50,50,50);
    $pdf->Cell(0,10,'Sommaire',1,1,'C'); // Centré
    $pdf->Cell(10,10," ",0,1,"L");
    $pdf->Cell(10,10,"Chanson 1",0,1,"L");
    $pdf->Cell(10,10,"Chanson 2", 0,1,"L");
    ajouteFichier($pdf,"germaine.pdf");
    ajouteFichier($pdf,"laJument.pdf");
    $pdf->Output('compile.pdf','F');
    echo ("Fichier <a href='compile.pdf'>compile.pdf</a> généré à partir de Germaine et La Jument de Michao");
}

function pdfCreeSongbook($idSongBook, $imageCouverture, $listeNomsChanson, $listeNomsFichiers, $listeIdChanson, $listeVersionsDoc)
{
    $pdf = new FPDI();
    $pdf->AddPage();
    $pdf->Image("../data/songbooks/".$idSongBook."/".$imageCouverture, 5, 5, 200, 287);
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->SetTextColor(50,50,50);
    $pdf->Cell(0,10,'Sommaire',1,1,'C'); // Centré
    $pdf->Cell(10,10," ",0,1,"L");
    foreach ($listeNomsChanson as $nomChanson){
        $pdf->Cell(10,10,$nomChanson,0,1,"L");
    }

    foreach ($listeNomsFichiers as $nomFichier){
        $idChanson = array_shift($listeIdChanson); // Pour récupérer l'id de la chanson
        $versionDoc = array_shift($listeVersionsDoc);
        $nomFichier = composeNomVersion($nomFichier, $versionDoc);
        //echo ("Tentative d'ajout du fichier : ".$nomFichier . "\n<br>");
        try {
            ajouteFichier($pdf,"../data/chansons/".$idChanson."/".$nomFichier);
        } catch (Exception $e) {
            echo "Le fichier $nomFichier n'a pas été traité. <br> Exception reçue : ",  $e->getMessage(), "\n<br>";
        }
    }

    $pdf->Output("../data/songbooks/".$idSongBook."/".'songbook_auto.pdf','F');
    // Enregistrement du document en base de données
    $taille = filesize("../data/songbooks/".$idSongBook."/".'songbook_auto.pdf');
    $version = creeModifieDocument("songbook_auto.pdf", $taille, "songbook", $idSongBook );
    $nouveauNom = composeNomVersion("songbook_auto.pdf", $version);
    rename ("../data/songbooks/".$idSongBook."/".'songbook_auto.pdf',"../data/songbooks/".$idSongBook."/".$nouveauNom);
    echo ("Fichier <a href='../data/songbooks/$idSongBook/$nouveauNom' target='_blank''>$nouveauNom</a> généré à partir de la liste des partoches");
}

function testeCreeSongBook(){
    $listeNomsChanson = ["Carmen","Carmen Tab"];
    $listeNomsFichiers = ["Habanera-v1.pdf","Habanera-Tablature-v1.pdf"];

    pdfCreeSongbook(28,"songbook-LesFacesA-v1.jpg",$listeNomsChanson, $listeNomsFichiers);
}

//testePdf();
//testeCreeSongbook();
