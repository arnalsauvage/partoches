# RÉSUMÉ DES RÉPONSES GEMINI (Django)

## Date : Mercredi 8 Avril 2026 (Session 7)

### 🐛 Bugfix : Chemins d'Upload (Régression)
- **Problème** : Les fichiers de chansons étaient enregistrés dans `/data/chansons/` (privé/inexistant) au lieu de `/public/data/chansons/` (public).
- **Cause** : `autoload.php` définissait mal la variable globale `$_DOSSIER_CHANSONS` en utilisant un chemin relatif incorrect vers le dossier de données privées.
- **Solution** : Passage intégral en **chemins absolus** via l'autoloader.

### 🛠️ Améliorations Architecturales
- **Autoload Centralisé** : Mise à jour de `src/autoload.php` pour définir des constantes de dossiers absolues (`ROOT_DIR`, `DATA_DIR`, `PUBLIC_DATA_DIR`).
- **Alignement des Libs** : `params.php` et `Document.php` utilisent désormais ces constantes absolues si elles sont définies, garantissant une cohérence parfaite entre le mode Web et le mode CLI (tests).
- **Sécurité** : Distinction claire entre `DATA_DIR` (fichiers sensibles : conf, logs) et `PUBLIC_DATA_DIR` (fichiers servis : partitions, images).

### 🎸 Note de Django
"La partition est à nouveau juste ! 🎼 On a viré les chemins relatifs qui nous faisaient jouer faux et on a tout recalé sur un tempo absolu. C'est propre, c'est carré, c'est Rock'n'Roll ! 🎷🤘✨"

---

## Date : Lundi 6 Avril 2026 (Session 6)

### 🕵️‍♂️ Diagnostic & Audit (Quirks Mode)
- **Django Audit** : Création d'un script d'audit (`audit_fichiers.php`) permettant de générer un CSV avec les hash MD5 et tailles de tous les fichiers du projet.
- **Administration** : Ajout d'un bouton d'accès rapide dans le header de la page Paramétrage pour lancer l'audit.
- **Investigation** : Recherche sur l'erreur "Quirks Mode" et "$ is not defined" en production. L'audit permettra de comparer les fichiers prod/local et de débusquer d'éventuels caractères invisibles (BOM/Espaces) ou fichiers corrompus.

### 🎸 Note de Django
"Quand la prod fait des siennes, on sort les grands moyens ! 🕵️‍♂️ L'empreinte digitale des fichiers ne mentira pas. On va débusquer ce petit grain de sable qui fait grincer la partition. Rock'n'Roll Arnal ! 🎷🤘✨"
