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
        // Palette Canopée
        $c_marron_fonce = "#2b1d1a";
        $c_marron_clair = "#D2B48C"; 
        $c_accent = "#8B4513";
        $c_beige = "#F5F5DC";

        $hauteur = $options['hauteur'] ?? "400px";
        $badgeSpecial = $options['badgeSpecial'] ?? ""; // Ex: "Brouillon"

        $html = "
        <div class='col-sm-6 col-md-4 col-lg-3' style='margin-bottom: 25px;'>
            <div class='thumbnail shadow-hover' style='height: $hauteur; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; padding: 0; border: 1px solid $c_marron_clair; background-color: $c_marron_fonce; position: relative;'>
                
                $badgeSpecial
                
                <a href='$urlVoir' style='text-decoration: none;'>
                    <div style='height: 180px; overflow: hidden; background-color: $c_marron_clair; display: flex; align-items: center; justify-content: center; border-bottom: 3px solid $c_accent;'>
                        $imageHtml
                    </div>
                </a>
                
                <div class='caption' style='padding: 15px; text-align: center; color: $c_beige;'>
                    <h4 style='margin-top: 0; margin-bottom: 5px; color: $c_marron_clair; height: 44px; overflow: hidden; font-weight: bold;'>$titre</h4>
                    
                    <div style='height: 40px; overflow: hidden; margin-bottom: 10px;'>
                        $sousTitre
                    </div>
                    
                    <div style='margin-bottom: 15px; height: 25px;'>
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
