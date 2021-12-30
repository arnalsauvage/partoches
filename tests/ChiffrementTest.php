<?php

use PHPUnit\Framework\TestCase;
require_once("../php/lib/utilssi.php");

class ChiffrementTest extends TestCase
{
    function setUp() :void
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
        $_chaine_chiffree_attendue = "YXFUaU9SdkkvMHZlVFgzRjlRTmZEdjV6Rk9Pd093PT0=";
        $chaine_chiffree = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $chaine_chiffree . "\n";
        $chaine_dechiffree = Chiffrement::decrypt($chaine_chiffree);

        $this->assertEquals($_chaine , $chaine_dechiffree);
    }
}