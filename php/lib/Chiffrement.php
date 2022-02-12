<?php
require_once('Cryptor.php');

use ioncube\phpOpensslCryptor\Cryptor;

// Classe chiffrement, utilisant phpOpensslCryptor https://github.com/ioncube/php-openssl-cryptor/
class Chiffrement
{
    private static $_key = 'Top5, Club Ukulele Fontenay-Sous-Bois'; // Clé de cryptage

    public static function crypt($data)
    {
        $data = Cryptor::Encrypt($data, self::$_key);
        return base64_encode($data);
    }

    public static function decrypt($data)
    {
        try {
            $data = base64_decode($data);
            $data = Cryptor::Decrypt($data, self::$_key);
            return ($data);
        } catch (Exception $e) {
            echo "Votre clé n'est plus valide, faites une demande d'oubli de mot de passe. ",  $e->getMessage(), "\n";
            return ("Erreur");
        }
    }
}