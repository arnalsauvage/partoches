<?php
/**
 * Classe de rendu pour le formulaire de chanson (Django Style)
 * Regroupe tous les composants HTML de l'interface d'édition.
 */

class ChansonFormRenderer
{
    // --- CONFIGURATION PAR DÉFAUT (Pour éviter les warnings sur constantes manquantes) ---
    private const CHANSON_POST = "chanson_post.php";
    private const RETOUR_RACINE = "../";

    /**
     * Génère le formulaire principal des informations de la chanson
     */
    public static function renderForm(Chanson $_chanson, string $mode): string
    {
        $id = $_chanson->getId();
        $nom = htmlspecialchars($_chanson->getNom(), ENT_QUOTES);
        $interprete = htmlspecialchars($_chanson->getInterprete(), ENT_QUOTES);
        $annee = $_chanson->getAnnee();
        $tempo = $_chanson->getTempo();
        $mesure = $_chanson->getMesure();
        $tonalite = $_chanson->getTonalite();
        $datePub = dateMysqlVersTexte($_chanson->getDatePub());
        $hits = $_chanson->getHits();
        $idUser = $_chanson->getIdUser();
        
        $selBinaire = ($_chanson->getPulsation() == "binaire") ? "selected" : "";
        $selTernaire = ($_chanson->getPulsation() == "ternaire") ? "selected" : "";
        $checkedPub = ($_chanson->getPublication() == 1) ? "checked" : "";
        
        $selectUser = Utilisateur::selectUtilisateur("nom", "%", "login", true, $idUser);
        $privilege = $_SESSION['privilege'] ?? 0;
        $disabledHits = ($privilege < ($GLOBALS["PRIVILEGE_ADMIN"] ?? 3)) ? "disabled='disabled'" : "";
        
        $actionPost = self::CHANSON_POST;

        return <<<HTML
        <form method="POST" action="$actionPost" name="Form" class="form-horizontal">
            <input type="hidden" name="id" value="$id">
            <input type="hidden" name="mode" value="$mode">
            
            <div class="form-group">
                <label class="col-sm-3 control-label">Nom :</label>
                <div class="col-sm-8"><input class="form-control" type="text" name="fnom" value="$nom" required></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Interprète :</label>
                <div class="col-sm-8"><input class="form-control" type="text" name="finterprete" value="$interprete"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Année :</label>
                <div class="col-sm-3"><input class="form-control" type="number" name="fannee" value="$annee"></div>
                <label class="col-sm-2 control-label">Tonalité :</label>
                <div class="col-sm-3"><input class="form-control" type="text" name="ftonalite" value="$tonalite"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Tempo :</label>
                <div class="col-sm-6">
                    <input type="range" min="30" max="250" step="1" oninput="document.querySelector('#tempo-val').value = value" name="ftempo" value="$tempo">
                </div>
                <div class="col-sm-2">
                    <output id="tempo-val" style="font-weight:bold; font-size:1.2em;">$tempo</output>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Mesure :</label>
                <div class="col-sm-3"><input class="form-control" type="text" name="fmesure" value="$mesure"></div>
                <label class="col-sm-2 control-label">Pulsation :</label>
                <div class="col-sm-3">
                    <select class="form-control" name="fpulsation">
                        <option value="binaire" $selBinaire>binaire</option>
                        <option value="ternaire" $selTernaire>ternaire</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Publication :</label>
                <div class="col-sm-1"><input type="checkbox" name="fpublication" value="1" $checkedPub></div>
                <div class="col-sm-7 text-left"><span class="text-muted small">(Visible par tous si coché)</span></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Date pub. :</label>
                <div class="col-sm-3"><input class="form-control" type="text" name="fdate" value="$datePub"></div>
                <label class="col-sm-2 control-label">Hits :</label>
                <div class="col-sm-3"><input class="form-control" type="number" name="fhits" value="$hits" $disabledHits></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Propriétaire :</label>
                <div class="col-sm-8">$selectUser</div>
            </div>
            <div class="form-group" style="margin-top: 30px;">
                <div class="col-sm-offset-3 col-sm-8">
                    <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="glyphicon glyphicon-save"></i> VALIDER LES MODIFICATIONS</button>
                </div>
            </div>
        </form>
HTML;
    }

    /**
     * Génère les liens de recherche externe
     */
    public static function renderExternalLinks(Chanson $_chanson): string
    {
        if (!$_chanson->getNom()) return "";
        
        $nom = urlencode($_chanson->getNom());
        $artiste = urlencode($_chanson->getInterprete());
        $query = urlencode($_chanson->getNom() . " " . $_chanson->getInterprete());

        return <<<HTML
        <div class="well" style="text-align: left; margin-top: 20px;">
            <strong><i class="glyphicon glyphicon-search"></i> Recherches externes :</strong><br>
            <ul class="list-inline" style="margin-top: 10px;">
                <li><a href="https://www.youtube.com/results?search_query=$query" target="_blank" class="btn btn-xs btn-default">YouTube</a></li>
                <li><a href="https://www.qwant.com/?q=discogs+$query&amp;t=images" target="_blank" class="btn btn-xs btn-default">Images</a></li>
                <li><a href="https://songbpm.com/searches/$artiste+$nom" target="_blank" class="btn btn-xs btn-default">Tempo (BPM)</a></li>
                <li><a href="https://fr.wikipedia.org/w/index.php?search=$query" target="_blank" class="btn btn-xs btn-default">Wikipedia</a></li>
            </ul>
        </div>
HTML;
    }

    /**
     * Génère la liste des documents attachés et les modales de gestion
     */
    public static function renderFiles(int $id, string $_dossier_chansons, string $iconePoubelle, string $cheminImages, array $listeSongbooks, Chanson $_chanson): string
    {
        $out = "<h2>Liste des documents</h2><ul class='list-group'>";
        $lignes = Document::chercheDocumentsTableId("chanson", $id);
        while ($ligneDoc = $lignes->fetch_row()) {
            $idDoc = $ligneDoc[0];
            $fichierCourt = Document::composeNomVersion($ligneDoc[1], $ligneDoc[4]);
            // Correction Arnal : On force un chemin relatif web pour l'affichage
            $fichierUrl = "../../data/chansons/$id/" . rawurlencode($fichierCourt);
            $ext = strtolower(pathinfo($ligneDoc[1], PATHINFO_EXTENSION));
            $poids = intval($ligneDoc[2] / 1024);
            
            $iconeSrc = "../../images/icones/$ext.png";
            if (!file_exists($iconeSrc)) $iconeSrc = "../../images/icones/fichier.png";
            $iconeHtml = image($iconeSrc, 32, 32, "icone");

            $nomAffiche = preg_replace('/-v[0-9]+(?=\.[a-z0-9]+$)/i', '', $fichierCourt);
            if ($nomAffiche === $fichierCourt) $nomAffiche = preg_replace('/-v[0-9]+$/', '', $fichierCourt);

            $out .= <<<HTML
            <li class="list-group-item" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="doc-info-container">
                    <a href="$fichierUrl" target="_blank">$iconeHtml</a>
                    <label class="doc" title="Cliquer pour renommer">$nomAffiche</label>
                    <i class="glyphicon glyphicon-pencil text-muted small" style="cursor:pointer;" onclick="activeRenommage($idDoc)"></i>
                    <div class="edit-doc-container" id="container-$idDoc">
                        <input class="edit-doc-input" id="input-$idDoc" value="$nomAffiche">
                        <i class="glyphicon glyphicon-ok edit-doc-btn-ok" onclick="valideRenommage($idDoc)"></i>
                        <i class="glyphicon glyphicon-remove edit-doc-btn-cancel" onclick="annuleRenommage($idDoc)"></i>
                    </div>
                    <small class="text-muted">($poids ko)</small>
                </div>
                <div class="doc-actions">
                    <button class="btn btn-xs btn-default" onclick="openModaleAjoutAuSongbook($idDoc)" title="Ajouter au songbook"><i class="glyphicon glyphicon-book"></i></button>
                    <button class="btn btn-xs btn-primary" onclick="openModaleNouvelleVersionDocument($idDoc, '$fichierCourt')" title="Nouvelle version"><i class="glyphicon glyphicon-upload"></i></button>
HTML;
            $privilege = $_SESSION['privilege'] ?? 0;
            if ($privilege > ($GLOBALS["PRIVILEGE_EDITEUR"] ?? 2)) {
                $out .= boutonSuppression(self::CHANSON_POST . "?id=$id&amp;idDoc=$idDoc&amp;mode=SUPPRDOC", $iconePoubelle, $cheminImages);
            }
            $out .= "</div></li>";
        }

        $optSongbooks = "";
        foreach ($listeSongbooks as $sb) {
            $optSongbooks .= "<option value='{$sb[0]}'>" . htmlentities($sb[1]) . "</option>";
        }

        $idChanson = $_chanson->getId();
        $urlRegen = self::CHANSON_POST . "?id=$idChanson&amp;mode=REGEN_THUMBS";
        $out .= <<<HTML
        </ul>
        <div style="margin-top: 15px; text-align: right;">
            <a href="$urlRegen" class="btn btn-xs btn-warning" title="Régénérer toutes les miniatures de cette chanson">
                <i class="glyphicon glyphicon-refresh"></i> RÉGÉNÉRER LES VIGNETTES
            </a>
        </div>
        <div class="well well-sm" style="margin-top: 20px;">
            <h4>Envoyer un nouveau fichier</h4>
            <form action="chanson_upload.php" method="post" enctype="multipart/form-data" class="form-inline">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
                <input type="hidden" name="id" value="$idChanson">
                <div class="form-group"><input type="file" name="fichierUploade" class="form-control"></div>
                <button type="submit" class="btn btn-success">Envoyer</button>
            </form>
        </div>

        <!-- MODALES DE GESTION -->
        <div id="myModalEnvoieNouvelleVersion" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModaleNouvelleVersionDocument()">&times;</span>
                <h3>Nouvelle version</h3>
                <p id="texteNomDocument">Chargement...</p>
                <form action="chanson_upload.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="$id">
                    <input type="hidden" id="oldFile" name="oldFile" value="">
                    <div class="form-group"><input type="file" name="fichierUploade" class="form-control"></div>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </form>
            </div>
        </div>

        <div id="myModalAjouterAuSongbook" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModaleAjouterAuSongbook()">&times;</span>
                <h3>Ajouter à un Songbook</h3>
                <input type="hidden" id="idDocumentEnvoiSongbook" value="">
                <div class="form-group">
                    <label>Choisir le recueil :</label>
                    <select class="form-control js-example-basic-single" name="idSongbook" style="width: 100%;">
                        <option value="">-- Sélectionner --</option>
                        $optSongbooks
                    </select>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button class="btn btn-default" onclick="closeModaleAjouterAuSongbook()">Annuler</button>
                    <button class="btn btn-success" onclick="envoieFichierDansSongbook()">Ajouter</button>
                </div>
            </div>
        </div>
HTML;
        return $out;
    }

    /**
     * Génère la corbeille des fichiers orphelins sur le disque
     */
    public static function renderTrash(int $id, string $_dossier_chansons, string $iconePoubelle, string $cheminImages, Chanson $_chanson): string
    {
        $out = "<h2>Corbeille</h2>";
        $fichiersEnBdd = [];
        $res = Document::chercheDocumentsTableId("chanson", (string)$id);
        while ($f = $res->fetch_row()) $fichiersEnBdd[] = Document::composeNomVersion($f[1], $f[4]);

        $fichiersSurDisque = $_chanson->fichiersChanson($_dossier_chansons);
        $nbOrphelins = 0;

        $out .= "<div class='list-group'>";
        for ($i = 0; $i < count($fichiersSurDisque); $i += 3) {
            $nomFic = $fichiersSurDisque[$i+1];
            if (!in_array($nomFic, $fichiersEnBdd)) {
                $nbOrphelins++;
                // Correction Arnal : On utilise le chemin physique propre pour unlink, sans le RETOUR_RACINE superflu
                $urlSuppr = self::CHANSON_POST . "?nomFic=" . urlencode($_dossier_chansons . $id . "/" . $nomFic) . "&amp;mode=SUPPRFIC&amp;id=$id";
                $btnSuppr = boutonSuppression($urlSuppr, $iconePoubelle, $cheminImages);
                
                $out .= <<<HTML
                <div class="list-group-item" style="display:flex; justify-content:space-between; align-items:center; background:#fff5f5;">
                    <span><i class="glyphicon glyphicon-trash text-danger"></i> $nomFic (Orphelin)</span>
                    <div>
                        <button class="btn btn-xs btn-warning" onclick="restaureDocument($id, '$nomFic')">Restaurer</button>
                        $btnSuppr
                    </div>
                </div>
HTML;
            }
        }
        if ($nbOrphelins == 0) $out .= "<p class='text-muted'>La corbeille est vide.</p>";
        $out .= "</div>";
        return $out;
    }

    /**
     * Génère la liste des rythmiques (Strums)
     */
    public static function renderStrums(Chanson $_chanson): string
    {
        $id = $_chanson->getId();
        $liens = LienStrumChanson::chercheLiensStrumChanson("idChanson", $id);
        
        $out = "<div class='strum-list-container'><div class='list-group'>";
        $nb = 0;
        while ($l = $liens->fetch_assoc()) {
            $nb++;
            $idStrum = (int)($l['idStrum'] ?? 0);
            $s = new Strum($idStrum);
            
            // Si le strum n'est pas trouvé par son ID, on tente par sa chaîne
            if ($s->getId() == 0 && !empty($l['strum'])) {
                $s->chercheStrumParChaine($l['strum']);
            }
            
            // Si toujours rien, on crée un strum "fantôme" pour afficher au moins le motif stocké dans le lien
            if ($s->getId() == 0 && !empty($l['strum'])) {
                $s->setStrum($l['strum']);
                $s->setDescription("Rythmique personnalisée");
            }
            
            $motif = str_replace(" ", "-", $s->getStrum());
            $badgeSwing = $s->getSwing() ? "<span class='label label-warning'>SWING</span>" : "";
            $idLien = $l['id'];
            $actionDel = "../liens/lienStrumChanson_post.php?id=$idLien&amp;mode=DEL&amp;idChanson=$id";

            $out .= <<<HTML
            <div class="list-group-item" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <code style="font-size: 1.2em; color: #8B4513;">$motif</code> $badgeSwing<br>
                    <small class="text-muted">{$s->getLongueur()} {$s->renvoieUniteEnFrancais()} - {$s->getDescription()}</small>
                </div>
                <a href="$actionDel" class="btn btn-xs btn-danger"><i class="glyphicon glyphicon-trash"></i></a>
            </div>
HTML;
        }
        if ($nb == 0) $out .= "<div class='alert alert-info'>Aucune rythmique associée.</div>";
        $out .= "</div>";

        $listeStrums = Strum::chargeStrumsBdd();
        $optStrums = "";
        foreach ($listeStrums as $st) {
            $motif = str_replace(" ", "-", $st->getStrum());
            $optStrums .= "<option value='{$st->getId()}'>$motif ({$st->getDescription()})</option>";
        }

        $actionPostStrum = "../liens/lienStrumChanson_post.php";

        $out .= <<<HTML
        <div class="strum-add-box">
            <form action="$actionPostStrum" method="post" class="form-inline">
                <input type="hidden" name="idChanson" value="$id">
                <input type="hidden" name="mode" value="NEW">
                <label>Ajouter :</label>
                <select name="idStrum" class="form-control js-example-basic-single" style="width:250px;">$optStrums</select>
                <button class="btn btn-success"><i class="glyphicon glyphicon-plus"></i></button>
            </form>
        </div></div>
HTML;
        return $out;
    }

    /**
     * Génère la liste des liens externes
     */
    public static function renderLinks(int $id): string
    {
        $selectUser0 = Utilisateur::selectUtilisateur("nom", "%", "login", true, 0, "utilisateur", "idUser0");

        $out = <<<HTML
        <div class="well well-sm" style="text-align:left;">
            <h4>Nouveau lien</h4>
            <div class="row">
                <div class="col-sm-4"><input class="form-control" id="lienType0" placeholder="Type (vidéo, article...)"></div>
                <div class="col-sm-8"><input class="form-control" id="lienUrl0" placeholder="URL (https://...)"></div>
            </div>
            <div class="row" style="margin-top:10px;">
                <div class="col-sm-12"><textarea class="form-control" id="lienDescription0" placeholder="Description courte"></textarea></div>
            </div>
            <div class="row" style="margin-top:10px;">
                <div class="col-sm-3"><input class="form-control" id="date0" placeholder="JJ/MM/AAAA"></div>
                <div class="col-sm-4">$selectUser0</div>
                <div class="col-sm-2"><input class="form-control" type="number" id="hits0" value="0"></div>
                <div class="col-sm-3"><button class="btn btn-primary btn-block" onclick="updateLienurl('NEW',0,'chanson', $id)">CRÉER LE LIEN</button></div>
            </div>
        </div>
HTML;

        $lignes = LienUrl::chercheLiensUrlsTableId("chanson", $id);
        while ($l = $lignes->fetch_row()) {
            $idL = $l[0];
            $url = htmlentities($l[3]);
            $type = htmlentities($l[4]);
            $desc = htmlentities($l[5]);
            $date = dateMysqlVersTexte($l[6]);
            $idU = $l[7];
            $hits = $l[8];
            $selectUser = Utilisateur::selectUtilisateur("nom", "%", "login", true, $idU, "utilisateur", "idUser$idL");

            $out .= <<<HTML
            <div class="lien-item text-left" id="divlienUrl$idL">
                <div class="row">
                    <div class="col-sm-3"><label>Type :</label><input class="form-control" id="lienType$idL" value="$type"></div>
                    <div class="col-sm-9"><label>URL :</label><input class="form-control" id="lienUrl$idL" value="$url"></div>
                </div>
                <label>Description :</label><textarea class="form-control" id="lienDescription$idL">$desc</textarea>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-3"><label>Date :</label><input class="form-control" id="date$idL" value="$date"></div>
                    <div class="col-sm-4"><label>Par :</label>$selectUser</div>
                    <div class="col-sm-2"><label>Hits :</label><input class="form-control text-center" type="number" id="hits$idL" value="$hits" style="font-weight:bold;"></div>
                    <div class="col-sm-3" style="padding-top:25px; text-align:right;">
                        <button class="btn btn-sm btn-success" onclick="updateLienurl('UPDATE',$idL,'chanson', $id)">MODIFIER</button>
                        <button class="btn btn-sm btn-danger" onclick="updateLienurl('DEL',$idL)">SUPPRIMER</button>
                    </div>
                </div>

            </div>
HTML;
        }
        return $out;
    }
}
