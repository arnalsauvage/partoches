<?php

require_once("../lib/utilssi.php");
require_once("../document/Document.php");
require_once("../lib/Image.php");
require_once("../navigation/menu.php");

$mode = "";
$table = "utilisateur";
$sortie = "";
// Chargement des donnees de l'utilisateur si l'identifiant est fourni
if (isset ($_GET ['id']) && $_GET ['id'] != "") {
    $donnee = chercheUtilisateur($_GET ['id']);
    if (($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) || $_SESSION ['user'] == $donnee [1]) {
        $mode = "MAJ";
        $donnee [2] = Chiffrement::decrypt($donnee [2]);
        $donnee [1] = htmlspecialchars($donnee [1]);
        $donnee [3] = htmlspecialchars($donnee [3]);
        $donnee [4] = htmlspecialchars($donnee [4]);
        $donnee [6] = htmlspecialchars($donnee [6]);
        $donnee [7] = htmlspecialchars($donnee [7]);
        $donnee [8] = htmlspecialchars($donnee [8]);
    }
} else if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $mode = "INS";
    $donnee [0] = 0; // id
    $donnee [1] = ""; // login
    $donnee [2] = ""; // mdp
    $donnee [3] = ""; // prenom
    $donnee [4] = ""; // nom
    $donnee [5] = ""; // image
    $donnee [6] = "http://"; // site
    $donnee [7] = "@"; // Adresse
    $donnee [8] = "Devise ou citation..."; // signature
    $donnee [9] = "1970-01-01"; // Date dernier login
    $donnee [10] = 0; // nbrelogins
    $donnee [11] = 0; // privilege
}

if ($mode == "MAJ"){
    $sortie .= "<H1> Mise à jour - " . $table . " : " . $donnee[1] . "</H1>";
}
else
{
    if ($mode == "INS")
    {
        $sortie .= "<H1> Création - " . $table . "</H1>";
    }
    else
    {
        return;
    }
}

// --- STRUCTURE DES ONGLETS BOOTSTRAP ---
$sortie .= "
<ul class='nav nav-tabs' role='tablist' style='margin-bottom: 20px;'>
    <li role='presentation' class='active'><a href='#general' aria-controls='general' role='tab' data-toggle='tab'>Général</a></li>
    " . ($mode == 'MAJ' ? "<li role='presentation'><a href='#publications' aria-controls='publications' role='tab' data-toggle='tab'>Publications</a></li>" : "") . "
</ul>

<div class='tab-content'>
    <!-- ONGLET GÉNÉRAL -->
    <div role='tabpanel' class='tab-pane active' id='general'>";

// Création du formulaire (Contenu actuel)
$dummy = "";
$f = new Formulaire ("POST", $table . "_get.php", $dummy);
$f->champCache("id", $donnee [0]);

// Avatar moderne via Image.php
$avatarFile = str_replace("/utilisateur/", "", $donnee [5]);
$avatarUrl = Image::getThumbnailUrl($donnee[0] . "/" . $avatarFile, 'sd', 'utilisateurs');
$sortie .= "<div class='text-center' style='margin-bottom: 20px;'><img src='$avatarUrl' class='img-circle shadow' style='width:150px; height:150px; object-fit:cover; border: 3px solid white;'></div>";

$listeImages = listeImages("/utilisateur");
$f->champListeImages("Image : ", "fimage", $avatarFile, 1, $listeImages);
$f->champTexte("Login :", "flogin", $donnee [1], 50, 32);
$f->champMotDePasse("Mot de passe : ", "fmdp", $donnee [2], 50, 32);
$f->champTexte("Prénom :", "fprenom", $donnee [3], 50, 64);
$f->champTexte("Nom :", "fnom", $donnee [4], 50, 64);
$f->champTexte("Site :", "fsite", $donnee [6], 50);
$f->champTexte("Email :", "femail", $donnee [7], 128);
$f->champFenetre("Signature :", "fsignature", $donnee [8], 5, 60);
$f->champTexte("Dernier login :", "fdateDernierLogin", dateMysqlVersTexte($donnee [9]), 50);
$f->champTexte("Nbre de logins :", "fnbreLogins", $donnee [10], 50);

$pListe = array(
    "utilisateur non validé",
    "abonné",
    "éditeur",
    "administrateur"
);
$f->champListe("Privileges :", "fprivilege", $donnee [11], 1, $pListe);

$f->champCache("mode", $mode);
$f->champValider("Valider la saisie", "valider");
$sortie .= $f->getHtml(); // On ne fait PLUS .= $f->fin() ici car fin() retourne tout le HTML accumulé !
// On va juste fermer la balise FORM manuellement pour éviter le doublement.
$sortie .= "</FORM>";

// Messages Toastr
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $script = "<script>$(function() { toastr.options = { 'positionClass': 'toast-top-center', 'closeButton': true };";
    
    if ($msg == 'depub_ok') {
        $script .= "toastr.success('Toutes les partoches de cet utilisateur ont été dépubliées.');";
    } elseif ($msg == 'error_rights') {
        $script .= "toastr.error('Erreur : Vous n\'avez pas les droits nécessaires.');";
    } elseif ($msg == 'error_db') {
        $script .= "toastr.error('Erreur lors de la mise à jour en base de données.');";
    }
    
    $script .= "});</script>";
    $sortie .= $script;
}

// Bouton Admin pour dépublier toutes les chansons d'un utilisateur
if (estAdmin() && $donnee[0] > 0) {
    $sortie .= "<div style='margin-top: 20px; padding: 15px; border: 1px solid #d9534f; border-radius: 8px; background-color: #f9f2f2;'>";
    $sortie .= "  <h4 style='color: #d9534f; margin-top: 0;'>Actions d'administration</h4>";
    $sortie .= "  <p>Voulez-vous masquer toutes les chansons publiées par cet utilisateur ?</p>";
    $sortie .= "  <a href='../chanson/chanson_depublier_tout.php?idUser=" . $donnee[0] . "' class='btn btn-danger' onclick='return confirm(\"Voulez-vous vraiment dépublier TOUTES les chansons de cet utilisateur ?\")'>Dépublier toutes les partoches</a>";
    $sortie .= "</div>";
}

$sortie .= "<h2>Envoyer une image sur le serveur</h2>
	<form action='utilisateur_upload.php' method='post'
		  enctype='multipart/form-data'>
		<input type='hidden' name='MAX_FILE_SIZE' value='150000'> 
		<input type='hidden' name='id' value='" . $donnee[0] . "'>
		<label	class='inline' for='fichier'> </label> <input type='file' id='fichier'
														  name='fichierUploade' size='40'> <input type='submit' value='Envoyer'>
	</form>";

$sortie .= "</div> <!-- Fin Tab Général -->";

if ($mode == 'MAJ') {
    // ONGLET PUBLICATIONS
    $sortie .= "<div role='tabpanel' class='tab-pane' id='publications' style='padding-top: 20px;'>";
    $sortie .= "<h3>Chansons de cet utilisateur</h3>";
    
    require_once("../chanson/Chanson.php");
    $db = $_SESSION['mysql'];
    $idUser = $donnee[0];
    $reqChansons = "SELECT id, nom, interprete, publication FROM chanson WHERE idUser = $idUser ORDER BY nom ASC";
    $resChansons = $db->query($reqChansons);
    
    if ($resChansons && $resChansons->num_rows > 0) {
        $sortie .= "<div class='row'>";
        while ($c = $resChansons->fetch_assoc()) {
            $idC = $c['id'];
            $nomImage = imageTableId("chanson", $idC);
            
            // Utilisation de la vignette moderne via Image.php
            if ($nomImage != "") {
                $pochetteUrl = Image::getThumbnailUrl($idC . "/" . $nomImage, 'mini', 'chansons');
                $pochette = "<img src='$pochetteUrl' style='width:64px; height:64px; object-fit:cover;' class='img-rounded' alt='pochette'>";
            } else {
                $pochette = "<div class='text-center img-rounded' style='width:64px; height:64px; display:flex; align-items:center; justify-content:center; background:#f5f5f5; border:1px solid #ddd;'>
                                <span class='glyphicon glyphicon-cd' style='font-size:32px; color:#ccc;'></span>
                             </div>";
            }
            
            $styleBrouillon = ($c['publication'] == 0) ? "border-left: 5px solid #d9534f; background-color: #f9f2f2;" : "";
            $badgeBrouillon = ($c['publication'] == 0) ? " <span id='badge-$idC' class='label label-danger'>BROUILLON</span>" : " <span id='badge-$idC' class='label label-danger' style='display:none;'>BROUILLON</span>";
            $checked = ($c['publication'] == 1) ? "checked" : "";
            
            $sortie .= "
            <div class='col-sm-6 col-md-4' style='margin-bottom: 10px;'>
                <div id='card-$idC' class='media' style='padding: 10px; border: 1px solid #ddd; border-radius: 8px; min-height: 100px; $styleBrouillon'>
                    <div class='media-left'>
                        <a href='../chanson/chanson_voir.php?id=$idC'>$pochette</a>
                    </div>
                    <div class='media-body'>
                        <h4 class='media-heading' style='font-size: 14px; font-weight: bold;'>
                            <a href='../chanson/chanson_voir.php?id=$idC'>" . htmlspecialchars($c['nom']) . "</a>$badgeBrouillon
                        </h4>
                        <p style='font-size: 12px; color: #666; margin: 0;'>" . htmlspecialchars($c['interprete']) . "</p>
                        <div style='margin-top: 5px;'>
                            <label style='font-size: 11px; font-weight: normal; cursor: pointer;'>
                                <input type='checkbox' class='pub-toggle' data-id='$idC' $checked> Publié
                            </label>
                            <a href='../chanson/chanson_form.php?id=$idC' class='btn btn-xs btn-default' style='margin-left: 10px;'><i class='glyphicon glyphicon-pencil'></i> Éditer</a>
                        </div>
                    </div>
                </div>
            </div>";
        }
        $sortie .= "</div>";
        
        // Script Ajax pour la bascule
        $sortie .= "
        <script>
        $(function() {
            $('.pub-toggle').on('change', function() {
                var checkbox = $(this);
                var id = checkbox.data('id');
                var isPublished = checkbox.is(':checked') ? 1 : 0;
                
                $.ajax({
                    url: '../chanson/chanson_publication_ajax.php',
                    method: 'POST',
                    data: { id: id, publication: isPublished },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.success('Visibilité mise à jour !');
                            if (isPublished) {
                                $('#badge-' + id).hide();
                                $('#card-' + id).css({'border-left': '1px solid #ddd', 'background-color': 'transparent'});
                            } else {
                                $('#badge-' + id).show();
                                $('#card-' + id).css({'border-left': '5px solid #d9534f', 'background-color': '#f9f2f2'});
                            }
                        } else {
                            toastr.error('Erreur : ' + response.message);
                            checkbox.prop('checked', !isPublished); // Reset
                        }
                    },
                    error: function() {
                        toastr.error('Erreur réseau.');
                        checkbox.prop('checked', !isPublished); // Reset
                    }
                });
            });
        });
        </script>";
    } else {
        $sortie .= "<p class='text-muted'>Aucune chanson publiée par cet utilisateur.</p>";
    }
    
    $sortie .= "</div> <!-- Fin Tab Publications -->";
}

$sortie .= "</div> <!-- Fin Tab Content -->";

// Si l'utilisateur n'est pas Admin
if ($_SESSION ['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) {
    // On désactive les champs dateDernierLogin, nbreLogins et privilege pour les non admins
    $sortie = str_replace("NAME='fdateDernierLogin'", "NAME='fdateDernierLogin' disabled='disabled' ", $sortie);
    $sortie = str_replace("NAME='fnbreLogins'", "NAME='fnbreLogins' disabled='disabled' ", $sortie);
    $sortie = str_replace("NAME='fprivilege'", "NAME='fprivilege' disabled='disabled' ", $sortie);
}

if ($_SESSION ['privilege'] > $GLOBALS["PRIVILEGE_EDITEUR"])
    $help = "(". $donnee [2] .")";

$sortie .= envoieFooter();
echo $sortie;
// privilege
// 0 : utilisateur non validé
// 1 : abonné (consultation + évaluation + commentaires)
// 2 : éditeur (idem + possibilité de rédiger, envoyer des fichiers)
// 3 : administrateur (droits complets sur le site)
