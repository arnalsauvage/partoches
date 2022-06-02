<?php
include_once("../lib/utilssi.php");
// Objet de gestion des notes utilisateur

class UtilisateurNote
{
    const PX_CCC = 'px,#ccc ';
    const DIV = "</div>";
    private  $_id; // id ds BDD
    private $_note; // note de 1 à 5
    private $_idUtilisateur; // identifiant de l'utilisateur
    private $_nomObjet; // nom de la table de l'objet noté
    private $_idObjet; // id de l'objet noté

    public function __construct($_note, $_idUtilisateur, $_nomObjetNote, $_idObjetNote)
    {
        $this->setId (0);
        $this->setIdUtilisateur( $_idUtilisateur);
        $this->setNomObjetNote($_nomObjetNote);
        $this->setIdObjetNote($_idObjetNote);
        $this->setNote($_note);
    }

    // Créée un champ html de vote à étoiles pour que l'utilisateur vote
    static function starBarUtilisateur($mediaNom, $mediaId, $nombreEtoiles, $largeurEtoiles) {

        $maNote = new UtilisateurNote( 0, 1, 1, 1);
        $maNote->chercheNoteUtilisateur($_SESSION['id'], $mediaNom, $mediaId);

        $nbrPixelsInDiv = $nombreEtoiles * $largeurEtoiles; // Calcule la largeur du DIV en pixels

        $numEnlightedPX = round($nbrPixelsInDiv * $maNote->getNote() / $nombreEtoiles, 0);

        $getJSON = array('nombreEtoiles' => $nombreEtoiles, 'mediaId' => $mediaId); // We create a JSON with the number of stars and the media ID
        $getJSON = json_encode($getJSON);
        $_id_star_bar = $mediaNom."_".$mediaId;
        $starBar = '<div>';
        $starBar .= '<div id="'.$_id_star_bar.'" class="star_bar" style="width:'.$nbrPixelsInDiv.'px; height:'.$largeurEtoiles.'px; background: 
    linear-gradient(to right, #ffc600 0px,#ffc600 '.$numEnlightedPX. self::PX_CCC .$numEnlightedPX. self::PX_CCC .$nbrPixelsInDiv.'px);" >';
//    linear-gradient(to right, #ffc600 0px,#ffc600 '.$numEnlightedPX. self::PX_CCC .$numEnlightedPX. self::PX_CCC .$nbrPixelsInDiv.'px);" rel=\''.$getJSON.'\'>';
        // Une boucle pour créer le nombre d'étoiles demandées
        for ($i=1; $i<=$nombreEtoiles; $i++) {
            $id_etoile = $mediaNom."_".$mediaId."_".$i;
            // echo ("UtilisateurNote.php : idEtoile = $id_etoile <br>\n");
            $starBar .= '<div title="'.$i.'/'.$nombreEtoiles.'" id="'.$id_etoile.'" class="star"';
            // Supprimé pour pouvoir changer son vote
            // if( !isset($_COOKIE[$cookie_name]) )
            $starBar .= '
                onmouseover="overStar('.$mediaId.', '.$i.', '.$nombreEtoiles.'); return false;"
                onmouseout="outStar('.$mediaId.', '.$i.', '.$nombreEtoiles.'); return false;"
                onclick="rateMedia(\'chanson\','.$mediaId.', '.$i.', '.$nombreEtoiles.', '.$largeurEtoiles.'); return false;"
            ';
            $starBar .= '></div>';
        }
        $starBar .= self::DIV;
        $starBar .= '<div class="resultMedia'.$mediaId.'" style="font-size: small; color: grey">'; // We show the rate score and number of rates
        if ($maNote->getNote() == 0) {
            $starBar .= 'Pas (encore) de vote';
        }
        else {

            $starBar .= 'Note : ' . $maNote->getNote() ;
        }
        $starBar .= self::DIV;
        $starBar .= '<div class="box'.$mediaId.'"></div>';
        $starBar .= self::DIV;
        return $starBar;
    }

    // Créée un champ html de vote à étoiles pour visualiser les votes

    static function starBar ($mediaNom, $mediaId, $nombreEtoiles, $largeurEtoiles) {

        // $cookie_name = 'tcRatingSystem2'.$mediaNom.$mediaId;
        $nbrPixelsInDiv = $nombreEtoiles * $largeurEtoiles; // Calcule la largeur du DIV en pixels

        $result = UtilisateurNote::scoreEtNombreDeVotes($mediaNom, $mediaId);

        //nombre de pixels à colorier en jaune selon le score atteint
        $numEnlightedPX = round($nbrPixelsInDiv * $result['average'] / $nombreEtoiles, 0);

        $getJSON = array('nombreEtoiles' => $nombreEtoiles, 'mediaId' => $mediaId); // We create a JSON with the number of stars and the media ID
        $getJSON = json_encode($getJSON);

        $starBar = '<div id="'.$mediaId.'">';
        $starBar .= '<div class="star_bar" style="width:'.$nbrPixelsInDiv.'px; height:'.$largeurEtoiles.'px; background: 
    linear-gradient(to right, #ffc600 0px,#ffc600 '.$numEnlightedPX. self::PX_CCC .$numEnlightedPX. self::PX_CCC .$nbrPixelsInDiv.'px);" rel=\''.$getJSON.'\'>';
        // Une boucle pour créer le nombre d'étoiles demandées
        for ($i=1; $i<=$nombreEtoiles; $i++) {
            $starBar .= '<div title="'.$i.'/'.$nombreEtoiles.'" id="'.$mediaId."_".$i.'" class="star"';
            $starBar .= '></div>';
        }
        $starBar .= self::DIV;
        $starBar .= '<div class="resultMedia'.$mediaId.'" style="font-size: small; color: grey">'; // We show the rate score and number of rates
        if ($result['nbrRate'] == 0) {
            $starBar .= 'Pas (encore) de vote';
        }
        else {
            $starBar .= $result['average'] . '/' . $nombreEtoiles . ' (' . $result['nbrRate'] . ' votes)';
        }
        $starBar .= self::DIV;
        $starBar .= '<div class="box'.$mediaId.'"></div>';
        $starBar .= self::DIV;
        return $starBar;
    }

    // Renvoie le score et nombre de votes pour le media en BDD sous forme de tableau associatif
    static function scoreEtNombreDeVotes($mediaNom, $mediaId)
    {
        $maRequete = 'SELECT round(avg(note), 2) AS average, count(note) AS nbrRate FROM noteUtilisateur WHERE idObjet=' . $mediaId . ' AND nomObjet = "' . $mediaNom . '"';
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème starBar #1 : " . $_SESSION ['mysql']->error);
        $result = $result->fetch_assoc();
        return $result;
    }

    // Cherche la note d'un utilisateur pour un media, et la charge si elle existe renvoie une valeur de résultat true ou d'échec false
    public function chercheNoteUtilisateur($idUtilisateur, $nomObjet, $idObjet)
    {
        $maRequete = "SELECT * FROM noteUtilisateur WHERE idUtilisateur = '$idUtilisateur' AND idObjet = '$idObjet' AND nomObjet = '$nomObjet'";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheNoteUtilisateur #1 : " . $_SESSION ['mysql']->error);
        // renvoie la ligne sélectionnée : id, idUtilisateur, nomObjet, idObjet, note
        if ($ligne = $result->fetch_row()) {
            $this->mysqlRowVersObjet($ligne);
            return (1);
        } else {
            return (0);
        }
    }

    // Passe une ligne résultat mysql dans un objet
    private function mysqlRowVersObjet($ligne)
    {
        $this->_id = $ligne[0];
        $this->_idUtilisateur = $ligne[1];
        $this->_nomObjet = $ligne[2];
        $this->_idObjet = $ligne[3];
        $this->_note = $ligne[4];
    }

    /**
     *      enregistre l'objet en BDD
     */
    public function creeModifieNoteUtilisateurBDD()
    {
        if ($this->_id == 0) {
            $this->creeNoteUtilisateurBDD();
            $this->setId($_SESSION ['mysql']->insert_id);
            return ($this->getId());
        } else {
            $maRequete = "UPDATE noteUtilisateur SET note = '$this->_note'
             WHERE nomObjet = '$this->_nomObjet' AND  idObjet = '$this->_idObjet' AND idUtilisateur = '$this->_idUtilisateur'";
            $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème modif dans creeModifieNoteUtilisateurBDD #1 : " . $_SESSION ['mysql']->error . " requete : " . $maRequete);
            return ($this->_id);
        }
    }

    // Cree une noteUtilisateur et renvoie l'id de la noteUtilisateur créée

    public function creeNoteUtilisateurBDD()
    {
        $maRequete = "INSERT INTO noteUtilisateur (id, idUtilisateur, nomObjet, idObjet, note)
	        VALUES (NULL, 
	        '$this->_idUtilisateur',
	         '$this->_nomObjet',
	          '$this->_idObjet', 
	          '$this->_note')";
        $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème creeNoteUtilisateurBDD#1 : " . $_SESSION ['mysql']->error);
        // On renseigne l'id de l'objet avec l'id créé en BDD
        $this->setId($_SESSION ['mysql']->insert_id);
        return ($this->getId());
    }

    // Supprime une noteUtilisateur si elle existe
    public function supprimeNoteUtilisateur()
    {
        if ($this->getId() <> 0) {
            // On supprime l' enregistrement dans noteUtilisateur
            $maRequete = "DELETE FROM noteUtilisateur WHERE id='" . $this->getId() . "'";
            $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème #1 dans supprimeNoteUtilisateur : " . $_SESSION ['mysql']->error);
        }
    }

    //// GETTERS et SETTERS /////////////////////

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }


    /**
     * @return mixed
     */
    public function getIdObjetNote()
    {
        return $this->_idObjet;
    }

    /**
     * @return mixed
     */
    public function getIdUtilisateur()
    {
        return $this->_idUtilisateur;
    }

    /**
     * @param mixed $idUtilisateur
     */
    public function setIdUtilisateur($idUtilisateur)
    {
        $this->_idUtilisateur = $idUtilisateur;
    }

    /**
     * @return mixed
     */
    public function getNomObjetNote()
    {
        return $this->_nomObjet;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->_note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->_note = $note;
    }

    /**
     * @param mixed $nomObjetNote
     */
    public function setNomObjetNote($nomObjetNote)
    {
        $this->_nomObjet = $nomObjetNote;
    }


    /**
     * @param mixed $idObjetNote
     */
    public function setIdObjetNote($idObjetNote)
    {
        $this->_idObjet = $idObjetNote;
    }

}
