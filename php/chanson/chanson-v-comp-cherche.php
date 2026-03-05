<?php
if (isset ($_SESSION['cherche']  )) {
    $nom = $_SESSION['cherche'];
    $nom = htmlspecialchars($nom,ENT_QUOTES );
    }
else {
    $nom = "";
}
global $pagination;
global $url;
$contenuHtmlCompCherche = "
<div class='row' style='margin-bottom: 20px;'>
    <div class='col-xs-12'>
        <form method='POST' action='chanson_liste.php' name='formRecherche' id='formRecherche' class='form-inline' style='background: none; border: none; width: 100%; padding: 0;'>
            <div class='input-group' style='width: 100%; max-width: 600px;'>
                <input id='rechercheChanson' type='text' name='cherche' class='form-control input-lg' value='$nom' maxlength='128' placeholder=\"Titre ou interprète...\" style='border-radius: 25px 0 0 25px; border: 2px solid #D2B48C; border-right: none; background-color: #fdfaf5; box-shadow: none;'>
                " . ($nom != "" ? "
                <span class='input-group-addon' style='background-color: #fdfaf5; border: 2px solid #D2B48C; border-left: none; border-right: none; padding: 0;'>
                    <a title='Effacer la recherche' href='?raz-recherche' style='color: #8B4513; padding: 0 10px; font-size: 20px; text-decoration: none; display: block; line-height: 40px;'>&times;</a>
                </span>" : "") . "
                <span class='input-group-btn'>
                    <button class='btn btn-lg' type='submit' title='Chercher' style='border-radius: 0 25px 25px 0; background-color: #D2B48C; color: #2b1d1a; border: 2px solid #D2B48C; border-left: none; font-weight: bold; height: 46px;'>
                        <span class='glyphicon glyphicon-search'></span>
                    </button>
                </span>
            </div>
        </form>
    </div>
</div>";
