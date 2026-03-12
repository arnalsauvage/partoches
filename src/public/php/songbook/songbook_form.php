<?php
require_once dirname(__DIR__) . "/lib/utilssi.php";
$headHtml = envoieHead("Songbook - Formulaire", "../../css/index.css");
echo $headHtml;
$pasDeMenu = true;
require_once PHP_DIR . "/navigation/menu.php";
echo $MENU_HTML;
require_once PHP_DIR . "/songbook/Songbook.php";
require_once PHP_DIR . "/document/Document.php";
require_once PHP_DIR . "/liens/lienDocSongbook.php";

/**
 * Fonctions Helpers de rendu (Clean Code / DRY)
 */

function renderDocumentRow($doc, $idSongbook): string {
    $idDoc = (int)$doc[0];
    $taille = (int)$doc[2];
    $fichierCourt = composeNomVersion($doc[1], $doc[4]);
    $url = "../../data/songbooks/$idSongbook/" . urlencode($fichierCourt);
    $ext = strtolower(pathinfo($doc[1], PATHINFO_EXTENSION));
    $iconePath = "../../images/icones/$ext.png";
    $icone = file_exists($iconePath) ? $iconePath : "../../images/icones/fichier.png";
    $poids = intval($taille/1024);
    
    return <<<HTML
        <div class="list-group-item sb-list-item">
            <div>
                <img src="$icone" width="24" alt="Icône $ext" style="margin-right: 10px;">
                <a href="$url" target="_blank" rel="noopener"><strong>$fichierCourt</strong></a>
                <span class="text-muted small">($poids ko)</span>
            </div>
            <a href="songbook_get.php?id=$idSongbook&amp;idDoc=$idDoc&amp;nomFic=$fichierCourt&amp;mode=SUPPRFIC"
               class="btn btn-xs btn-danger"
               title="Supprimer ce fichier"
               aria-label="Supprimer le fichier $fichierCourt"
               onclick="return confirm('Supprimer ce fichier ?')">
                <i class="glyphicon glyphicon-trash" aria-hidden="true"></i>
            </a>
        </div>
HTML;
}

function renderSommaireRow($docLien, $idSongbook, $index): string {
    $idDoc = (int)$docLien[0];
    $nomFic = composeNomVersion($docLien[1], $docLien[4]);
    return <<<HTML
        <li class="ui-state-default sb-sortable-item" data-index="$idDoc" data-position="$index">
            <span>
                <i class="glyphicon glyphicon-menu-hamburger text-muted" style="margin-right: 15px;" aria-hidden="true"></i>
                <strong>$index.</strong> $nomFic
            </span>
            <a href="songbook_get.php?id=$idSongbook&amp;idDoc=$idDoc&amp;mode=SUPPRDOC"
               class="btn btn-link btn-xs text-danger"
               title="Retirer du recueil"
               aria-label="Retirer $nomFic du recueil"
               style="margin-left: auto;">
                <i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
            </a>
        </li>
HTML;
}

// 1. SÉCURITÉ ET DROITS
$privilege = $_SESSION['privilege'] ?? 0;
$lvlEditeur = $GLOBALS["PRIVILEGE_EDITEUR"] ?? 2;

if ($privilege < $lvlEditeur) {
    $idRedirect = (int)($_GET['id'] ?? 0);
    $url = "songbook_voir.php" . ($idRedirect ? "?id=$idRedirect" : "");
    header("Location: $url");
    exit();
}

require_once PHP_DIR . "/navigation/menu.php";

// 2. TRAITEMENT DES ACTIONS (POST)
$id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));

if (isset($_POST['documentJoint']) && $id > 0) {
    LienDocSongbook::ordonneLiensSongbook($id);
    LienDocSongbook::creeLienDocSongbook($_POST['documentJoint'], $id);
    if (($_POST['ajax'] ?? 0) == 11) {
        echo "succes";
        exit();
    }
}

// 3. CHARGEMENT DES DONNÉES
$sb = new Songbook($id);
$mode = ($sb->getId() > 0) ? "MAJ" : "INS";
$liens = null;

if ($mode == "MAJ") {
    ordonneLiensSongbook($id);
    $liens = chercheLiensDocSongbook('idSongbook', $id, "ordre");
}

// Préparation des variables
$pageTitle = ($mode === 'MAJ') ? 'Modifier le recueil' : 'Créer un nouveau recueil';
$nomSongbook = htmlspecialchars($sb->getNom());
$nom = $nomSongbook;
$desc = htmlspecialchars($sb->getDescription());
$date = dateMysqlVersTexte($sb->getDate());
$type = $sb->getType();
$image = htmlspecialchars($sb->getImage());
$hits = $sb->getHits();

$opt1 = ($type == 1) ? 'selected' : '';
$opt2 = ($type == 2) ? 'selected' : '';
$opt3 = ($type == 3) ? 'selected' : '';

// --- RENDU HTML ---

$html = <<<HTML
<link rel="stylesheet" href="../../css/songbookform.css">

<div class="container sb-form-container">
    <div class="row">
        <div class="col-xs-12">
            <h1 class="sb-header-title">
                <i class="glyphicon glyphicon-edit" aria-hidden="true"></i>
                $pageTitle
            </h1>
HTML;

if ($mode == "MAJ") {
    $html .= "<p class='text-muted'>Vous modifiez : <strong>$nomSongbook</strong> &bull; <a href='songbook_voir.php?id=$id' class='btn btn-xs btn-default'>Voir le rendu public</a></p>";
}

$html .= <<<HTML
            <section class="well sb-well-custom">
                <form action="songbook_get.php" method="POST" class="form-horizontal">
                    <input type="hidden" name="id" id="idSongbook" value="$id">
                    <input type="hidden" name="mode" value="$mode">
                    <input type="hidden" name="fimage" value="$image">
                    <input type="hidden" name="fhits" value="$hits">
                    
                    <div class="form-group">
                        <label for="fnom" class="col-sm-2 control-label">Titre :</label>
                        <div class="col-sm-10">
                            <input type="text" id="fnom" name="fnom" class="form-control" value="$nom" required placeholder="Nom du songbook">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fdescription" class="col-sm-2 control-label">Description :</label>
                        <div class="col-sm-10">
                            <textarea id="fdescription" name="fdescription" class="form-control" rows="3" placeholder="Petit texte de présentation...">$desc</textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="ftype" class="col-sm-4 control-label">Genre :</label>
                                <div class="col-sm-8">
                                    <select id="ftype" name="ftype" class="form-control">
                                        <option value="1" $opt1>Anthologie</option>
                                        <option value="2" $opt2>Concert</option>
                                        <option value="3" $opt3>Thématique</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="fdate" class="col-sm-4 control-label">Date :</label>
                                <div class="col-sm-8">
                                    <input type="text" id="fdate" name="fdate" class="form-control" value="$date" placeholder="JJ/MM/AAAA">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary sb-btn-save">
                                <i class="glyphicon glyphicon-save" aria-hidden="true"></i> ENREGISTRER LES INFORMATIONS
                            </button>
                        </div>
                    </div>
                </form>
            </section>
HTML;

if ($mode == "MAJ") {
    // --- DOCUMENTS RATTACHÉS ---
    $html .= <<<HTML
    <section>
        <h3 class="sb-section-title"><i class="glyphicon glyphicon-paperclip" aria-hidden="true"></i> Documents du recueil</h3>
        <p class="text-muted small">Fichiers propres au recueil (Couverture, Index, PDF complet...)</p>
        <div class="list-group" style="margin-bottom: 20px;">
HTML;
    
    $docsRecueil = chercheDocumentsTableId("songbook", $id);
    while ($doc = $docsRecueil->fetch_row()) {
        $html .= renderDocumentRow($doc, $id);
    }
    $html .= "</div>";

    // UPLOAD
    $html .= <<<HTML
        <div class="well well-sm">
            <form action="songbook_upload.php" method="POST" enctype="multipart/form-data" class="form-inline">
                <input type="hidden" name="id" value="$id">
                <div class="form-group">
                    <label for="fichierUploade">Ajouter un fichier :</label>
                    <input type="file" id="fichierUploade" name="fichierUploade" class="form-control" style="display: inline-block;">
                </div>
                <button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-upload" aria-hidden="true"></i> Envoyer</button>
            </form>
        </div>
    </section>
HTML;

    // --- SOMMAIRE ---
    $html .= <<<HTML
    <section>
        <h3 class="sb-section-title"><i class="glyphicon glyphicon-list" aria-hidden="true"></i> Sommaire des morceaux</h3>
        <p class="text-muted small">Faites glisser les titres pour réorganiser l'ordre dans le recueil.</p>
        <ul id="sortable" class="list-unstyled">
HTML;
    
    $liens = chercheLiensDocSongbook('idSongbook', $id, "ordre");
    $n = 0;
    while ($lien = $liens->fetch_row()) {
        $n++;
        $docLien = chercheDocument($lien[1]);
        if ($docLien) {
            $html .= renderSommaireRow($docLien, $id, $n);
        }
    }
    $html .= <<<HTML
        </ul>
    </section>
HTML;

    // LIAISON AJAX
    $html .= <<<HTML
    <section class="sb-add-box">
        <h4 class="sb-add-title"><i class="glyphicon glyphicon-plus-sign" aria-hidden="true"></i> Ajouter un morceau au recueil</h4>
        <p class="text-muted small">Cherchez une chanson par son titre (min. 4 car.), puis choisissez le document PDF à inclure.</p>
        
        <div class="form-group" style="position: relative;">
            <div class="input-group">
                <span class="input-group-addon" id="addon-search"><i class="glyphicon glyphicon-search" aria-hidden="true"></i></span>
                <input type="text" id="rechercheChansonSB" class="form-control input-lg" placeholder="Nom de la chanson..." autocomplete="off" aria-describedby="addon-search">
            </div>
            <div id="resultsChansonSB" class="sb-results-dropdown" role="listbox"></div>
        </div>

        <div id="selectionPdfSB" class="sb-pdf-selection">
            <h5 style="font-weight: bold; margin-top: 0; color: #2b1d1a;">Documents PDF trouvés :</h5>
            <div id="listePdfsSB" class="list-group" style="margin-bottom: 0;"></div>
        </div>

        <form action="songbook_form.php" method="POST" id="formFinalAjout" style="display: none;">
            <input type="hidden" name="id" value="$id">
            <input type="hidden" name="documentJoint" id="inputDocFinal">
        </form>
    </section>
HTML;

    // GÉNÉRATION FINALE
    $html .= <<<HTML
    <footer class="sb-footer-actions">
        <button onclick='genereUnPdf($id)' class="btn btn-lg btn-success sb-btn-generate">
            <i class="glyphicon glyphicon-refresh" aria-hidden="true"></i> RÉGÉNÉRER LE PDF COMPLET
        </button>
    </footer>
HTML;
}

$html .= "</div></div></div>";

// JavaScript Spécifique
$html .= '<script src="../../js/songbookform.js"></script>';

echo $html;
echo envoieFooter();
