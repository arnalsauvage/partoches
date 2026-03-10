<?php
/**
 * Classe Utilisateur (Django Style)
 * Gère les profils, les connexions et les privilèges.
 */

$nomTableUtilisateur = "utilisateur";
$GLOBALS["PRIVILEGE_INVITE"] = 0;
$GLOBALS["PRIVILEGE_MEMBRE"] = 1;
$GLOBALS["PRIVILEGE_EDITEUR"] = 2;
$GLOBALS["PRIVILEGE_ADMIN"] = 3;

require_once dirname(__DIR__) . "/lib/utilssi.php";
require_once dirname(__DIR__) . "/lib/Chiffrement.php";

if (!class_exists('Utilisateur')) {
class Utilisateur
{
    private int $_id;
    private string $_login;
    private string $_prenom;
    private string $_nom;
    private string $_image;
    private string $_site;
    private string $_email;
    private string $_signature;
    private string $_dateDernierLogin;
    private int $_nbreLogins;
    private int $_privilege;
    private int $_nbChansons = -1; // Cache pour le compteur

    public function __construct(int $id = 0)
    {
        $this->_id = $id;
        if ($id > 0) {
            $this->chargeDonnees();
        } else {
            $this->_login = "";
            $this->_prenom = "";
            $this->_nom = "";
            $this->_image = "utilisateur/defaut.png";
            $this->_site = "";
            $this->_email = "";
            $this->_signature = "";
            $this->_dateDernierLogin = "";
            $this->_nbreLogins = 0;
            $this->_privilege = 0;
        }
    }

    private function chargeDonnees(): void
    {
        $donnee = self::chercheUtilisateur($this->_id);
        if (is_array($donnee)) {
            $this->_login = (string)$donnee[1];
            $this->_prenom = (string)($donnee[3] ?? "");
            $this->_nom = (string)($donnee[4] ?? "");
            $this->_image = (string)($donnee[5] ?? "utilisateur/defaut.png");
            $this->_site = (string)($donnee[6] ?? "");
            $this->_email = (string)($donnee[7] ?? "");
            $this->_signature = (string)($donnee[8] ?? "");
            $this->_dateDernierLogin = (string)($donnee[9] ?? "");
            $this->_nbreLogins = (int)($donnee[10] ?? 0);
            $this->_privilege = (int)($donnee[11] ?? 0);
        }
    }

    // Getters
    public function getId(): int { return $this->_id; }
    public function getLogin(): string { return $this->_login; }
    public function getPrenom(): string { return $this->_prenom; }
    public function getNom(): string { return $this->_nom; }
    public function getImage(): string { return $this->_image; }
    public function getPrivilege(): int { return $this->_privilege; }
    public function getNbreLogins(): int { return $this->_nbreLogins; }
    public function getDateDernierLogin(): string { return $this->_dateDernierLogin; }

    /**
     * Retourne le nombre de chansons créées par cet utilisateur
     */
    public function getNbChansons(): int
    {
        if ($this->_nbChansons === -1) {
            $db = $_SESSION['mysql'];
            $maRequete = "SELECT COUNT(*) FROM chanson WHERE idUser = ?";
            $stmt = $db->prepare($maRequete);
            $stmt->bind_param("i", $this->_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $this->_nbChansons = ($res) ? (int)$res->fetch_row()[0] : 0;
        }
        return $this->_nbChansons;
    }

    /**
     * Charge tous les utilisateurs avec option de tri
     */
    public static function chargeUtilisateursBdd(string $tri = 'recent'): array
    {
        $db = $_SESSION['mysql'];
        
        switch ($tri) {
            case 'logins':
                $ordre = "u.nbreLogins DESC, u.login ASC";
                break;
            case 'chansons':
                $ordre = "nb_chansons DESC, u.login ASC";
                break;
            case 'recent':
            default:
                $ordre = "u.dateDernierLogin DESC, u.login ASC";
                break;
        }

        $maRequete = "SELECT u.id, COUNT(c.id) as nb_chansons 
                      FROM utilisateur u 
                      LEFT JOIN chanson c ON u.id = c.idUser 
                      GROUP BY u.id 
                      ORDER BY $ordre";
        
        $result = $db->query($maRequete);
        $liste = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $liste[] = new Utilisateur((int)$row['id']);
            }
        }
        return $liste;
    }

    /**
     * Cherche un utilisateur par email
     */
    public static function chercheUtilisateurParEmail(string $email)
    {
        $db = $_SESSION ['mysql'];
        $email = $db->real_escape_string($email);
        $maRequete = "SELECT * FROM utilisateur WHERE email LIKE '$email'";
        $result = $db->query($maRequete) or die ("Problème chercheUtilisateurParEmail #1 : " . $db->error);
        return $result->fetch_row();
    }

    /**
     * Cherche les utilisateurs correspondant à un critère
     */
    public static function chercheUtilisateurs($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true)
    {
        $db = $_SESSION ['mysql'];
        $critere = $db->real_escape_string($critere);
        $valeur = $db->real_escape_string($valeur);
        $maRequete = "SELECT * FROM utilisateur WHERE $critere LIKE '$valeur' ORDER BY $critereTri";
        $maRequete .= $bTriAscendant ? " ASC" : " DESC";
        $result = $db->query($maRequete) or die ("Problème chercheUtilisateurs #1 : " . $db->error . $maRequete);
        return $result;
    }

    /**
     * Cherche un utilisateur par son ID
     */
    public static function chercheUtilisateur($id)
    {
        if (!$id) return 0;
        $db = $_SESSION ['mysql'];
        $id = (int)$id;
        $maRequete = "SELECT * FROM utilisateur WHERE utilisateur.id = '$id'";
        $result = $db->query($maRequete);
        if (!$result) {
            die ("Problème chercheutilisateur #1 : " . $db->error);
        }
        return $result->fetch_row() ?: 0;
    }

    /**
     * Cherche un utilisateur par son login
     */
    public static function chercheUtilisateurParLeLogin(string $login)
    {
        $db = $_SESSION ['mysql'];
        $login = $db->real_escape_string($login);
        $maRequete = "SELECT * FROM utilisateur WHERE utilisateur.login = '$login'";
        $result = $db->query($maRequete);
        if (!$result) {
            die ("Problème chercheutilisateurParLeNom #1 : " . $db->error);
        }
        return $result->fetch_row() ?: 0;
    }

    /**
     * Crée un utilisateur
     */
    public static function creeUtilisateur($login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $privilege)
    {
        $db = $_SESSION['mysql'];
        $crypt = Chiffrement::crypt($mdp);
        $date = convertitDateJJMMAAAAversMySql(date("d/m/Y"));
        
        $login = $db->real_escape_string($login);
        $prenom = $db->real_escape_string($prenom);
        $nom = $db->real_escape_string($nom);
        $image = $db->real_escape_string($image);
        $site = $db->real_escape_string($site);
        $email = $db->real_escape_string($email);
        $signature = $db->real_escape_string($signature);

        $maRequete = "INSERT INTO utilisateur VALUES (NULL, '$login', '$crypt', '$prenom', '$nom', '$image', '$site', '$email', '$signature', '$date', '0', '$privilege')";
        $result = $db->query($maRequete) or die ("Problème creeUtilisateur#1 : " . $db->error);
        return $result;
    }

    /**
     * Modifie un utilisateur
     */
    public static function modifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $nbreLogins, $privilege)
    {
        $db = $_SESSION['mysql'];
        $date = convertitDateJJMMAAAAversMySql(date("d/m/Y"));
        $crypt = Chiffrement::crypt($mdp);

        $login = $db->real_escape_string($login);
        $prenom = $db->real_escape_string($prenom);
        $nom = $db->real_escape_string($nom);
        $image = $db->real_escape_string($image);
        $site = $db->real_escape_string($site);
        $email = $db->real_escape_string($email);
        $signature = $db->real_escape_string($signature);

        $maRequete = "UPDATE utilisateur
            SET login = '$login', mdp = '$crypt', prenom = '$prenom', nom = '$nom', 
                image = '$image', site = '$site', email = '$email', signature = '$signature', 
                dateDernierLogin = '$date', nbreLogins = '$nbreLogins', privilege = '$privilege'
            WHERE id='$id'";
        $db->query($maRequete) or die ("Problème modifieUtilisateur#1 : " . $db->error);
    }

    /**
     * Supprime un utilisateur
     */
    public static function supprimeUtilisateur($id)
    {
        $db = $_SESSION['mysql'];
        $maRequete = "DELETE FROM utilisateur WHERE id='$id'";
        $db->query($maRequete) or die ("Problème supprimeUtilisateur#1 : " . $db->error);
    }

    /**
     * Tente de connecter un utilisateur
     */
    public static function login_utilisateur($login, $mdp)
    {
        $donnee = self::chercheUtilisateurParLeLogin($login);
        if (is_array($donnee) && isset($donnee[2])) {
            if ($mdp == Chiffrement::decrypt($donnee[2])) {
                $donnee[10] = (int)$donnee[10] + 1;
                self::modifieUtilisateur($donnee[0], $donnee[1], $mdp, $donnee[3], $donnee[4], $donnee[5], $donnee[6], $donnee[7], $donnee[8], $donnee[10], $donnee[11]);
                return $donnee;
            }
        }
        return false;
    }

    /**
     * Renvoie le libellé du statut
     */
    public static function statut($privilege)
    {
        switch ((int)$privilege) {
            case 0 : return "invité";
            case 1 : return "membre";
            case 2 : return "éditeur";
            case 3 : return "administrateur";
        }
        return "invité";
    }

    /**
     * Retourne un tableau des avatars
     */
    public static function portraitDesUtilisateurs()
    {
        $db = $_SESSION['mysql'];
        $maRequete = "SELECT id, login, image FROM utilisateur";
        $result = $db->query($maRequete) or die ("Problème portraitDesUtilisateurs#1 : " . $db->error);
        $tableau = [];
        while ($ligne = $result->fetch_row()) {
            $tableau[$ligne[0]][0] = $ligne[1];
            $tableau[$ligne[0]][1] = $ligne[2];
        }
        return $tableau;
    }

    /**
     * Prépare un combo HTML avec les utilisateurs
     */
    public static function selectUtilisateur($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true, $idSelectionne = 0, $nomDuChamp ='fidUser', $idDuChamp="fiduser")
    {
        $retour = "<select class='js-example-basic-single' name='$nomDuChamp' id='$idDuChamp'>\n";
        $lignes = self::chercheUtilisateurs($critere, $valeur, $critereTri, $bTriAscendant);
        while ($ligne = $lignes->fetch_row()) {
            $selected = ($ligne[0] == $idSelectionne) ? " selected" : "";
            $retour .= "<option value='" . $ligne[0] . "'$selected>" . htmlentities($ligne[1] . " - " . $ligne[3] . " " . $ligne[4]) . "</option>\n";
        }
        $retour .= "</select>\n";
        return $retour;
    }

    /**
     * Affiche une carte utilisateur moderne
     */
    public function afficheCarte(): string
    {
        $id = $this->_id;
        $login = htmlspecialchars($this->_login);
        $nomComplet = htmlspecialchars($this->_prenom . " " . $this->_nom);
        $image = $this->_image;
        $image = str_replace("/utilisateur", "/", $image);
        $statut = self::statut($this->_privilege);
        $nbLogins = $this->_nbreLogins;
        $dateLogin = dateMysqlVersTexte($this->_dateDernierLogin);
        $nbChansons = $this->getNbChansons();
        
        require_once dirname(__DIR__) . "/lib/Image.php";
        $urlAvatar = Image::getThumbnailUrl($id . "/" . str_replace("/utilisateur", "", $image), 'mini', 'utilisateurs');

        $classeStatut = match($this->_privilege) {
            3 => "label-danger", // Admin
            2 => "label-warning", // Editeur
            1 => "label-primary", // Membre
            default => "label-default" // Invité
        };

        $actionsAdmin = "";
        if (aDroits($GLOBALS["PRIVILEGE_EDITEUR"])) {
            $urlEdit = "utilisateur_form.php?id=$id";
            $urlSuppr = "utilisateur_get.php?id=$id&mode=SUPPR";
            $actionsAdmin = <<<HTML
            <div class="user-card-actions">
                <a href="$urlEdit" class="btn btn-xs btn-primary" title="Modifier"><i class="glyphicon glyphicon-pencil"></i></a>
                <a href="$urlSuppr" class="btn btn-xs btn-danger" title="Supprimer" onclick="return confirm('Supprimer cet utilisateur ?')"><i class="glyphicon glyphicon-trash"></i></a>
            </div>
HTML;
        }

        return <<<HTML
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="thumbnail user-card">
                <div class="user-card-header">
                    <img src="$urlAvatar" alt="$login" class="user-avatar-big">
                    <h3>$login</h3>
                    <span class="label $classeStatut">$statut</span>
                </div>
                <div class="user-card-body">
                    <p class="user-name">$nomComplet</p>
                    <div class="user-stats">
                        <div class="stat-item" title="Chansons créées">
                            <i class="glyphicon glyphicon-music" style="color: #8B4513;"></i> <strong>$nbChansons</strong> chansons
                        </div>
                        <div class="stat-item" title="Logins totaux">
                            <i class="glyphicon glyphicon-log-in"></i> <strong>$nbLogins</strong> connexions
                        </div>
                        <small class="text-muted" style="display:block; margin-top:5px; font-size:0.8em;">Dernier accès : $dateLogin</small>
                    </div>
                    $actionsAdmin
                </div>
            </div>
        </div>
HTML;
    }
}
}

// --- FONCTIONS WRAPPERS (POUR COMPATIBILITÉ) ---

if (!function_exists('chercheUtilisateurParEmail')) {
    function chercheUtilisateurParEmail($email) {
        return Utilisateur::chercheUtilisateurParEmail($email);
    }
}
if (!function_exists('chercheUtilisateurs')) {
    function chercheUtilisateurs($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true) {
        return Utilisateur::chercheUtilisateurs($critere, $valeur, $critereTri, $bTriAscendant);
    }
}
if (!function_exists('chercheUtilisateur')) {
    function chercheUtilisateur($id) {
        return Utilisateur::chercheUtilisateur($id);
    }
}
if (!function_exists('chercheUtilisateurParLeLogin')) {
    function chercheUtilisateurParLeLogin($login) {
        return Utilisateur::chercheUtilisateurParLeLogin($login);
    }
}
if (!function_exists('creeUtilisateur')) {
    function creeUtilisateur($login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $privilege) {
        return Utilisateur::creeUtilisateur($login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $privilege);
    }
}
if (!function_exists('modifieUtilisateur')) {
    function modifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $nbreLogins, $privilege) {
        Utilisateur::modifieUtilisateur($id, $login, $mdp, $prenom, $nom, $image, $site, $email, $signature, $nbreLogins, $privilege);
    }
}
if (!function_exists('supprimeUtilisateur')) {
    function supprimeUtilisateur($id) {
        Utilisateur::supprimeUtilisateur($id);
    }
}
if (!function_exists('login_utilisateur')) {
    function login_utilisateur($login, $mdp) {
        return Utilisateur::login_utilisateur($login, $mdp);
    }
}
if (!function_exists('statut')) {
    function statut($privilege) {
        return Utilisateur::statut($privilege);
    }
}
if (!function_exists('selectUtilisateur')) {
    function selectUtilisateur($critere, $valeur, $critereTri = 'nom', $bTriAscendant = true, $idSelectionne = 0, $nomDuChamp ='fidUser', $idDuChamp="fiduser") {
        return Utilisateur::selectUtilisateur($critere, $valeur, $critereTri, $bTriAscendant, $idSelectionne, $nomDuChamp, $idDuChamp);
    }
}
if (!function_exists('portraitDesUtilisateurs')) {
    function portraitDesUtilisateurs() {
        return Utilisateur::portraitDesUtilisateurs();
    }
}
