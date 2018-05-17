<?php
include_once ("lib/utilssi.php");
include_once ("menu.php");
include_once ("chanson.php");
include_once ("document.php");
$table = "chanson";
$contenuHtml = "<div class='container'>
  <div class='starter-template'> \n";
$monImage = "";

$fichiersDuSongbook = fichiersChanson ( $_GET ['id'] );

// On choisit une des images du songbook
$monImage = imageTableId("chanson", $_GET ['id']);

$donnee = chercheChanson ( $_GET ['id']);
if ($donnee != null) {
    $idChanson = $_GET ['id'];
    $nom = $donnee [1]; // nom
    $interprete = $donnee [2]; // interprete
    $annee = $donnee [3]; // annee
    $tempo = $donnee [4]; // tempo
    $mesure = $donnee [5]; // mesure
    $pulsation = $donnee [6]; // pulsation
    $datePub = dateMysqlVersTexte ( $donnee [7] ); // datePub
    $idauteur = $donnee [8]; // idUser
    $nomAuteur = chercheUtilisateur ( $idauteur );
    $nomAuteur = $nomAuteur [3];
    $hits = $donnee [9] + 1; // hits
    $tonalite = $donnee [10]; // tonalite
}

$contenuHtml .= "<div class='row'>";
    $contenuHtml .= "<section class='col-sm-8'>";

        $contenuHtml .= "<div class='row'>";
            $contenuHtml .= " <div class='col-sm-10'><h2>$donnee[1]</h2></div>"; // Titre
            $contenuHtml .= "<div class='col-sm-2'>";

                if ($_SESSION ['privilege'] > 1) {
                    $contenuHtml .= Ancre("chanson_form.php?id=" . $_GET ['id'], Image($cheminImages . $iconeEdit, 32, 32)); // Nom));
                }
        $contenuHtml .= "</div></div>";

    $contenuHtml .= "<div class='row'>";
        $contenuHtml .= " <div class='col-sm-11'><h3>$interprete - $annee </h3></div>\n";
    $contenuHtml .= "</div>";
    $contenuHtml .= "<div class='row'>";
        $contenuHtml .= " <div class='col-sm-8'>Tonalité : $tonalite, Tempo : $tempo, mesure : $mesure, pulsation : $pulsation <br>\n";
        $contenuHtml .= " Publiée le  :$datePub, par $nomAuteur, affichée $hits fois. <br>\n</div>\n";

// Propose des recherches sur la chanson
        $contenuHtml .= "<div class='col-sm-4'><a href='https://www.youtube.com/results?search_query=" . urlencode ( $donnee [1] . " " . $interprete) . "' target='_blank'><img src='https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/YouTube_Logo_2017.svg/280px-YouTube_Logo_2017.svg.png' width='64'></a>\n";
        $rechercheWikipedia = "https://fr.wikipedia.org/w/index.php?search=" . urlencode ( ($donnee [1] . " " . $donnee [2]) );
        $contenuHtml .= "<a href='$rechercheWikipedia' target='_blank'><img src='https://fr.wikipedia.org/static/images/project-logos/frwiki.png' width='64'></a><br>\n</div>\n";
    $contenuHtml .= "</div>";
    $contenuHtml .= "</section>";
    $contenuHtml .= "<section class='col-sm-4'>";


    if ("" != $monImage) {
        $contenuHtml .= Image ( "../data/chansons/". $_GET ['id'] . "/" . $monImage, 200, "", "pochette", "img-thumbnail"  );
    }
    $contenuHtml .= "</section>";
$contenuHtml .= "</div>";

$contenuHtml .= "<h2> Documents attachés à cette chanson</h2>";

// Cherche un document et le renvoie s'il existe
$result = chercheDocumentsTableId ( "chanson", $idChanson );

augmenteHits ( $table, $idChanson );

$contenuHtml .= "<section class='row'>\n";

// Pour chaque document
while ( $ligne = $result->fetch_row () ) {

    $contenuHtml .= "<div class='col-xs-4 col-sm-3 col-md-2 centrer'>\n";
    $fichierCourt = composeNomVersion ( $ligne [1], $ligne [4] );
    // $fichier = "../data/chansons/" . $_GET ['id'] . "/" . composeNomVersion ( $ligne [1], $ligne [4] );
    $fichierSec = substr (  $ligne [1],0,strrpos ( $ligne [1], '.' ) );
    $extension = substr ( strrchr ( $ligne [1], '.' ), 1 );
    $icone = Image ( "../images/icones/" . $extension . ".png", 32, 32, "icone" );
    if (! file_exists ( "../images/icones/" . $extension . ".png" ))
        $icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );

    $contenuHtml .= "<a href= '" . lienUrlAffichageDocument($ligne [0]) . "' target='_blank'> $icone  <br>" . htmlentities($fichierSec) . "</a> <br>\n";
    $contenuHtml .= "</div>";
}
$contenuHtml .= " </section>\n";

/// Affichage des audios mp3 avec un outil de lecture audio

$contenuHtml .= "<br><section class='row'>\n";
$result = chercheDocumentsTableId("chanson", $idChanson);

// Pour chaque fichier audio
while ($ligne = $result->fetch_row()) {
    $fichierCourt = composeNomVersion($ligne [1], $ligne [4]);
    $fichierSec = substr($ligne [1], 0, strrpos($ligne [1], '.'));
    $extension = substr(strrchr($ligne [1], '.'), 1);
    if ($extension == "mp3") {
        $contenuHtml .= "<div class='col-xs-12 col-sm-6 col-md-4 centrer'>\n";
        $baliseAudio = htmlentities($fichierSec) . "<br><audio controls='controls'>   <source src='" . lienUrlAffichageDocument($ligne [0]) . "' type='audio/mp3'>
            Votre navigateur ne prend pas en charge l'élément <code>audio</code></audio>";
        $contenuHtml .= $baliseAudio . "\n";
        $contenuHtml .= "</div>";
    }
}
$contenuHtml .= " </section>\n";


$contenuHtml .= "</div><!-- /.starter-template -->\n
</div><!-- /.container -->\n";
$contenuHtml .= envoieFooter (  );
echo $contenuHtml;
