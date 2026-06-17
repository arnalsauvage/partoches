<?php
/**
 * CLASSE : PlaylistFormRenderer
 * Responsabilité : Gérer le rendu du formulaire de playlist via un template .phtml.
 */

class PlaylistFormRenderer
{
    /**
     * Rendu complet de la page.
     * @param array $data Les données préparées par le service
     * @param string $message Message de succès ou d'erreur
     * @return string Le HTML généré
     */
    public static function render(array $data, string $message = ''): string
    {
        $id = $data['id'];
        $playlist = $data['playlist'];
        $mode = $data['mode'];

        $titrePage = ($mode === "MAJ") ? "Mise à jour - " . htmlspecialchars($playlist['nom'] ?? '') : "Nouvelle Playlist";

        // En-tête HTML
        $html = envoieHead($titrePage, "../../css/playlistform.css");
        $pasDeMenu = true;
        require_once dirname(__DIR__) . "/navigation/menu.php";
        $html .= $MENU_HTML;

        // Injection des messages dans les données
        $data['message'] = $message;

        // Capture du rendu du template
        ob_start();
        extract($data);
        include __DIR__ . '/views/playlist_form_view.phtml';
        $html .= ob_get_clean();

        $html .= envoieFooter();

        return $html;
    }
}
