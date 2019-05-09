<?php
// require_once 'PHPUnit/Autoload.php';

use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function testConstructeur()
    {
        // Avec 11 items et 5 items par page
        $_partoche = new Pagination (11, 5);

        // On doit avoir 3 pages
        $this->assertEquals(3, $_partoche->getNombreDePages());
        // Page en cours = 1
        $this->assertEquals(1, $_partoche->getPageEnCours());
    }

    public function testPagePrecedente()
    {
        // Avec 11 items et 5 items par page
        $_partoche = new Pagination (11, 5);

        // On doit avoir 3 pages
        // Page en cours =
        // item début = 1
        // item fin = 5

        // Page en cours = 1
        $_partoche->goPagePrecedente();
        $this->assertEquals(1, $_partoche->getPageEnCours());

        // En passant page suivante
        $_partoche->goPageSuivante();

        $_partoche->goPagePrecedente();
        // Page en cours = 1
        $this->assertEquals(1, $_partoche->getPageEnCours());
    }

    public function testPageSuivante()
    {
        // Avec 11 items et 5 items par page
        $_partoche = new Pagination (11, 5);

        // On doit avoir 3 pages
        // Page en cours = 1
        // item début = 1
        // item fin = 5

        // En passant page suivante
        $_partoche->goPageSuivante();
        // Page en cours = 2
        $this->assertEquals(2, $_partoche->getPageEnCours());

        // En passant dernière page
        $_partoche->goPageSuivante();
        // Page en cours = 3
        $this->assertEquals(3, $_partoche->getPageEnCours());

        $_partoche->goPageSuivante();
        // Page en cours = 3
        $this->assertEquals(3, $_partoche->getPageEnCours());
    }

    public function testItemsDebutEtFin()
    {
        // Avec 11 items et 5 items par page
        $_partoche = new Pagination (11, 5);

        // On doit avoir 3 pages
        // Page en cours = 1
        // item début = 1
        // item fin = 5

        // item début = 1
        $this->assertEquals(1, $_partoche->getItemDebut());
        // item fin = 5
        $this->assertEquals(5, $_partoche->getItemFin());

        // En passant page suivante
        $_partoche->goPageSuivante();
        // item début = 6
        $this->assertEquals(6, $_partoche->getItemDebut());
        // item fin = 10
        $this->assertEquals(10, $_partoche->getItemFin());

        // En passant dernière page
        $_partoche->goPageSuivante();
        // item début = 11
        $this->assertEquals(11, $_partoche->getItemDebut());
        // item fin = 11
        $this->assertEquals(11, $_partoche->getItemFin());
    }

    public function testSetNombreItemsParPage()
    {
        // Avec 11 items et 5 items par page
        $_partoche = new Pagination (11, 5);

        // On doit avoir 3 pages
        // Page en cours =
        // item début = 1
        // item fin = 5

        // 1 page avec 11 elements
        $_partoche->setNombreItemsParPage(11);
        $this->assertEquals(1, $_partoche->getNombreDePages());
        $this->assertEquals(1, $_partoche->getPageEnCours());

        // 1 page avec 12 elements par page
        $_partoche->setNombreItemsParPage(12);
        $this->assertEquals(1, $_partoche->getNombreDePages());

    }
}