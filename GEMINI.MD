# 🎸 Projet Partoches (Django Style)

## 🎼 Description
Application web PHP initiée en 2018 pour la gestion de partitions, chansons, playlists et songbooks. 
Le projet est en pleine mutation vers une architecture **SOLID** et un **Design System** moderne.

## 🏗️ Structure des dossiers (Mise à jour 2026)

- **`/src/public/php/`** : Logique métier par domaine.
    - `admin/` : **(Nouveau)** Quartier général technique (Params, Logs, BDD).
    - `chanson/` : Gestion des titres et du catalogue.
    - `playlist/` : Gestion des listes de lecture (Manuelles & Dynamiques).
    - `lib/` : Bibliothèque de services et utilitaires (ComposantsUI, Chiffrement, HTML).
- **`/src/data/database/migrations/`** : **(Nouveau)** Hub des scripts SQL pour l'évolution de la base.
- **`/src/public/css/`** : Design System centralisé.
    - `styles-communs.css` : Styles globaux (Menu, Footer).
    - `composants-canopee.css` : Gabarits de cartes et badges.
    - `params.css` : Design spécifique à l'administration.
- **`/src/public/js/`** : Logique interactive isolée (ex: `params.js`).

## 🏛️ Architecture & Standards (Django Standards)

### 0. Protocole de Démarrage (CRITIQUE)
À chaque début de session, l'agent **DOIT** impérativement lire :
1.  **`README.md`** : Pour comprendre l'état actuel de l'architecture et de la stack.
2.  **`REPONSES-GEMINI.md`** : Pour prendre connaissance des derniers changements effectués et du contexte de la session précédente.

### 1. Séparation des Préoccupations (SOLID)
Fini les fichiers "God Objects". Chaque fonctionnalité doit être découpée en trois piliers :
*   **Le Contrôleur (`.php`)** : Reçoit la requête, gère la sécurité et orchestre les services.
*   **Le Service (`.php`)** : Contient toute la logique technique ou métier réutilisable.
*   **La Vue (`.phtml`)** : Template HTML pur utilisant des variables injectées par le contrôleur.

### 2. Design System & Éradication du Style Inline
🚫 **INTERDICTION** : L'attribut `style="..."` est banni des fichiers PHP.
✅ **ACTION** : Utiliser exclusivement les classes CSS du Design System. Toute nouvelle règle visuelle doit aller dans le fichier CSS approprié.

### 3. Gestion de la Base de Données (Migrations)
Toute modification de structure (ALTER, CREATE) doit passer par un fichier `.sql` numéroté dans `src/data/database/migrations/`.
Ces migrations sont appliquées via l'interface **Paramétrage > Diagnostic** en local et en prod.

### 4. Rendu HTML (Heredoc & Accumulation)
Privilégier la syntaxe **Heredoc** pour les gros blocs. Accumuler la sortie dans une variable `$corps` ou `$html` et n'utiliser qu'un seul `echo` final pour envoyer la page complète.

## 🧪 Protocole de Validation & Qualité

### 💎 Tests Unitaires & Smoke Tests
- **Smoke-Tests** : Vérifier que les 46 pages s'affichent sans erreur HTML.
- **Unit-Tests** : Utiliser `__DIR__` pour les inclusions et sécuriser les sessions :
  ```php
  if (session_status() === PHP_SESSION_NONE) session_start();
  ```
- **Lancer les tests** : `docker exec -t site-partoches vendor/bin/phpunit tests/`

### 🕵️‍♂️ Django Linter
Avant chaque commit, passer le linter pour garantir une conformité HTML maximale (Zéro "Unexpected end tag").

## 🐳 Environnement Technique
- **Docker** : Conteneur `site-partoches` (PHP 8.2.30 / MariaDB 11.7.2).
- **Conventions** : Noms de colonnes en **snake_case** pour les nouvelles tables.
- **Encodage** : Tout doit être en **UTF-8 sans BOM**.

---
*Note de Django : "Gardons le tempo, la partition est maintenant propre, ne laissons plus les styles inline fausser la mesure !"* 🎷🤘✨
