<?php
require_once __DIR__ . '/../src/public/php/lib/configMysql.php';
require_once __DIR__ . '/../src/public/php/lib/Chiffrement.php';
require_once __DIR__ . '/../src/public/php/utilisateur/Utilisateur.php';

$db = $_SESSION['mysql'];

// Supprimer un éventuel ancien compte admin
$db->query("DELETE FROM utilisateur WHERE login = 'admin'");

// Créer le compte admin avec le mot de passe kazoo et privilège 2 (Admin)
Utilisateur::creeUtilisateur(
    'admin',
    'kazoo',
    'Admin',
    'Partoches',
    'utilisateur/defaut.png',
    '',
    'admin@example.com',
    'Administrateur Local',
    2
);

echo "COMPTE ADMIN CRÉÉ AVEC SUCCÈS !\n";
echo "Login : admin\n";
echo "Mot de passe : kazoo\n";
