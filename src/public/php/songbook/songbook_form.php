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
<div class="container" id="django-config-page" style="padding-top: 20px;">
    <div class="header-django">
        <h1><i class="glyphicon glyphicon-book"></i> $pageTitle</h1>
        <div class="actions">
HTML;

if ($mode == "MAJ") {
    $html .= "            <a href='songbook_voir.php?id=$id' class='btn-dj btn-dj-info'><i class='glyphicon glyphicon-eye-open'></i> Voir public</a>";
}

$html .= <<<HTML
            <a href="songbook_liste.php" class="btn-dj btn-dj-default"><i class="glyphicon glyphicon-list"></i> Retour liste</a>
        </div>
    </div>

    <div class="content-django" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 25px;">
        <form action="songbook_get.php" method="POST" class="form-dj-reset">
            <input type="hidden" name="id" id="idSongbook" value="$id">
            <input type="hidden" name="mode" value="$mode">
            <input type="hidden" name="fimage" value="$image">
            <input type="hidden" name="fhits" value="$hits">
            
            <div class="form-group-django">
                <label for="fnom" class="label-django">Titre du recueil :</label>
                <input type="text" id="fnom" name="fnom" class="input-django" value="$nom" required placeholder="Nom du songbook">
            </div>
            
            <div class="form-group-django">
                <label for="fdescription" class="label-django">Description :</label>
                <textarea id="fdescription" name="fdescription" class="input-django" rows="3" placeholder="Petit texte de présentation...">$desc</textarea>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group-django">
                        <label for="ftype" class="label-django">Genre / Type :</label>
                        <select id="ftype" name="ftype" class="input-django">
                            <option value="1" $opt1>Anthologie</option>
                            <option value="2" $opt2>Concert</option>
                            <option value="3" $opt3>Thématique</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group-django">
                        <label for="fdate" class="label-django">Date :</label>
                        <input type="text" id="fdate" name="fdate" class="input-django" value="$date" placeholder="JJ/MM/AAAA">
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-dj btn-dj-primary btn-block" style="font-weight: bold; font-size: 1.1em;">
                    <i class="glyphicon glyphicon-save" aria-hidden="true"></i> ENREGISTRER LES INFORMATIONS
                </button>
            </div>
        </form>

        <hr style="margin: 30px 0; border-top: 1px solid #eee;">
HTML;

if ($mode == "MAJ") {
    // --- DOCUMENTS RATTACHÉS ---
    $html .= <<<HTML
    <section>
        <h3 class="sb-section-title"><i class="glyphicon glyphicon-paperclip" aria-hidden="true"></i> Documents du recueil</h3>
        <p class="text-muted small">Fichiers propres au recueil (Couverture, Index, PDF complet...)</p>
        <div class="list-group" style="margin-bottom: 20px;">
HTML;
    
    $docsRecueil = Document::chercheDocumentsTableId("songbook", $id);
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

    <!-- Modal pour le rapport de génération -->
    <div class="modal fade" id="modalPdfReport" tabindex="-1" role="dialog" aria-labelledby="modalPdfReportLabel">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalPdfReportLabel">Rapport de Génération PDF</h4>
          </div>
          <div class="modal-body" id="pdf-report-body">
            <!-- Le contenu sera injecté par JS -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
          </div>
        </div>
      </div>
    </div>
HTML;
}

$html .= "</div></div>";

// JavaScript Spécifique
$html .= '<script src="../../js/songbookform.js?v=' . time() . '"></script>';

echo $html;
echo envoieFooter();
