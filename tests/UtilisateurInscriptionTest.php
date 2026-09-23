<?php
use PHPUnit\Framework\TestCase;

if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (session_status() === PHP_SESSION_NONE) session_start();
$_SERVER['DOCUMENT_ROOT'] = "../";
require_once __DIR__ . "/../src/autoload.php";
require_once PHP_DIR . "/utilisateur/UtilisateurInscriptionService.php";

class UtilisateurInscriptionTest extends TestCase
{
    private string $testLogin;

    protected function setUp(): void
    {
        $_SESSION['privilege'] = 0;
        unset($_SESSION['user']);
        $this->testLogin = "unit_user_" . rand(1000, 9999);
    }

    protected function tearDown(): void
    {
        if (!empty($this->testLogin)) {
            $u = Utilisateur::chercheUtilisateurParLeLogin($this->testLogin);
            if ($u && isset($u[0])) {
                supprimeUtilisateur($u[0]);
            }
        }
    }

    public function testInscriptionChampsManquants()
    {
        $res = UtilisateurInscriptionService::processInscription([], []);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('remplir tous les champs', $res['error']);
    }

    public function testInscriptionMotDePasseMismatched()
    {
        $postData = [
            'flogin' => $this->testLogin,
            'fmdp' => 'secret123',
            'fmdp_confirm' => 'different123',
            'fprenom' => 'Jean',
            'fnom' => 'Dupont',
            'femail' => 'jean@example.com'
        ];
        $res = UtilisateurInscriptionService::processInscription($postData, []);
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('confirmation du mot de passe', $res['error']);
    }

    public function testInscriptionSuccessAndActivation()
    {
        $postData = [
            'flogin' => $this->testLogin,
            'fmdp' => 'secret123',
            'fmdp_confirm' => 'secret123',
            'fprenom' => 'Jean',
            'fnom' => 'Dupont',
            'femail' => 'jean@example.com',
            'fsite' => 'http://example.com',
            'fsignature' => 'Ma signature'
        ];

        $res = UtilisateurInscriptionService::processInscription($postData, []);
        $this->assertTrue($res['success']);
        $this->assertTrue($res['requires_activation']);
        $this->assertNotEmpty($res['token']);

        // 1. Vérification en BDD que le compte est créé en attente (est_actif = 0, privilege = 0)
        $u = Utilisateur::chercheUtilisateurParLeLogin($this->testLogin);
        $this->assertNotEmpty($u);
        $this->assertEquals($this->testLogin, $u[1]);
        $this->assertEquals(0, (int)$u[11]); // Privilège 0 avant activation
        $this->assertEquals(0, (int)$u[13]); // est_actif = 0

        // 2. Vérification que la connexion échoue tant que le compte n'est pas activé
        $loginRes = Utilisateur::login_utilisateur($this->testLogin, 'secret123');
        $this->assertFalse($loginRes);

        // 3. Activation du compte via le jeton
        $activatedUser = Utilisateur::activeCompteParToken($res['token']);
        $this->assertNotFalse($activatedUser);
        $this->assertEquals(1, (int)$activatedUser[11]); // Privilège 1 (Membre)

        // 4. Connexion après activation
        $loginAfter = Utilisateur::login_utilisateur($this->testLogin, 'secret123');
        $this->assertNotFalse($loginAfter);
    }

    public function testInscriptionDuplicateLogin()
    {
        $postData = [
            'flogin' => $this->testLogin,
            'fmdp' => 'secret123',
            'fmdp_confirm' => 'secret123',
            'fprenom' => 'Jean',
            'fnom' => 'Dupont',
            'femail' => 'jean@example.com'
        ];

        // Première inscription
        $res1 = UtilisateurInscriptionService::processInscription($postData, []);
        $this->assertTrue($res1['success']);

        // Deuxième inscription avec le même login
        $res2 = UtilisateurInscriptionService::processInscription($postData, []);
        $this->assertFalse($res2['success']);
        $this->assertStringContainsString('déjà utilisé', $res2['error']);
    }
}
