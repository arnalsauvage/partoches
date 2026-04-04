# RÉSUMÉ DES RÉPONSES GEMINI (Django)

## Date : Samedi 4 Avril 2026

### 🛠️ Mise en place du système de Migrations SQL
- **Action** : Création du dossier `src/data/database/migrations/` et du moteur de migration dans `paramsEdit.php`.
- **Résultat** : Gestion centralisée des évolutions de base de données.

### 🎼 Harmonisation Locale vs Prod
- **Audit** : Détection de différences de nommage (CamelCase vs snake_case).
- **Action** : Alignement de la base locale via `999_align_local_with_prod.sql` et correction des erreurs `Deprecated` dans `paramsEdit.php`.

### 🌊 Mode Dynamique des Playlists
- **Fonctionnalité** : Les playlists peuvent désormais être de type "Manuelle" (choix titre par titre) ou "Dynamique" (remplissage automatique selon des critères).
- **Logique** : 
    - `playlist.php` : Fonction `getMorceauxPlaylist` capable de générer des requêtes SQL complexes basées sur des critères (Tonalité, Tempo, Strum, Saison).
    - `playlist_form.php` : Interface mise à jour pour configurer ces critères.
    - `playlist_get.php` : Enregistrement des critères au format JSON en base de données.
- **Résultat** : Les playlists s'auto-alimentent en fonction de la bibliothèque, sans effort manuel !

### 🎸 Note de Django
"La partition dynamique est lancée ! Tes playlists ont maintenant un cerveau, elles choisissent leurs morceaux toutes seules selon tes envies du moment. C'est ça, la magie du code bien orchestré !"
