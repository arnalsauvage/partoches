# 🧪 Politique des Tests - Projet Partoches

La qualité et la stabilité du projet reposent sur une double stratégie de tests automatisés exécutés via **PHPUnit**. L'objectif est de garantir qu'aucune modification de code n'introduit de "fausse note" (Parse Error, bug logique ou régression visuelle).

## 🚀 1. Les Smoke Tests (Tests de Fumée)

C'est la première ligne de défense du projet. Leur rôle est de vérifier que les pages "ne fument pas" lors du chargement.

### Stratégie
- **Chargement exhaustif** : Un script parcourt automatiquement la liste des pages clés du site (46 pages actuellement).
- **Détection d'erreurs fatales** : Le test échoue immédiatement si une page renvoie une erreur 500, une `Fatal Error` PHP ou un `Warning`.
- **Conformité HTML (Linter)** : Chaque rendu HTML est passé au crible pour détecter les balises mal fermées (ex: `Unexpected end tag`), les erreurs d'imbrication ou les non-conformités W3C.

### Emplacement
- `tests/smoke/SongbookSmokeTest.php` : Liste des URLs et vérification du statut HTTP.
- `tests/smoke/HtmlLintTest.php` : Audit de la structure HTML.

## 🎼 2. Les Tests Unitaires (PHPUnit)

Ils vérifient le cœur des instruments : la logique métier pure, indépendamment de l'affichage.

### Stratégie
- **Isolation** : Test des classes et services (ex: `Chiffrement`, `Footer`, `Chanson`).
- **Validation Algorithmique** : S'assurer que les calculs, les transformations de données et les interactions avec la base de données produisent le résultat attendu.
- **Résilience** : Tester les cas limites (ex: déchiffrement d'une chaîne invalide).

### Emplacement
- `tests/*.php` : Chaque fichier correspond généralement à une classe ou un domaine fonctionnel (ex: `ChiffrementTest.php`, `FooterTest.php`).

## 🌲 3. Les Tests End-to-End (Cypress)

Ils simulent la navigation réelle d'un utilisateur dans le navigateur afin d'assurer l'étanchéité des parcours complets (User Stories).

### Stratégie
- **Parcours Métier majeurs** : Authentification modal, Inscription & Activation e-mail, Galerie Médias (restriction audio MP3), consultation des Songbooks.
- **Interactions IHM** : Validation des ouvertures/fermetures de fenêtres modales JavaScript, formulaires multi-champs et comportement responsive.
- **Non-régression visuelle** : Vérification de la conformité du Design System Canopée et de la présence des badges et notifications.

### Emplacement & Commandes
- Dossier : `cypress/e2e/` (Spécifications : `01_auth.cy.js`, `02_inscription.cy.js`, `03_medias.cy.js`, `04_songbook.cy.js`).
- **Mode Headless (CI / Console)** : `npx cypress run`
- **Mode Interactif (Dev)** : `npx cypress open`

## 🐳 4. Exécution des Tests (Où et comment lancer ?)

Le projet combine deux environnements d'exécution complémentaires :

| Type de Test | Environnement | Commande à exécuter | Rôle & Justification |
| :--- | :--- | :--- | :--- |
| **Tests PHPUnit & Smoke Tests** | **DANS Docker** (`site-partoches`) | `docker exec -i site-partoches vendor/bin/phpunit tests/` | Exécute le code PHP 8.2 et interroge MariaDB dans l'environnement exact du serveur web. |
| **Tests Cypress E2E** | **DEPUIS Windows** (Console / PowerShell) | `npx cypress run` *(Console)*<br>`npx cypress open` *(IHM graphique)* | Ouvre un vrai navigateur (Chrome/Edge/Firefox) sur Windows qui pilote le site `http://localhost:8080`. |

### Commandes utiles pour PHPUnit (dans Docker)
- **Lancer tous les tests PHPUnit** :
  ```powershell
  docker exec -i site-partoches vendor/bin/phpunit tests/
  ```
- **Lancer uniquement les Smoke Tests** :
  ```powershell
  docker exec -i site-partoches vendor/bin/phpunit tests/smoke/
  ```

### Commandes utiles pour Cypress (depuis Windows)
- **Exécution headless (console)** :
  ```powershell
  npx cypress run
  ```
- **Exécution interactive avec IHM graphique** :
  ```powershell
  npx cypress open
  ```

## 🛠️ 4. Règles de maintenance des tests

Pour que les tests restent "accordés", chaque nouveau test ou modification doit respecter ces principes :
1.  **Chemins Absolus** : Toujours utiliser `__DIR__` pour les inclusions (ex: `require_once __DIR__ . '/../src/...'`).
2.  **Sécurité de Session** : Protéger les ouvertures de session pour éviter les warnings :
    ```php
    if (session_status() === PHP_SESSION_NONE) session_start();
    ```
3.  **Protection des Constantes** : Vérifier l'existence d'une constante avant de la définir :
    ```php
    if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
    ```
4.  **Nettoyage (TearDown)** : Toujours supprimer les données de test créées en base de données après l'exécution.

---
*🎼 "Un code testé est un code qui chante vrai."* 🎷🤘✨
