<?php
use PHPUnit\Framework\TestCase;

/**
 * Test de génération du PDF pour un Songbook spécifique.
 * Ce test nécessite une base de données fonctionnelle (Docker).
 */
class SongbookPdfTest extends TestCase
{
    protected function setUp(): void
    {
        // On s'assure que Songbook.php est chargé pour avoir accès à CreeSongBookPdf
        require_once __DIR__ . '/../src/public/php/songbook/Songbook.php';
        require_once __DIR__ . '/../src/public/php/lib/pdf.php';
        
        // On simule une session si nécessaire
        if (!isset($_SESSION['mysql'])) {
            require_once __DIR__ . '/../src/public/php/lib/configMysql.php';
            $_SESSION['mysql'] = $mysqli;
        }
    }

    /**
     * Test de génération du Songbook 121
     * @group slow
     */
    public function testGenereSongbook121(): void
    {
        $idSongbook = 121;
        
        // On vérifie d'abord si le songbook existe en base
        $db = $_SESSION['mysql'];
        $res = $db->query("SELECT id, nom FROM songbook WHERE id = $idSongbook");
        
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $nom = $row['nom'];
            
            echo "\n--- Test de génération du Songbook #$idSongbook ($nom) ---\n";
            
            // On capture la sortie pour éviter de polluer PHPUnit
            ob_start();
            try {
                CreeSongBookPdf($idSongbook);
            } finally {
                $output = ob_get_clean();
            }
            
            echo "Sortie : $output\n";
            
            // On vérifie que le fichier a bien été créé
            // Le nom du fichier dépend du slugify (make_alias) du titre
            $safeTitle = make_alias("songbook_" . $nom);
            
            // On cherche le document en base pour avoir sa version exacte
            $nomGenere = $safeTitle . ".pdf";
            $doc = chercheDocumentNomTableId($nomGenere, "songbook", $idSongbook);
            
            $this->assertNotEmpty($doc, "Le document n'a pas été trouvé en base de données après génération.");
            
            $version = $doc[4];
            $finalFileName = composeNomVersion($nomGenere, $version);
            $filePath = __DIR__ . "/../src/public/data/songbooks/$idSongbook/$finalFileName";
            
            $this->assertFileExists($filePath, "Le fichier PDF n'a pas été créé physiquement sur le disque : $filePath");
            $this->assertGreaterThan(1000, filesize($filePath), "Le fichier PDF semble trop petit (corrompu ?) : $filePath");
            
            echo "✅ Succès : Songbook généré avec succès ($finalFileName).\n";
        } else {
            $this->markTestSkipped("Le songbook #$idSongbook n'existe pas dans la base de données de test.");
        }
    }
}
