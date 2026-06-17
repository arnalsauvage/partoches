<?php
/**
 * CLASSE : ChansonFormNewRenderer
 * Responsabilité : Gérer le rendu du nouveau formulaire de chanson via un template .phtml.
 */

class ChansonFormNewRenderer
{
    /**
     * Rendu complet de la page.
     * @param Chanson $chanson
     * @param string $mode 'INS' ou 'MAJ'
     * @param array $context Données de contexte supplémentaires
     * @return string
     */
    public static function render(Chanson $chanson, string $mode, array $context = []): string
    {
        $titrePage = ($mode === 'MAJ')
            ? 'Mise à jour - ' . $chanson->getNom()
            : 'Création chanson (Expérimental)';

        // Inclusion explicite de la CSS spécifique
        $headHtml = envoieHead($titrePage, '../../css/chansonform.css');
        $pasDeMenu = true;
        require_once dirname(__DIR__) . '/navigation/menu.php';

        // Données pour le template
        $data = [
            'chanson' => $chanson,
            'mode' => $mode,
            'context' => $context
        ];

        // Capture du rendu du template
        ob_start();
        extract($data);
        include __DIR__ . '/views/chanson_form_view.phtml';
        $sortie = ob_get_clean();

        $final = $headHtml;
        $final .= $MENU_HTML;
        $final .= $sortie;
        $final .= envoieFooter();

        return $final;
    }
}
