<?php
/**
 * Service d'Audit des Liens.
 * Responsabilité : Récupérer les URLs en base et vérifier leur statut HTTP.
 */

class LienAuditService
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère la liste des liens externes à auditer.
     */
    public function getUrlsToAudit(): array
    {
        $sql = "SELECT l.id, l.url, l.type, l.description, c.nom as chanson_nom, c.id as chanson_id
                FROM lienurl l
                LEFT JOIN chanson c ON l.idtable = c.id AND l.nomtable = 'chanson'
                ORDER BY l.date DESC";
        
        $result = $this->db->query($sql);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * Vérifie le statut HTTP d'une URL de manière optimisée (HEAD request).
     */
    public function checkUrl(string $url): int
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return 0;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Partoches-LinkChecker/1.0)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (int)$httpCode;
    }
}
