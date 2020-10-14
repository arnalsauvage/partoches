<?php
// require_once 'PHPUnit/Autoload.php';

use PHPUnit\Framework\TestCase;

class ChiffrementTest extends TestCase
{
    public function testEncrypte()
    {
        // Avec 11 items et 5 items par page
        $_chaine = "kazoo";

        // Page en cours = 1
        $crypt = Chiffrement::crypt($chaine);

        $this->assertEquals("klmklm", $crypt);
    }

}