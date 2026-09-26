<?php
use PHPUnit\Framework\TestCase;

class BaseFunctionalTest extends TestCase
{
    private string $baseUrl = 'http://127.0.0.1';
    private string $cookieFile;

    protected function setUp(): void
    {
        $this->cookieFile = sys_get_temp_dir() . '/test_cookie_' . uniqid() . '.txt';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    private function request(string $path, string $method = 'GET', array $postData = [], bool $followRedirects = true): array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirects);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            $this->markTestSkipped("Serveur local inaccessible ($url) : $error");
        }

        return [
            'status' => $httpCode,
            'body' => $body,
            'url' => $effectiveUrl
        ];
    }

    /**
     * 1. Affichage de la page d'accueil
     */
    public function testAffichagePageAccueil(): void
    {
        $res = $this->request('/php/media/listeMedias.php');
        $this->assertSame(200, $res['status'], "La page d'accueil doit renvoyer un code HTTP 200");
        $this->assertStringContainsString('Partoches', $res['body'], "La page d'accueil doit contenir le titre du site");
    }

    /**
     * 2. Présence du bouton entrer
     */
    public function testPresenceDuBoutonEntrer(): void
    {
        $res = $this->request('/php/media/listeMedias.php');
        $this->assertSame(200, $res['status']);
        $this->assertTrue(
            str_contains($res['body'], 'entrer-btn') || str_contains($res['body'], 'Entrer'),
            "Le bouton d'accès au catalogue ('Entrer' / class 'entrer-btn') doit être présent sur la page d'accueil"
        );
        $this->assertStringContainsString('chanson_liste.php', $res['body'], "Le lien vers la liste des chansons doit être présent");
    }

    /**
     * 3. Présence de medias
     */
    public function testPresenceDeMedias(): void
    {
        $res = $this->request('/php/media/listeMedias.php');
        $this->assertSame(200, $res['status']);
        $this->assertTrue(
            str_contains($res['body'], 'carte-canopee') || str_contains($res['body'], 'Affichage de') || str_contains($res['body'], 'carte-media'),
            "Des cartes de médias doivent être présentes sur la page d'accueil"
        );
    }

    /**
     * 4. Connexion au site
     */
    public function testConnexionAuSite(): void
    {
        // 1. Accès en tant qu'invité : présence du bouton Se connecter
        $resInit = $this->request('/php/chanson/chanson_liste.php?razFiltres=1');
        $this->assertStringContainsString('afficherPopup', $resInit['body'], "L'invité doit voir le bouton de connexion");

        // 2. Envoi du formulaire de login
        $resLogin = $this->request('/php/navigation/login.php', 'POST', [
            'user' => 'admin',
            'pass' => 'kazoo'
        ]);
        $this->assertSame(200, $resLogin['status']);

        // 3. Vérification de l'état connecté sur la page suivante
        $resAfter = $this->request('/php/chanson/chanson_liste.php?razFiltres=1');
        $this->assertTrue(
            str_contains($resAfter['body'], 'glyphicon-off') || str_contains($resAfter['body'], 'logoff=1'),
            "L'utilisateur connecté doit voir le bouton de déconnexion (glyphicon-off)"
        );
    }

    /**
     * 5. Déconnexion du site
     */
    public function testDeconnexionDuSite(): void
    {
        // 1. Connexion préalable
        $this->request('/php/navigation/login.php', 'POST', [
            'user' => 'admin',
            'pass' => 'kazoo'
        ]);

        // 2. Déconnexion
        $resLogout = $this->request('/php/navigation/login.php?logoff=1');
        $this->assertSame(200, $resLogout['status']);

        // 3. Vérification que l'on redevient invité
        $resAfter = $this->request('/php/chanson/chanson_liste.php?razFiltres=1');
        $this->assertStringContainsString('afficherPopup', $resAfter['body'], "Après déconnexion, le bouton se connecter doit réapparaître");
        $this->assertStringNotContainsString('logoff=1', $resAfter['body'], "Le lien de déconnexion ne doit plus être présent");
    }

    /**
     * 6. Page liste chansons : affichage des chansons
     */
    public function testPageListeChansonsAffichage(): void
    {
        $res = $this->request('/php/chanson/chanson_liste.php?razFiltres=1');
        $this->assertSame(200, $res['status'], "La liste des chansons doit être accessible");
        $this->assertStringContainsString('chanson_voir.php?id=', $res['body'], "La page doit lister des liens vers des fiches chansons");
    }

    /**
     * 7. Page songbook : affichage des songbooks
     */
    public function testPageSongbookAffichage(): void
    {
        $res = $this->request('/php/songbook/songbook-portfolio.php');
        $this->assertSame(200, $res['status'], "La page songbook doit renvoyer un code 200");
        $this->assertTrue(
            str_contains($res['body'], 'songbook') || str_contains($res['body'], 'Songbook'),
            "La page songbook doit afficher le catalogue de songbooks"
        );
    }

    /**
     * 8. Page strums : affichage de différents strums
     */
    public function testPageStrumsAffichage(): void
    {
        $res = $this->request('/php/strum/strum_liste.php');
        $this->assertSame(200, $res['status'], "La page strums doit renvoyer un code 200");
        $this->assertTrue(
            str_contains($res['body'], 'strum') || str_contains($res['body'], 'Rythmique') || str_contains($res['body'], 'btn-strum-play'),
            "La page strums doit afficher des éléments de rythmiques/strums"
        );
    }
}
