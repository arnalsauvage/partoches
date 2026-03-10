<?php

// Remonte d'un niveau depuis navigation pour atteindre lib/
require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/../lib/configMysql.php";

class Footer
{
    const MYSQL = 'mysql';
    private string $_html;

    public function __construct()
    {
        $this->_html = "";
        $this->chargeDepuisBdd();
    }

    /**
     * Charge le footer HTML depuis la table parametres.
     * La table doit contenir une ligne : nom='footerHtml', valeur='<html>'
     */
    public function chargeDepuisBdd(): void
    {
        $maRequete = "SELECT valeur FROM parametres WHERE nom='footerHtml' LIMIT 1";
        $result = $_SESSION[self::MYSQL]->query($maRequete)
        or die("Problème chargeDepuisBdd : " . $_SESSION[self::MYSQL]->error . " requête : " . $maRequete);

        if ($ligne = $result->fetch_row()) {
            $this->_html = $ligne[0];
        } else {
            $this->_html = "";
        }
    }

    /**
     * Sauvegarde le footer HTML dans la table parametres.
     * S'il existe déjà, on le met à jour, sinon on crée la ligne.
     */
    public function sauveBdd(): void
    {
        $valeur = $_SESSION[self::MYSQL]->real_escape_string($this->_html);

        // Vérifie si la clé existe déjà
        $check = "SELECT COUNT(*) FROM parametres WHERE nom='footerHtml'";
        $res = $_SESSION[self::MYSQL]->query($check)
        or die("Problème vérification parametres : " . $_SESSION[self::MYSQL]->error);
        $exists = ($res->fetch_row()[0] > 0);

        if ($exists) {
            $maRequete = "UPDATE parametres SET valeur='$valeur' WHERE nom='footerHtml'";
        } else {
            $maRequete = "INSERT INTO parametres (nom, valeur) VALUES ('footerHtml', '$valeur')";
        }

        $_SESSION[self::MYSQL]->query($maRequete)
        or die("Problème sauveBdd : " . $_SESSION[self::MYSQL]->error . " requête : " . $maRequete);
    }

    /**
     * Retourne le code HTML du footer prêt à afficher
     */
    public function getHtml(): string
    {
        return $this->_html;
    }

    /**
     * Définit le HTML du footer
     */
    public function setHtml(string $html): void
    {
        $this->_html = $html;
    }

    /**
     * Retourne le formulaire HTML pour modifier le footer
     */
    public function getHtmlForm(): string
    {
        $footerHtml = htmlspecialchars($this->_html);
        return <<<HTML
<div class="tab-pane fade" id="footer">
    <div class="mb-3">
        <label for="footerHtml" class="form-label">Contenu HTML du pied de page</label>
        <textarea class="form-control" name="footerHtml" id="footerHtml" rows="10" style="font-family: monospace;">$footerHtml</textarea>
        <small class="text-muted d-block mt-2">
            Vous pouvez insérer des liens, des images et du texte (balises autorisées : &lt;a&gt;, &lt;br&gt;, &lt;img&gt;, &lt;strong&gt;, &lt;em&gt;).
        </small>
    </div>
</div>
HTML;
    }
}
