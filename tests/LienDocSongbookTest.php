<?php
use PHPUnit\Framework\TestCase;

/**
 * Tests pour la classe LienDocSongbook.
 * Note: Cette classe dépend fortement de la BDD via $_SESSION['mysql'].
 */
class LienDocSongbookTest extends TestCase
{
    private $mysqliMock;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../src/public/php/liens/LienDocSongbook.php';
        
        // Mock de l'objet mysqli
        $this->mysqliMock = $this->getMockBuilder(mysqli::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
        $_SESSION['mysql'] = $this->mysqliMock;
    }

    /**
     * Teste la recherche d'un lien par ID
     */
    public function testChercheLienDocSongbook(): void
    {
        $resultMock = $this->getMockBuilder(mysqli_result::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $expectedRow = [1, 10, 20, 1]; // id, idDoc, idSB, ordre
        
        $resultMock->method('fetch_row')->willReturn($expectedRow);
        
        $this->mysqliMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains("SELECT * FROM liendocsongbook WHERE id = '1'"))
            ->willReturn($resultMock);

        $result = LienDocSongbook::chercheLienDocSongbook(1);
        
        $this->assertEquals($expectedRow, $result);
    }

    /**
     * Teste le comptage des liens d'un songbook
     */
    public function testNombreDeLiensDuSongbook(): void
    {
        // On crée un vrai résultat depuis la BDD Docker qui est dispo
        $realDb = new mysqli('db', 'root', 'root', 'dbPartoches');
        $result = $realDb->query("SELECT 1 UNION SELECT 2 UNION SELECT 3"); // 3 lignes
        
        $this->mysqliMock->expects($this->once())
            ->method('query')
            ->willReturn($result);

        $count = LienDocSongbook::nombreDeLiensDuSongbook(123);
        
        $this->assertEquals(3, $count);
        $realDb->close();
    }

    /**
     * Teste la création d'un lien (avec vérification d'existence)
     */
    public function testCreeLienDocSongbookExist(): void
    {
        // On simule que le lien existe déjà
        $resultMock = $this->getMockBuilder(mysqli_result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock->method('fetch_row')->willReturn([1, 10, 20, 1]);
        
        $this->mysqliMock->method('query')->willReturn($resultMock);

        $result = LienDocSongbook::creeLienDocSongbook(10, 20);
        
        $this->assertFalse($result, "Devrait retourner false si le lien existe déjà");
    }

    /**
     * Teste la modification de l'ordre
     */
    public function testModifieOrdreLienDocSongbook(): void
    {
        $this->mysqliMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains("UPDATE liendocsongbook SET ordre = '5' WHERE idDocument = '10' AND idSongbook = '20'"))
            ->willReturn(true);

        $result = LienDocSongbook::modifieOrdreLienDocSongbook(10, 20, 5);
        
        $this->assertTrue($result);
    }
}
