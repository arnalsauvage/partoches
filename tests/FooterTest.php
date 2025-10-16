<?php
use PHPUnit\Framework\TestCase;

const PHPUNIT_RUNNING = true;
session_start();
require_once "../php/lib/utilssi.php";
require_once "../php/navigation/Footer.php"; // Ton fichier Footer.php

class FooterTest extends TestCase
{
    const CLE_TEST = "footerHtmlTest"; // clé temporaire pour les tests
    const HTML_TEST = "<p>Contenu de test</p>";
    const HTML_MODIF = "<p>Contenu modifié</p>";

    private Footer $footer;

    protected function setUp(): void
    {
        // Création de la table si nécessaire
        $_SESSION["mysql"]->query("
            CREATE TABLE IF NOT EXISTS parametres (
                nom VARCHAR(255) PRIMARY KEY,
                valeur TEXT
            )
        ");

        // On initialise un objet Footer sur la clé temporaire
        $this->footer = new Footer(self::CLE_TEST);
        // On supprime la clé temporaire si elle existe
        $_SESSION["mysql"]->query("DELETE FROM parametres WHERE nom='" . self::CLE_TEST . "'");
    }

    protected function tearDown(): void
    {
        // On supprime la clé temporaire après le test
        $_SESSION["mysql"]->query("DELETE FROM parametres WHERE nom='" . self::CLE_TEST . "'");
    }

    public function testConstructeur()
    {
        $this->assertInstanceOf(Footer::class, $this->footer);
    }

    public function testSetGetHtml()
    {
        $this->footer->setHtml(self::HTML_TEST);
        $this->assertEquals(self::HTML_TEST, $this->footer->getHtml());
    }

    public function testCreeModifieBDD()
    {
        $this->footer->setHtml(self::HTML_TEST);
        $this->footer->sauveBdd();

        // On recharge un nouvel objet pour vérifier la valeur
        $footer2 = new Footer(self::CLE_TEST);
        $this->assertEquals(self::HTML_TEST, $footer2->getHtml());

        // On modifie et on vérifie
        $footer2->setHtml(self::HTML_MODIF);
        $footer2->sauveBdd();
        $footer3 = new Footer(self::CLE_TEST);
        $this->assertEquals(self::HTML_MODIF, $footer3->getHtml());
    }

    public function testGetHtmlForm()
    {
        $this->footer->setHtml(self::HTML_TEST);
        $this->footer->sauveBdd();
        $formHtml = $this->footer->getHtmlForm();
        $this->assertStringContainsString('<textarea', $formHtml);
        $html = htmlspecialchars_decode($formHtml);
        $this->assertStringContainsString("<p>Contenu de test</p>", $html);

        $this->assertStringContainsString('<div class="tab-pane fade" id="footer">
    <div class="mb-3">
        <label for="footerHtml" class="form-label">Contenu HTML du pied de page</label>
        <textarea class="form-control" name="footerHtml" id="footerHtml" rows="10" style="font-family: monospace;">&lt;p&gt;Contenu de test&lt;/p&gt;</textarea>
        <small class="text-muted d-block mt-2">
            Vous pouvez insérer des liens, des images et du texte (balises autorisées : &lt;a&gt;, &lt;br&gt;, &lt;img&gt;, &lt;strong&gt;, &lt;em&gt;).
        </small>
    </div>
</div>', $formHtml);

    }
}
