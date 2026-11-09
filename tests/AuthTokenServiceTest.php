<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/public/autoload.php';
require_once __DIR__ . '/../src/public/php/lib/utilssi.php';
require_once __DIR__ . '/../src/public/php/utilisateur/AuthTokenService.php';

class AuthTokenServiceTest extends TestCase
{
    private mysqli $db;
    private int $testUserId = 0;

    protected function setUp(): void
    {
        global $mysqli;
        $this->db = $mysqli;
        $_SESSION['mysql'] = $this->db;

        // On vérifie ou crée un utilisateur de test actif
        $res = $this->db->query("SELECT id FROM utilisateur WHERE login = 'test_remember_user' LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $this->testUserId = (int)$row['id'];
        } else {
            $this->db->query("INSERT INTO utilisateur (login, mdp, prenom, nom, email, privilege, est_actif) 
                             VALUES ('test_remember_user', 'secret_hash', 'Jean', 'Test', 'jean.test@example.com', 1, 1)");
            $this->testUserId = (int)$this->db->insert_id;
        }

        // Nettoyage des tokens de test existants
        $this->db->query("DELETE FROM auth_token WHERE user_id = {$this->testUserId}");
        $_COOKIE = [];
        unset($_SESSION['user'], $_SESSION['id'], $_SESSION['privilege'], $_SESSION['login']);
    }

    protected function tearDown(): void
    {
        if ($this->testUserId > 0) {
            $this->db->query("DELETE FROM auth_token WHERE user_id = {$this->testUserId}");
            $this->db->query("DELETE FROM utilisateur WHERE id = {$this->testUserId}");
        }
        $_COOKIE = [];
        unset($_SESSION['user'], $_SESSION['id'], $_SESSION['privilege'], $_SESSION['login']);
        $_SESSION['mysql'] = $this->db;
    }

    public function testCreerTokenInsereEnBdd(): void
    {
        $succes = AuthTokenService::creerToken($this->testUserId);
        $this->assertTrue($succes);

        $res = $this->db->query("SELECT * FROM auth_token WHERE user_id = {$this->testUserId}");
        $this->assertNotNull($res);
        $this->assertEquals(1, $res->num_rows);

        $row = $res->fetch_assoc();
        $this->assertEquals(32, strlen($row['selector']));
        $this->assertEquals(64, strlen($row['token_hash']));
        $this->assertGreaterThan(date('Y-m-d H:i:s'), $row['date_expiration']);
    }

    public function testVerifierEtConnecterAvecCookieValide(): void
    {
        // On génère manuellement un couple selector / validator pour tester
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expiration = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $this->db->prepare("INSERT INTO auth_token (user_id, selector, token_hash, date_expiration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $this->testUserId, $selector, $hash, $expiration);
        $stmt->execute();
        $stmt->close();

        // On simule le cookie dans $_COOKIE
        $_COOKIE[AuthTokenService::COOKIE_NAME] = $selector . ':' . $validator;

        $connecte = AuthTokenService::verifierEtConnecter();
        $this->assertTrue($connecte);
        $this->assertEquals($this->testUserId, $_SESSION['id']);
        $this->assertEquals('test_remember_user', $_SESSION['user']);
        $this->assertEquals(1, $_SESSION['privilege']);
        $this->assertEquals('ok', $_SESSION['login']);
    }

    public function testRejeterSiValidatorInvalide(): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expiration = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $this->db->prepare("INSERT INTO auth_token (user_id, selector, token_hash, date_expiration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $this->testUserId, $selector, $hash, $expiration);
        $stmt->execute();
        $stmt->close();

        // Faux validator
        $fauxValidator = bin2hex(random_bytes(32));
        $_COOKIE[AuthTokenService::COOKIE_NAME] = $selector . ':' . $fauxValidator;

        $connecte = AuthTokenService::verifierEtConnecter();
        $this->assertFalse($connecte);
        $this->assertArrayNotHasKey('user', $_SESSION);

        // Vérification de révocation automatique par sécurité
        $check = $this->db->query("SELECT * FROM auth_token WHERE selector = '$selector'");
        $this->assertEquals(0, $check->num_rows);
    }

    public function testRejeterSiTokenExpire(): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expirationPassee = date('Y-m-d H:i:s', time() - 3600); // 1h dans le passé

        $stmt = $this->db->prepare("INSERT INTO auth_token (user_id, selector, token_hash, date_expiration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $this->testUserId, $selector, $hash, $expirationPassee);
        $stmt->execute();
        $stmt->close();

        $_COOKIE[AuthTokenService::COOKIE_NAME] = $selector . ':' . $validator;

        $connecte = AuthTokenService::verifierEtConnecter();
        $this->assertFalse($connecte);
    }

    public function testRevoquerTokenSupprimeEnBdd(): void
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expiration = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $this->db->prepare("INSERT INTO auth_token (user_id, selector, token_hash, date_expiration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $this->testUserId, $selector, $hash, $expiration);
        $stmt->execute();
        $stmt->close();

        $_COOKIE[AuthTokenService::COOKIE_NAME] = $selector . ':' . $validator;

        AuthTokenService::revoquerToken($selector);

        $check = $this->db->query("SELECT * FROM auth_token WHERE selector = '$selector'");
        $this->assertEquals(0, $check->num_rows);
    }

    public function testRejeterCompteDesactive(): void
    {
        // Désactivation du compte
        $this->db->query("UPDATE utilisateur SET est_actif = 0 WHERE id = {$this->testUserId}");

        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expiration = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $this->db->prepare("INSERT INTO auth_token (user_id, selector, token_hash, date_expiration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $this->testUserId, $selector, $hash, $expiration);
        $stmt->execute();
        $stmt->close();

        $_COOKIE[AuthTokenService::COOKIE_NAME] = $selector . ':' . $validator;

        $connecte = AuthTokenService::verifierEtConnecter();
        $this->assertFalse($connecte);
    }
}
