<?php
include_once ("lib/utilssi.php");
$pasDeMenu = true;
include_once ("menu.php");
include_once ("songbook.php");
include_once ("document.php");
include_once("chanson.php");
$table = "songbook";
?>
<html>

<head>
<meta content="text/html; charset=UTF-8" http-equiv="content-type">
<title>Songbooks ukulele en ligne</title>
<link rel="stylesheet" type="text/css" href="../css/styles.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>

<body>
	<div class="navigation">
		<h1>les songbooks en ligne</h1>
		<ul>
			<a href="chanson_liste.php">
				<li>par genre</li>
				<li>par interprète</li>
				<li>par année ...</li>
			</a>
		</ul>
	</div>

	<div class="content-box">

    <?php
				// On se constitue une liste des titres de chansons
				$titresChansons = chargeLibelles ( "chanson", "nom" );
				
				// Chargement de la liste des songbooks
				$resultatSongbook = chercheSongbooks ( "nom", "%", "date", false );
				$numligne = 0;
				
				// Boucle pour tous les songbooks
				while ( $songbook = $resultatSongbook->fetch_row () ) {
					// Songbook : [0]id [1]nom [2]description [3]date [4]image [5]hits [6]idUser
					
					$docsSongbook = chercheDocumentsTableId ( "songbook", $songbook [0] );
					$nomFichier = "vide";
					while ( $docSongbook = $docsSongbook->fetch_row () ) {
						if (strstr ( strtolower ( $docSongbook [1] ), "pdf" ))
							$nomFichier = composeNomVersion ( $docSongbook [1], $docSongbook [4] );
					}
					$imageSongBook = imageSongbook ( $songbook [0] );
					$dateSongbook = dateMysqlVersTexte ( $songbook [3] );
					?>

        <div class="songbook">
			<div class="pochette">
            <?php
            		// Si on n'a pas de pdf pour le songbook, on affiche juste l'image
					if ($nomFichier == "vide") {
						?>
                
					<img
						src="../data/songbooks/<?= $songbook[0] ?>/<?= $imageSongBook ?>" alt="<?= $songbook[1] ?>"/>
             <?php
					} else {
						// Sinon, on affiche un lien vers le doc + l'image
						?>
					<a href="../data/songbooks/<?= $songbook[0] ?>/<?= $nomFichier ?>"
					target="_blank"> <img
					src="../data/songbooks/<?= $songbook[0] ?>/<?= $imageSongBook ?>"
					alt="<?= $songbook[1] ?>"/></a>
            	<?php
					}
					?>
            </div>
			<div class="titres">
                <?php
					$lignes = chercheLiensDocSongbook ( 'idSongbook', $songbook [0], "ordre", true );
					$listeDocs = "";
				$iconeMusique = "<span class='glyphicon glyphicon-music'></span>";
					while ( $ligne = $lignes->fetch_row () ) {
						$ligneDoc = chercheDocument ( $ligne [1] );
						$fichierCourt = composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
						
						$fichier = "../data/chansons/" . $ligneDoc [6] . "/" . composeNomVersion ( $ligneDoc [1], $ligneDoc [4] );
						$icone = Image ( "../images/icones/" . $fichier [2] . ".png", 32, 32, "icone" );
						if (! file_exists ( "../images/icones/" . $fichier [2] . ".png" ))
							$icone = Image ( "../images/icones/fichier.png", 32, 32, "icone" );
						$titreCourt = limiteLongueur($titresChansons [$ligneDoc [6]], 18);
						echo "<a href= '" . $fichier . "' target='_blank' title='" . $titresChansons [$ligneDoc [6]] . "'> " . $titreCourt . "</a> \n";
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
				}
				?>

</div>
	<!-- content box -->
</body>

</html>
