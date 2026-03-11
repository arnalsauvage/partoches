# Résumé des interventions Django (Mercredi 11 Mars 2026)

## Bug Fixes & Stabilité
- **Upload Utilisateur** : Suppression de l'inclusion de `vignette.php` (inexistant) dans `utilisateur_upload.php`. L'upload fonctionne désormais en prod.
- **Paramétrage Prod** : Sécurisation du chemin des logs (`__DIR__`) dans `paramsEdit.php`. Les logs s'affichent correctement.
- **Logo Twitter** : Correction du lien cassé et ajout d'une détection automatique des réseaux sociaux (Twitter, FB, Insta) dans la galerie des liens.

## Améliorations UX & UI (Mobile First)
- **Strums** : Bouton "Ajouter un strum" rétabli pour les admins. Agrandissement des boutons de gestion (btn-sm) et espacement amélioré.
- **Admin Mobile** : Correction du CSS de `paramsEdit.php` pour éviter la superposition des icônes et le débordement des onglets sur smartphone.
- **Navbar Mobile** : Optimisation de la barre de navigation pour éviter que le menu burger ne se superpose au titre/logo sur petit écran.
- **Formulaire Songbook** : Modernisation visuelle complète (Palette Canopée, ombres, focus).
- **Portfolio Songbook** : Extension de la limite d'affichage des titres de chansons (25 -> 50 caractères).

## Nouvelles Fonctionnalités (Roadbook)
- **Incitation Connexion** : Sur `chanson_voir.php`, les médias (audio/vidéo) sont masqués pour les non-connectés au profit d'une mention incitative avec accès direct au login.
- **Système de TAGS (Exercices, Strums, Atelier...)** :
    - Ajout de la colonne `tags` en BDD (chanson, strum, songbook).
    - Implémentation complète dans les classes PHP (getters/setters, persistance).
    - Ajout du champ "Tags" dans tous les formulaires d'édition.
    - Affichage automatique de BADGES (labels) sur les cartes et les vues détaillées.

## Maintenance Technique
- **Audit Images** : `imagesCheck.php` déplacé dans `src/public/php/audit/` pour une meilleure organisation. Chemins sécurisés via `__DIR__`.
- **Refactoring Global** : Sécurisation de 152 fichiers PHP (chemins absolus via `__DIR__`).
- **Tests Unitaires** : Ajout de `DocumentTest.php` et `FichierIniTest.php` (validés via PHPUnit).
