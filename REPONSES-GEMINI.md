# Résumé des interventions Django (Mercredi 11 Mars 2026)

## Bug : Titre du site manquant ou incorrect sur les sous-pages
- Résolu par l'utilisation de `__DIR__` dans `params.php` et l'ajout de fallbacks.

## Audit & Refactoring Global : Chemins Relatifs Fragiles
**Problème :** 
- Environ 305 occurrences de `../` dans 152 fichiers PHP de `src/public/php/` rendaient l'application fragile aux inclusions distantes et aux déplacements de fichiers.

**Action corrective (Opération "Chemins de Fer") :**
- Refactorisation massive de tous les dossiers : `chanson`, `document`, `liens`, `media`, `navigation`, `note`, `playlist`, `songbook`, `strum`, `todo`, `utilisateur`.
- Transformation des `require`, `include` et accès fichiers (`file_exists`, `fopen`, etc.) pour utiliser `__DIR__ . "/../..."`.
- Suppression des parenthèses superflues sur les instructions d'inclusion.
- Protection des chemins clients (URLs HTML et redirections `header`) qui sont restés relatifs.

**Résultat :**
- L'application est désormais structurellement robuste. Les inclusions fonctionnent quel que soit le point d'entrée ou le niveau d'imbrication.
- Aucun bug introduit (vérifié par les smoke-tests PHPUnit).
