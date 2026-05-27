<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la classe Songbook.
 * On teste ici la logique métier pure (POPO) sans interaction BDD.
 */
class SongbookTest extends TestCase
{
    protected function setUp(): void
    {
        // On s'assure que Songbook.php est chargé
        require_once __DIR__ . '/../src/public/php/songbook/Songbook.php';
        
        // Mock de la session MySQL pour éviter les erreurs lors des appels de compatibilité
        // ou si le constructeur tente de toucher à la BDD.
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    /**
     * Teste l'instanciation par défaut (Constructeur sans argument)
     */
    public function testDefaultConstructor(): void
    {
        $sb = new Songbook();
        
        $this->assertEquals(0, $sb->getId());
        $this->assertEquals("", $sb->getNom());
        $this->assertEquals("", $sb->getDescription());
        $this->assertEquals(1, $sb->getType()); // Par défaut Anthologie
        $this->assertEquals("Anthologie", $sb->getLabelType());
        $this->assertEquals(0, $sb->getHits());
    }

    /**
     * Teste les getters et setters
     */
    public function testGettersSetters(): void
    {
        $sb = new Songbook();
        
        $sb->setId(42);
        $sb->setNom("Mon Super Recueil");
        $sb->setDescription("Une description test");
        $sb->setType(2); // Concert
        $sb->setHits(150);
        $sb->setDate("2026-05-27");
        $sb->setImage("couverture.jpg");
        $sb->setIdUser(5);

        $this->assertEquals(42, $sb->getId());
        $this->assertEquals("Mon Super Recueil", $sb->getNom());
        $this->assertEquals("Une description test", $sb->getDescription());
        $this->assertEquals(2, $sb->getType());
        $this->assertEquals("Concert", $sb->getLabelType());
        $this->assertEquals(150, $sb->getHits());
        $this->assertEquals("2026-05-27", $sb->getDate());
        $this->assertEquals("couverture.jpg", $sb->getImage());
        $this->assertEquals(5, $sb->getIdUser());
    }

    /**
     * Teste le mapping des labels de type
     */
    public function testLabelTypes(): void
    {
        $sb = new Songbook();
        
        $sb->setType(1);
        $this->assertEquals("Anthologie", $sb->getLabelType());
        
        $sb->setType(2);
        $this->assertEquals("Concert", $sb->getLabelType());
        
        $sb->setType(3);
        $this->assertEquals("Thématique", $sb->getLabelType());
        
        $sb->setType(99);
        $this->assertEquals("Inconnu", $sb->getLabelType());
    }

    /**
     * Teste l'hydratation depuis une ligne MySQL
     */
    public function testHydrationFromMysqlRow(): void
    {
        $sb = new Songbook();
        
        // Simule une ligne fetch_row() : [0]id, [1]nom, [2]desc, [3]date, [4]image, [5]hits, [6]idUser, [7]type
        $row = [
            '100',
            'Recueil de Test',
            'Description de test',
            '2026-01-01',
            'test.png',
            '50',
            '1',
            '3'
        ];
        
        $sb->mysqlRowVersObjet($row);
        
        $this->assertEquals(100, $sb->getId());
        $this->assertEquals("Recueil de Test", $sb->getNom());
        $this->assertEquals("Thématique", $sb->getLabelType());
        $this->assertEquals(50, $sb->getHits());
    }
}
