<?php

use PHPUnit\Framework\TestCase;

require_once("../php/lib/utilssi.php");
require_once("../php/UtilisateurNote.php");

class UtilisateurNoteTest extends TestCase
{
    function setUp():void
    {
        @session_start();
    }

    public function testSetIdObjetNote()
    {
        $_utilisateurNote = new UtilisateurNote ( 5, 12, "chanson", 3);
        $this->assertEquals(5, $_utilisateurNote->getNote());
        $this->assertEquals(3, $_utilisateurNote->getIdObjetNote());
        $this->assertEquals(12, $_utilisateurNote->getIdUtilisateur());
        $this->assertEquals("chanson", $_utilisateurNote->getNomObjetNote());
    }

    public function testEnregistreNoteBDD()
    {
        $_utilisateurNote = new UtilisateurNote ( 5, 12, "chanson", 3);
        $id = $_utilisateurNote->creeNoteUtilisateurBDD();
        $_userCharge = new UtilisateurNote ( 6, 13, "chanson", 4);
        $_userCharge->chercheNoteUtilisateur(12,"chanson",3);
        $this->assertEquals(5, $_userCharge->getNote());
    }

    public function testChercheEtSupprimeNote()
    {
        $_utilisateurNote = new UtilisateurNote ( 0, 0, "rien", 0);
        $_utilisateurNote->chercheNoteUtilisateur(12,"chanson", 3);
        $_utilisateurNote->supprimeNoteUtilisateur();
        $this->assertEquals(0,$_utilisateurNote->chercheNoteUtilisateur(12,"chanson", 3));
    }
}
