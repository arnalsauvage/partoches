<?php
/**
 * PAGE : playlist_form.php
 * Formulaire de gestion des playlists avec support du mode Dynamique.
 */

require_once __DIR__ . "/../lib/utilssi.php";
$pasDeMenu = true;
require_once __DIR__ . "/../navigation/menu.php";
require_once("playlist.php");
require_once __DIR__ . "/../document/Document.php";
require_once __DIR__ . "/../liens/lienChansonPlaylist.php";
require_once __DIR__ . "/../chanson/Chanson.php";
require_once __DIR__ . "/../strum/Strum.php";

$table = "playlist";

// Sécurité : Droits d'édition requis
if ($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_EDITEUR"]) {
    $urlRedirection = $table . "_voir.php" . (isset($_GET['id']) ? "?id=" . (int)$_GET['id'] : "");
    redirection($urlRedirection);
}

// Récupération de l'ID
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$mode = ($id > 0) ? "MAJ" : "INS";

// --- GESTION DES ACTIONS ---
$message = "";
if ($id > 0) {
    if (isset($_POST['chanson']) && is_numeric($_POST['chanson'])) {
        creelienChansonPlaylist($_POST['chanson'], $id);
        ordonneLiensPlaylist($id);
        $message = "Morceau ajouté avec succès !";
    }
    if (isset($_GET['action']) && isset($_GET['rang'])) {
        $rang = (int)$_GET['rang'];
        if ($_GET['action'] == "up") remonteTitrePlaylist($id, $rang, 1);
        if ($_GET['action'] == "down") descendTitrePlaylist($id, $rang, 1);
        if ($_GET['action'] == "del" && isset($_GET['idLien'])) {
            supprimelienChansonPlaylist((int)$_GET['idLien']);
            ordonneLiensPlaylist($id);
        }
        redirection("playlist_form.php?id=$id&msg=OK_ACTION");
    }
}

// Chargement des données
if ($mode == "MAJ") {
    $donnee = chercheplaylist($id);
    $titrePage = "Mise à jour - " . htmlspecialchars($donnee['nom'] ?? '');
    $typePl = $donnee['type'] ?? 0;
    $criteres = json_decode($donnee['criteres'] ?? "[]", true);
} else {
    $donnee = ['id'=>0, 'nom'=>'', 'description'=>'', 'date_creation'=>date("Y-m-d"), 'image'=>'', 'hits'=>0, 'type'=>0, 'criteres'=>''];
    $titrePage = "Nouvelle Playlist";
    $typePl = 0;
    $criteres = [];
}

echo envoieHead($titrePage, "styles-communs.css");
echo $MENU_HTML;
?>

<div class="container" style="padding-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #8B4513; padding-bottom: 10px;">
        <h1 style="margin: 0; color: #2b1d1a;"><?php echo ($mode == "MAJ" ? "Mise à jour" : "Création"); ?> Playlist</h1>
        <div>
            <a href="playlist_liste.php" class="btn btn-default btn-sm"><i class='glyphicon glyphicon-arrow-left'></i> Retour à la liste</a>
            <?php if ($mode == "MAJ"): ?>
                <a href="playlist_voir.php?id=<?php echo $id; ?>" class="btn btn-info btn-sm"><i class="glyphicon glyphicon-eye-open"></i> Voir</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="tabs">
        <ul>
            <li><a href="#tabs-1"><i class="glyphicon glyphicon-info-sign"></i> Configuration</a></li>
            <?php if ($typePl == 0): ?>
                <li><a href="#tabs-2"><i class="glyphicon glyphicon-music"></i> Morceaux</a></li>
            <?php endif; ?>
        </ul>

        <div id="tabs-1">
            <form method="POST" action="playlist_get.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                
                <div class="well" style="background-color: #F5F5DC; border: 1px solid #D2B48C; border-radius: 12px; margin-top: 15px;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom de la playlist :</label>
                                <input type="text" name="fnom" class="form-control" value="<?php echo htmlspecialchars($donnee['nom'] ?? $donnee[1] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Description :</label>
                                <textarea name="fdescription" class="form-control" rows="3"><?php echo htmlspecialchars($donnee['description'] ?? $donnee[2] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type :</label>
                                <select name="ftype" id="playlist_type" class="form-control" style="background-color: #2b1d1a; color: #F5F5DC; font-weight: bold;">
                                    <option value="0" <?php echo ($typePl == 0 ? 'selected' : ''); ?>>📝 Manuelle</option>
                                    <option value="1" <?php echo ($typePl == 1 ? 'selected' : ''); ?>>🤖 Dynamique</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Image :</label>
                                <input type="text" name="fimage" class="form-control" value="<?php echo htmlspecialchars($donnee['image'] ?? $donnee[4] ?? ''); ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date :</label>
                                        <input type="text" name="fdate" class="form-control" value="<?php echo dateMysqlVersTexte($donnee['date'] ?? $donnee[3] ?? date('Y-m-d')); ?>" <?php echo ($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"] ? 'readonly' : ''); ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hits :</label>
                                        <input type="text" name="fhits" class="form-control" value="<?php echo $donnee['hits'] ?? $donnee[5] ?? 0; ?>" <?php echo ($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"] ? 'readonly' : ''); ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="dynamic_options" class="well" style="display: <?php echo ($typePl == 1 ? 'block' : 'none'); ?>; border: 2px solid #8B4513; background-color: #fff;">
                    <h3 style="color: #8B4513; margin-top: 0;"><i class="glyphicon glyphicon-cog"></i> Critères automatiques</h3>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Tonalité :</label>
                            <select name="dyn_tonalite" class="form-control">
                                <option value="">-- Peu importe --</option>
                                <?php
                                $tonas = ['C','C#','Db','D','D#','Eb','E','F','F#','Gb','G','G#','Ab','A','A#','Bb','B'];
                                foreach ($tonas as $t) {
                                    foreach (['', 'm'] as $m) {
                                        $v = $t.$m;
                                        $sel = ($criteres['tonalite'] ?? '') == $v ? 'selected' : '';
                                        echo "<option value='$v' $sel>$v</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Tempo :</label>
                            <select name="dyn_tempo" class="form-control">
                                <option value="">-- Peu importe --</option>
                                <?php
                                foreach (['Largo','Adagio','Andante','Moderato','Allegro','Presto'] as $t) {
                                    $sel = ($criteres['tempo_famille'] ?? '') == $t ? 'selected' : '';
                                    echo "<option value='$t' $sel>$t</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Saison :</label>
                            <select name="dyn_saison" class="form-control">
                                <option value="">-- Peu importe --</option>
                                <?php
                                for ($a = date('Y'); $a >= 2018; $a--) {
                                    $sel = ($criteres['saison'] ?? '') == $a ? 'selected' : '';
                                    echo "<option value='$a' $sel>$a-".($a+1)."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Rythmique :</label>
                            <select name="dyn_strum" class="form-control">
                                <option value="">-- Peu importe --</option>
                                <?php
                                $db = $_SESSION['mysql'];
                                // Détection automatique de la colonne du nom dans 'strum'
                                $resStrum = $db->query("SELECT * FROM strum LIMIT 1");
                                if ($resStrum) {
                                    $fields = $resStrum->fetch_fields();
                                    $colName = 'nom'; // fallback
                                    foreach ($fields as $f) { if ($f->name == 'strum') $colName = 'strum'; }
                                    $resList = $db->query("SELECT id, $colName FROM strum ORDER BY $colName ASC");
                                    while($row = $resList->fetch_assoc()) {
                                        $sel = ($criteres['idStrum'] ?? '') == $row['id'] ? 'selected' : '';
                                        echo "<option value='".$row['id']."' $sel>".htmlspecialchars($row[$colName])."</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-center" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-lg btn-success" style="background-color: #2b1d1a; color: #F5F5DC; border: 1px solid #D2B48C; padding: 10px 40px;">
                        <i class="glyphicon glyphicon-floppy-disk"></i> ENREGISTRER LA CONFIGURATION
                    </button>
                </div>
            </form>
        </div>

        <?php if ($mode == "MAJ" && $typePl == 0): ?>
        <div id="tabs-2">
            <div class="row" style="margin-top: 15px;">
                <div class="col-md-7">
                    <table class="table table-striped" style="background: white; border: 1px solid #ddd;">
                        <thead><tr style="background: #2b1d1a; color: #F5F5DC;"><th>Ordre</th><th>Titre</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php 
                            $lignes = chercheLiensChansonPlaylist('idPlaylist', $id, "ordre", true);
                            while ($ligne = $lignes->fetch_row()): $ch = new Chanson($ligne[1]); ?>
                                <tr>
                                    <td><?php echo $ligne[3]; ?></td>
                                    <td><strong><?php echo htmlspecialchars($ch->getNom()); ?></strong></td>
                                    <td>
                                        <div class="btn-group btn-group-xs">
                                            <a href="?id=<?php echo $id; ?>&action=up&rang=<?php echo $ligne[3]; ?>" class="btn btn-default"><i class="glyphicon glyphicon-arrow-up"></i></a>
                                            <a href="?id=<?php echo $id; ?>&action=down&rang=<?php echo $ligne[3]; ?>" class="btn btn-default"><i class="glyphicon glyphicon-arrow-down"></i></a>
                                            <a href="?id=<?php echo $id; ?>&action=del&idLien=<?php echo $ligne[0]; ?>" class="btn btn-danger" onclick="return confirm('Enlever ?');"><i class="glyphicon glyphicon-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-5">
                    <div class="well" style="background-color: #2b1d1a; color: #F5F5DC;">
                        <h4>Ajouter un morceau</h4>
                        <form method="POST" action="playlist_form.php?id=<?php echo $id; ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <select name="chanson" class="form-control select2">
                                <?php $res = $db->query("SELECT id, nom FROM chanson ORDER BY nom ASC");
                                while($row = $res->fetch_assoc()) echo "<option value='".$row['id']."'>".htmlspecialchars($row['nom'])."</option>"; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-block" style="margin-top:10px; background:#8B4513; border:none;">AJOUTER</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#tabs").tabs();
    if ($.fn.select2) $('.select2').select2({ theme: "bootstrap" });
    $('#playlist_type').on('change', function() {
        if ($(this).val() == '1') { $('#dynamic_options').slideDown(); } 
        else { $('#dynamic_options').slideUp(); }
    });
});
</script>
<?php echo envoieFooter(); ?>
