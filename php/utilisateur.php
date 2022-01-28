<?php

$nomtable = "utilisateur";
$GLOBALS["PRIVILEGE_INVITE"] = 0;
$GLOBALS["PRIVILEGE_MEMBRE"] = 1;
$GLOBALS["PRIVILEGE_EDITEUR"] = 2;
$GLOBALS["PRIVILEGE_ADMIN"] = 3;


// Fonctions de gestion de l'utilisateur

// Cherche les utilisateurs correspondant à un critère
function chercheUtilisateurs($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
{
    global $nomtable;
    $maRequete = "SELECT * FROM " . $nomtable . " WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
    if (!$bTriAscendant)
    {
        $maRequete .= " DESC";
    }
    else
    {
        $maRequete .= " ASC";
    }
    // echo "ma requete : " . $maRequete;
    $result = $_SESSION ['mysql']->query($maRequete) or die ("Problème chercheUtilisateurs #1 : " . $_SESSION ['mysql']->error);
    return $result;
}

// Cherche un utilisateur par son identifiant et le renvoie s'il existe
function chercheUtilisateur($id)
{
    $maRequete = "SELECT * FROM utilisateur WHERE utilisateur.id = '$id'";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
    {
        die ("Problème chercheutilisateur #1 : " . $_SESSION ['mysql']->error);
    }
    // renvoie la lisgne sélectionnée : id, nom, taille, date
    if ($ligne = $result->fetch_row())
    {
        return ($ligne);
    }
    else
    {
        return (0);
    }
}

// Cherche un utilisateur par le login et le renvoie s'il existe
function chercheUtilisateurParLeLogin($login)
{
    $maRequete = "SELECT * FROM utilisateur WHERE utilisateur.login = '$login'";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
    {
        die ("Problème chercheutilisateurParLeNom #1 : " . $_SESSION ['mysql']->error);
    }
    // renvoie la lisgne sélectionnée : id, nom, taille, date
    $ligne = $result->fetch_row();
    if ($ligne)
    {
        return ($ligne);
    }
    else
    {
        return (0);
    }
}

// Crée un utilisateur
function creeUtilisateur($login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $privilege)
{
    // Crypter le mdp
    $crypt = Chiffrement::crypt($mdp);
    // vérifier l'email
    $date = convertitDateJJMMAAAA(date("d/m/Y"));
    $login = $_SESSION ['mysql']->escape_string($login);
    $prenom = $_SESSION ['mysql']->escape_string($prenom);
    $nom = $_SESSION ['mysql']->escape_string($nom);
    $image = $_SESSION ['mysql']->escape_string($image);
    $site = $_SESSION ['mysql']->escape_string($site);
    $email = $_SESSION ['mysql']->escape_string($email);
    $signature = $_SESSION ['mysql']->escape_string($signature);

    $maRequete = "INSERT INTO  utilisateur VALUES (NULL, '$login', '$crypt', '$prenom', '$nom', '$image', '$site', '$email', '$signature', '$date', '0', '$privilege')";
    // echo "Ma requete : $maRequete<br>\n";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
    {
        die ("Problème creeUtilisateur#1 : " . $_SESSION ['mysql']->error);
    }
}

// Formate les chaînes pour enregistrement en base

// Modifie en base le utilisateur
function modifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $nbreLogins, $privilege)
{
    // On convertit la date au format mysql
    $date = convertitDateJJMMAAAA(date("d/m/Y"));
    // Crypter le mdp
    $crypt = Chiffrement::crypt($mdp);

    $login = $_SESSION ['mysql']->real_escape_string($login);
    $prenom = $_SESSION ['mysql']->real_escape_string($prenom);
    $nom = $_SESSION ['mysql']->real_escape_string($nom);
    $image = $_SESSION ['mysql']->real_escape_string($image);
    $site = $_SESSION ['mysql']->real_escape_string($site);
    $email = $_SESSION ['mysql']->real_escape_string($email);
    $signature = $_SESSION ['mysql']->real_escape_string($signature);

    $maRequete = "UPDATE  utilisateur
	SET id = '$id', login = '$login', mdp = '$crypt',
	prenom = '$prenom' , nom = '$nom', image = '$image', site = '$site', email = '$email',
	signature = '$signature', dateDernierLogin = '$date', nbreLogins = '$nbreLogins', privilege = '$privilege'
	WHERE id='$id'";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
    {
        die ("Problème modifieUtilisateur#1 : " . $_SESSION ['mysql']->error);
    }
}

// Modifie en base le utilisateur
function modifieMdpUtilisateur($id, $mdp)
{
    // Crypter le mdp
    $crypt = Chiffrement::crypt($mdp);

    $maRequete = "UPDATE  utilisateur
	SET  mdp = '$crypt'
	WHERE id='$id'";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
    {
        die ("Problème modifieMdpUtilisateur#1 : " . $_SESSION ['mysql']->error);
    }
}


// Cette fonction supprime un utilisateur si il existe
function supprimeUtilisateur($idUtilisateur)
{

    // On supprime les enregistrements dans utilisateur
    $maRequete = "DELETE FROM  utilisateur
	WHERE id='$idUtilisateur'";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
    {
        die ("Problème supprimeUtilisateur#1 : " . $_SESSION ['mysql']->error);
    }
}

// Cette fonction modifie ou crée un utilisateur si besoin
function creeModifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $nbreLogins, $privilege)
{
    if (chercheutilisateur($id))
        modifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $nbreLogins, $privilege);
    else
        creeUtilisateur($login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $privilege);
}

// Cette fonction tente de loguer un utilisateur avec le mot de passe mdp

function login_utilisateur($login, $mdp)
{
    $donnee = chercheUtilisateurParLeLogin($login);

    if ($mdp == Chiffrement::decrypt($donnee [2])) {
        $donnee [10] = $donnee [10] + 1;
        // echo "login ok";
        modifieUtilisateur($donnee [0], $donnee [1], $mdp, $donnee [3], $donnee [4], $donnee [5], $donnee [6], $donnee [7], $donnee [8], $donnee [10], $donnee [11]);
        return $donnee;
    } else
        echo "Erreur de mot de passe : $mdp";
    return false;
}

function portraitDesUtilisateurs()
{
    $maRequete = "SELECT id, login, image FROM  utilisateur";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
        die ("Problème supprimeUtilisateur#1 : " . $_SESSION ['mysql']->error);
    while ($ligne = $result->fetch_row()) {
        $tableau[$ligne[0]][0] = $ligne[1];
        $tableau[$ligne[0]][1] = $ligne[2];
    }
    return $tableau;
}

function statut($privilege)
{
    switch ($privilege) {
        case 0 :
            return "invité";
        case 1 :
            return "membre";
        case 2 :
            return "éditeur";
        case 3 :
            return "administrateur";
    }
    return "invité";
}

// Pour le test
// Cette fonction renvoie une chaine de description de l'utilisateur
function infos($id)
{
    $enr = chercheUtilisateur($id);
    // id_journee id_joueur poste statut
    $retour = "Id : " . $enr [0] . " Nom : " . $enr [1] . " Desc : " . $enr [2] . " date : " . $enr [3] . " image : " . $enr [4] . " hits : " . $enr [5];
    return $retour . "<br>\n";
}

// Fonction test affichage de tous les utilisateurs
function testUtilisateurs()
{
    $maRequete = "SELECT * FROM utilisateur";
    $result = $_SESSION ['mysql']->query($maRequete);
    if (!$result)
        die ("Problème testUtilisateurs #1 : pas d'utilisateurs trouvés ! - " . $_SESSION ['mysql']->error);
    // renvoie la lisgne sélectionnée : id, nom, taille, date
    while ($ligne = $result->fetch_row()) {
        echo infos($ligne[0]) . " Pass : " . Chiffrement::decrypt($ligne[2]) . "<br> \n\r";
    }
}

// Prépare un combo en html avec les utilisateurs
// SELECT * FROM utilisateur WHERE $critere LIKE '$valeur' ORDER BY $critereTri
function selectUtilisateur($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true, $idSelectionne = 0)
{
    $retour = "<select name='fidUser'>\n";
    // Ajouter des options
    $lignes = chercheUtilisateurs($critere, $valeur, $critereTri, $bTriAscendant);
    while ($ligne = $lignes->fetch_row()) {
        //if (strstr($ligne[1],"pdf"))
        if ($ligne [0] == $idSelectionne)
            $selected = " selected";
        else
            $selected = "";
        $retour .= "<option  value = '" . $ligne [0] . "' " . $selected . "> " . htmlEntities($ligne [1] . " - " . $ligne[3] . " " . $ligne[4]);
        $retour .= "</option>\n";
    }
    $retour .= "</select>\n";
    return $retour;
}

function majMdpUtilisateur($login, $mdp)
{
    $donnee = chercheUtilisateurParLeLogin($login);

    $mdp == Chiffrement::crypt($mdp);
    modifieUtilisateur($donnee [0], $donnee [1], $mdp, $donnee [3], $donnee [4], $donnee [5], $donnee [6], $donnee [7], $donnee [8], $donnee [10], $donnee [11]);

    return true;
}

// Fonction de test
function testeUtilisateur()
{
    echo "Test de creeUtilisateur<br>\n";
    creeUtilisateur("test", "kazoo", "Alain", "Minc", "test.png", "http://samere", "truc@bidule.com", "bla bla bla", "2");
    $id = chercheUtilisateurParLeLogin("test");
    $id = $id [0];
    chercheUtilisateur($id);
    echo infos($id);
    echo "Test de creeUtilisateur 2<br>\n";
    creeUtilisateur("test2", "kazoo2", "Alain", "Minc", "test.png", "http://samere", "truc@bidule.com", "bla bla bla", 2);
    echo "Test de creeUtilisateur 3<br>\n";
    creeModifieUtilisateur($id, "test3", "kazoo3", "Alainx", "Mincx", "tesxt.png", "http://samere.org", "truc@bidule.com", "bla bla bla", 5, 1);

    $id = chercheUtilisateurParLeLogin("test");
    supprimeUtilisateur($id [0]);

    $id = chercheUtilisateurParLeLogin("test2");
    supprimeUtilisateur($id [0]);

    $id = chercheUtilisateurParLeLogin("test3");
    supprimeUtilisateur($id[0]);

    $motDePasse = "coucou";
    echo("Cryptage de mot de passe : " . $motDePasse . "<br> \n\r");
    echo(Chiffrement::crypt($motDePasse) . "<br> \n\r");

    $chaine = "YumgdDnP5Oomf2jI1Lmy/A==";
    echo("Décryptage de chaine : " . $chaine . "<br> \n\r");
    echo("Resultat : " . Chiffrement::decrypt($chaine) . "<br> \n\r");
}