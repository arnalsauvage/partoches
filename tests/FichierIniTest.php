<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/public/autoload.php';
require_once __DIR__ . '/../src/public/php/lib/FichierIni.php';

/**
 * Test unitaire pour la classe FichierIni
 */
class FichierIniTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'test_ini_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    /**
     * Teste le chargement d'un fichier INI simple
     */
    public function testLoadFichier()
    {
        $content = <<<INI
[general]
cle1 = valeur1
cle2 = valeur2
[mysql]
host = localhost
INI;
        file_put_contents($this->tempFile, $content);

        $ini = new FichierIni();
        $ini->m_load_fichier($this->tempFile);

        $this->assertEquals('valeur1', $ini->m_valeur('cle1', 'general'));
        $this->assertEquals('valeur2', $ini->m_valeur('cle2', 'general'));
        $this->assertEquals('localhost', $ini->m_valeur('host', 'mysql'));
    }

    /**
     * Teste la modification et l'ajout de valeurs
     */
    public function testMPut()
    {
        $ini = new FichierIni();
        $ini->m_put('valeur_modifiee', 'cle1', 'general');
        $ini->m_put('nouvelle_valeur', 'cle_nouvelle', 'general');

        $this->assertEquals('valeur_modifiee', $ini->m_valeur('cle1', 'general'));
        $this->assertEquals('nouvelle_valeur', $ini->m_valeur('cle_nouvelle', 'general'));
    }

    /**
     * Teste la sauvegarde d'un fichier INI
     */
    public function testSave()
    {
        $ini = new FichierIni();
        $ini->m_load_fichier($this->tempFile);
        $ini->m_put('nouvelle_valeur', 'cle_test', 'general');
        $ini->save();

        $ini2 = new FichierIni();
        $ini2->m_load_fichier($this->tempFile);
        $this->assertEquals('nouvelle_valeur', $ini2->m_valeur('cle_test', 'general'));
    }

    /**
     * Teste le comptage des items
     */
    public function testMCount()
    {
        $ini = new FichierIni();
        $ini->m_put('v1', 'i1', 'g1');
        $ini->m_put('v2', 'i2', 'g1');
        $ini->m_put('v3', 'i3', 'g2');

        $this->assertEquals(2, $ini->m_count('g1'));
        $this->assertEquals(1, $ini->m_count('g2'));
        
        $total = $ini->m_count();
        $this->assertEquals(2, $total[1]); // 2 groupes
        $this->assertEquals(3, $total[0]); // 3 items total
    }

    /**
     * Teste la résolution du chemin params.ini et du dossier logs
     */
    public function testGetParamsIniPathEtLogsDir()
    {
        $paramsPath = FichierIni::getParamsIniPath();
        $this->assertNotEmpty($paramsPath);
        $this->assertStringEndsWith('params.ini', $paramsPath);

        $logsDir = FichierIni::getLogsDir();
        $this->assertNotEmpty($logsDir);
        $this->assertTrue(is_dir($logsDir));
    }
}
