<?php
require_once("lib/utilssi.php");
require_once("menu.php");
require_once ("strum.php");

global $largeur_max_vignette;
global $iconeAttention;
global $cheminImages;
global $iconePoubelle;
global $iconeCreer;
global $cheminVignettes;

$strumList = "strum_liste.php";
$strumGet = "strum_post.php";
$largeur_max_vignette = 64;
$table = "strum";
$_renduHtml = "";


// Gestion du parametre
if (isset($_GET['id']))
{
    $_idStrum = intval($_GET['id']);
    $_strum = new Strum($_idStrum);
}
else
{
    $_strum = new Strum();
}
?>
<h1>Strums</h1>
<div id='editionStrum' class = 'formulaire' >

        <span class='glyphicon glyphicon-book' title='aide' aria-labelledby="aide" onclick="document.getElementById('aide').style.display='inline';"></span>
        <br>
        <input id='id' type='hidden' value='<?= $_strum->getId(); ?>'>
        <label for='unite'>Unité</label>
        <input id='unite' type='text' placeholder='4 pour noire, 8 pour croche, 16 pour double-croche'
               value='<?php echo $_strum->getUnite(); ?>'>
        <label for='longueur'>Longueur</label>
        <input id='longueur' type='number' placeholder='4, ou  ou 16 le plus souvent'
               value='<?= $_strum->getLongueur(); ?>'>
        <label for='strum'>Strum</label>
        <input id='strum' type='text' placeholder='ex : B BH HBH, voir aide'
               value='<?= $_strum->getStrum(); ?>'>
        <label for='description'>Description</label>
        <input id='description' type='text' placeholder='ici le nom du strum et sa description'
               value='<?= $_strum->getDescription(); ?>'> <br>
        <div id='aide' style='display:none'>
            <span class='glyphicon glyphicon-remove' title='fermer' aria-label '' onclick="document.getElementById('aide').style.display='none';"></span>
        <p> Les caractères conseillés sont B et H pour bas ou haut , X pour le chunk, espace pour le non joué,
            b et h pour bas ou haut mais doucement ! <br>
            Pour la longueur, on utilisera le plus souvent 8 croches, donc ce sera alors longueur 8 et unité = 8 <br>
            Pour les strums à la double croche, on mettra longueur 16, unité 16 : "B   B bh H H HBH"
            <br>
            Si le strum est sur deux mesures et à la croche, on aura longueur = 16 et unité = 8
        </p>
        </div>
        <?php
        if ($_strum->getId() == 0)
        {
            echo "  <button class='btn-success' name= 'creer'  >Créer</button>";
        }
        else {
            echo "  <button class='btn-warning' name='modifier' >Modifier</button>";
        }
        ?>

        <button class='btn-danger'onclick="window.history.back();">Retour</button>

</div>
<div id="retour">
    résultat...
</div>
<?php
$_renduHtml .= envoieFooter();
$_renduHtml .= "
<script src='../js/strum_liste.js'></script>
";
echo $_renduHtml;
