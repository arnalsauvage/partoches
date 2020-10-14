<?php
// require_once 'PHPUnit/Autoload.php';

use PHPUnit\Framework\TestCase;

class ChiffrementTest extends TestCase
{
    function setUp()
    {
        @session_start();
    }

    public function testEncrypte()
    {
        // Avec 11 items et 5 items par page
        $_chaine = "kazoo";

        // Page en cours = 1
        $crypt = Chiffrement::crypt($_chaine);

        // Page en cours = 1
        $decrypt = Chiffrement::decrypt($crypt);

        $this->assertEquals("kazoo", $decrypt);
    }

    public function testDecrypte()
    {
        // Avec 11 items et 5 items par page
        $_chaine = "WURycGVqZ3lyUjRHbTVFdFh6UmpKQVNDcmtiQw==";

        // Page en cours = 1
        $decrypt = Chiffrement::decrypt($_chaine);

        $this->assertEquals("kazoo", $decrypt);
    }

}