<?php
use PHPUnit\Framework\TestCase;
// require_once 'PHPUnit/Autoload.php';
session_start();
require_once "../php/lib/utilssi.php";
require_once "../php/strum.php";


class utilssiTest extends TestCase
{
    public function testValidateDate()
    {
        // Etant données les valeurs suivantes
        $dateDonneeKO = "2012/03/01";
        $dateDonneeOk = "01/03/2012";

        // Alors j'obtiens l'objet avec les valeurs attendues
        $this->assertEquals(false, validateDate($dateDonneeKO));
        $this->assertEquals(true, validateDate($dateDonneeOk));
    }

}