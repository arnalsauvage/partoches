# Documentation de la Feature : Gestion des Songbooks (Recueils)

Ce document est la source de vérité décrivant l'implémentation de la gestion des songbooks, permettant de regrouper des partitions (documents) en recueils thématiques ou de concerts.

## 1. Présentation du concept "Songbook"

Un **Songbook** (ou Recueil) est une collection ordonnée de partitions issues du catalogue de chansons.

- **Rôle dans l'écosystème** : Il permet aux utilisateurs de naviguer dans des sélections de morceaux (ex: "Anthologie Ukulélé", "Concert du 12 juin") et de télécharger un PDF unique regroupant toutes les partitions du recueil avec une couverture et un sommaire générés automatiquement.
- **Liaison Document vs Songbook** : Un songbook ne contient pas directement des chansons, mais des **documents** (fichiers PDF de partitions). Cette approche permet de choisir précisément quelle version de partition (ex: "V3 avec accords simplifiés") intégrer au recueil.
- **Types de Songbooks** :
    1. **Anthologie** : Grandes collections de référence.
    2. **Concert** : Liste de morceaux pour une prestation spécifique.
    3. **Thématique** : Regroupement par style ou instrument.

## 2. User Stories (Implémentées)

### Pour les Visiteurs / Utilisateurs
- **feat-songbook-01** : Consulter la galerie des songbooks (Portfolio) pour découvrir les recueils disponibles.
- **feat-songbook-02** : Visualiser le détail d'un songbook (sommaire, description, nombre de vues).
- **feat-songbook-03** : Télécharger le songbook complet au format PDF (fusion automatique de toutes les partitions).

### Pour les Éditeurs / Admins
- **feat-songbook-04** : Créer et éditer les informations d'un songbook (titre, description, type).
- **feat-songbook-05** : Gérer la couverture du songbook en uploadant une image dédiée.
- **feat-songbook-06** : Ajouter des partitions à un songbook depuis la fiche d'une chanson.
- **feat-songbook-07** : Réorganiser l'ordre des morceaux via une interface de glisser-déposer (Drag & Drop).
- **feat-songbook-08** : Déclencher la génération du PDF final (fusion logicielle via Ghostscript/FPDI).
- **feat-songbook-09** : Dupliquer un songbook existant pour créer rapidement une nouvelle version ou un concert similaire.

## 3. Implémentation technique

### Base de données

- **Table `songbook`** :
    - `id`, `nom`, `description`, `date`, `image`, `hits`, `idUser`, `type` (INT : 1=Anthologie, 2=Concert, 3=Thème).
- **Table `liendocsongbook`** (Many-to-Many ordonné) :
    - `id`, `idDocument` (FK -> document.id), `idSongbook` (FK -> songbook.id), `ordre` (INT).
- **Table `document`** :
    - Utilisée pour stocker les métadonnées de la couverture (nomTable='songbook') et du PDF généré.

### Backend (PHP)

- **Classe `Songbook` (`src/public/php/songbook/Songbook.php`)** :
    - Classe métier gérant le CRUD et le rendu des cartes UI (`afficheCarteSongbook`).
    - Gère l'incrémentation des "hits" (vues).
- **Classe `LienDocSongbook` (`src/public/php/liens/LienDocSongbook.php`)** :
    - Gère la logique d'association et de tri (`remonteTitre`, `ordonneLiensSongbook`).
- **Logiciel de PDF (`src/public/php/lib/pdf.php`)** :
    - **`SongbookPdfService`** : Orchestre la création du PDF.
    - Utilise **Ghostscript** via `exec()` pour assurer la compatibilité des PDFs importés (conversion en v1.4) avant la fusion.
    - Génère une couverture personnalisée et un sommaire avec numérotation de pages.
- **API / Contrôleur (`src/public/php/songbook/songbook_get.php`)** :
    - Point d'entrée unique pour les actions (INS, MAJ, SUPPR, DUP, GENEREPDF).
    - Le mode `GENEREPDF` renvoie un JSON de succès/erreur pour le feedback client.

### Frontend

- **Vue Galerie (`src/public/php/songbook/songbook-portfolio.php`)** :
    - Affichage sous forme de grille moderne (Design System "Canopée").
- **Vue Détail (`src/public/php/songbook/songbook_voir.php`)** :
    - Présentation riche, liste des morceaux avec liens vers les fiches chansons.
- **Interface Admin (`src/public/php/songbook/songbook_form.php`)** :
    - Formulaire de gestion.
    - Intégration de **jQuery UI Sortable** pour le tri visuel des morceaux.
    - Bouton de génération PDF avec barre de progression/spinner via AJAX.
- **JavaScript (`src/public/js/songbookform.js`)** :
    - Gère les appels AJAX pour la sauvegarde de l'ordre et la génération du PDF.
- **CSS (`src/public/css/songbookform.css`)** :
    - Styles spécifiques pour l'interface de tri et les éléments de formulaire.

## 4. Points de vigilance spécifiques

- **Dépendance Ghostscript** : La génération de PDF nécessite que `gs` soit installé sur le serveur (ou dans le conteneur Docker). C'est crucial pour fusionner des PDFs de versions différentes.
- **Performances** : La génération d'un gros recueil (ex: 50+ morceaux) peut être longue et consommatrice en mémoire. Le script augmente dynamiquement `memory_limit` et `max_execution_time`.
- **Chemin des fichiers** : Les fichiers sont stockés dans `src/data/songbooks/{id}/`. Ce dossier contient l'image de couverture, les fichiers sources éventuels et le PDF final généré.
- **Encodage** : Le système utilise `ISO-8859-1` pour certaines parties de la génération PDF (TC-PDF/FPDI) tout en traitant l'UTF-8 en entrée, une conversion propre est gérée dans `SongbookPdfService`.
