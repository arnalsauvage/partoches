<?php
use PHPUnit\Framework\TestCase;

/**
 * Test unitaire pour la classe Document (Django Style)
 */
class DocumentTest extends TestCase
{
    protected function setUp(): void
    {
        // On mocke la session MySQL pour éviter les accès réels à la BDD
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
        
        $mysqlMock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['query', 'error'])
            ->getMock();
            
        $_SESSION['mysql'] = $mysqlMock;
    }

    /**
     * Teste la composition du nom de fichier avec version
     */
    public function testComposeNomVersion()
    {
        $nom = "ma_partition.pdf";
        $version = 2;
        $attendu = "ma_partition-v2.pdf";
        
        $resultat = Document::composeNomVersion($nom, $version);
        $this->assertEquals($attendu, $resultat);
    }

    /**
     * Teste la composition avec un point dans le nom
     */
    public function testComposeNomVersionPointDansNom()
    {
        $nom = "ma.partition.super.pdf";
        $version = 3;
        $attendu = "ma.partition.super-v3.pdf";
        
        $resultat = Document::composeNomVersion($nom, $version);
        $this->assertEquals($attendu, $resultat);
    }

    /**
     * Teste la recherche de document (Mock MySQL)
     */
    public function testChercheDocument()
    {
        $id = 42;
        $mockResult = $this->getMockBuilder(stdClass::class)
            ->addMethods(['fetch_row'])
            ->getMock();
            
        $mockResult->expects($this->once())
            ->method('fetch_row')
            ->willReturn(['42', 'test.pdf', '100', '2026-03-11', '1', 'chanson', '123', '1', '0']);

        $_SESSION['mysql']->expects($this->once())
            ->method('query')
            ->with($this->stringContains("SELECT * FROM document WHERE document.id = '42'"))
            ->willReturn($mockResult);

        $resultat = Document::chercheDocument($id);
        
        $this->assertIsArray($resultat);
        $this->assertEquals('test.pdf', $resultat[1]);
    }
}
