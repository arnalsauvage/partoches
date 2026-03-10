<?php
/**
 * Liste des utilisateurs (Django Style)
 * Affichage moderne sous forme de cartes Canopée avec tri dynamique.
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once "../navigation/menu.php";

// Palette Canopée
$c_marron_fonce = "#2b1d1a";
$c_marron_clair = "#D2B48C"; 
$c_accent = "#8B4513";
$c_orange = "#e67e22";

// Sécurité et Session
$userSession = $_SESSION['user'] ?? '';
$privilegeSession = $_SESSION['privilege'] ?? 0;

// Gestion du tri
$tri = $_GET['tri'] ?? 'recent';
$users = Utilisateur::chargeUtilisateursBdd($tri);
$nbUsers = count($users);

// Classes actives pour les boutons de tri
$activeRecent = ($tri == 'recent') ? 'active' : '';
$activeChansons = ($tri == 'chansons') ? 'active' : '';
$activeLogins = ($tri == 'logins') ? 'active' : '';

// --- RENDU HTML ---
$html = envoieHead("Communauté", "../../css/utilisateur_liste.css");

$html .= <<<HTML
<div class="container user-container">
    
    <div class="row">
        <div class="col-xs-12 text-center" style="margin-bottom: 20px;">
            <h1 style="color: $c_marron_fonce; font-weight: 900; font-size: 42px; text-transform: uppercase; letter-spacing: 5px; margin-bottom: 10px;">
                <span class="glyphicon glyphicon-user"></span> LA COMMUNAUTÉ
            </h1>
            <p style="color: $c_marron_clair; font-size: 14px; letter-spacing: 3px; font-weight: bold;">$nbUsers MUSICIENS INSCRITS</p>
            <div style="width: 80px; height: 4px; background: $c_orange; margin: 15px auto; border-radius: 2px;"></div>
HTML;

// Bouton Admin : Proposer setlist
if ($privilegeSession > $GLOBALS["PRIVILEGE_MEMBRE"]){
    $html .= "<a href='utilisateurBand_form.php' class='btn btn-sm btn-default' style='margin-bottom: 15px;' title='Meilleure setlist pour un groupe'><i class='glyphicon glyphicon-list-alt'></i> Setlist par band</a>";
}

$html .= "</div></div>";

// CONSOLE DE TRI
$html .= <<<HTML
<div class="row" style="margin-bottom: 40px;">
    <div class="col-xs-12 text-center">
        <div class="btn-group" role="group">
            <a href="?tri=recent" class="btn btn-default $activeRecent" style="border-radius: 20px 0 0 20px; font-weight: bold; color: $c_marron_fonce;">
                <i class="glyphicon glyphicon-time"></i> RÉCENTS
            </a>
            <a href="?tri=chansons" class="btn btn-default $activeChansons" style="font-weight: bold; color: $c_marron_fonce;">
                <i class="glyphicon glyphicon-music"></i> TOP CONTRIBUTEURS
            </a>
            <a href="?tri=logins" class="btn btn-default $activeLogins" style="border-radius: 0 20px 20px 0; font-weight: bold; color: $c_marron_fonce;">
                <i class="glyphicon glyphicon-star"></i> PLUS ACTIFS
            </a>
        </div>
HTML;

// Bouton Admin : Créer utilisateur
if ($privilegeSession > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $html .= <<<HTML
    <div style="margin-top: 20px;">
        <a href="utilisateur_form.php" class="btn btn-create-user">
            <i class="glyphicon glyphicon-plus-sign"></i> CRÉER UN NOUVEL UTILISATEUR
        </a>
    </div>
HTML;
}

$html .= <<<HTML
    </div>
</div>

<div class="row">
HTML;

// Boucle sur les utilisateurs chargés et triés
foreach ($users as $u) {
    // Filtrage visuel (Admin voit tout, Membre ne voit que les autres ou lui-même)
    if (($privilegeSession > $GLOBALS["PRIVILEGE_MEMBRE"]) || $userSession == $u->getLogin()) {
        $html .= $u->afficheCarte();
    }
}

$html .= <<<HTML
    </div>
</div>
HTML;

$html .= envoieFooter();

// Affichage final
echo $html;
