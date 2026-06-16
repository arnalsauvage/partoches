# Documentation de la Feature : Gestion des Exercices

Ce document est la source de vérité décrivant l'implémentation de la bibliothèque d'exercices, un pilier de l'écosystème pédagogique de l'application Ateliers Canopée.

## 1. Présentation du concept "Exercices"

La fonctionnalité "Exercices" offre une bibliothèque de supports pédagogiques réutilisables et structurés.

- **Rôle dans l'écosystème** : Les exercices sont des entités autonomes qui peuvent être organisées par **catégories** (ex: "Rythmique", "Accords", "Théorie"). Ils sont ensuite associés à un ou plusieurs **ateliers** pour former le programme d'une séance. Cette structure many-to-many permet une grande flexibilité dans la création des cours.

- **Différence Exercice vs. Progression** : Il est crucial de distinguer :
    1.  L'**Exercice** : L'entité de base, réutilisable et unique dans la bibliothèque, contenant le matériel pédagogique (créé par l'Admin).
    2.  La **Progression** : Le suivi individuel de l'élève sur un exercice donné. C'est un enregistrement qui lie un utilisateur à un exercice via une **note de 0 à 5**, matérialisant son niveau de maîtrise personnel.

- **Format de contenu** : Le corps d'un exercice est rédigé en **Markdown**, permettant un formatage riche (gras, listes, titres). Le système supporte également l'association d'une **image d'illustration** à chaque exercice pour un appui visuel.

## 2. User Stories (Implémentées)

Voici les fonctionnalités actuellement codées, priorisées par valeur métier.

- **feat-exercices-01 (Élève)** : En tant qu'élève, je veux consulter la liste des exercices associés à un atelier afin de savoir quoi travailler après le cours.
- **feat-exercices-02 (Élève)** : En tant qu'élève, je veux m'auto-évaluer sur une échelle de 1 à 5 pour chaque exercice afin de suivre visuellement ma progression.
    - **Critères d'Acceptance** :
        - Un groupe de 5 boutons cliquables est présent pour chaque exercice.
        - La note actuelle est visuellement distincte (bouton "actif").
        - Un clic sur une note déclenche un appel API pour sauvegarder la progression sans recharger la page.
        - Le composant de notation est indisponible pour les visiteurs non connectés.
- **feat-exercices-03 (Élève)** : En tant qu'élève, je veux ouvrir le détail d'un exercice en plein écran afin de me concentrer sur les instructions pédagogiques.
- **feat-exercices-04 (Admin)** : En tant qu'admin, je veux créer et éditer des exercices dans une bibliothèque centrale afin de les réutiliser dans différents ateliers.
- **feat-exercices-05 (Admin)** : En tant qu'admin, je veux créer des catégories d'exercices à la volée depuis l'interface de gestion afin d'organiser ma bibliothèque sans quitter le formulaire.
- **feat-exercices-06 (Admin)** : En tant qu'admin, je veux uploader une image pour un exercice afin d'illustrer visuellement le concept technique.
- **feat-exercices-07 (Admin)** : En tant qu'admin, je veux cloner un exercice existant afin de créer rapidement des variantes sans tout ressaisir.
- **feat-exercices-08 (Admin)** : En tant qu'admin, je veux lier/délier des exercices à un atelier via une liste de cases à cocher afin de préparer ou modifier le programme d'une séance.

## 3. Implémentation technique

### Base de données

- **Table `exercices`** :
    - `id`, `nom`, `description_courte`, `contenu` (TEXT), `image` (VARCHAR), `categorie_id`, `created_at`.
- **Table `categories_exercices`** :
    - `id`, `nom`.
- **Table `atelier_exercices`** :
    - `atelier_id` (FK -> ateliers.id ON DELETE CASCADE)
    - `exercice_id` (FK -> exercices.id ON DELETE CASCADE)
    - `PRIMARY KEY (atelier_id, exercice_id)`
- **Table `user_exercices_progress`** :
    - `user_id` (FK -> users.id ON DELETE CASCADE)
    - `exercice_id` (FK -> exercices.id ON DELETE CASCADE)
    - `note` (INT, DEFAULT 0) : Contrainte métier (0-5) gérée côté backend.
    - `updated_at` (TIMESTAMP)
    - `PRIMARY KEY (user_id, exercice_id)` : Permet l'utilisation de `INSERT ... ON DUPLICATE KEY UPDATE`.

### Backend (PHP)

- **Classe `classes/Exercice.php`** :
    - Gère le CRUD complet pour les exercices (`create`, `read`, `readOne`, `update`, `delete`) et les catégories (`createCategory`, `readCategories`).
    - `cloneExercice($id)` : Duplique un enregistrement.
    - `getByAtelier($atelier_id, $user_id)` : Récupère les exercices d'un atelier en joignant la note de l'utilisateur.
    - `linkToAtelier($atelier_id, $exercice_ids)` : Synchronise les liaisons pour un atelier.
- **Classe `classes/Progression.php`** :
    - `updateNote($user_id, $exercice_id, $note)` : Gère la logique de sauvegarde de la note. Valide la note (0-5) et utilise une requête `INSERT ... ON DUPLICATE KEY UPDATE` pour une performance optimale.
- **API Exercices (`public/api/exercices/`)** :
    *   `read_exercices.php` : Liste globale ou par saison (via `?saison=X`). Supporte l'action `read_categories` pour alimenter les filtres. Gère l'enrichissement des URLs d'images via le système de fichiers.
    *   `read_one_exercice.php` : Détails complets d'un exercice, incluant les fichiers joints et les ateliers liés.
    *   `create_exercice.php` / `update_exercice.php` : Gestion administrative (POST). Gère la synchronisation des liaisons avec les chansons (`chanson_ids_json`) et les fichiers (`attached_file_ids_json`).
    *   `delete_exercice.php` / `clone_exercice.php` : Actions administratives.
    *   `update_progress.php` : Enregistre la note d'auto-évaluation (1-5). Déclenche un taggage automatique si c'est la première fois que l'exercice est évalué.
    *   `get_exercice_chansons.php` : Récupère les chansons liées à un exercice spécifique.
    *   `link_exercice_atelier.php` / `unlink_exercice_atelier.php` : Gestion granulaire des associations avec les ateliers.
- **Sécurité** : Les APIs de modification (create, update, delete, link) sont protégées et requièrent un rôle 'admin'. Le contenu Markdown est épuré côté client par la librairie de rendu.

### Frontend

- **Vues PHP (`includes/modals.php`)** :
    - `#exercicesManagerModal` : Modale admin pour le CRUD de la bibliothèque d'exercices.
    - `#exerciceDetailModal` : Modale pour afficher un exercice en détail.
    - `#linkExercicesModal` : Modale admin pour lier les exercices à un atelier.
    - L'affichage des exercices est intégré dans `#atelierDetailsModal` et la vue "Focus" de l'atelier.
- **Modules JS** :
    - `public/assets/js/modules/exercices.js` : Le module `App.Exercices` centralise toute la logique client :
        - Fonctions de lecture (`loadAll`, `loadForAtelier`, `showDetail`).
        - Fonctions de modification (`save`, `delete`, `clone`, `createCategory`).
        - `updateNote` pour l'auto-évaluation.
- **CSS spécifiques** :
    - Les classes `.btn-warning` et `.active` sont utilisées dans la fonction `renderNotePicker` pour créer le composant de notation par étoiles/boutons.
    - Le rendu du Markdown est stylisé via le CSS global pour les balises `h1`, `p`, `ul`, `code`, etc.
- **Dépendances** :
    - **`marked.js`** : Une dépendance externe est nécessaire pour le rendu du Markdown. Elle est appelée dans la fonction `App.Exercices.showDetail`.

## 4. Points de vigilance spécifiques

- **Gestion de l'upload d'images** : L'upload d'images pour les exercices est géré via un `FormData` et traité par les API PHP (`create_exercice.php`, `update_exercice.php`). Aucune conversion WebP n'est actuellement en place (US.15 non implémentée pour cette feature).
- **Performance** : La méthode `getByAtelier` de la classe `Exercice` effectue une sous-requête pour calculer `user_history_count`. Sur de très grands volumes, cela pourrait être un point à surveiller, mais l'impact est actuellement négligeable.
- **UX Admin** : L'association exercices/atelier se fait via une longue liste de cases à cocher dans la modale `#addAtelierModal`. Pour une bibliothèque très fournie, un champ de recherche/filtrage serait une amélioration pertinente.