<?php
use PHPUnit\Framework\TestCase;

class HtmlLintTest extends TestCase
{
    private function getBaseUrl(): string
    {
        // En interne Docker, on utilise 127.0.0.1 (port 80)
        return 'http://127.0.0.1';
    }

    /**
     * @dataProvider pageProvider
     */
    public function testHtmlConformity(string $path, array $params = [])
    {
        $baseUrl = $this->getBaseUrl();
        $params['smoke_test'] = '1';
        $url = $baseUrl . $path . '?' . http_build_query($params);
        
        $content = @file_get_contents($url);

        if ($content === false) {
            $this->markTestSkipped("URL inaccessible : $url");
        }

        // --- LINTING VIA DOMDOCUMENT ---
        $dom = new DOMDocument();
        // On désactive les warnings libxml pour les gérer nous-mêmes
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        // Chargement du HTML
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        // On filtre les erreurs pour ne garder que les vrais problèmes de structure
        $criticalErrors = [];
        $ignoredPatterns = [
            'Tag header invalid',
            'Tag nav invalid',
            'Tag section invalid',
            'Tag main invalid',
            'Tag footer invalid',
            'Tag article invalid',
            'Tag aside invalid',
            'Tag output invalid',
            'Tag canvas invalid',
            'Tag video invalid',
            'Tag audio invalid',
            'Tag datalist invalid'
        ];

        foreach ($errors as $error) {
            $isIgnored = false;
            foreach ($ignoredPatterns as $pattern) {
                if (str_contains($error->message, $pattern)) {
                    $isIgnored = true;
                    break;
                }
            }

            if (!$isIgnored && $error->level >= LIBXML_ERR_ERROR) {
                $criticalErrors[] = sprintf(
                    "Ligne %d : %s",
                    $error->line,
                    trim($error->message)
                );
            }
        }

        if (!empty($criticalErrors)) {
            $logDir = __DIR__ . '/../../rendered_html';
            if (!is_dir($logDir)) mkdir($logDir, 0777, true);
            $safeName = str_replace(['/', ' '], ['_', '_'], trim($path, '/'));
            file_put_contents($logDir . '/' . $safeName . '.html', $content);
        }

        $this->assertEmpty($criticalErrors, "La page $path présente des erreurs HTML :\n" . implode("\n", $criticalErrors));
        
        // --- VÉRIFICATION DES IDS EN DOUBLE ---
        $ids = [];
        $tagsWithId = $dom->getElementsByTagName('*');
        foreach ($tagsWithId as $tag) {
            if ($tag->hasAttribute('id')) {
                $id = $tag->getAttribute('id');
                $this->assertNotContains($id, $ids, "ID en double trouvé dans $path : '$id'");
                $ids[] = $id;
            }
        }
    }

    public static function pageProvider(): array
    {
        // On réutilise la liste du SongbookSmokeTest
        require_once __DIR__ . '/SongbookSmokeTest.php';
        return SongbookSmokeTest::pageProvider();
    }
}
