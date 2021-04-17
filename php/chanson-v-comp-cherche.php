<?php
if (isset ($_SESSION['cherche']  )) {
    $nom = $_SESSION['cherche'];
    $nom = htmlspecialchars($nom,ENT_QUOTES );
    }
else {
    $nom = "";
}
$contenuHtmlCompCherche = "
<FORM  METHOD='POST' ACTION='chanson_liste.php' NAME='Form'>
<label class='labelTitreInterprete' >Titre ou interprète:</label>
<INPUT TYPE='TEXT' NAME='cherche' class='rechercheChanson' VALUE='$nom' SIZE='100' MAXLENGTH='128' placeholder=\"recherche chanson par titre ou nom de l'interprete\">
<a class='inline' href='" . $pagination->urlAjouteParam($url,"raz-recherche") . "'> 
<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='red' class='bi bi-x-circle-fill' viewBox='0 0 16 16'>
  <path d='M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z'/>
</svg>
</a>
<br>
<label class='inline'> </label><INPUT TYPE='submit' NAME='chercher' VALUE=' chercher ' >
<br>
</FORM>";
