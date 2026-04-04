<?php
/**
 * Gestion de la génération des Songbooks en PDF.
 * Utilise FPDF et FPDI.
 */

require_once 'fpdf/fpdf.php';
require_once 'fpdi/autoload.php';
require_once 'fpdi/Fpdi.php';

use setasign\Fpdi\Fpdi;

/**
 * Classe de rendu PDF personnalisée pour les Songbooks.
 * Responsabilité : Dessiner les éléments graphiques du document.
 */
class SongbookPdf extends Fpdi
{
    public const FONT_ARIAL = 'Arial';
    public const LOGO_DEFAULT = '../../images/icones/top5.png';

    public function __construct()
    {
        parent::__construct();
        $this->SetAutoPageBreak(true, 15);
    }

    /**
     * Pied de page automatique
     */
    public function Footer(): void
    {
        if ($this->PageNo() > 2) {
            $this->SetY(-15);
            $this->SetFont(self::FONT_ARIAL, 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    /**
     * Ajoute une page de couverture
     */
    public function addCover(string $imagePath, string $title, int $version, string $date): void
    {
        $this->AddPage();
        
        if (file_exists($imagePath)) {
            $this->Image($imagePath, 5, 5, 190, 250);
        }

        $this->SetY(260);
        $this->SetFont(self::FONT_ARIAL, 'B', 10);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 12, (function_exists('mb_convert_encoding') ? mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8') : utf8_decode($title)) . " - v" . $version . " du " . $date, 0, 0, "C");
    }

    /**
     * Ajoute le sommaire au document (gère plusieurs pages si nécessaire)
     */
    public function addTableOfContents(array $songs, string $logoPath, array $pageNumbers): void
    {
        $this->AddPage();
        
        // Titre Sommaire
        $this->SetFont(self::FONT_ARIAL, 'B', 20);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(30, 10, ' ', 0, 0, "C");
        $this->Cell(150, 10, 'Sommaire', 1, 1, "C");

        // Logo du club
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 6, 20);
        }

        // Configuration de l'espacement
        $maxSongsPerPage = 35;
        $totalSongs = count($songs);
        
        if ($totalSongs <= $maxSongsPerPage) {
            // Ancienne logique pour les petits songbooks (auto-ajustement)
            $lineHeight = ($totalSongs > 0) ? 240 / ($totalSongs + 1) : 10;
            if ($lineHeight > 20) $lineHeight = 20;
            if ($lineHeight < 6) $lineHeight = 6;
        } else {
            // Nouvelle logique pour les gros songbooks (taille fixe, multi-pages)
            $lineHeight = 7;
        }

        $this->SetFont(self::FONT_ARIAL, 'B', $lineHeight);
        $this->Cell(10, $lineHeight / 2, " ", 0, 1, "L");

        $songCount = 0;
        foreach ($songs as $index => $songName) {
            if ($songCount > 0 && $songCount % $maxSongsPerPage == 0 && $totalSongs > $maxSongsPerPage) {
                $this->AddPage();
                $this->SetY(20);
                $this->SetFont(self::FONT_ARIAL, 'B', $lineHeight);
            }
            
            $pageNumber = $pageNumbers[$index] ?? '?';
            $this->Cell(10, $lineHeight, $pageNumber . " - " . mb_convert_encoding($songName, 'ISO-8859-1', 'UTF-8'), 0, 1, "L");
            $songCount++;
        }
    }

    /**
     * Importe et ajoute les pages d'un fichier PDF existant
     */
    public function appendPdfFile(string $filePath): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        $pageCount = $this->setSourceFile($filePath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $this->ImportPage($i);
            $size = $this->getTemplatesize($templateId);
            $this->AddPage('P', [$size['width'], $size['height']]);
            $this->useTemplate($templateId);
        }
        
        return (int)$pageCount;
    }
}

/**
 * Service orchestrateur pour la création de Songbooks.
 * Responsabilité : Logique métier, gestion des fichiers, base de données.
 */
class SongbookPdfService
{
    private string $chansonsDir;
    private string $songbooksDir;
    private string $logFile;
    
    public function __construct()
    {
        $this->chansonsDir = __DIR__ . "/../../data/chansons/";
        $this->songbooksDir = __DIR__ . "/../../data/songbooks/";
        $this->logFile = __DIR__ . "/../../../data/logs/songbook_gen.log";
    }

    private function log(string $message): void
    {
        $date = date("Y-m-d H:i:s");
        $formattedMessage = "[$date] $message\n";
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
        error_log("SongbookGen: $message"); // Doublon dans le log serveur classique
    }

    /**
     * Génère le fichier PDF final et met à jour la BDD
     * Retourne un tableau avec le statut et les détails
     */
    public function create(
        int $id,
        int $version,
        string $title,
        string $coverImage,
        array $songNames,
        array $fileNames,
        array $songIds,
        array $docVersions
    ): array {
        $this->log("--- DÉBUT GÉNÉRATION SONGBOOK #$id ($title) ---");
        $this->log("Nombre de chansons demandées : " . count($songNames));
        
        $pdf = new SongbookPdf();
        $dateStr = date("d/m/Y");
        $results = [
            'success' => false,
            'errors' => [],
            'warnings' => [],
            'skipped' => [],
            'file' => ''
        ];

        // 1. Couverture
        $this->log("Étape 1 : Traitement de la couverture...");
        $coverPath = $this->songbooksDir . $id . "/" . $coverImage;
        if (!empty($coverImage) && file_exists($coverPath)) {
            try {
                $this->log("Image de couverture trouvée : $coverImage");
                $coverPath = $this->ensurePdfCompatibleImage($coverPath);
                $pdf->addCover($coverPath, $title, $version + 1, $dateStr);
                $this->log("Couverture ajoutée au PDF.");
            } catch (Exception $e) {
                $this->log("ERREUR COUVERTURE : " . $e->getMessage());
                $results['warnings'][] = "Erreur image couverture : " . $e->getMessage();
                $pdf->AddPage();
            }
        } else {
            $this->log("Pas d'image de couverture (chemin : $coverPath)");
            $pdf->AddPage();
            $results['warnings'][] = "Pas d'image de couverture trouvée.";
        }

        // 2. Chansons
        $this->log("Étape 2 : Incorporation des chansons...");
        $currentPage = 2;
        $startPages = [];
        $validSongs = [];

        foreach ($fileNames as $index => $fileName) {
            $songId = $songIds[$index];
            $docVersion = $docVersions[$index];
            $songName = $songNames[$index];
            
            $fullFileName = composeNomVersion($fileName, $docVersion);
            $filePath = $this->chansonsDir . $songId . "/" . $fullFileName;

            $this->log("[$index] Traitement : $songName (Fichier : $fullFileName)");

            if (!file_exists($filePath)) {
                $this->log("  -> ❌ Fichier introuvable à l'adresse : $filePath");
                $results['skipped'][] = "$songName (Fichier introuvable)";
                continue;
            }

            try {
                $memBefore = round(memory_get_usage()/1024/1024, 2);
                $pagesAdded = $pdf->appendPdfFile($filePath);
                $memAfter = round(memory_get_usage()/1024/1024, 2);
                
                if ($pagesAdded > 0) {
                    $this->log("  -> ✅ Ajouté ($pagesAdded page(s)) - RAM: $memAfter Mo");
                    $startPages[] = $currentPage;
                    $validSongs[] = $songName;
                    $currentPage += $pagesAdded;
                } else {
                    $this->log("  -> ⚠️ PDF semble vide ou illisible.");
                    $results['skipped'][] = "$songName (PDF vide)";
                }
            } catch (Exception $e) {
                $this->log("  -> ❌ ERREUR : " . $e->getMessage());
                $results['skipped'][] = "$songName (Erreur : " . $e->getMessage() . ")";
            }
            
            if ($index % 20 == 0) gc_collect_cycles();
        }

        if (empty($validSongs)) {
            $this->log("ÉCHEC : Aucune chanson valide ajoutée.");
            $results['errors'][] = "Aucune chanson valide n'a pu être ajoutée.";
            return $results;
        }

        // 3. Sommaire
        $this->log("Étape 3 : Génération du sommaire...");
        try {
            $pdf->addTableOfContents($validSongs, $this->getLogoPath(), $startPages);
            $this->log("Sommaire généré avec succès.");
        } catch (Exception $e) {
            $this->log("ERREUR SOMMAIRE : " . $e->getMessage());
            $results['warnings'][] = "Erreur sommaire : " . $e->getMessage();
        }

        // 4. Sortie et Enregistrement
        $this->log("Étape 4 : Finalisation du fichier final...");
        $safeTitle = $this->slugify($title);
        $tempFileName = "songbook_" . $safeTitle . ".pdf";
        $finalPathDir = $this->songbooksDir . $id . "/";
        
        try {
            $this->log("Écriture du fichier temporaire : $tempFileName");
            $pdf->Output($finalPathDir . $tempFileName, 'F');
            $finalName = $this->finalizeDocument($id, $tempFileName, $finalPathDir);
            $results['success'] = true;
            $results['file'] = $finalName;
            $this->log("--- SUCCÈS : $finalName généré avec succès ---");
        } catch (Exception $e) {
            $this->log("ERREUR FINALE : " . $e->getMessage());
            $results['errors'][] = "Erreur enregistrement : " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Logique de finalisation : BDD + renommage avec version
     * Retourne le nom final du fichier
     */
    private function finalizeDocument(int $id, string $tempName, string $dir): string
    {
        $fileSize = filesize($dir . $tempName);
        $newVersion = creeModifieDocument($tempName, $fileSize, "songbook", $id);
        $finalName = composeNomVersion($tempName, $newVersion);
        
        if (file_exists($dir . $finalName)) {
            unlink($dir . $finalName);
        }
        rename($dir . $tempName, $dir . $finalName);
        
        return $finalName;
    }

    /**
     * Récupère le chemin du logo configuré
     */
    public function getLogoPath(): string
    {
        $logo = $_SESSION['logoSite'] ?? '';
        
        if (!$logo) {
            $fichierIni = __DIR__ . '/../../../data/conf/params.ini';
            if (file_exists($fichierIni)) {
                require_once __DIR__ . '/FichierIni.php';
                $ini = new FichierIni();
                $ini->m_load_fichier($fichierIni);
                $logo = $ini->m_valeur('logoSite', 'general');
            }
        }

        if ($logo) {
            $path = __DIR__ . '/../../images/navigation/' . $logo;
            if (file_exists($path)) {
                return $path;
            }
        }

        return __DIR__ . '/../../images/icones/top5.png';
    }

    /**
     * S'assure que l'image est compatible (convertit WebP en JPG si besoin)
     */
    private function ensurePdfCompatibleImage(string $path): string
    {
        if (str_ends_with(strtolower($path), '.webp')) {
            $jpgPath = str_replace('.webp', '-pdf.jpg', $path);
            if (!file_exists($jpgPath) || filemtime($path) > filemtime($jpgPath)) {
                require_once __DIR__ . "/Image.php";
                $img = Image::load($path);
                Image::save($img, $jpgPath, 'jpg', 90);
                imagedestroy($img);
            }
            return $jpgPath;
        }
        return $path;
    }

    /**
     * Nettoyage du nom de fichier (remplace make_alias)
     */
    private function slugify(string $text): string
    {
        return make_alias($text); // On garde la fonction existante pour la cohérence des noms
    }
}

// --- COUCHE DE COMPATIBILITÉ (Wrappers pour les anciens appels) ---

/**
 * Ancienne fonction conservée pour ne pas casser les appels existants (ex: Songbook.php)
 */
function pdfCreeSongbook($id, $version, $intitule, $image, $songs, $files, $ids, $versions): void
{
    // Augmentation des limites pour les gros songbooks
    ini_set('memory_limit', '512M');
    set_time_limit(300); // 5 minutes

    $service = new SongbookPdfService();
    $service->create($id, (int)$version, $intitule, $image, $songs, $files, $ids, $versions);
}

// Les fonctions utilitaires restent accessibles si besoin ailleurs
function make_alias($name) {
    $alias = mb_strtolower($name, 'UTF-8');
    $alias = mb_strtolower(trim($alias));
    $search = array('@[ÈÉÊËèéêë]@i', '@[ÀÁÂÃÄÅàáâãäå]@i', '@[ÌÍÎÏìíîï]@i', '@[ÙÚÛÜùúûü]@i', '@[ÒÓÔÕÖðòóôõö]@i', '@[çÇ]@i', '@[Ýýÿ]@i', '@[,;:!§/.?*°+\'\-]@i', '@[\s]@');
    $replace = array('e', 'a', 'i', 'u', 'o', 'c', 'y', '', '-');
    $alias = preg_replace($search, $replace, $alias);
    $search = array('.', ',', '?', ';', ':', '/', '!', '§', '%', 'ù', '*', 'µ', '¨', '^', '$', '£', 'ø', '=', '+', '}', ')', '°', ']', '@', '^', '\\', '|', '[', '{', '#', '~', '}', ']', '&', '²');
    $alias = str_replace($search, '', $alias);
    return $alias;
}
