<?php

use PHPUnit\Framework\TestCase;

require_once("../php/lib/utilssi.php");
require_once("../php/UtilisateurNote.php");

class htmlTest extends TestCase
{
    function setUp():void
    {
        @session_start();
    }

    public function testSimplifieNom()
    {
        $this->assertEquals("Lopportuniste.pdf", simplifieNomFichier("L'opportuniste.pdf"));
        $this->assertEquals("Le-jeu-du-telephone.pdf", simplifieNomFichier("Le jeu du téléphone.pdf"));
        $this->assertEquals("Viens-a-la-maison.pdf", simplifieNomFichier("Viens à la maison.pdf"));
    }

    public function testEnregistreNoteBDD()
    {
        $_utilisateurNote = new UtilisateurNote (5, 12, "chanson", 3);
        $id = $_utilisateurNote->creeNoteUtilisateurBDD();
        $_userCharge = new UtilisateurNote (6, 13, "chanson", 4);
        $_userCharge->chercheNoteUtilisateur(12, "chanson", 3);
        $this->assertEquals(5, $_userCharge->getNote());
    }

    public function testChercheEtSupprimeNote()
    {
        $_utilisateurNote = new UtilisateurNote (0, 0, "rien", 0);
        $_utilisateurNote->chercheNoteUtilisateur(12, "chanson", 3);
        $_utilisateurNote->supprimeNoteUtilisateur();
        $this->assertEquals(0, $_utilisateurNote->chercheNoteUtilisateur(12, "chanson", 3));
    }

    public function testeEchappeGuillemetsSimples()
    {
        $_titre_original = "Concert d'automne";
        $_titre_modifie = "Concert d&#39;automne";

        $this->assertEquals($_titre_modifie, echappeGuillemetSimple( $_titre_original));
    }
}