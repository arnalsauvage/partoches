<?php
/**
 * CLASSE : ComposantsUI
 * Centralise les éléments d'interface réutilisables (Gabarits Canopée).
 */

class ComposantsUI
{
    /**
     * Affiche une carte moderne standardisée (Thumbnail Bootstrap 3 revisité).
     * 
     * @param string $titre Titre principal
     * @param string $sousTitre Sous-titre ou description courte
     * @param string $imageHtml Bloc HTML de l'image (ex: <img...>)
     * @param string $urlVoir URL cible lors du clic sur l'image
     * @param string $badgesHtml HTML des petits badges (labels) au centre
     * @param string $actionsHtml HTML des boutons d'action en bas
     * @param array  $options Options supplémentaires (ex: badgeSpecial, hauteur)
     * @return string
     */
    public static function afficheCarteCanopee($titre, $sousTitre, $imageHtml, $urlVoir, $badgesHtml = "", $actionsHtml = "", $options = []): string
    {
        $hauteur = $options['hauteur'] ?? "400px";
        $badgeSpecial = $options['badgeSpecial'] ?? ""; // Ex: HTML du badge brouillon

        $html = "
        <div class='col-sm-6 col-md-4 col-lg-3' style='margin-bottom: 25px;'>
            <div class='thumbnail carte-canopee' style='height: $hauteur;'>
                
                $badgeSpecial
                
                <a href='$urlVoir'>
                    <div class='image-container'>
                        $imageHtml
                    </div>
                </a>
                
                <div class='caption'>
                    <h4>$titre</h4>
                    
                    <div class='sous-titre'>
                        $sousTitre
                    </div>
                    
                    <div class='badges-container'>
                        $badgesHtml
                    </div>
                    
                    <div class='btn-group btn-group-justified' role='group'>
                        $actionsHtml
                    </div>
                </div>
            </div>
        </div>";

        return $html;
    }
}
