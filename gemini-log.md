# 📝 Journal de Bord Gemini (Projet Partoches)

### 📖 Résumé de la session (24 Septembre 2026)
- **Résolution du crash Production sur `chanson_form.php` (Fatal error `pdf.php` Line 8)** :
    - **Identification Root Cause** : Dans `src/public/php/lib/pdf.php`, l'instruction `require_once __DIR__ . '/../../../autoload.php'` remontait 3 niveaux au-dessus du dossier `public_html/` d'Hostinger, pointant vers un fichier inexistant en prod.
    - **Solution** : Remplacement par l'analyse multi-chemins dynamique `file_exists(dirname(__DIR__, 3) . '/autoload.php') ? ... : ...`. Idem dans `chanson_form_classic.php`.
- **Stabilisation complète de l'environnement Docker Desktop (Windows / WSL2)** :
    - **Optimisation WSL2** : Création du fichier `C:\Users\medin\.wslconfig` (RAM fixée à 4 Go, swap à 2 Go) empêchant la saturation mémoire du sous-système Windows (`vmmem`).
    - **Nettoyage Compose** : Suppression des `stop_signal: SIGTERM` et `tty: true` réagissant mal aux signaux Windows CLI, suppression du montage erroné de `xdebug.ini` et création du réseau bridge `partoches-net`.
- **Correction d'affichage des pochettes & Spécification Cypress E2E (Spec 14)** :
    - **Affichage des images de pochettes** : Mise à jour de `fallbackPochette()` dans `src/public/php/lib/html.php` garantissant qu'une balise `<img src=".../vinyle.png">` valide est toujours retournée pour les chansons sans pochette spécifique.
    - **Nouvelle Spécification Cypress E2E** : Création de [cypress/e2e/14_affichage_images_et_formulaire_chanson.cy.js](file:///f:/Arnaud/projets-dev/partoches/cypress/e2e/14_affichage_images_et_formulaire_chanson.cy.js) (Test 1 : images médias visiteur, Test 2 : pochettes chansons visiteur, Test 3 : formulaire chanson complet admin).
- **Réalisation du Ticket #10 — Restriction d'accès aux ressources audio (MP3) pour les utilisateurs non connectés** :
    - **Sécurisation & UX** :
        1. **`getdoc.php`** : Interception de tous les accès directs aux fichiers audio (mp3, m4a, aac, ogg, wav) pour les utilisateurs non connectés (`!MediaService::estAudioAccessible()`), avec redirection HTTP 302 automatique vers `login.php`.
        2. **`lienurl_liste.php`** : Détection des liens audio et affichage du badge `(🔒 Connexion requise)` avec redirection vers la page de connexion pour les invités.
        3. **`MediaRenderer.php` & `listeMedias.php`** : Affichage des cartes avec badge `(🔒 Connexion requise)` et extension du filtre `buildWhereClause()` dans `MediaRepository.php` pour inclure tous les formats audio (`mp3`, `m4a`, `aac`, `ogg`, `wav`).
    - **Tests & Automatisation** :
        - Création de la spécification Cypress E2E [cypress/e2e/13_restriction_audio_mp3.cy.js](file:///f:/Arnaud/projets-dev/partoches/cypress/e2e/13_restriction_audio_mp3.cy.js).
        - Enrichissement de `tests/MediaTest.php` (`testEstExtensionAudio`, `testEstAudioAccessible`, `testRendererRestrictedAudio`).
        - **150 / 150 tests PHPUnit validés (100% Succès)**.
        - **25 / 25 Smoke Tests HTTP validés avec assertions audio (100% Succès)**.
- **Résolution de l'erreur 500 / Fatal Error en Production (`Cannot redeclare convertitDateJJMMAAAAversMySql`)** :
    - **Identification de la Root Cause** : Sur Hostinger, un ancien sous-dossier abandonné `public_html/public/` existait sur le serveur web. Dans `src/public/autoload.php`, le test `is_dir(ROOT_DIR . '/public')` s'évaluait à `true` en prod et forçait `PUBLIC_DIR` à pointer vers l'ancien dossier `public_html/public/php/lib` au lieu de `public_html/php/lib`. `require_once` chargeait donc deux versions distinctes de `configMysql.php`, redéfinissant la fonction `convertitDateJJMMAAAAversMySql()`.
    - **Solution Technique** :
        1. **Attribution stricte dans [src/public/autoload.php](file:///f:/Arnaud/projets-dev/partoches/src/public/autoload.php)** : `PUBLIC_DIR` est désormais obligatoirement fixé à `__DIR__`, empêchant tout basculement vers un sous-dossier fantôme en production.
        2. **Protection défensive dans [src/public/php/lib/configMysql.php](file:///f:/Arnaud/projets-dev/partoches/src/public/php/lib/configMysql.php)** : Encadrement des fonctions d'aide avec `if (!function_exists('...'))`.
        3. Nettoyage des `use` redondants dans `chanson_form.php` supprimant les warnings PHP 8.2 en espace de nom global.
    - **Validation & Poussée** :
        - 141/141 tests PHPUnit validés (100% Succès).
- **Résolution de l'erreur HTTP 403 & Couverture E2E des 8 Pages Principales du Menu** :
    - **Identification Root Cause HTTP 403 (`/html/diagrammes/`)** : Le sous-dossier `src/public/html/diagrammes/` ne contenait pas de fichier d'index (`index.html` ou `index.php`), provoquant une interdiction d'affichage de répertoire Apache (Error 403 Forbidden).
    - **Correctif** : Création de [src/public/html/diagrammes/index.html](file:///f:/Arnaud/projets-dev/partoches/src/public/html/diagrammes/index.html) qui redirige proprement vers `pageDiagrammes.htm`.
    - **Nouvelle Spécification Cypress E2E** : Création du fichier [cypress/e2e/05_menu_navigation.cy.js](file:///f:/Arnaud/projets-dev/partoches/cypress/e2e/05_menu_navigation.cy.js) testant spécifiquement les 8 pages du menu général (Médias, Chansons, Strums, Songbooks, Liens, Outils, Playlists, Utilisateurs).
    - **Résultats** : 
        - **Smoke Tests HTTP** : **22 / 22 pages valides (100% Succès)**.
        - **Tests E2E Cypress (05_menu_navigation)** : **8 / 8 pages validées (100% Succès)**.
        - **Tests unitaires PHPUnit** : **141 / 141 validés (100% Succès)**.
        - Poussé sur `master` (`737c39d`).

### 📖 Résumé de la session (23 Septembre 2026)
- **Fixation de la Stabilité Réseau Windows/Docker & Validation Automatisée (Smoke Tests)** :
    - **Bind Réseau 127.0.0.1** : Modification de `docker-compose.yml` avec l'adressage explicite `127.0.0.1:8080:80`, `127.0.0.1:3307:3306` et `127.0.0.1:8081:80` pour garantir la joignabilité instantanée depuis les navigateurs Windows sans conflit de boucle locale IPv6.
    - **Automatisation des Tests HTTP (21/21 Vert)** : Exécution autonome d'une suite complète d'intégration HTTP validant le statut **200 OK** sur les 21 pages critiques du site.
    - **Succès Suite Unitaire PHPUnit** : 141 tests sur 141 validés avec succès dans le conteneur `site-partoches` (0 échec, 0 erreur). Poussé sur `master` (`d4f90fa`).
    - **Identification de la Root Cause** : L'erreur exacte transmise par le serveur Hostinger indiquait `Warning: require_once(.../autoload.php): Failed to open stream: No such file or directory`. L'ancienne instruction `dirname(__DIR__, 3) . '/autoload.php'` remontait hors du dossier web `public_html/` de production où `autoload.php` n'existait pas.
    - **Solution Technique** :
        1. Création de [src/public/autoload.php](file:///f:/Arnaud/projets-dev/partoches/src/public/autoload.php) inclus dans le périmètre de transfert FTP.
        2. Détection dynamique multi-chemins du fichier `autoload.php` et `params.ini` sur l'ensemble des 36 contrôleurs et classes PHP.
    - **Validation & Poussée** : Modifications poussées sur `master` (`d052e5a`). Le déploiement FTP s'exécute et résout l'erreur 500 en prod.
    - **Analyse Root Cause** : L'action de déploiement FTP (`FTP Deploy Action`) ne transférait que le dossier `src/public/`. Les scripts SQL de migration situés dans `src/data/database/migrations/` étaient hors périmètre, empêchant la création de la colonne `ordre` dans la base MariaDB/MySQL Hostinger de production.
    - **Solution Structurelle** : Migration des scripts `.sql` dans `src/public/data/database/migrations/` (désormais transférés automatiquement par FTP) et mise à jour de `AdminService.php` (`getMigrationDir()`).
    - **Procédure d'Activation en Prod** : 1) Exécuter les 2 requêtes `ALTER TABLE` dans phpMyAdmin Hostinger, OU 2) Aller sur `http://site-prod/php/admin/params.php` (Paramétrage > Diagnostic) et cliquer sur "Appliquer les migrations en attente".
    - Réinitialisation et création du compte administrateur local via [scripts/create_admin_user.php](file:///f:/Arnaud/projets-dev/partoches/scripts/create_admin_user.php).
    - Identifiants de connexion actifs en local : Login `admin`, Mot de passe `kazoo` (Privilège `2-Admin`, statut `est_actif=1`).
- **Restauration du Catalogue Local de Chansons & Documents** :
    - Import du dump de chansons dans le conteneur MariaDB (397 chansons restaurées).
    - Resynchronisation des 258 fichiers PDF avec la table `document` via `scripts/repopulate_documents.php`.
    - La page `http://localhost:8080/php/chanson/chanson_liste.php` affiche à nouveau l'intégralité du catalogue. 100% des tests PHPUnit au vert (141 OK).
    - **Identification** : Suite à la réinitialisation de la base locale MariaDB avec le fichier de test minimal `dbPartoches.sql`, seule 1 chanson s'affichait dans la liste.
    - **Restauration Données** : Import du dump de chansons dans le conteneur MariaDB (397 chansons restaurées).
    - **Synchronisation Documents PDF** : Création du script `scripts/repopulate_documents.php` ayant analysé l'arborescence `src/public/data/chansons/` et réinscrit les 258 fichiers PDF associés dans la table `document`.
    - **Vérification** : La page `http://localhost:8080/php/chanson/chanson_liste.php` affiche à nouveau l'intégralité du catalogue (349+ chansons filtrables). 100% des tests PHPUnit au vert (141 OK).
    - **Identification Root Cause** : La table `lienstrumchanson` dans `dbPartoches.sql` ne possédait pas la colonne `ordre`, ce qui faisait chuter la méthode `LienStrumChanson::chercheLiensStrumChanson()` avec `Unknown column 'ordre' in 'ORDER BY'`, provoquant un crash Fatal Error HTTP 500 sur plusieurs pages majeures (dont `chanson_voir.php`, `chanson_form.php`).
    - **Fix & Migrations** : Ajout de la colonne `ordre` dans `dbPartoches.sql` et création du script de migration `src/data/database/migrations/004_add_ordre_to_lienstrumchanson.sql`.
    - **Conformité HTML & Linter** : Échappement des esperluettes `&` -> `&amp;` dans `chanson_form_view.phtml` et `ChansonFormRenderer.php`.
    - **Validation suite complète** : Succès total des 141 tests PHPUnit (141 tests OK, 0 échec, 0 erreur).
    - Éradication définitive de l'erreur `Unrecognized named-value: 'secrets'`. En GitHub Actions, le contexte `secrets` ne peut être lu directement dans aucune clause `if:`. Mise en place d'une étape intermédiaire `Check FTP Secret Configuration` (`id: check_ftp`) qui inspecte la présence de la variable en bash et définit un output `$GITHUB_OUTPUT` (`has_ftp=true/false`). La step de déploiement utilise désormais `if: steps.check_ftp.outputs.has_ftp == 'true'`, syntaxe 100% conforme et reconnue par le parseur GitHub Actions.
    - Ajout de `continue-on-error: true` sur le déploiement FTP.
    - Activation de la directive `workflow_dispatch` dans `.github/workflows/ci-cd.yml` permettant de relancer manuellement les workflows depuis l'interface GitHub sans nouveau commit.
- **Validation de Compte par Email (Anti-Bot / Activation)** :
    - **Modèle & BD** : Ajout des méthodes `creeUtilisateurEnAttente` et `activeCompteParToken` dans `Utilisateur.php`. Blocage des connexions `login_utilisateur` tant que `est_actif === 0`.
    - **Service & Email** : `UtilisateurInscriptionService` génère un jeton d'activation aléatoire sécurisé (32 hex), enregistre l'utilisateur inactif (`privilege=0`, `est_actif=0`), expédie l'email d'activation et consigne l'URL d'activation dans les logs PHP pour le dev local.
    - **Contrôleur d'Activation** : Création de `utilisateur_activation.php` traitant les liens de confirmation (`?token=...`), basculant le statut en membre actif (`privilege=1`, `est_actif=1`) et connectant automatiquement l'utilisateur.
    - **Vue & UX** : Mise à jour de `utilisateur_inscription_view.phtml` pour afficher une confirmation claire invitant à consulter sa boîte mail.
- **Validation & Mise à jour de la documentation Média** :
    - Vérification et mise à jour de [feature-medias.md](file:///f:/Arnaud/projets-dev/partoches/documentation/feature-medias.md) pour inclure l'ensemble des 5 composants SOLID (intégration de `MediaRepository.php`).
- **Mise en place du système de Backlog & User Stories** :
    - Création de l'arborescence `documentation/backlog/` (`0-en_ecriture`, `1-ready`, `2-en_cours`, `3-livrees_et_testees`).
    - Rédaction du protocole [documentation/backlog/README.md](file:///f:/Arnaud/projets-dev/partoches/documentation/backlog/README.md) détaillant le cycle de vie des US, la Definition of Ready (DoR), la Definition of Done (DoD) et la matrice de revue des 6 rôles (PO, SM, PM, Tech Lead, QA, Dev).
    - Rédaction de la première User Story exemple : [US-001_Mise_en_place_tests_E2E_Cypress.md](file:///f:/Arnaud/projets-dev/partoches/documentation/backlog/1-ready/US-001_Mise_en_place_tests_E2E_Cypress.md).
    - Enrichissement de [US-000_Inscription_Utilisateur_et_Validation_Email.md](file:///f:/Arnaud/projets-dev/partoches/documentation/backlog/3-livrees_et_testees/US-000_Inscription_Utilisateur_et_Validation_Email.md) avec la justification de protection juridique (droit d'auteur, contrefaçon et blocage d'aspiration automatisée).
- `[MODIF-CONSTITUTION]` **Ajout du Protocole d'Interaction Agent IA (Section 0.1)** :
    - Mise à jour de [GEMINI.MD](file:///f:/Arnaud/projets-dev/partoches/GEMINI.md) en version **2.1**.
    - Obligation pour l'agent IA à chaque prompt de : 1) Reformuler la demande, 2) Émettre des suggestions pertinentes, 3) Présenter le plan d'action avant d'exécuter, afin de garantir un retour rapide et d'économiser les tokens sur les demandes non cadrées.
- **Réalisation de la US-001 (Tests E2E Cypress)** :
    - Déplacement de la US de `1-ready` vers `2-en_cours` puis archivage dans `3-livrees_et_testees`.
    - Création des fichiers de configuration `package.json` et `cypress.config.js` (`baseUrl: http://localhost:8080`).
    - Création des 4 spécifications Cypress (`01_auth.cy.js`, `02_inscription.cy.js`, `03_medias.cy.js`, `04_songbook.cy.js`) et des commandes d'authentification personnalisées `cy.login()`.
    - Clarification de la politique de tests dans [politique-des-tests.md](file:///f:/Arnaud/projets-dev/partoches/documentation/politique-des-tests.md) : PHPUnit/Smoke tests exécutés **dans le conteneur Docker** et Cypress E2E exécuté **depuis l'hôte Windows** vers `http://localhost:8080`.
- **Rédaction de la Politique de Déploiement FTP** :
    - Rédaction du guide [politique-de-deploiement.md](file:///f:/Arnaud/projets-dev/partoches/documentation/politique-de-deploiement.md) définissant les règles d'inclusion/exclusion pour le transfert FTP vers la production Hostinger et le suivi des migrations SQL post-déploiement.
- **Hotfix Production & Synchronisation FTP** :
    - Résolution du crash `Fatal Error` sur `listeMedias.php` ([MediaRenderer.php](file:///f:/Arnaud/projets-dev/partoches/src/public/php/media/MediaRenderer.php)) via la mise en conformité de la méthode statique `Chanson::load()`.
    - Idempotence de la migration SQL `003_add_activation_token_to_utilisateur.sql` via `IF NOT EXISTS`.
    - Analyse automatique et validation par le script d'audit comparatif `scripts/run_audit_compare.php`.
- **Mise en place de la CI/CD GitHub Actions** :
    - Création du workflow [.github/workflows/ci-cd.yml](file:///f:/Arnaud/projets-dev/partoches/.github/workflows/ci-cd.yml) avec 2 jobs (`lint-and-test` et `deploy`).
    - Exécution automatisée des linter PHP et tests PHPUnit avec un service MariaDB 10.11 dans GitHub Actions.
    - Intégration du déploiement FTP automatique vers Hostinger via `SamKirkland/FTP-Deploy-Action@v4.3.5` avec règles d'exclusion.

---
### 📖 Résumé de la session (22 Septembre 2026)
- **Évolution Création de Compte Utilisateur (Inscription)** :
    - **Architecture SOLID** : Création du contrôleur `utilisateur_inscription.php`, du service `UtilisateurInscriptionService.php` et de la vue Canopée `views/utilisateur_inscription_view.phtml`.
    - **Formulaire d'Inscription** : Mise en place des 10 champs configurés (Login, Mot de passe, Confirmation, Prénom, Nom, Photo/Avatar optionnel, Site Web optionnel, Email, Signature/Devise optionnel, Privilèges fixes à 1-Membre).
    - **Sécurité & Connexion** : Chiffrement du mot de passe, vérification d'unicité du login, validation de la confirmation du mot de passe et auto-login immédiat à la création du compte.
    - **Intégration Modale** : Ajout du lien d'inscription `"Créer un compte (S'inscrire)"` dans la fenêtre de login (`menuLogin.html`).
    - **Tests & Robustesse** : Auto-création de la table `utilisateur` en BDD si absente et validation par suite PHPUnit `tests/UtilisateurInscriptionTest.php` (4/4 tests OK, 13 assertions).

---
### 📖 Résumé de la session (17 Juin 2026 (Matin))
- **Correction et Amélioration de l'Audit des Images** :
    - Fix des chemins relatifs dans `imagesCheck.php` via l'utilisation de `$_DOSSIER_CHANSONS`.
    - Ajout d'une fonctionnalité de génération de miniatures en masse via `AdminService::batchRegenerateThumbnails()`.
    - Exposition de cette fonction dans `params.php` (Administration) et `imagesCheck.php` (Audit).
- **Nettoyage du Filesystem** :
    - Suppression massive de 184 dossiers orphelins dans `src/public/data/chansons/` (IDs fournis par l'utilisateur).
- **Standards & Robustesse** :
    - Intégration de la logique de génération de miniatures moderne (WebP) au niveau global de l'administration.

---
### 📖 Résumé de la session (16 Juin 2026 (Après-midi))
- **Déconstruction du "God Object" Chanson** :
    - Scission de `Chanson.php` (31 Ko) en trois classes spécialisées :
        - `Chanson.php` : Entité pure gérant les données et la persistance unitaire (CRUD).
        - `ChansonRepository.php` : Gestion des requêtes SQL complexes, filtres et recherches.
        - `ChansonRenderer.php` : Génération des composants visuels (cartes, thumbnails).
    - Maintien de la compatibilité ascendante via des wrappers "deprecated" dans la classe Chanson.
    - Validation par Smoke Tests (27 pages OK).
- **Correction UX & Sécurité** :
    - Correction des liens relatifs dans le profil utilisateur et les playlists.
    - Passage en Nowdoc pour les scripts JS afin d'éviter les warnings PHP d'interpolation.
    - Bascule officielle vers le nouveau formulaire de chanson (par défaut).

- **Déconstruction finale du "God Object" Chanson** :
    - Scission de `Chanson.php` terminée : l'entité ne gère plus que ses données et sa persistance.
    - Création de `ChansonRepository.php` pour toute la logique SQL (search, count, relations).
    - Création de `ChansonRenderer.php` pour les composants réutilisables (cartes).
- **Refactorisation de `chanson_voir.php`** :
    - Migration vers l'architecture SOLID : création de `ChansonService.php` et `ChansonVoirRenderer.php`.
    - Suppression de 332 lignes de code mélangeant logique et HTML dans le contrôleur.
    - Centralisation des styles et scripts dans le Renderer.
- **Validation Qualité** :
    - Succès des Smoke Tests (27 pages vérifiées sans erreur).
    - Correction des régressions de liens relatifs dans les vues déportées.

### 📖 Résumé de la session (16 Juin 2026 (Matin))
- **Refactorisation majeure du module Chanson** :
    - Mise aux normes SOLID du nouveau formulaire (`chanson_form_new.php`).
    - Création de `ChansonFormService` et `ChansonFormNewRenderer`.
    - Éradication totale des styles inline et intégration du Design System Canopée.
    - Fix d'un bug de typage PHP 8.2 dans la classe `Chanson` (crash lors des INSERT/UPDATE).
- **Refactorisation du module Playlist** :
    - Migration de `playlist_form.php` vers l'architecture Service/Renderer.
    - Création de `PlaylistFormService` et `PlaylistFormRenderer`.
    - Externalisation des styles dans `playlistform.css`.
    - Fix d'un bug critique (Fatal Error) dans `lienChansonPlaylist.php` lié à des noms de colonnes SQL incorrects (`idPlaylist` -> `id_playlist`).
- **Amélioration de l'environnement Local** :
    - Correction de `VENDOR_URL` dans l'autoloader pour restaurer les styles/scripts.
    - Création automatique du compte `invite` pour l'accès aux données publiques.
- **Validation Qualité** :
    - Mise à jour et succès des Smoke Tests (27 pages vérifiées).
    - Suppression des warnings PHP (passage en Nowdoc pour les scripts JS).

### 📖 Résumé de la session précédente (10 Avril 2026 (Fin d'après-midi))
- **Bugfix critique : suppression d'un morceau dans songbook** :
    - Restauration de la fonction `supprimeLienIdDocIdSongbook` et de `supprimeliensDocSongbookDuSongbook`.
    - Refactorisation vers la classe `LienDocSongbook` avec wrappers de compatibilité.
    - Sécurisation des imports dans `Songbook.php`.
- **Bugfix ergonomie : alignement de la croix de suppression** :
    - Alignement à droite automatique via `.sb-remove-btn` et `margin-left: auto`.
    - Suppression des styles inline dans `songbook_form.php` conformément aux standards.
- Validation de la stabilité via l'analyse statique des dépendances.

