<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/navigation/menu.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/songbook/songbook.php";

// Palette Canopée
$c_marron_fonce = "#2b1d1a";
$c_marron_clair = "#D2B48C"; 
$c_accent = "#8B4513";
$c_ivoire = "#fcfaf2"; 
$c_orange = "#e67e22";

// Gestion des paramètres
$tri = $_GET['tri'] ?? ($_GET['triDesc'] ?? "date");
$ordreAsc = !isset($_GET['triDesc']);
$typeFiltre = $_GET['type'] ?? "";
$recherche = $_GET['recherche'] ?? "";
$vueActive = $_SESSION['vue'] ?? 'cartes';
if (isset($_GET['vue'])) { $vueActive = $_GET['vue']; $_SESSION['vue'] = $vueActive; }

// Chargement
$songbooks = Songbook::chercheSongbooks($recherche, $typeFiltre, $tri, $ordreAsc);

$html = <<<HTML
<style>
    body { background-color: $c_ivoire !important; }
    .console-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 25px;
        background: #eee;
        padding: 8px;
        border-radius: 10px;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
    }
    .form-compact {
        display: flex;
        background: $c_marron_fonce;
        padding: 5px 15px;
        border-radius: 30px;
        flex-grow: 1;
        align-items: center;
        gap: 10px;
    }
    .form-compact input, .form-compact select {
        background: white !important;
        color: black !important;
        border: none;
        border-radius: 20px;
        height: 30px;
        padding: 0 15px;
        font-size: 13px;
    }
    .btn-tool {
        background: white;
        color: $c_marron_fonce;
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: bold;
        transition: all 0.2s;
    }
    .btn-tool:hover, .btn-tool.active {
        background: $c_orange;
        color: white;
        border-color: $c_orange;
    }
    .label-tool { font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; display: block; margin-bottom: 3px; }
</style>

<div class="container" style="padding: 20px 15px;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 0 10px;">
        <h2 style="color: $c_marron_fonce; margin: 0; font-weight: 900; font-size: 26px; letter-spacing: 2px;">
            <span class="glyphicon glyphicon-book" style="color: $c_accent;"></span> SONGBOOKS
        </h2>
        
        <?php if (aDroits(\$GLOBALS["PRIVILEGE_EDITEUR"])): ?>
            <a href="songbook_form.php" class="btn btn-sm" style="background: #27ae60; color: white; font-weight: bold; padding: 8px 20px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <i class="glyphicon glyphicon-plus"></i> NOUVEAU RECUEIL
            </a>
        <?php endif; ?>
    </div>

    <!-- CONSOLE DE FILTRAGE -->
    <div class="console-bar" style="margin: 0 10px 30px 10px;">
        
        <!-- GAUCHE : TRI -->
        <div class="text-left">
            <span class="label-tool">Trier par</span>
            <div class="btn-group">
                <a href="?tri=nom&recherche=$recherche&type=$typeFiltre" class="btn-tool btn @TRI_NOM_ACTIVE@" title="Nom"><i class="glyphicon glyphicon-sort-by-alphabet"></i></a>
                <a href="?triDesc=date&recherche=$recherche&type=$typeFiltre" class="btn-tool btn @TRI_DATE_ACTIVE@" title="Récent"><i class="glyphicon glyphicon-calendar"></i></a>
                <a href="?triDesc=hits&recherche=$recherche&type=$typeFiltre" class="btn-tool btn @TRI_HITS_ACTIVE@" title="Vues"><i class="glyphicon glyphicon-fire"></i></a>
            </div>
        </div>

        <!-- CENTRE : FORMULAIRE -->
        <form action="songbook_liste.php" method="GET" class="form-compact">
            <input type="hidden" name="tri" value="$tri">
            <input type="hidden" name="vue" value="$vueActive">
            
            <input type="text" name="recherche" placeholder="Rechercher..." value="$recherche" style="flex-grow: 2;">
            
            <select name="type" onchange="this.form.submit()" style="flex-grow: 1;">
                <option value="">Tous les genres</option>
                <option value="1" @TYPE_1_SELECTED@>Anthologie</option>
                <option value="2" @TYPE_2_SELECTED@>Concert</option>
                <option value="3" @TYPE_3_SELECTED@>Thématique</option>
            </select>

            <button type="submit" class="btn btn-xs" style="background: $c_marron_clair; color: white; border-radius: 20px; padding: 4px 15px; font-weight: bold;">OK</button>
            <a href="songbook_liste.php" class="btn btn-xs btn-danger" style="border-radius: 50%; width: 24px; height: 24px; padding: 3px 0; text-align: center;"><i class="glyphicon glyphicon-remove"></i></a>
        </form>

        <!-- DROITE : VUE -->
        <div class="text-right">
            <span class="label-tool">Affichage</span>
            <div class="btn-group">
                <a href="?vue=cartes&recherche=$recherche&type=$typeFiltre&tri=$tri" class="btn-tool btn @VUE_CARTES_ACTIVE@" title="Vignettes"><i class="glyphicon glyphicon-th"></i></a>
                <a href="?vue=liste&recherche=$recherche&type=$typeFiltre&tri=$tri" class="btn-tool btn @VUE_LISTE_ACTIVE@" title="Tableau"><i class="glyphicon glyphicon-th-list"></i></a>
            </div>
        </div>

    </div>

    <!-- RÉSULTATS -->
    <div id="resultats-sb">
HTML;

// Remplacement placeholders
$html = str_replace([
    '@TYPE_1_SELECTED@', '@TYPE_2_SELECTED@', '@TYPE_3_SELECTED@',
    '@TRI_NOM_ACTIVE@', '@TRI_DATE_ACTIVE@', '@TRI_HITS_ACTIVE@',
    '@VUE_CARTES_ACTIVE@', '@VUE_LISTE_ACTIVE@'
], [
    ($typeFiltre == "1" ? "selected" : ""), ($typeFiltre == "2" ? "selected" : ""), ($typeFiltre == "3" ? "selected" : ""),
    ($tri == 'nom' ? 'active' : ''), ($tri == 'date' ? 'active' : ''), ($tri == 'hits' ? 'active' : ''),
    ($vueActive == 'cartes' ? 'active' : ''), ($vueActive == 'liste' ? 'active' : '')
], $html);

if (empty($songbooks)) {
    $html .= '<div class="text-center" style="padding: 50px; background: white; border-radius: 10px; border: 1px solid #ddd; color: #999;">Aucun songbook trouvé.</div>';
} else {
    if ($vueActive == 'cartes') {
        $html .= '<div class="row">';
        foreach ($songbooks as $sb) { $html .= $sb->afficheCarteSongbook(); }
        $html .= '</div>';
    } else {
        $html .= '<div class="table-responsive" style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee;">
                    <table class="table table-hover">
                        <thead><tr style="font-size: 10px; text-transform: uppercase; color: #bbb; letter-spacing: 1px;"><th>Visuel</th><th>Titre</th><th>Genre</th><th>Description</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>';
        foreach ($songbooks as $sb) {
            $_id = $sb->getId();
            $img = imageSongbook($_id);
            // Utilisation de la vignette optimisée (64x64)
            if ($img) {
                $vignettePath = "../../vignettes/" . $img;
                $src = file_exists($vignettePath) ? $vignettePath : "../../data/songbooks/$_id/" . urlencode($img);
                $imgTag = "<a href='songbook_voir.php?id=$_id'><img src='$src' width='45' height='45' style='object-fit: cover; border-radius: 5px;'></a>";
            } else {
                $imgTag = "<a href='songbook_voir.php?id=$_id' style='text-decoration:none;'><div style='width:45px; height:45px; background:#f5f5f5; border-radius:5px; display:flex; align-items:center; justify-content:center; color:#ccc;'><i class='glyphicon glyphicon-book'></i></div></a>";
            }
            
            $actions = "<a href='songbook_voir.php?id=$_id' class='btn btn-link btn-sm' title='Ouvrir' style='color: $c_marron_fonce;'><i class='glyphicon glyphicon-eye-open'></i></a>";
            if (aDroits($GLOBALS["PRIVILEGE_EDITEUR"])) $actions .= " <a href='songbook_form.php?id=$_id' class='btn btn-link btn-sm' title='Éditer' style='color: $c_orange;'><i class='glyphicon glyphicon-pencil'></i></a>";
            
            $html .= "<tr>
                        <td style='vertical-align: middle; border: none;'>$imgTag</td>
                        <td style='vertical-align: middle; border: none;'><strong><a href='songbook_voir.php?id=$_id' style='color: $c_marron_fonce; font-size: 16px;'>".$sb->getNom()."</a></strong></td>
                        <td style='vertical-align: middle; border: none;'><span style='color: #999; font-size: 11px; font-weight: bold;'>".$sb->getLabelType()."</span></td>
                        <td style='vertical-align: middle; border: none;'><small class='text-muted'>".limiteLongueur($sb->getDescription(), 100)."</small></td>
                        <td style='vertical-align: middle; border: none;' class='text-right'>$actions</td>
                      </tr>";
        }
        $html .= '</tbody></table></div>';
    }
}

$html .= "</div></div>";
echo $html;
echo envoieFooter();
