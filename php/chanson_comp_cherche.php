<?php
$contenuHtmlCompCherche = "<div class='container'>
  <div class='starter-template'> \n";

$contenuHtmlCompCherche .= "
<FORM  METHOD='POST' ACTION='chanson_liste.php' NAME='Form'>
<label class='inline'>Titre :</label><INPUT TYPE='TEXT' NAME='chercheT' VALUE='' SIZE='64' MAXLENGTH='128' placeholder='recherche chanson par titre'><br>
<label class='inline'>Interprète :</label><INPUT TYPE='TEXT' NAME='chercheI' VALUE='' SIZE='64' MAXLENGTH='128' placeholder='recherche chanson par interprete'><br>
<br>
<label class='inline'> </label><INPUT TYPE='submit' NAME='chercher' VALUE=' chercher ' ><br>
</FORM>";