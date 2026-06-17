<?php
/**
 * Point d'entrée du formulaire de chanson nouvelle génération.
 *
 * Responsabilité unique :
 * - Initialiser les dépendances nécessaires.
 * - Déléguer la préparation du formulaire au service métier.
 * - Déléguer le rendu au renderer dédié.
 * - Afficher la page finale.
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once __DIR__ . "/../lib/utilssi.php";
require_once __DIR__ . "/../utilisateur/Utilisateur.php";

use ChansonFormService;
use ChansonFormNewRenderer;

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;

// 1. Préparation métier (sécurité + initialisation entité + mode)
$context = ChansonFormService::prepareForm($id);
$chanson = $context['chanson'];
$mode = $context['mode'];

// 2. Contexte complémentaire pour le renderer
$contextRendu = [
    'listeSongbooks' => Songbook::listeSongbooks(),
    'dossierChanson' => $_DOSSIER_CHANSONS ?? '',
    'iconePoubelle' => $iconePoubelle ?? '',
    'cheminImages' => $cheminImages ?? '',
];

// 3. Rendu HTML complet
echo ChansonFormNewRenderer::render($chanson, $mode, $contextRendu);
