<?php
require_once("lib/utilssi.php");
require_once("Chiffrement.php");
$pasDeMenu = true;
require_once("menu.php");
require_once("songbook.php");
require_once("document.php");
require_once("chanson.php");
$table = "songbook";
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">

    <!--    Indexation OpenGraph pourles réseaux sociaux-->
    <meta property="og:title" content="Songbooks ukulele en ligne : les partoches du club de ukulele top 5"/>
    <meta property="og:type" content="sur partoches, les amis de top 5 partagent des partoches de ukulélé venues
    de top 5 ou d'ailleurs, pour le plaisir de chanter, en grattant son ukulélé."/>
    <meta property="og:url" content="http://partoches.top5.re/"/>
    <meta property="og:image" content="http://partoches.top5.re/apple-touch-icon-152x152-precomposed.png"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Songbooks ukulele en ligne : les partoches du club de ukulele top 5</title>
    <meta name="description" content="sur partoches, les amis de top 5 partagent des partoohes de ukulélé venues
    de top 5 ou d'ailleurs, pour le plaisir de chanter, en grattant son ukulélé.">
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="icon" href="/favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120-precomposed.png"/>
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152-precomposed.png"/>
</head>

<body>
<div class="navigation">
    <h1>les songbooks en ligne

        <a href="chanson_liste.php" class="btn btn-success">
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
						src="../data/songbooks/<?= $songbook[0] ?>/<?= $imageSongBook ?>" loading="lazy"  alt="<?= $songbook[1] ?>"/>
             <?php
            } else {
                // Sinon, on affiche un lien vers le doc + l'image
                ?>
						<a href="../data/songbooks/<?= myUrlEncode($songbook[0]) ?>/<?= $pdfSongbook ?>"
						   target="_blank">
						   <img src="../data/songbooks/<?= $songbook[0] ?>/<?= $imageSongBook ?>" loading="lazy"
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

                $fichier = "../data/chansons/" . $ligneDoc [6] . "/" . myUrlEncode(composeNomVersion($ligneDoc [1], $ligneDoc [4]));
                $icone = Image("../images/icones/" . $fichier [2] . ".png", 32, 32, "icone");
                if (!file_exists("../images/icones/" . $fichier [2] . ".png")) {
                    $icone = Image("../images/icones/fichier.png", 32, 32, "icone");
                }
                $titreCourt = htmlspecialchars(limiteLongueur($titresChansons [$ligneDoc [6]], 18), ENT_QUOTES);
                echo "<a href= '" . $fichier . "' target='_blank' title='" . htmlspecialchars($titresChansons [$ligneDoc [6]], ENT_QUOTES) . "'> " . $titreCourt . "</a> \n";

                echo "<a href= 'chanson_voir.php?id=" . $ligneDoc [6] . "' > $iconeMusique </a> <br>\n";
                ?>
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
