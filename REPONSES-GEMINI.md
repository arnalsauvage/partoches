# Résumé des interventions de Django - 04/03/2026

## Amélioration de la page des paramètres (paramsEdit.php)
- **Nouveaux Onglets** :
    - **Logs** : Visualisation en direct des fichiers de log du dossier `/logs/` (via Ajax).
    - **Console SQL** : Exécution de requêtes SQL directement sur la base avec affichage des résultats en tableau Bootstrap.
    - **Système** : Affichage des versions PHP/MySQL, poids de la base de données et taille du dossier des chansons.
- **Gestion du mode Debug** :
    - Ajout d'une option "Afficher les erreurs PHP" dans l'onglet Général.
    - Modification de `php/lib/configMysql.php` pour appliquer dynamiquement `ini_set('display_errors', 1)` si l'option est activée.
- **Nettoyage** :
    - Retrait du code d'auto-migration temporaire pour la colonne `publication`.
