<?php
use PHPUnit\Framework\TestCase;

class SongbookSmokeTest extends TestCase
{
    /**
     * Détermine l'URL de base (8080 si externe, 80 si interne Docker)
     */
    private function getBaseUrl(): string
    {
        // Apache est maintenant configuré pour pointer sur src/public
        return 'http://127.0.0.1';
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
            // --- PAGES DU MENU ---
            'Accueil (Medias)' => ['/php/media/listeMedias.php', []],
            'Songbooks (Liste)' => ['/php/songbook/songbook_liste.php', []],
            'Songbooks (Portfolio)' => ['/php/songbook/songbook-portfolio.php', []],
            'Chansons' => ['/php/chanson/chanson_liste.php', ['razFiltres' => '1']],
            'Strums' => ['/php/strum/strum_liste.php', []],
            'Liens' => ['/php/liens/lienurl_liste.php', []],
            'Utilisateurs' => ['/php/utilisateur/utilisateur_liste.php', []],
            'Documents' => ['/php/document/documents_voir.php', []],
            'Paramétrage' => ['/php/navigation/paramsEdit.php', []],
            'Connexion' => ['/php/navigation/login.php', []],

            // --- PAGES ADMIN / FORMULAIRES ---
            'Form Chanson (New)' => ['/php/chanson/chanson_form.php', []],
            'Form Chanson (Edit ID 728)' => ['/php/chanson/chanson_form.php', ['id' => 728]],
            'Upload Chanson' => ['/php/chanson/chanson_upload.php', []],
            'Form Utilisateur (Edit ID 1)' => ['/php/utilisateur/utilisateur_form.php', ['id' => 1]],
            'Form Songbook (Edit ID 40)' => ['/php/songbook/songbook_form.php', ['id' => 40]],
            'Liste Documents Admin' => ['/php/document/documents_voir.php', []],

            // --- PAGES HTML ET OUTILS ---
            'Mentions Légales' => ['/html/mentionsLegales.html', []],
            'Mercis' => ['/html/merci.html', []],
            'Boîte à Strum' => ['/html/boiteAstrum/index.html', []],

            // --- PAGES DE DÉTAIL / FORMULAIRES ---
            'Voir Songbook (ID 40)' => ['/php/songbook/songbook_voir.php', ['id' => 40]],
            'Form Songbook (Edit ID 40)' => ['/php/songbook/songbook_form.php', ['id' => 40]],
            'Chanson Voir (ID 1)' => ['/php/chanson/chanson_voir.php', ['id' => 1]],
            'Form Strum (Edit ID 1)' => ['/php/strum/strum_form.php', ['id' => 1]],
        ];
    }
}
