<?php
/**
 * Modale AJAX : Options de duplication d'une chanson
 * Permet à l'administrateur de choisir précisément les éléments à copier :
 * - Nom (titre) personnalisable
 * - Fichiers attachés (documents) : liste à cocher, décochés par défaut
 * - Rythmiques (strums) : liste à cocher, décochés par défaut
 * - Liens et vidéos (lienurl) : liste à cocher, décochés par défaut
 * Les informations générales sont conservées par défaut.
 */

require_once file_exists(dirname(__DIR__, 3) . '/autoload.php') ? dirname(__DIR__, 3) . '/autoload.php' : dirname(__DIR__, 2) . '/autoload.php';
require_once dirname(__DIR__) . '/lib/utilssi.php';
require_once dirname(__DIR__) . '/lib/configMysql.php';

if (!estAdmin()) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Accès réservé aux administrateurs.</div>';
    exit;
}

$idChanson = (int)($_GET['id'] ?? 0);
if ($idChanson <= 0) {
    http_response_code(400);
    echo '<div class="alert alert-warning">Identifiant de chanson invalide.</div>';
    exit;
}

require_once __DIR__ . '/Chanson.php';
$chanson = Chanson::load($idChanson);
if (!$chanson || $chanson->getId() === 0) {
    http_response_code(404);
    echo '<div class="alert alert-warning">Chanson introuvable.</div>';
    exit;
}

require_once dirname(__DIR__) . '/document/Document.php';
require_once dirname(__DIR__) . '/liens/LienStrumChanson.php';
require_once dirname(__DIR__) . '/liens/LienUrl.php';

// Récupération des documents attachés
$resDocs = Document::chercheDocumentsTableId('chanson', $idChanson);
$docs = [];
if ($resDocs) {
    while ($row = $resDocs->fetch_assoc()) {
        $docs[] = $row;
    }
}

// Récupération des rythmiques associées
$resStrums = LienStrumChanson::chercheLiensStrumChanson('idChanson', $idChanson);
$strums = [];
if ($resStrums) {
    while ($row = $resStrums->fetch_assoc()) {
        $strums[] = $row;
    }
}

// Récupération des liens et vidéos
$resLiens = LienUrl::chercheLiensUrlsTableId('chanson', $idChanson);
$liens = [];
if ($resLiens) {
    while ($row = $resLiens->fetch_assoc()) {
        $liens[] = $row;
    }
}

$nomChanson = htmlspecialchars($chanson->getNom(), ENT_QUOTES, 'UTF-8');
$interprete = htmlspecialchars($chanson->getInterprete(), ENT_QUOTES, 'UTF-8');
$annee = (int)$chanson->getAnnee();
$tempo = (int)$chanson->getTempo();
$mesure = htmlspecialchars($chanson->getMesure(), ENT_QUOTES, 'UTF-8');
$pulsation = htmlspecialchars($chanson->getPulsation(), ENT_QUOTES, 'UTF-8');
$tonalite = htmlspecialchars($chanson->getTonalite(), ENT_QUOTES, 'UTF-8');

?>
<form id="formDupliquerChanson" action="chanson_post.php" method="POST">
    <input type="hidden" name="mode" value="DUP">
    <input type="hidden" name="id" value="<?= $idChanson ?>">

    <div class="alert alert-info">
        <i class="glyphicon glyphicon-info-sign"></i> Choisissez les éléments à copier vers la nouvelle chanson. Les attributs généraux sont conservés.
    </div>

    <!-- TITRE DE LA NOUVELLE CHANSON -->
    <div class="form-group">
        <label for="dup_nom">Titre de la nouvelle chanson :</label>
        <input type="text" class="form-control" id="dup_nom" name="nom" value="<?= $nomChanson ?>" required autocomplete="off">
        <span class="help-block">Vous pouvez modifier le titre maintenant ou le conserver tel quel.</span>
    </div>

    <!-- RAPPEL DES INFORMATIONS GÉNÉRALES -->
    <div class="well well-sm">
        <strong>Informations générales conservées :</strong><br>
        <span>Artiste : <em><?= $interprete ?: 'Non renseigné' ?></em></span> |
        <span>Année : <?= $annee ?></span> |
        <span>Tempo : <?= $tempo ?> BPM</span> |
        <span>Mesure : <?= $mesure ?></span> |
        <span>Pulsation : <?= $pulsation ?></span> |
        <span>Tonalité : <?= $tonalite ?></span>
    </div>

    <!-- FICHIERS ATTACHÉS (DOCUMENTS) -->
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <span class="panel-title pull-left">
                <i class="glyphicon glyphicon-file"></i> Fichiers attachés (<?= count($docs) ?>)
            </span>
            <?php if (!empty($docs)): ?>
            <div class="btn-group btn-group-xs pull-right dupliquer-btn-group-toggle">
                <button type="button" class="btn btn-default" onclick="cocherTous('chk-doc', true)">Tout cocher</button>
                <button type="button" class="btn btn-default" onclick="cocherTous('chk-doc', false)">Tout décocher</button>
            </div>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <?php if (empty($docs)): ?>
                <p class="text-muted">Aucun fichier attaché à cette chanson.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($docs as $d): ?>
                        <?php
                            $docId = (int)$d['id'];
                            $docNom = htmlspecialchars($d['nom'], ENT_QUOTES, 'UTF-8');
                            $docTaille = (int)$d['tailleKo'];
                            $docExt = strtolower(pathinfo($d['nom'], PATHINFO_EXTENSION));
                        ?>
                        <label class="list-group-item dupliquer-item-label" for="doc_<?= $docId ?>">
                            <input type="checkbox" class="chk-doc" id="doc_<?= $docId ?>" name="doc_ids[]" value="<?= $docId ?>">
                            <span class="label label-primary"><?= strtoupper($docExt) ?></span>
                            <span><?= $docNom ?></span>
                            <span class="badge"><?= $docTaille ?> Ko</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RYTHMIQUES (STRUMS) -->
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <span class="panel-title pull-left">
                <i class="glyphicon glyphicon-music"></i> Rythmiques (<?= count($strums) ?>)
            </span>
            <?php if (!empty($strums)): ?>
            <div class="btn-group btn-group-xs pull-right dupliquer-btn-group-toggle">
                <button type="button" class="btn btn-default" onclick="cocherTous('chk-strum', true)">Tout cocher</button>
                <button type="button" class="btn btn-default" onclick="cocherTous('chk-strum', false)">Tout décocher</button>
            </div>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <?php if (empty($strums)): ?>
                <p class="text-muted">Aucune rythmique associée à cette chanson.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($strums as $s): ?>
                        <?php
                            $strumId = (int)$s['id'];
                            $strumMotif = htmlspecialchars($s['strum'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <label class="list-group-item dupliquer-item-label" for="strum_<?= $strumId ?>">
                            <input type="checkbox" class="chk-strum" id="strum_<?= $strumId ?>" name="strum_ids[]" value="<?= $strumId ?>">
                            <code><?= $strumMotif ?></code>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LIENS ET VIDÉOS -->
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <span class="panel-title pull-left">
                <i class="glyphicon glyphicon-link"></i> Liens et Vidéos (<?= count($liens) ?>)
            </span>
            <?php if (!empty($liens)): ?>
            <div class="btn-group btn-group-xs pull-right dupliquer-btn-group-toggle">
                <button type="button" class="btn btn-default" onclick="cocherTous('chk-lien', true)">Tout cocher</button>
                <button type="button" class="btn btn-default" onclick="cocherTous('chk-lien', false)">Tout décocher</button>
            </div>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <?php if (empty($liens)): ?>
                <p class="text-muted">Aucun lien ou vidéo associé à cette chanson.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($liens as $l): ?>
                        <?php
                            $lienId = (int)$l['id'];
                            $lienType = htmlspecialchars($l['type'], ENT_QUOTES, 'UTF-8');
                            $lienDesc = htmlspecialchars($l['description'] ?? '', ENT_QUOTES, 'UTF-8');
                            $lienUrl = htmlspecialchars($l['url'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <label class="list-group-item dupliquer-item-label" for="lien_<?= $lienId ?>">
                            <input type="checkbox" class="chk-lien" id="lien_<?= $lienId ?>" name="lien_ids[]" value="<?= $lienId ?>">
                            <span class="label label-info"><?= $lienType ?></span>
                            <span><?= $lienDesc ?: $lienUrl ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>
