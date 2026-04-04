<?php

// Remonte d'un niveau depuis navigation pour atteindre lib/
require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/../lib/configMysql.php";

class Footer
{
    const MYSQL = 'mysql';
    private string $_html;
    private string $_cle;

    public function __construct(string $cle = 'footerHtml')
    {
        $this->_html = "";
        $this->_cle = $cle;
        $this->chargeDepuisBdd();
    }

    /**
     * Charge le footer HTML depuis la table parametres.
     */
    public function chargeDepuisBdd(): void
    {
        // On s'assure que la table existe pour eviter les plantages
        $db = $_SESSION[self::MYSQL] ?? null;
        if (!$db) {
            $this->_html = "";
            return;
        }
        
        $db->query("CREATE TABLE IF NOT EXISTS parametres (nom VARCHAR(255) PRIMARY KEY, valeur TEXT)");

        $maRequete = "SELECT valeur FROM parametres WHERE nom='" . $this->_cle . "' LIMIT 1";
        $result = $db->query($maRequete);

        if ($result && ($ligne = $result->fetch_row())) {
            $this->_html = $ligne[0] ?? '';
        } else {
            $this->_html = "";
        }
    }

    /**
     * Sauvegarde le footer HTML dans la table parametres.
     */
    public function sauveBdd(): void
    {
        $db = $_SESSION[self::MYSQL] ?? null;
        if (!$db) return;

        $valeur = $db->real_escape_string($this->_html);

        // Vérifie si la clé existe déjà
        $check = "SELECT COUNT(*) FROM parametres WHERE nom='" . $this->_cle . "'";
        $res = $db->query($check);
        $exists = ($res && ($row = $res->fetch_row()) && $row[0] > 0);

        if ($exists) {
            $maRequete = "UPDATE parametres SET valeur='$valeur' WHERE nom='" . $this->_cle . "'";
        } else {
            $maRequete = "INSERT INTO parametres (nom, valeur) VALUES ('" . $this->_cle . "', '$valeur')";
        }

        $db->query($maRequete);
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
