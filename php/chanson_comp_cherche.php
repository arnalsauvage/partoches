<?php
if (isset ($_SESSION['cherche']  ))
    $nom = $_SESSION['cherche'];
else
    $nom = "";

$contenuHtmlCompCherche = "
<FORM  METHOD='POST' ACTION='chanson_liste.php' NAME='Form'>
<label class='inline'>Titre ou interprète:</label><INPUT TYPE='TEXT' NAME='cherche' VALUE='$nom' SIZE='64' 
MAXLENGTH='128' placeholder=\"recherche chanson par titre ou nom de l'interprete\"><br>
<br>
<label class='inline'> </label><INPUT TYPE='submit' NAME='chercher' VALUE=' chercher ' ><br>
</FORM>";