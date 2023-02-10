<?php
include('../lib/utilssi.php');
include_once "UtilisateurNote.php";

// On gère le cas d'un appel POST
if($_POST) {
    // Seuls les utilisateurs enregistrés sont autorisés à voter
    if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_INVITE"]) {

        $mediaId = $_POST['mediaId'];
        $mediaNom = $_POST['mediaName'];
        $note = $_POST['rate'];

        /*
         * Le cookie est utilisé pour empêcher quelqu'un de revoter
        $expire = 3600; // 1 heure
        setcookie('tcRatingSystem2'.$mediaNom.$mediaId, 'rated', time() + $expire, '/'); // Place a cookie
        */
        $utilisateurNote = new UtilisateurNote( $note, $_SESSION['id'] , $mediaNom, $mediaId );
        $utilisateurNote->chercheNoteUtilisateur( $_SESSION['id'] , $mediaNom, $mediaId);
        /*
        if ($utilisateurNote->chercheNoteUtilisateur( $_SESSION['id'] , $mediaNom, $mediaId)==1){
            echo "mise à jour <br>";
        }
        else {
            echo "création enregistrement";
        }
        */
        $utilisateurNote->setNote($note);
        $utilisateurNote->creeModifieNoteUtilisateurBDD();
//        $query = $bdd->execute('INSERT INTO tc_tuto_rating (media, rate) VALUES ('.$mediaId.', "'.$rate.'")'); // We insert the new rate
        $result = UtilisateurNote::scoreEtNombreDeVotes($mediaNom, $mediaId);
        $dataBack = array('avg' => $result['average'], 'nbrRate' => $result['nbrRate']);
    }
    else {
        $dataBack = " Seul un utilisateur enregistré peut voter !";
    }
}
else {
    $dataBack = "Pas de données POST reçues !";
}
$dataBack = json_encode($dataBack);
echo $dataBack;
