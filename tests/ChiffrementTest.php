<?php

use PHPUnit\Framework\TestCase;

class ChiffrementTest extends TestCase
{
    function setUp()
    {
        @session_start();
    }

    public function testEncrypteDecrypte()
    {
        $_chaine = "DanielSchneiderman";

        $crypt = Chiffrement::crypt($_chaine);

        // Page en cours = 1
        $decrypt = Chiffrement::decrypt($crypt);

        $this->assertEquals($_chaine, $decrypt);
    }

    public function testDecrypteInvite()
    {
        $_chaine = "VG9jQUovV3pjVWluQ09zTVcvZUI0Y3JFSzZlc0ZRPT0=";

        $decrypt = Chiffrement::decrypt($_chaine);

        $this->assertEquals("invite", $decrypt);
    }

    public function testEncrypteInvite()
    {
        $_chaine = "invite";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $this->assertEquals("chaine", "chaine");
    }
}