<?php
/**
 * Renderer pour le formulaire de Playlist.
 */

class PlaylistFormRenderer
{
    /**
     * Rendu complet de la page.
     */
    public static function render(array $data, string $message = ''): string
    {
        $id = $data['id'];
        $mode = $data['mode'];
        $playlist = $data['playlist'];
        $typePl = $data['typePl'];
        $criteres = $data['criteres'];

        $titrePage = ($mode === "MAJ") ? "Mise à jour - " . htmlspecialchars($playlist['nom'] ?? '') : "Nouvelle Playlist";

        $html = envoieHead($titrePage, "../../css/playlistform.css");
        $pasDeMenu = true;
        require_once dirname(__DIR__) . "/navigation/menu.php";
        $html .= $MENU_HTML;

        $html .= "<div class='container playlist-form-container'>";
        $html .= self::renderHeader($mode, $id);
        
        if ($message) {
            $html .= "<div class='alert alert-success'>$message</div>";
        }

        $html .= "<div id='tabs' class='tabs-django'>";
        $html .= "<ul>";
        $html .= "<li><a href='#tabs-1'><i class='glyphicon glyphicon-info-sign'></i> Configuration</a></li>";
        if ($typePl == 0) {
            $html .= "<li><a href='#tabs-2'><i class='glyphicon glyphicon-music'></i> Morceaux</a></li>";
        }
        $html .= "</ul>";

        $html .= "<div id='tabs-1'>";
        $html .= self::renderConfigForm($playlist, $id, $mode, $typePl, $criteres);
        $html .= "</div>";

        if ($mode === "MAJ" && $typePl == 0) {
            $html .= "<div id='tabs-2'>";
            $html .= self::renderSongsTab($id);
            $html .= "</div>";
        }

        $html .= "</div></div>"; // /tabs /container

        $html .= self::renderScripts();
        $html .= envoieFooter();

        return $html;
    }

    private static function renderHeader(string $mode, int $id): string
    {
        $titreH1 = ($mode === "MAJ" ? "Mise à jour" : "Création") . " Playlist";
        $btnVoir = ($mode === "MAJ") ? "<a href='playlist_voir.php?id=$id' class='btn btn-info btn-sm'><i class='glyphicon glyphicon-eye-open'></i> Voir</a>" : "";

        return <<<HTML
    <div class="playlist-header">
        <h1 class="playlist-title">$titreH1</h1>
        <div class="playlist-header-actions">
            <a href="playlist_liste.php" class="btn btn-default btn-sm"><i class='glyphicon glyphicon-arrow-left'></i> Retour à la liste</a>
            $btnVoir
        </div>
    </div>
HTML;
    }

    private static function renderConfigForm(array $playlist, int $id, string $mode, int $typePl, array $criteres): string
    {
        $nom = htmlspecialchars($playlist['nom'] ?? '');
        $description = htmlspecialchars($playlist['description'] ?? '');
        $image = htmlspecialchars($playlist['image'] ?? '');
        $date = dateMysqlVersTexte($playlist['date'] ?? date('Y-m-d'));
        $hits = $playlist['hits'] ?? 0;
        $readonlyDate = ($_SESSION['privilege'] < $GLOBALS["PRIVILEGE_ADMIN"]) ? 'readonly' : '';

        $selManuelle = ($typePl == 0) ? 'selected' : '';
        $selDynamique = ($typePl == 1) ? 'selected' : '';

        $displayDyn = ($typePl == 1) ? 'block' : 'none';

        $html = <<<HTML
            <form method="POST" action="playlist_get.php">
                <input type="hidden" name="id" value="$id">
                <input type="hidden" name="mode" value="$mode">
                
                <div class="well playlist-config-well">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom de la playlist :</label>
                                <input type="text" name="fnom" class="form-control" value="$nom" required>
                            </div>
                            <div class="form-group">
                                <label>Description :</label>
                                <textarea name="fdescription" class="form-control" rows="3">$description</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type :</label>
                                <select name="ftype" id="playlist_type" class="form-control playlist-type-select">
                                    <option value="0" $selManuelle>📝 Manuelle</option>
                                    <option value="1" $selDynamique>🤖 Dynamique</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Image :</label>
                                <input type="text" name="fimage" class="form-control" value="$image">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date :</label>
                                        <input type="text" name="fdate" class="form-control" value="$date" $readonlyDate>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hits :</label>
                                        <input type="text" name="fhits" class="form-control" value="$hits" $readonlyDate>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="dynamic_options" class="well dynamic-options-well" style="display: $displayDyn;">
                    <h3 class="dynamic-options-title"><i class="glyphicon glyphicon-cog"></i> Critères automatiques</h3>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Tonalité :</label>
                            <select name="dyn_tonalite" class="form-control">
                                <option value="">-- Peu importe --</option>
HTML;
        $tonas = ['C','C#','Db','D','D#','Eb','E','F','F#','Gb','G','G#','Ab','A','A#','Bb','B'];
        foreach ($tonas as $t) {
            foreach (['', 'm'] as $m) {
                $v = $t.$m;
                $sel = ($criteres['tonalite'] ?? '') == $v ? 'selected' : '';
                $html .= "<option value='$v' $sel>$v</option>";
            }
        }

        $html .= <<<HTML
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Tempo :</label>
                            <select name="dyn_tempo" class="form-control">
                                <option value="">-- Peu importe --</option>
HTML;
        foreach (['Largo','Adagio','Andante','Moderato','Allegro','Presto'] as $t) {
            $sel = ($criteres['tempo_famille'] ?? '') == $t ? 'selected' : '';
            $html .= "<option value='$t' $sel>$t</option>";
        }

        $html .= <<<HTML
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Saison :</label>
                            <select name="dyn_saison" class="form-control">
                                <option value="">-- Peu importe --</option>
HTML;
        for ($a = date('Y'); $a >= 2018; $a--) {
            $sel = ($criteres['saison'] ?? '') == $a ? 'selected' : '';
            $html .= "<option value='$a' $sel>$a-".($a+1)."</option>";
        }

        $html .= <<<HTML
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Rythmique :</label>
                            <select name="dyn_strum" class="form-control">
                                <option value="">-- Peu importe --</option>
HTML;
        $db = $_SESSION['mysql'];
        $resStrum = $db->query("SELECT * FROM strum LIMIT 1");
        if ($resStrum) {
            $fields = $resStrum->fetch_fields();
            $colName = 'nom'; 
            foreach ($fields as $f) { if ($f->name == 'strum') $colName = 'strum'; }
            $resList = $db->query("SELECT id, $colName FROM strum ORDER BY $colName ASC");
            while($row = $resList->fetch_assoc()) {
                $sel = ($criteres['idStrum'] ?? '') == $row['id'] ? 'selected' : '';
                $html .= "<option value='".$row['id']."' $sel>".htmlspecialchars($row[$colName])."</option>";
            }
        }

        $html .= <<<HTML
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-20">
                    <button type="submit" class="btn btn-lg btn-success btn-save-playlist">
                        <i class="glyphicon glyphicon-floppy-disk"></i> ENREGISTRER LA CONFIGURATION
                    </button>
                </div>
            </form>
HTML;
        return $html;
    }

    private static function renderSongsTab(int $id): string
    {
        $html = <<<HTML
            <div class="row mt-15">
                <div class="col-md-7">
                    <table class="table table-striped playlist-songs-table">
                        <thead><tr><th>Ordre</th><th>Titre</th><th>Actions</th></tr></thead>
                        <tbody>
HTML;
        $lignes = chercheLiensChansonPlaylist('id_playlist', $id, "ordre", true);
        while ($ligne = $lignes->fetch_row()) {
            $ch = new Chanson($ligne[1]);
            $nomCh = htmlspecialchars($ch->getNom());
            $html .= <<<HTML
                                <tr>
                                    <td>{$ligne[3]}</td>
                                    <td><strong>$nomCh</strong></td>
                                    <td>
                                        <div class="btn-group btn-group-xs">
                                            <a href="?id=$id&action=up&rang={$ligne[3]}" class="btn btn-default"><i class="glyphicon glyphicon-arrow-up"></i></a>
                                            <a href="?id=$id&action=down&rang={$ligne[3]}" class="btn btn-default"><i class="glyphicon glyphicon-arrow-down"></i></a>
                                            <a href="?id=$id&action=del&idLien={$ligne[0]}" class="btn btn-danger" onclick="return confirm('Enlever ?');"><i class="glyphicon glyphicon-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
HTML;
        }
        $html .= <<<HTML
                        </tbody>
                    </table>
                </div>
                <div class="col-md-5">
                    <div class="well add-song-well">
                        <h4>Ajouter un morceau</h4>
                        <form method="POST" action="playlist_form.php?id=$id">
                            <input type="hidden" name="id" value="$id">
                            <select name="chanson" class="form-control select2">
HTML;
        $db = $_SESSION['mysql'];
        $res = $db->query("SELECT id, nom FROM chanson ORDER BY nom ASC");
        while($row = $res->fetch_assoc()) {
            $html .= "<option value='".$row['id']."'>".htmlspecialchars($row['nom'])."</option>";
        }
        
        $html .= <<<HTML
                            </select>
                            <button type="submit" class="btn btn-primary btn-block mt-10 btn-add-song">AJOUTER</button>
                        </form>
                    </div>
                </div>
            </div>
HTML;
        return $html;
    }

    private static function renderScripts(): string
    {
        return <<<'JAVASCRIPT'
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
JAVASCRIPT;
    }
}
