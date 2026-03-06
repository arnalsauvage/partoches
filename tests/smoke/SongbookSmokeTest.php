<?php
use PHPUnit\Framework\TestCase;

class SongbookSmokeTest extends TestCase
{
    /**
     * Détermine l'URL de base (8080 si externe, 80 si interne Docker)
     */
    private function getBaseUrl(): string
    {
        $ports = ['80', '8080'];
        foreach ($ports as $port) {
            $url = "http://localhost:$port";
            $fp = @fsockopen('localhost', (int)$port, $errno, $errstr, 0.1);
            if ($fp) {
                fclose($fp);
                return $url;
            }
        }
        return 'http://localhost:8080';
    }

    /**
     * @dataProvider pageProvider
     */
    public function testPagesDoNotSmoke(string $path, array $params = [])
    {
        $baseUrl = $this->getBaseUrl();
        // On force le mode smoke_test pour avoir les privilèges admin
        $params['smoke_test'] = '1';
        $queryString = http_build_query($params);
        $url = $baseUrl . $path . ($queryString ? '?' . $queryString : '');
        
        $content = @file_get_contents($url);

        if ($content === false) {
            $this->markTestSkipped("Le serveur local n'est pas accessible pour l'URL : $url");
        }

        // Liste des termes "interdits" dans une page saine
        $forbiddenTerms = [
            'Fatal error',
            'Parse error',
            'Warning:',
            'Notice:',
            'Deprecated:',
            'Uncaught Error'
        ];

        foreach ($forbiddenTerms as $term) {
            $this->assertStringNotContainsString($term, $content, "La page $path contient une erreur PHP : '$term'");
        }
    }

    /**
     * Liste des pages à tester
     */
    public static function pageProvider(): array
    {
        return [
            'Accueil (Medias)' => ['/php/media/listeMedias.php', []],
            'Liste Songbooks' => ['/php/songbook/songbook_liste.php', ['vue' => 'cartes']],
            'Voir Songbook (ID 40)' => ['/php/songbook/songbook_voir.php', ['id' => 40]],
            'Form Songbook (New)' => ['/php/songbook/songbook_form.php', []],
            'Form Songbook (Edit ID 40)' => ['/php/songbook/songbook_form.php', ['id' => 40]],
            'Portfolio Songbook' => ['/php/songbook/songbook-portfolio.php', []],
            'Chanson Voir (ID 1)' => ['/php/chanson/chanson_voir.php', ['id' => 1]],
            'Chanson Chercher (Songbook)' => ['/php/chanson/chanson_chercher.php', ['idSongbook' => 1]],
            'Document Voir (Table ID 1)' => ['/php/document/documents_voir.php', ['idTable' => 1, 'nomTable' => 'songbook']],
            'Songbook Get (Mode Test)' => ['/php/songbook/songbook_get.php', ['mode' => 'TEST']],
        ];
    }
}
