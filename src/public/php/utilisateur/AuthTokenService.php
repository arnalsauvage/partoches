<?php
/**
 * SERVICE : AuthTokenService
 * Gestion de l'authentification persistante ("Se souvenir de moi" - 14 jours)
 * Conforme aux recommandations OWASP & NIST (Pattern Selector / Validator).
 */

class AuthTokenService
{
    public const COOKIE_NAME = 'partoches_remember';
    public const DUREE_VALIDITE_SECONDES = 1209600; // 14 jours (14 * 24 * 3600)

    /**
     * Récupère l'instance MySQLi active
     */
    protected static function getDb(): ?mysqli
    {
        return $_SESSION['mysql'] ?? ($GLOBALS['mysqli'] ?? null);
    }

    /**
     * Crée un nouveau jeton de connexion persistante et dépose le cookie sécurisé.
     * @param int $userId ID de l'utilisateur
     * @return bool True si créé avec succès
     */
    public static function creerToken(int $userId): bool
    {
        $db = self::getDb();
        if (!$db || $userId <= 0) {
            return false;
        }

        try {
            // Nettoyage préalable des tokens expirés
            self::nettoyerTokensExpires();

            // 1. Génération cryptographique aléatoire
            $selector = bin2hex(random_bytes(16)); // 32 caractères hexadécimaux
            $validator = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux
            $tokenHash = hash('sha256', $validator);
            $dateExpiration = date('Y-m-d H:i:s', time() + self::DUREE_VALIDITE_SECONDES);

            // 2. Enregistrement en BDD (requête préparée obligatoire)
            $stmt = $db->prepare("INSERT INTO auth_token (user_id, selector, token_hash, date_expiration) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("isss", $userId, $selector, $tokenHash, $dateExpiration);
            $succes = $stmt->execute();
            $stmt->close();

            if (!$succes) {
                return false;
            }

            // 3. Émission du cookie HttpOnly / SameSite=Lax
            return self::deposerCookie($selector, $validator);
        } catch (Throwable $e) {
            error_log("Erreur AuthTokenService::creerToken : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie le cookie de connexion et restaure la session de l'utilisateur si valide.
     * @return bool True si l'utilisateur a été authentifié automatiquement
     */
    public static function verifierEtConnecter(): bool
    {
        if (empty($_COOKIE[self::COOKIE_NAME])) {
            return false;
        }

        $cookieVal = $_COOKIE[self::COOKIE_NAME];
        $parties = explode(':', $cookieVal, 2);
        if (count($parties) !== 2) {
            self::supprimerCookie();
            return false;
        }

        [$selector, $validator] = $parties;
        if (strlen($selector) !== 32 || strlen($validator) !== 64) {
            self::supprimerCookie();
            return false;
        }

        $db = self::getDb();
        if (!$db) {
            return false;
        }

        try {
            $stmt = $db->prepare(
                "SELECT a.id, a.user_id, a.token_hash, a.date_expiration, 
                        u.id as uid, u.login, u.email, u.image, u.privilege, u.est_actif 
                 FROM auth_token a 
                 JOIN utilisateur u ON a.user_id = u.id 
                 WHERE a.selector = ? AND a.date_expiration > NOW() 
                 LIMIT 1"
            );
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $res = $stmt->get_result();

            if (!$res || $res->num_rows === 0) {
                $stmt->close();
                self::supprimerCookie();
                return false;
            }

            $row = $res->fetch_assoc();
            $stmt->close();

            // Vérification compte actif
            if (isset($row['est_actif']) && (int)$row['est_actif'] === 0) {
                self::revoquerToken($selector);
                self::supprimerCookie();
                return false;
            }

            // Vérification cryptographique à temps constant
            $calcHash = hash('sha256', $validator);
            if (!hash_equals($row['token_hash'], $calcHash)) {
                // Suspicion d'altération : on révoque immédiatement
                self::revoquerToken($selector);
                self::supprimerCookie();
                return false;
            }

            // Authentification réussie : initialisation de la session
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                session_regenerate_id(true);
            }

            $_SESSION['id'] = (int)$row['uid'];
            $_SESSION['user'] = $row['login'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['image'] = $row['image'];
            $_SESSION['privilege'] = (int)$row['privilege'];
            $_SESSION['login'] = "ok";

            // Mise à jour de la date de dernier login
            $dateJour = date('Y-m-d');
            $userId = (int)$row['uid'];
            $db->query("UPDATE utilisateur SET dateDernierLogin = '$dateJour' WHERE id = $userId");

            return true;
        } catch (Throwable $e) {
            error_log("Erreur AuthTokenService::verifierEtConnecter : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Révoque un jeton d'authentification en BDD.
     * @param string|null $selector Si null, extrait le sélecteur depuis le cookie courant
     */
    public static function revoquerToken(?string $selector = null): void
    {
        $db = self::getDb();
        if (!$db) {
            return;
        }

        if ($selector === null && !empty($_COOKIE[self::COOKIE_NAME])) {
            $parties = explode(':', $_COOKIE[self::COOKIE_NAME], 2);
            $selector = $parties[0] ?? null;
        }

        if (!$selector || strlen($selector) !== 32) {
            return;
        }

        try {
            $stmt = $db->prepare("DELETE FROM auth_token WHERE selector = ?");
            if ($stmt) {
                $stmt->bind_param("s", $selector);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            error_log("Erreur AuthTokenService::revoquerToken : " . $e->getMessage());
        }
    }

    /**
     * Révoque tous les jetons actifs d'un utilisateur (ex: changement de mot de passe).
     * @param int $userId ID de l'utilisateur
     */
    public static function revoquerTousTokensUtilisateur(int $userId): void
    {
        $db = self::getDb();
        if (!$db || $userId <= 0) {
            return;
        }

        try {
            $stmt = $db->prepare("DELETE FROM auth_token WHERE user_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            error_log("Erreur AuthTokenService::revoquerTousTokensUtilisateur : " . $e->getMessage());
        }
    }

    /**
     * Supprime le cookie du navigateur.
     */
    public static function supprimerCookie(): void
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            unset($_COOKIE[self::COOKIE_NAME]);
        }

        if (!headers_sent()) {
            setcookie(self::COOKIE_NAME, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => self::estHttps()
            ]);
        }
    }

    /**
     * Nettoie les jetons expirés en base de données.
     * @return int Nombre de lignes supprimées
     */
    public static function nettoyerTokensExpires(): int
    {
        $db = self::getDb();
        if (!$db) {
            return 0;
        }

        try {
            $res = $db->query("DELETE FROM auth_token WHERE date_expiration <= NOW()");
            return ($res) ? $db->affected_rows : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Dépose le cookie sur le client avec options de sécurité modernes.
     */
    public static function deposerCookie(string $selector, string $validator): bool
    {
        $valeur = $selector . ':' . $validator;
        $expire = time() + self::DUREE_VALIDITE_SECONDES;

        if (headers_sent()) {
            $_COOKIE[self::COOKIE_NAME] = $valeur;
            return true;
        }

        $res = setcookie(self::COOKIE_NAME, $valeur, [
            'expires' => $expire,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => self::estHttps()
        ]);

        if ($res) {
            $_COOKIE[self::COOKIE_NAME] = $valeur;
        }

        return $res;
    }

    /**
     * Détecte si la requête courante s'effectue en HTTPS.
     */
    protected static function estHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    }
}
