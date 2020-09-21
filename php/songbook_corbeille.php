<h2> Corbeille des fichiers effacés</h2>
<?php
$debug = true;
//echo "<br> En bdd : <br>;";
$fichiersEnBdd = [];
$resultat = chercheDocumentsTableId($table, $id);
while ($fichierEnBdd = $resultat->fetch_row()) {
    array_push($fichiersEnBdd, $fichierEnBdd);
    // echo "fichier en bdd :" ;
    //print_r($fichierEnBdd);

}

$fichiersSurDisque = fichiersSongbook($id); // repertoire nom extension

$nbFichiersKO = 0;
//   echo "Fichiers du songbook : <br>";
// print_r ($fichiersSurDisque);

while (count($fichiersSurDisque) > 0) {
    // echo "nb fichiers : "     .   count($fichiersSurDisque);
    $fichierSurDisque = array_shift($fichiersSurDisque);
    //echo ".......FichierDisque ". $fichierSurDisque[1] ."<br>";
    $fichierOk = false;
    foreach ($fichiersEnBdd as $fichierEnBdd) {
        // echo "cherche version du " . $fichierEnBdd[1] . " " . $fichierEnBdd[4] . "<br>";
        // si le fichierBDD est sur disque, alors fichierOk
        if (composeNomVersion($fichierEnBdd[1], $fichierEnBdd[4]) == $fichierSurDisque[1]) {
            $fichierOk = true;
            //echo "Fichier $fichierSurDisque[1] trouvé !!!!!!!!!!!!!!!!!!!<br>";
        }

    }
    if (!$fichierOk) {
        $nbFichiersKO++;
        //   echo "Fichier $fichierSurDisque[1] NON trouvé !!!!!!!!!!!!!!!!!!!<br>";
        echo "Fichier corbeille : " . $fichierSurDisque[1] . " non répertorié par la Bdd ";
        echo boutonSuppression("songbook_get.php?nomFic=" . urlencode($fichierSurDisque[1]) . "&mode=SUPPRFICPOU&id=$id", $iconePoubelle, $cheminImages) . "<br>";
        $numeroElement = count($fichiersSurDisque) + 1;
        ?>
        <button onclick='restaureDocument<?php echo $numeroElement; ?>()'>Restaurer le document dans le songbook
        </button>

        <div id="div<?php echo $numeroElement; ?>"></div>
        <script>
            function restaureDocument<?php echo $numeroElement;?>() {
                $.ajax({
                    type: "POST",
                    url: "songbook_get.php",
                    data: "id=<?php echo $id;?>&nomFic=<?php echo $fichierSurDisque[1];?>&mode=RESTAUREDOC",
                    datatype: 'html', // type de la donnée à recevoir
                    success: function (code_html, statut) { // success est toujours en place, bien sûr !
                        if (code_html.search("n'a pas été traité.") === -1)
                            toastr.success("Le document a été restauré ! <br> Le fichier a été raccroché au songbook <br> Vous pouvez raffraîchir la page pour le voir.");
                        else {
                            toastr.warning("Erreur dans l'opération...<br>Le document n'a pas pu être raccroché...");
                            $("#div1").html(code_html);
                        }
                    },
                    error: function (resultat, statut, erreur) {
                        $("#div<?php echo $numeroElement;?>").html(resultat);
                    }

                });
            }

            function formSuccess() {
                $("#msgSubmit").removeClass("hidden");
            }
        </script>

        <?php
    }
}
if ($nbFichiersKO == 0) {
    echo "La corbeille est vide pour ce songbook.\n";
}

echo "    </div> \n";
echo "        	<script src='../js/chansonForm.js '></script>";
?>