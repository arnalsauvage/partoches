<?php
/**
 * Renderer pour le formulaire Utilisateur.
 */
class UtilisateurFormRenderer
{
    public static function render(array $data, string $msg = ''): string
    {
        $id = $data['id'];
        $donnee = $data['donnee'];
        $mode = $data['mode'];

        $titrePage = ($mode === "MAJ") ? "Profil de " . $donnee[1] : "Création Utilisateur";
        
        $html = envoieHead($titrePage, "../../css/form.css");
        $pasDeMenu = true;
        require_once dirname(__DIR__) . "/navigation/menu.php";
        $html .= $MENU_HTML;

        // Message Toastr
        if ($msg) {
            $msgScript = self::getMsgScript($msg);
            $html .= $msgScript;
        }

        $html .= "<div class='container' style='margin-top: 20px;'>";
        $html .= "<h1><i class='glyphicon glyphicon-user'></i> $titrePage</h1>";

        $html .= "<div id='tabs' class='card-shadow-django' style='background:white; padding:20px; border-radius:8px;'>";
        $html .= "<ul>";
        $html .= "<li><a href='#tab-general'>Informations</a></li>";
        if ($mode === "MAJ") {
            $html .= "<li><a href='#tab-publications'>Mes Publications</a></li>";
        }
        $html .= "</ul>";

        // TAB 1 : GENERAL
        $html .= "<div id='tab-general'>";
        $html .= self::renderGeneralForm($donnee, $mode);
        $html .= "</div>";

        // TAB 2 : PUBLICATIONS
        if ($mode === "MAJ") {
            $html .= "<div id='tab-publications'>";
            $html .= self::renderPublications($id);
            $html .= "</div>";
        }

        $html .= "</div></div>"; // /tabs /container

        $html .= self::renderScripts();
        $html .= envoieFooter();

        return $html;
    }

    private static function renderGeneralForm(array $donnee, string $mode): string
    {
        $id = $donnee[0];
        $avatarFile = str_replace("/utilisateur/", "", $donnee[5] ?? '');
        $avatarUrl = Image::getThumbnailUrl($avatarFile, 'sd', 'utilisateurs');

        $html = "<div class='row'>";
        
        // Côté gauche : Avatar et Upload
        $html .= "<div class='col-md-4 text-center'>";
        $html .= "<div class='well' style='background:#fdfdfd;'>";
        $html .= "<img id='user-avatar-preview' src='$avatarUrl' class='img-circle shadow' style='width:180px; height:180px; object-fit:cover; border: 4px solid var(--c-marron-clair); margin-bottom:15px;'>";
        
        $html .= "<h4>Changer la photo</h4>";
        $html .= "<form action='utilisateur_upload.php' method='post' enctype='multipart/form-data' class='text-left'>";
        $html .= "<input type='hidden' name='id' value='$id'>";
        $html .= "<div class='form-group'>";
        $html .= "<input type='file' name='fichierUploade' class='form-control'>";
        $html .= "</div>";
        $html .= "<button type='submit' class='btn btn-primary btn-block'><i class='glyphicon glyphicon-upload'></i> Envoyer</button>";
        $html .= "</form>";
        $html .= "</div>";

        if (estAdmin() && $id > 0) {
            $html .= "<div class='well' style='border: 1px solid #d9534f; background:#fff5f5;'>";
            $html .= "<h4 style='color:#d9534f;'>Administration</h4>";
            $html .= "<a href='../chanson/chanson_depublier_tout.php?idUser=$id' class='btn btn-danger btn-block' onclick='return confirm(\"Voulez-vous vraiment dépublier TOUTES les chansons de cet utilisateur ?\")'>Dépublier tout</a>";
            $html .= "</div>";
        }
        $html .= "</div>";

        // Côté droit : Formulaire
        $html .= "<div class='col-md-8'>";
        $html .= "<form id='user-form' method='POST' action='utilisateur_get.php' class='form-dj-reset'>";
        $html .= "<input type='hidden' name='id' value='$id'>";
        $html .= "<input type='hidden' name='mode' value='$mode'>";
        $html .= "<input type='hidden' name='fimage' value='{$donnee[5]}'>";

        $html .= self::renderField("Identifiant (Login)", "flogin", $donnee[1], "Identifiant de connexion", $mode === 'MAJ');
        $html .= self::renderField("Mot de passe", "fmdp", $donnee[2], "Mot de passe", false, "password");
        
        $html .= "<div class='row'>";
        $html .= "<div class='col-sm-6'>" . self::renderField("Prénom", "fprenom", $donnee[3], "Votre prénom") . "</div>";
        $html .= "<div class='col-sm-6'>" . self::renderField("Nom", "fnom", $donnee[4], "Votre nom") . "</div>";
        $html .= "</div>";

        $html .= self::renderField("Site Web", "fsite", $donnee[6], "http://...");
        $html .= self::renderField("Email", "femail", $donnee[7], "votre@email.com");

        $html .= "<div class='form-group-django'>";
        $html .= "<label class='label-django'>Signature / Devise :</label>";
        $html .= "<textarea name='fsignature' class='input-django' rows='4'>".htmlspecialchars($donnee[8])."</textarea>";
        $html .= "</div>";

        if (estAdmin()) {
            $html .= "<div class='row'>";
            $html .= "<div class='col-sm-6'>";
            $html .= "<div class='form-group-django'>";
            $html .= "<label class='label-django'>Privilèges :</label>";
            $html .= "<select name='fprivilege' class='input-django'>";
            $labels = ["0 - Invité", "1 - Membre", "2 - Éditeur", "3 - Administrateur"];
            foreach ($labels as $idx => $lbl) {
                $sel = ((int)$donnee[11] === $idx) ? 'selected' : '';
                $html .= "<option value='$idx' $sel>$lbl</option>";
            }
            $html .= "</select></div></div>";
            $html .= "<div class='col-sm-6'>" . self::renderField("Nombre de logins", "fnbreLogins", $donnee[10], "", false, "number") . "</div>";
            $html .= "</div>";
        }

        $html .= "<div class='mt-20'>";
        $html .= "<button type='submit' class='btn btn-success btn-lg btn-block'><i class='glyphicon glyphicon-save'></i> ENREGISTRER LES MODIFICATIONS</button>";
        $html .= "</div>";

        $html .= "</form></div>";
        $html .= "</div>"; // row

        return $html;
    }

    private static function renderField($label, $name, $value, $placeholder = "", $readonly = false, $type = "text"): string
    {
        $ro = $readonly ? "readonly" : "";
        $val = htmlspecialchars($value);
        return "
            <div class='form-group-django'>
                <label class='label-django'>$label :</label>
                <input type='$type' name='$name' value='$val' class='input-django' placeholder='$placeholder' $ro>
            </div>";
    }

    private static function renderPublications(int $idUser): string
    {
        $html = "<h3>Partitions publiées</h3>";
        if (!class_exists('Chanson')) require_once dirname(__DIR__) . "/chanson/Chanson.php";
        
        // Appel corrigé : critère global %, tri par nom, ascendant, filtre contributeur
        $resIds = Chanson::chercheChansons('%', 'nom', true, 'contributeur', $idUser);
        
        if (!empty($resIds)) {
            $html .= "<div class='row'>";
            foreach ($resIds as $idCh) {
                $c = new Chanson($idCh);
                $html .= $c->afficheCarteChanson();
            }
            $html .= "</div>";
        } else {
            $html .= "<div class='alert alert-info'>Aucune publication trouvée pour cet utilisateur.</div>";
        }
        return $html;
    }

    private static function renderScripts(): string
    {
        return <<<'JAVASCRIPT'
<script>
$(document).ready(function() {
    var activeTab = sessionStorage.getItem('userFormActiveTab') || 0;
    $("#tabs").tabs({
        active: parseInt(activeTab),
        activate: function(event, ui) {
            sessionStorage.setItem('userFormActiveTab', ui.newTab.index());
        }
    });
});
</script>
JAVASCRIPT;
    }

    private static function getMsgScript(string $m): string
    {
        $js = "<script>$(function() { ";
        switch($m) {
            case 'OK_UPLOAD':  $js .= "toastr.success('Image mise à jour avec succès !');"; break;
            case 'OK_ACTION':  $js .= "toastr.success('Action effectuée.');"; break;
            case 'AUTH_DENIED': $js .= "toastr.error('Accès refusé.');"; break;
            case 'ERR_SIZE':   $js .= "toastr.error('Le fichier est trop volumineux (max 2Mo).');"; break;
            case 'ERR_EXT':    $js .= "toastr.error('Format d\'image non autorisé (jpg, png, webp).');"; break;
            case 'ERR_COPY':   $js .= "toastr.error('Erreur technique lors de la copie de l\'image.');"; break;
            case 'ERR_UPLOAD': $js .= "toastr.error('Erreur lors du transfert du fichier.');"; break;
            case 'ERR_NO_FILE': $js .= "toastr.warning('Aucun fichier sélectionné.');"; break;
        }
        $js .= " });</script>";
        return $js;
    }
}
