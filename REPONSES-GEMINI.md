# Résumé des interventions Django (Mercredi 11 Mars 2026)

## Bug Fixes & Stabilité
- **Upload Avatar (Fixé)** : Utilisation de chemins absolus vers `data/utilisateurs/`. Plus d'erreur "No such file or directory".
- **Notifications Toastr** : Remplacement des messages d'erreur bruts par des notifications Toastr élégantes (Rouge pour les erreurs, Vert pour le succès).
- **Taille Upload** : Augmentation de la limite à 2 Mo pour plus de confort utilisateur.

## Améliorations UX & UI
- **Strums (Tri & Filtre)** : Ajout d'une barre d'outils sur `strum_liste.php` pour classer par Nom, Récent ou Popularité, et filtrer par mesure (4/4, 3/4, 6/8).
- **Strum Class (Update)** : La méthode `chargeStrumsBdd` gère désormais dynamiquement les tris et les clauses WHERE.

## Rappel des chantiers précédents (Aujourd'hui)
- Titre du site et fallbacks harmonisés.
- Refactoring massif des chemins relatifs (152 fichiers).
- Système de TAGS complet (BDD, Classes, Formulaires, Rendu).
- Portfolio Songbook et logos sociaux.
