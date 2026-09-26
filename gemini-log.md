# 📝 Journal de Bord Gemini (Projet Partoches)

### 📖 Résumé de la session (26 Septembre 2026 - Matin)
- **🐛 Résolution du Bug de Nommage des Fichiers Uploadés (`-v.ext` au lieu de `-v1.ext`)** :
    - **Demande PO & Directive d'Excellence** : Rejet catégorique des indices numériques (`$doc[4]`, `$doc[1]`) et de toute solution de compromis "double compatibilité". Passage intégral à une version propre, lisible et maintenable utilisant les noms explicites d'attributs (`$doc['nom']`, `$doc['version']`, `$doc['id']`, `$doc['nomTable']`, `$doc['idTable']`).
    - **Identification Root Cause** : Suite au passage récent de `Document::chercheDocument()` et `chercheDocumentNomTableId()` en `fetch_assoc()` pour sécuriser l'ordre des colonnes BDD, les scripts appelants (`chanson_upload.php`, `songbook_upload.php`, `Document.php`, `getdoc.php`) continuaient de lire l'indice numérique `$doc[4]`. Celui-ci valait `null`, provoquant la génération de noms de fichiers physiques tronqués sans numéro de version (`-v.mp3`, `-v.pdf`).
    - **Refactorisation & Éradication des Indices Numériques** :
        - `Document.php` : Utilisation stricte de `fetch_assoc()`, renommage et incrémentation de version basés sur `$resultat['version']`, `$resultat['nom']`, etc.
        - `Document::composeNomVersion($nom, $version = 1)` : Ajout d'un garde-fou strict avec repli par défaut sur `1` si `$version` est vide ou null (impossible désormais de produire `-v.ext`).
        - `chanson_upload.php` & `songbook_upload.php` : Migration vers `$doc['nom']` et `Document::composeNomVersion($name_file, $doc['version'])`.
        - `getdoc.php` : Migration vers `$doc['nom']`, `$doc['version']`, `$doc['nomTable']`, `$doc['idTable']`.
        - `ChansonFormRenderer.php`, `ChansonService.php`, `views/chanson_voir_view.phtml`, `documentChercheAjax.php`, `Songbook.php`, `playlist.php` : Remplacement complet des lectures par indices (`$f[1]`, `$f[4]`) par les noms d'attributs explicites.
    - **Nettoyage & Refus de Code Verrue** :
        - Le PO ayant corrigé les deux noms de fichiers directement en FTP sur Hostinger, tout code d'auto-guérison ponctuel a été immédiatement retiré de `Document.php` et `getdoc.php` pour préserver un code source 100% pur, sans béquille ni dette technique.
    - **Modernisation de la Popin de Connexion (`menuLogin.html` & `styles-communs.css`)** :
        - **Mise en page des liens** : Séparation propre de *"Créer un compte gratuitement"* et *"oubli de mot de passe"* sur deux lignes distinctes (flexbox en colonne avec espacement équilibré), éliminant tout retour à la ligne accidentel.
        - **Design Canopée & Esthétique** :
            - Carte moderne avec bandeau supérieur d'accent boisé (`border-top: 4px solid var(--c-accent)`), ombre portée douce et coins arrondis (10px).
            - Ajout d'un en-tête sobre avec icône cadenas et croix de fermeture `&times;` (en plus du lien de fermeture inférieur).
            - Nettoyage des vieux champs de saisie (éradication des ombres roses/grises `box-shadow inset` et de la bordure rose héritée d'`index.css`) au profit d'inputs sobres et lumineux avec focus ring doré/marron.
            - Remplacement du bouton bleu brut *"Ok"* par un bouton chaleureux Canopée en dégradé de marron chaud avec le libellé explicite **"Se connecter"**.
            - Mise en valeur du lien *"Créer un compte gratuitement"* sous forme de pastille interactive discrète.
        - **Nettoyage architectural** :
            - Centralisation complète de `.contenu_popup` dans `styles-communs.css` (chargé sur 100% des pages du site).
            - Suppression des règles redondantes et obsolètes de `index.css`.
            - Zero style inline (`style="..."`).
    - **Validation & Couverture de Tests** :
        - `DocumentTest.php` : Migration des mocks vers `fetch_assoc`, tests validant le fallback `composeNomVersion(..., null)` (4/4 tests OK, 9 assertions).
        - Smoke Tests : **28 / 28 pages vérifiées avec succès (100%)**.

### 📖 Résumé de la session (26 Septembre 2026 - Nuit)
- **🔥 Résolution de l'incident critique de production & alignement architectural** :
    - **Identification Root Cause du 404 généralisé** : Une règle de réécriture Apache (`RewriteCond %{REQUEST_URI} !^/public/` / `RewriteRule ^(.*)$ public/$1`) avait été injectée dans `src/public/.htaccess`. Sur Hostinger (LiteSpeed) comme sous Docker local, le DocumentRoot sert déjà directement le contenu de `public/` (ou `public_html/`). La réécriture forçait donc la recherche d'un sous-dossier inexistant `/public/public/...`, provoquant un 404 sur l'intégralité du site.
    - **Suppression du bloc de redirection fantôme** : Éradication de la règle dans `src/public/.htaccess` et désactivation de `Options +FollowSymlinks` (incompatible LiteSpeed / mutualisé Hostinger).
    - **Sécurisation absolue de `FichierIni.php`** : Ajout du guard `if (class_exists('FichierIni', false)) return;` à la racine de la classe. Aucune double inclusion ne peut plus provoquer d'erreur fatale PHP.
    - **Normalisation de l'Autoloader** : Chargement automatique dès la première ligne de `utilssi.php` et `configMysql.php`. Rétablissement des inclusions propres des bibliothèques (`__DIR__ . '/FichierIni.php'`).
    - **Optimisation de `BackupServiceTest`** : Isolation d'un dossier temporaire léger pour les tests unitaires au lieu de compresser en direct les 500 Mo de partitions réelles (temps d'exécution ramené de 30s à 0,25s).
    - **Alignement parfait Prod vs Local** : Déplacement propre des fichiers de `public_html/public/` vers `public_html/` sur Hostinger. Les environnements local et prod sont désormais de parfaits jumeaux sans divergence de chemin.
    - **Validation & Smoke Tests** : **28 / 28 pages HTTP (100% Succès)** sans aucune erreur, validation des URL réécrites propres (`/chanson/{id}`).

### 📖 Résumé de la session (25 Septembre 2026)
- **Réalisation de la US-002 — Audit Fichiers Orphelins & Suite d'Intégrité Globale** :
    - **Architecture & API AJAX** : Transformation de `audit_orphelins.php` en contrôleur mixte HTML / API JSON (`?action=scan_orphans`, `?action=scan_duplicates`, `?action=scan_integrity`, etc.).
    - **UX Asynchrone & Lazy Loading** : Chargement instantané de la vue Canopée `views/audit_orphelins_view.phtml` et scans à la demande par onglet.
    - **Tri Multi-Colonnes Interactif** : En-têtes de colonnes triables au clic (Nom, Chemin, Taille, Date) avec flèches d'orientation (Croissant ▲ / Décroissant ▼).
    - **Dossiers Orphelins sur Disque** : Implémentation de `findOrphanDirectories()` et `deleteOrphanDirectory()` pour repérer et supprimer les dossiers `data/chansons/{id}/`, `data/songbooks/{id}/`, etc. dont l'entité SQL a été supprimée.
    - **Fichiers Manquants & Relations Caduques** : Détection des références SQL brisées 404 (`findMissingFilesFromDb()`) et nettoyage automatisé des tables de liaison orphelines (`cleanOrphanDbRelations()`).
    - **Extension de l'Intégrité Relationnelle SQL Inter-Tables** : Enrichissement de `findOrphanDbRelations()` et `cleanOrphanDbRelations()` dans `DataAuditService.php` pour détecter et réparer les liaisons caduques des tables `document` (documents rattachés à des chansons/songbooks supprimés), `lienurl` (liens web orphelins), `noteUtilisateur` et des tables de liaison (`liendocsongbook`, `lienstrumchanson`, `lienchansonplaylist`).
    - **Boutons de Référence & Cache MD5** : Boutons d'accès rapide `"Voir Chanson #X"` et mise en cache persistant JSON dans `data/temp/md5_cache.json` pour des scans quasi-instantanés.
- **Réalisation de la US-003 — Module de Sauvegarde Globale & Extension ZipArchive** :
    - **Extension PHP `zip`** : Compilation et activation de `libzip-dev` et `ZipArchive` dans le conteneur `site-partoches` et mise à jour permanente dans `Dockerfile`.
    - **Exportation autonome** : Génération d'une archive ZIP contenant le dump SQL complet de la base MariaDB (`db_dump.sql`), le dossier `data/` complet, la configuration `conf/params.ini` et `manifest.json`.
- **Qualité, Tests & Couverture Automatisée** :
    - **Tests Unitaires PHPUnit** : 100% des tests validés (`DataAuditServiceTest` 6/6 OK).
    - **Smoke Tests HTTP** : **28 / 29 tests validés**.

### 📖 Résumé de la session (24 Septembre 2026)
- **🛑 GEL DES DEPLOIEMENTS & RESTAURATION PROD (Directive PO)** :
    - Arrêt total de tout commit / push Git et déploiement FTP.
    - Le PO effectue la restauration de la production via les sauvegardes d'origine.
    - Reprise des livraisons planifiée pour ce week-end.
- **Clarification PO & Isolation Stricte du dossier `/data/` par Environnement** :
    - **Directive PO** : Le dossier `data/` est strictement propre à chaque environnement (Prod vs Dev). Les fichiers et médias de prod (partitions, pochettes uploadées par les éditeurs) vivent sur le serveur de prod et ne doivent JAMAIS être écrasés ni synchronisés par Git / FTP CI-CD.
    - **Restauration CI/CD** : Ré-établissement immédiat de `data/**` dans la liste `exclude` de [.github/workflows/ci-cd.yml](file:///f:/Arnaud/projets-dev/partoches/.github/workflows/ci-cd.yml) pour garantir qu'aucun déploiement FTP ne touche, modifie ou supprime l'arborescence `/public_html/data/` sur Hostinger.
    - **Recherche résiliente des pochettes** : Conservation de l'amélioration dans [src/public/php/lib/Image.php](file:///f:/Arnaud/projets-dev/partoches/src/public/php/lib/Image.php) qui effectue une recherche intelligente `glob()` dans `data/chansons/$id/` sur prod sans toucher au système de fichiers.
- **Restauration des Assets Visuels en Prod (`src/public/images/`) & Fin des 404** :
    - **Identification Root Cause** : L'inspection directe des URL d'images en prod (`/images/icones/vinyle.png`, `/images/navigation/logo_site.png`, `/images/icones/icone_musique.png`) renvoyait un code HTTP **404 Not Found**. La règle globale `images/` dans `.gitignore` ignorait l'arborescence `src/public/images/`, l'empêchant d'être versionnée sur GitHub et transférée par FTP vers Hostinger.
    - **Solution Structurelle** : Ajustement dans `.gitignore` avec la bascule vers `/images/` (racine) et l'inclusion explicite `!src/public/images/`. Ajout et publication de l'intégralité du dossier `src/public/images/` dans le dépôt Git.
    - **Validation** : Rétablissement de l'affichage de l'image vinyle de fallback et des icônes sur le serveur de prod.
- **Hotfix Production : Eradication de l'Erreur 500 `Class "setasign\Fpdi\TcpdfFpdi" not found`** :
    - **Identification Root Cause** : L'analyse des logs d'exécution sur le serveur de prod a révélé que la classe `setasign\Fpdi\TcpdfFpdi` provoquait une `Fatal Error` lors du chargement de `pdf.php` et `Songbook.php`. Deux causes conjointes :
        1. `.gitignore` contenait la règle globale `vendor/`, empêchant Git d'inclure le sous-dossier `src/public/vendor/` et donc d'expédier TCPDF et FPDI vers Hostinger via le déploiement FTP.
        2. `src/public/vendor/php/fpdi/autoload.php` avait un chemin codé en dur `/var/www/html/vendor/autoload.php` inexistant en prod.
    - **Solution Structurelle & Correctifs** :
        1. **`.gitignore`** : Ajustement de `vendor/` en `/vendor/` (racine) avec inclusion explicite `!src/public/vendor/`.
        2. **Intégration Vendor** : Copie et versionnage des paquets TCPDF / FPDI / Composer dans `src/public/vendor/`.
        3. **Portabilité Autoload** : Modification de `src/public/vendor/php/fpdi/autoload.php` et `src/public/php/lib/pdf.php` avec des détections dynamiques multi-chemins et vérification `if (!class_exists('setasign\Fpdi\TcpdfFpdi'))`.
    - **Validation** : 150 / 150 tests PHPUnit (100% Succès), déploiement FTP automatisé.
- **Résolution Défensive de l'Affichage des Pochettes (Fallback & Multi-sources)** :
    - **Identification Root Cause** : `ChansonRenderer::renderCard()` et `chanson_voir_view.phtml` s'appuyaient uniquement sur `Document::imageTableId()`. Lorsque les images n'étaient pas répertoriées dans la table `document` (mais stockées dans la colonne `chanson.cover` ou présentes physiquement dans `data/chansons/`), le système retombait immédiatement sur la vignette vinyle par défaut (`vinyle.png`).
    - **Solutions Appliquées** :
        1. **`Document::imageTableId()`** : Migration de `fetch_row()` vers `fetch_assoc()` avec extraction sécurisée par noms de colonnes (`nom`, `version`).
        2. **`affichePochette()`** : Prise en charge universelle des URL HTTP/HTTPS externes (Discogs, images en ligne), fallback automatique vers `$chanson->getCover()` si `Document::imageTableId()` renvoie vide, et vérification physique de l'image source sur disque avant affichage du vinyle par défaut.
        3. **Renderers UI** : Alignement de `ChansonRenderer.php` et `chanson_voir_view.phtml` sur ce pipeline d'images résilient.
    - **Validation** : 150 / 150 tests PHPUnit (100% Succès).
- **Audit Comparatif Prod vs Local & Éradication des Décalages de Colonnes BDD (`fetch_assoc`)** :
    - **Audit & Nettoyage du Dossier Fantôme Hostinger** :
        1. Analyse du CSV d'audit prod (`audit-partoches-2026-09-24(1).csv`).
        2. Suppression du sous-dossier fantôme `/public_html/public/` sur Hostinger, ramenant le nombre de fichiers de prod de 477 à 274 (assainissement à 83% d'identité exacte).
    - **Résolution du Bug de Décalage des Colonnes SQL (Pochettes & Tonalité originale)** :
        1. **Root Cause** : `Chanson::loadInstance()`, `Songbook::chercheSongbook()`, `Document::chercheDocument()` et `documents_voir.php` lisaient les lignes MySQL avec `fetch_row()` (indices numériques `$row[11]`, `$row[12]`). Comme l'ordre des colonnes MariaDB différait entre Dev et Prod, `tonalite_originale` recevait l'URL de l'image et `cover` recevait `NULL`.
        2. **Correctif Défensif** : Bascule vers `fetch_assoc()` et l'extraction par noms de colonnes (`$row['cover']`, `$row['tonalite_originale']`, `$row['nom']`, etc.) dans `Chanson.php`, `Songbook.php`, `Document.php`, `MediaService.php`, `documents_voir.php`.
        3. **Enrichissement UX** : Intégration du badge `Orig. <Tonalité>` sur les cartes de chansons dans `ChansonRenderer.php`.
        4. **Migration SQL 005** : Création du script [src/public/data/database/migrations/005_delete_test_chansons_above_764.sql](file:///f:/Arnaud/projets-dev/partoches/src/public/data/database/migrations/005_delete_test_chansons_above_764.sql) pour nettoyer les entrées de test.
    - **Validation** : 150 / 150 tests PHPUnit (100% Succès), 27/28 Smoke Tests HTTP validés.
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

