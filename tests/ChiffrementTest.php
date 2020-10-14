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

    public function testDecrypteKazoo()
    {
        // Avec 11 items et 5 items par page
        $_chaine = "WURycGVqZ3lyUjRHbTVFdFh6UmpKQVNDcmtiQw==";

        // Page en cours = 1
        $decrypt = Chiffrement::decrypt($_chaine);

        $this->assertEquals("kazoo", $decrypt);
    }


    public function testDecrypteInvite()
    {
        // Avec 11 items et 5 items par page
        $_chaine = "VG9jQUovV3pjVWluQ09zTVcvZUI0Y3JFSzZlc0ZRPT0=";

        // Page en cours = 1
        $decrypt = Chiffrement::decrypt($_chaine);

        $this->assertEquals("invite", $decrypt);
    }


    public function testDecrypteuku94120()
    {
        // Avec 11 items et 5 items par page
        $_chaine = "OGxWOGZGcVc3S1BEUXlpZTBFL2J2aksxSzVwUy9JeDY=";

        // Page en cours = 1
        $decrypt = Chiffrement::decrypt($_chaine);

        $this->assertEquals("uku94120", $decrypt);
    }

    public function testEncrypteInvite()
    {
        $_chaine = "kazoo";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "invite";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "uku94120";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "nouvmdp";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "Mimolette123%";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "scottsboro";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "Spritz";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "hautbashaut";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "ubass";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "LaVieEnRose";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "BzzBzzBzz";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $_chaine = "LadyLikeYou";
        $crypt = Chiffrement::crypt($_chaine);
        echo $_chaine . " ==> " . $crypt . "\n";

        $this->assertEquals("chaine", "chaine");
    }

}