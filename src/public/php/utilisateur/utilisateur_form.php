<?php
/**
 * PAGE : utilisateur_form.php
 * Formulaire de gestion du profil utilisateur (Refactorisé SOLID / Canopée).
 */

require_once dirname(__DIR__, 3) . "/autoload.php";
require_once __DIR__ . "/UtilisateurFormService.php";
require_once __DIR__ . "/UtilisateurFormRenderer.php";

// Récupération de l'ID et du message éventuel
$id = (int)($_GET['id'] ?? 0);
$msg = $_GET['msg'] ?? '';

// --- PRÉPARATION DES DONNÉES (VIA SERVICE) ---
$formData = UtilisateurFormService::prepareData($id);

// --- RENDU HTML (VIA RENDERER) ---
echo UtilisateurFormRenderer::render($formData, $msg);
