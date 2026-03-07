<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/lib/utilssi.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/navigation/menu.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/php/strum/strum.php";

// Palette Canopée
$c_marron_fonce = "#2b1d1a";
$c_marron_clair = "#D2B48C"; 
$c_accent = "#8B4513";
$c_ivoire = "#fcfaf2";
$c_orange = "#e67e22";

// Gestion du tri
$tri = $_GET['tri'] ?? 'pop'; // Par défaut : Popularité
$strums = Strum::chargeStrumsBdd($tri);
$nbStrums = count($strums);

// Classes actives pour les boutons
$activePop = ($tri == 'pop') ? 'active' : '';
$activeNom = ($tri == 'nom') ? 'active' : '';
$activeDate = ($tri == 'date') ? 'active' : '';

// --- RENDU ---

$html = <<<HTML
<!-- CSS Spécifique -->
<link rel="stylesheet" href="../../css/strum_liste.css">

<div class="container strum-container">
    
    <div class="row">
        <div class="col-xs-12 text-center" style="margin-bottom: 20px;">
            <h1 style="color: $c_marron_fonce; font-weight: 900; font-size: 42px; text-transform: uppercase; letter-spacing: 5px; margin-bottom: 10px;">
                <span class="glyphicon glyphicon-option-vertical"></span> STRUMS
            </h1>
            <p style="color: $c_marron_clair; font-size: 14px; letter-spacing: 3px; font-weight: bold;">DICTIONNAIRE DE $nbStrums RYTHMIQUES</p>
            <div style="width: 80px; height: 4px; background: $c_orange; margin: 15px auto; border-radius: 2px;"></div>
        </div>
    </div>

    <!-- CONSOLE DE TRI -->
    <div class="row" style="margin-bottom: 30px;">
        <div class="col-xs-12 text-center">
            <div class="btn-group" role="group">
                <a href="?tri=pop" class="btn btn-default $activePop" style="border-radius: 20px 0 0 20px; font-weight: bold; color: $c_marron_fonce;">
                    <i class="glyphicon glyphicon-star"></i> LES PLUS JOUÉS
                </a>
                <a href="?tri=nom" class="btn btn-default $activeNom" style="font-weight: bold; color: $c_marron_fonce;">
                    <i class="glyphicon glyphicon-sort-by-alphabet"></i> ALPHABÉTIQUE
                </a>
                <a href="?tri=date" class="btn btn-default $activeDate" style="border-radius: 0 20px 20px 0; font-weight: bold; color: $c_marron_fonce;">
                    <i class="glyphicon glyphicon-time"></i> NOUVEAUTÉS
                </a>
            </div>
        </div>
    </div>

    <!-- ACTIONS ADMIN -->
HTML;

if (aDroits($GLOBALS["PRIVILEGE_MEMBRE"])) {
    $html .= <<<HTML
    <div class="row" style="margin-bottom: 30px;">
        <div class="col-xs-12 text-right">
            <a href="strum_form.php" class="btn btn-success" style="background-color: #2e7d32; border: none; font-weight: bold; border-radius: 30px; padding: 10px 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <i class="glyphicon glyphicon-plus"></i> CRÉER UN NOUVEAU STRUM
            </a>
        </div>
    </div>
HTML;
}

$html .= '<div class="row">';

if (empty($strums)) {
    $html .= '<div class="col-xs-12 text-center"><div class="alert alert-info">Aucun strum n\'est encore enregistré en base de données.</div></div>';
} else {
    foreach ($strums as $s) {
        $html .= $s->afficheCarteStrum();
    }
}

$html .= '</div>'; // Fin row des cartes

// --- VERSION TABLEAU (POUR LES PURISTES OU ADMINS) ---
if (aDroits($GLOBALS["PRIVILEGE_EDITEUR"])) {
    $html .= <<<HTML
    <div style="margin-top: 80px; border-top: 1px solid #ddd; padding-top: 40px;">
        <h3 style="color: #999; text-transform: uppercase; font-size: 14px; letter-spacing: 2px; margin-bottom: 20px;">Vue technique (Administrateurs)</h3>
        <div class="table-responsive" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <table class="table table-hover">
                <thead>
                    <tr style="color: $c_marron_fonce; font-size: 11px; text-transform: uppercase;">
                        <th>ID</th>
                        <th>Strum</th>
                        <th>Unité</th>
                        <th>Description</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
HTML;
    foreach ($strums as $s) {
        $id = $s->getId();
        $strumStr = str_replace(" ", "-", $s->getStrum());
        $html .= <<<HTML
                    <tr>
                        <td style="color: #ccc;">#$id</td>
                        <td><code style="background: #f5f5f5; color: $c_accent; padding: 2px 6px;">$strumStr</code></td>
                        <td><small>{$s->getLongueur()} {$s->renvoieUniteEnFrancais()}</small></td>
                        <td><small class="text-muted">{$s->getDescription()}</small></td>
                        <td class="text-right">
                            <a href="strum_form.php?id=$id" class="btn btn-xs btn-default"><i class="glyphicon glyphicon-pencil"></i></a>
                            <a href="strum_post.php?id=$id&mode=SUPPR" class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ?')"><i class="glyphicon glyphicon-trash"></i></a>
                        </td>
                    </tr>
HTML;
    }
    $html .= '</tbody></table></div></div>';
}

$html .= <<<HTML
</div>

<script>
function voirChansonsStrum(idStrum, nomStrum) {
    $('#modalTitle').html('Morceaux utilisant : <code style="background:#eee; color:$c_orange;">' + nomStrum + '</code>');
    $('#modalBodyChansons').html('<div class="text-center" style="padding:20px;"><span class="glyphicon glyphicon-refresh"></span> Chargement des chansons...</div>');
    $('#modalChansonsStrum').modal('show');
    
    $.ajax({
        url: 'chansons_par_strum_ajax.php',
        data: { idStrum: idStrum },
        success: function(html) {
            $('#modalBodyChansons').html(html);
        },
        error: function() {
            $('#modalBodyChansons').html('<div class="alert alert-danger">Erreur lors du chargement des chansons.</div>');
        }
    });
}
</script>

<!-- MODALE DES CHANSONS DU STRUM -->
<div class="modal fade modal-strum" id="modalChansonsStrum" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalTitle">Chansons utilisant ce strum</h4>
      </div>
      <div class="modal-body" id="modalBodyChansons" style="max-height: 450px; overflow-y: auto; padding: 20px;">
        <div class="text-center"><i class="glyphicon glyphicon-refresh"></i> Chargement...</div>
      </div>
      <div class="modal-footer" style="background: #f9f9f9; border-top: 1px solid #eee;">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 20px; font-weight: bold;">Fermer</button>
      </div>
    </div>
  </div>
</div>
HTML;

echo $html;
echo envoieFooter();
