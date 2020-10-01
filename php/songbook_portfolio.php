<?php
include_once("lib/utilssi.php");
$pasDeMenu = true;
include_once("menu.php");
include_once("songbook.php");
include_once("document.php");
include_once("chanson.php");
$table = "songbook";
?>
<!DOCTYPE html>
<html>

<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <title>Songbooks ukulele en ligne</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>

<body>
<div class="navigation">
    <h1>les songbooks en ligne

        <a href="chanson_liste.php" style="bottom: 45px; right: 24px" class="btn btn-success">
            Entrez !
        </a>
    </h1>
</div>

<div class="content-box">

    <?php
    // On se constitue une liste des titres de chansons
    $titresChansons = chargeLibelles("chanson", "nom");

    // Chargement de la liste des songbooks
    $listeSongbooks = chercheSongbooks("nom", "%", "date", false);
    $numligne = 0;

    // Boucle pour tous les songbooks
    while ($songbook = $listeSongbooks->fetch_row()) {
    // Songbook : [0]id [1]nom [2]description [3]date [4]image [5]hits [6]idUser

    $maRequete = "SELECT * FROM document WHERE document.idTable = '$songbook[0]' AND document.nomTable = '$table' ORDER BY document.date ASC";
    $docsSongbook = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheDocumentsTableId #1 : " . $_SESSION ['mysql']->error);

    $pdfSongbook = "vide";
    // Boucle pour sélectionner le dernier pdf rattaché au songbook
    while ($docSongbook = $docsSongbook->fetch_row()) {
        if (strstr(strtolower($docSongbook [1]), "pdf")) {
            $pdfSongbook = composeNomVersion($docSongbook [1], $docSongbook [4]);
            // echo "fichier trouvé : " . $pdfSongbook ." ";
        }
    }
    $imageSongBook = imageSongbook($songbook [0]);
    $dateSongbook = dateMysqlVersTexte($songbook [3]);
    ?>

    <div class="songbook">
        <div class="madate"><a href='./songbook_voir.php?id=<?= $songbook[0] . "'>" . $songbook[1]; ?></a></div>
			<div class="pochette">

            <?php
            // Si on n'a pas de pdf pour le songbook, on affiche juste l'image
            if ($pdfSongbook == "vide") {
                ?>
                
					<img
						src="../data/songbooks/<?= $songbook[0] ?>/<?= $imageSongBook ?>" alt="<?= $songbook[1] ?>"/>
             <?php
            } else {
                // Sinon, on affiche un lien vers le doc + l'image
                ?>
						<a href="../data/songbooks/<?= urlencode($songbook[0]) ?>/<?= $pdfSongbook ?>"
						   target="_blank"> <img
								src="../data/songbooks/<?= $songbook[0] ?>/<?= $imageSongBook ?>"
								alt="<?= $songbook[1] ?>"/></a>
						<?php
            }
            ?>
            </div>
			<div class="titres">
                <?php
            $lignes = chercheLiensDocSongbook('idSongbook', $songbook [0], "ordre", true);
            $listeDocs = "";
            $iconeMusique = "<span class='glyphicon glyphicon-music'></span>";
            while ($ligne = $lignes->fetch_row()) {
                $ligneDoc = chercheDocument($ligne [1]);
                $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);

                $fichier = "../data/chansons/" . $ligneDoc [6] . "/" . urlencode(composeNomVersion($ligneDoc [1], $ligneDoc [4]));
                $icone = Image("../images/icones/" . $fichier [2] . ".png", 32, 32, "icone");
                if (!file_exists("../images/icones/" . $fichier [2] . ".png"))
                    $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
                $titreCourt = htmlspecialchars(limiteLongueur($titresChansons [$ligneDoc [6]], 18), ENT_QUOTES);
                echo "<a href= '" . $fichier . "' target='_blank' title='" . htmlspecialchars($titresChansons [$ligneDoc [6]], ENT_QUOTES) . "'> " . $titreCourt . "</a> \n";
                //echo "<a href= 'getdoc.php?doc=" . $ligne [1] . "' target='_blank'> [t] </a> <br>\n";

                echo "<a href= 'chanson_voir.php?id=" . $ligneDoc [6] . "' > $iconeMusique </a> <br>\n";
                ?>
                    <!--                <a href="http://www.rendevuke.com/eupelode/Quand-je-serai-K.O..pdf" target="_blank">Quand j’serai-->
				<!--                    KO </a> <br/>-->

                    <?php
            }
            ?>
            </div>
			<div class="madate"><?= $dateSongbook; ?></div>
		</div>

        <?php
            } // Boucle tous les songbooks
            ?>

</div>
	<!-- content box -->
</body>

</html>
