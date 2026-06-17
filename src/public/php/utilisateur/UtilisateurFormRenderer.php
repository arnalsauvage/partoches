<?php
/**
 * CLASSE : UtilisateurFormRenderer
 * Responsabilité : Gérer le rendu du formulaire utilisateur via un template .phtml.
 */

class UtilisateurFormRenderer
{
    /**
     * Rendu complet de la page.
     * @param array $data Les données préparées par le service
     * @param string $msg Message Toastr à afficher
     * @return string Le HTML généré
     */
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
            $html .= self::getMsgScript($msg);
        }

        // Capture du rendu du template
        ob_start();
        extract($data);
        include __DIR__ . '/views/utilisateur_form_view.phtml';
        $html .= ob_get_clean();

        $html .= envoieFooter();

        return $html;
    }

    /**
     * Génère le script JS pour les notifications Toastr.
     */
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
