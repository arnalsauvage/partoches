<?php
/**
 * CLASSE : ChansonVoirRenderer
 * Responsabilité : Gérer le rendu de la page de détail d'une chanson en utilisant un template .phtml.
 */
class ChansonVoirRenderer
{
    /**
     * Rendu complet de la page.
     * @param array $data Les données préparées par le service
     * @return string Le HTML généré
     */
    public static function render(array $data): string
    {
        // On extrait les variables pour qu'elles soient disponibles dans le template
        extract($data);

        // On utilise la mise en tampon pour capturer le rendu du fichier .phtml
        ob_start();
        include __DIR__ . '/views/chanson_voir_view.phtml';
        return ob_get_clean();
    }
}
