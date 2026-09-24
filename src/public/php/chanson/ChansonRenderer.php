<?php
/**
 * CLASSE : ChansonRenderer
 * Responsabilité : Générer les composants visuels (HTML) pour les chansons.
 */

class ChansonRenderer
{
    /**
     * Affiche une carte moderne (thumbnail Bootstrap 3) pour la chanson
     * @param Chanson $chanson
     * @return string HTML de la carte
     */
    public static function renderCard(Chanson $chanson): string
    {
        $id = $chanson->getId();
        
        if (!class_exists('Document')) require_once dirname(__DIR__) . "/document/Document.php";
        if (!class_exists('ComposantsUI')) require_once dirname(__DIR__) . "/lib/ComposantsUI.php";
        
        $nomImage = Document::imageTableId("chanson", $id);
        $imagePochette = affichePochette($nomImage, $id, 200, 200);
        $titre = htmlspecialchars(limiteLongueur($chanson->getNom(), 25));
        $interpreteFull = $chanson->getInterprete();
        $interpreteAffiche = htmlspecialchars(limiteLongueur($interpreteFull, 25));
        $annee = $chanson->getAnnee();
        $tempo = $chanson->getTempo();
        $tonalite = $chanson->getTonalite();
        $tonaliteOrig = $chanson->getTonaliteOriginale();

        // Construction des liens de filtrage (pointent vers la liste des chansons)
        $urlBase = "../chanson/chanson_liste.php";
        $urlInterprete = "$urlBase?filtre=interprete&amp;valFiltre=" . urlencode($interpreteFull);
        $urlAnnee = "$urlBase?filtre=annee&amp;valFiltre=" . urlencode((string)$annee);
        $urlTempo = "$urlBase?filtre=tempo&amp;valFiltre=" . urlencode((string)$tempo);
        $urlTonalite = "$urlBase?filtre=tonalite&amp;valFiltre=" . urlencode($tonalite);

        // Sous-titre (Interprète)
        $sousTitre = "<p style='font-style: italic; margin: 0;'>
                        <a href='$urlInterprete' title='Filtrer par cet interprète' class='text-muted' style='text-decoration: none;'>$interpreteAffiche</a>
                      </p>";

        $badgeTonaOrig = "";
        if (!empty($tonaliteOrig)) {
            $urlTonaOrig = "$urlBase?filtre=tonalite_originale&amp;valFiltre=" . urlencode($tonaliteOrig);
            $badgeTonaOrig = "<a href='$urlTonaOrig' title='Filtrer par la tonalité originale ($tonaliteOrig)' style='text-decoration: none;'>
                <span class='label label-default' style='background-color: #6c757d; color: #fff;'>Orig. $tonaliteOrig</span>
            </a>";
        }

        // Badges (Année, Tempo, Tona, Tona Orig)
        $badges = "
            <a href='$urlAnnee' title='Filtrer par cette année' style='text-decoration: none;'>
                <span class='label label-default' style='background-color: var(--c-marron-clair); color: var(--c-marron-fonce);'>$annee</span>
            </a>
            <a href='$urlTempo' title='Filtrer par ce tempo' style='text-decoration: none;'>
                <span class='label label-default'>$tempo BPM</span>
            </a>
            <a href='$urlTonalite' title='Filtrer par cette tonalité' style='text-decoration: none;'>
                <span class='label' style='background-color: var(--c-accent);'>$tonalite</span>
            </a>
            $badgeTonaOrig";

        // Actions (Voir, Editer)
        $actions = "
            <div class='btn-group' role='group'>
                <a href='../chanson/chanson_voir.php?id=$id' class='btn btn-canopee-voir'>Voir</a>
            </div>";
        
        if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
            $actions .= "
            <div class='btn-group' role='group'>
                <a href='../chanson/chanson_form.php?id=$id' class='btn btn-canopee-editer'>Editer</a>
            </div>";
        }

        // Badge Spécial (Brouillon)
        $badgeSpecial = "";
        if ($chanson->getPublication() == 0) {
            if (estAdmin() || (isset($_SESSION['id']) && $_SESSION['id'] == $chanson->getIdUser())) {
                $badgeSpecial = "<div class='badge-brouillon'>Brouillon</div>";
            }
        }

        return ComposantsUI::afficheCarteCanopee($titre, $sousTitre, $imagePochette, "../chanson/chanson_voir.php?id=$id", $badges, $actions, ['badgeSpecial' => $badgeSpecial]);
    }
}
