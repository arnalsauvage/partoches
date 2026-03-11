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
        $this->Cell(0, 12, utf8_decode($title) . " - v" . $version . " du " . $date, 0, 0, "C");
    }

    /**
     * Ajoute le sommaire au document
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

        // Calcul de l'espacement
        $lineHeight = (!empty($songs)) ? 240 / (count($songs) + 1) : 10;
        if ($lineHeight > 40) {
            $lineHeight = 40;
        }

        $this->SetFont(self::FONT_ARIAL, 'B', $lineHeight);
        $this->Cell(10, $lineHeight / 2, " ", 0, 1, "L");

        foreach ($songs as $index => $songName) {
            $pageNumber = $pageNumbers[$index] ?? '?';
            $this->Cell(10, $lineHeight, $pageNumber . " - " . utf8_decode($songName), 0, 1, "L");
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
    
    public function __construct()
    {
        // On pourrait injecter ces chemins via un container
        $this->chansonsDir = __DIR__ . "/../../data/chansons/";
        $this->songbooksDir = __DIR__ . "/../../data/songbooks/";
    }

    /**
     * Génère le fichier PDF final et met à jour la BDD
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
    ): void {
        $pdf = new SongbookPdf();
        $dateStr = date("d/m/Y");

        // 1. Couverture
        $coverPath = $this->songbooksDir . $id . "/" . $coverImage;
        $coverPath = $this->ensurePdfCompatibleImage($coverPath);
        $pdf->addCover($coverPath, $title, $version + 1, $dateStr);

        // 2. Chansons
        $currentPage = 2;
        $startPages = [];
        $validSongs = [];

        foreach ($fileNames as $index => $fileName) {
            $songId = $songIds[$index];
            $docVersion = $docVersions[$index];
            $songName = $songNames[$index];
            
            $fullFileName = composeNomVersion($fileName, $docVersion);
            $filePath = $this->chansonsDir . $songId . "/" . $fullFileName;

            try {
                $pagesAdded = $pdf->appendPdfFile($filePath);
                if ($pagesAdded > 0) {
                    $startPages[] = $currentPage;
                    $validSongs[] = $songName;
                    $currentPage += $pagesAdded;
                }
            } catch (Exception $e) {
                error_log("Erreur import PDF ($filePath) : " . $e->getMessage());
            }
        }

        // 3. Sommaire
        $pdf->addTableOfContents($validSongs, $this->getLogoPath(), $startPages);

        // 4. Sortie et Enregistrement
        $safeTitle = $this->slugify($title);
        $tempFileName = "songbook_" . $safeTitle . ".pdf";
        $finalPathDir = $this->songbooksDir . $id . "/";
        
        $pdf->Output($finalPathDir . $tempFileName, 'F');
        
        // Gestion BDD et renommage (logique existante)
        $this->finalizeDocument($id, $tempFileName, $finalPathDir);
    }

    /**
     * Logique de finalisation : BDD + renommage avec version
     */
    private function finalizeDocument(int $id, string $tempName, string $dir): void
    {
        $fileSize = filesize($dir . $tempName);
        $newVersion = creeModifieDocument($tempName, $fileSize, "songbook", $id);
        $finalName = composeNomVersion($tempName, $newVersion);
        
        if (file_exists($dir . $finalName)) {
            unlink($dir . $finalName);
        }
        rename($dir . $tempName, $dir . $finalName);
        
        echo "Fichier <a href='../../data/songbooks/$id/$finalName' target='_blank'>$finalName</a> généré avec succès.";
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
    $service = new SongbookPdfService();
    $service->create($id, (int)$version, $intitule, $image, $songs, $files, $ids, $versions);
}

// Les fonctions utilitaires restent accessibles si besoin ailleurs
function make_alias($name) {
    $alias = mb_strtolower($name, 'UTF-8');
    $alias = mb_strtolower(trim($alias));
    $search = array(utf8_decode('@[ÈÉÊËèéêë]@i'), utf8_decode('@[ÀÁÂÃÄÅàáâãäå]@i'), utf8_decode('@[ÌÍÎÏìíîï]@i'), utf8_decode('@[ÙÚÛÜùúûü]@i'), utf8_decode('@[ÒÓÔÕÖðòóôõö]@i'), utf8_decode('@[çÇ]@i'), utf8_decode('@[Ýýÿ]@i'), utf8_decode('@[,;:!§/.?*°+\'\-]@i'), utf8_decode('@[\s]@'));
    $replace = array('e', 'a', 'i', 'u', 'o', 'c', 'y', '', '-');
    $alias = preg_replace($search, $replace, utf8_decode($alias));
    $search = array('.', ',', '?', ';', ':', '/', '!', '§', '%', 'ù', '*', 'µ', '¨', '^', '$', '£', 'ø', '=', '+', '}', ')', '°', ']', '@', '^', '\\', '|', '[', '{', '#', '~', '}', ']', '&', '²');
    $alias = str_replace($search, '', $alias);
    $search = array('@-{2,}@i');
    $alias = preg_replace($search, '-', $alias);
    $alias = utf8_encode($alias);
    return $alias;
}
