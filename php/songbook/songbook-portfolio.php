<?php
require_once("../lib/utilssi.php");

require_once("../navigation/menu.php");
require_once("songbook.php");
require_once("../document/document.php");
require_once("../chanson/chanson.php");
$table = "songbook";

global $_DOSSIER_CHANSONS;

?>

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
            // pour debug : echo "fichier trouvé : " . $pdfSongbook ." ";
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
            $largeur_max_vignette = 200;
            $hauteur_max_vignette = "";
            $baliseImage = afficheVignette($imageSongBook, "../../data/songbooks/$songbook[0]/" , "../../data/songbooks/vignettes/", "vignette du songbook " . $songbook [1]);
            if ($pdfSongbook == "vide") {
                echo $baliseImage;
            } else {
                // Sinon, on affiche un lien vers le doc + l'image
                ?>
						<a href="../../data/songbooks/<?= myUrlEncode($songbook[0]) ?>/<?= $pdfSongbook ?>" target="_blank">
			<?php
                echo $baliseImage;
            }
            ?>
            </a>
            </div>
			<div class="titres" style="height: 240px; overflow:hidden">
                <?php
            $lignes = chercheLiensDocSongbook('idSongbook', $songbook [0], "ordre");
            $listeDocs = "";
            $iconeMusique = "<span class='glyphicon glyphicon-music'></span>";
            while ($ligne = $lignes->fetch_row()) {
                $ligneDoc = chercheDocument($ligne [1]);
                $fichierCourt = composeNomVersion($ligneDoc [1], $ligneDoc [4]);

                $fichier =  $_DOSSIER_CHANSONS . $ligneDoc [6] . "/" . myUrlEncode(composeNomVersion($ligneDoc [1], $ligneDoc [4]));
                $icone = Image(  ICONES . $fichier [2] . ".png", 32, 32, "icone");
                if (!file_exists(ICONES . $fichier [2] . ".png")) {
                    $icone = Image(ICONES . "fichier.png", 32, 32, "icone");
                }
                $titreCourt = htmlspecialchars(limiteLongueur($titresChansons [$ligneDoc [6]], 18), ENT_QUOTES);
                echo "<a href= '../../" . $fichier . "' target='_blank' title='" . htmlspecialchars($titresChansons [$ligneDoc [6]], ENT_QUOTES) . "'> " . $titreCourt . "</a> \n";

                echo "<a aria-label='ouvrir la chanson " . htmlspecialchars($titresChansons [$ligneDoc [6]]) . "' href= '../chanson/chanson_voir.php?id=" . $ligneDoc [6] . "' > $iconeMusique </a> <br>\n";
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
</div>
</div>	<!-- content box -->
