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

## 🐳 3. Exécution des Tests

Tous les tests doivent être lancés à l'intérieur du conteneur Docker pour garantir la parité des environnements.

### Commandes utiles
- **Lancer toute la suite (Recommandé)** :
  ```powershell
  docker exec -t site-partoches vendor/bin/phpunit tests/
  ```
- **Lancer uniquement les smoke tests (Rapide)** :
  ```powershell
  docker exec -t site-partoches vendor/bin/phpunit tests/smoke/
  ```
- **Lancer un test spécifique** :
  ```powershell
  docker exec -t site-partoches vendor/bin/phpunit tests/ChiffrementTest.php
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
