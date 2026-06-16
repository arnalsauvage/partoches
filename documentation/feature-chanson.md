# Feature : Chanson

Contexte : gestion du catalogue de chansons dans Partoches. Cette page référence les fichiers impliqués dans cette feature, sans doublons inutiles.

## 📁 Contrôleurs / Pages principales

- `src/public/php/chanson/chanson_form.php` — formulaire d’édition “classique”.
- `src/public/php/chanson/chanson_form_new.php` — formulaire d’édition “amélioré”.
- `src/public/php/chanson/chanson_post.php` — point d’entrée POST pour création / mise à jour / suppression document / régénération vignettes.
- `src/public/php/chanson/chanson_liste.php` — listing des chansons avec tris, filtres, pagination, vue cartes/liste.
- `src/public/php/chanson/chanson_voir.php` — fiche publique d’une chanson.
- `src/public/php/chanson/chanson_chercher.php` — recherche via API externe (GetSongBPM).
- `src/public/php/chanson/chanson_recherche_ajax.php` — recherche AJAX pour l’autocomplétion.
- `src/public/php/chanson/chanson_upload.php` — upload de documents attachés à une chanson.
- `src/public/php/chanson/chanson_publication_ajax.php` — bascule de publication via AJAX.
- `src/public/php/chanson/chanson_depublier_tout.php` — dépublication massive par utilisateur.
- `src/public/php/chanson/chanson-v-comp-cherche.php` — composant de recherche / comparaison utilisé par le listing.
- `src/public/php/liens/lienStrumChanson_post.php` — traitement POST des liens chanson ↔ strum.
- `src/public/php/liens/lienChansonPlaylist.php` — liens entre chanson et playlist.
- `src/public/php/liens/LienStrumChanson.php` — entité / logique du lien chanson ↔ strum.

## 🧠 Logique métier / Classes

- `src/public/php/chanson/Chanson.php` — entité métier chanson.
- `src/public/php/chanson/ChansonFormRenderer.php` — rendu des blocs du formulaire (onglets fichiers, strums, liens).
- `src/public/php/liens/LienStrumChanson.php` — gestion du lien entre une chanson et un strum.

## 🎨 Styles

- `src/public/css/chansonform.css` — styles spécifiques au formulaire de chanson et aux composants associés.

## ⚙️ JavaScript

- `src/public/js/chansonForm.js` — comportements du formulaire : onglets, covers, renommage document, modales, liens, AJAX.

## 🧪 Tests

- `tests/chansonTest.php`
- `tests/chansonFiltreTest.php`
- `tests/chansonListeTest.php`

## 🔗 Données / routes clés

- Publication lue/écrite par :
  - `Chanson.php` (`getPublication()` / `setPublication()`, filtres `publication = 1`), `chanson_publication_ajax.php`, `chanson_depublier_tout.php`
- Mise à jour via :
  - `chanson_form.php` -> POST `chanson_post.php`
  - `chanson_form_new.php` -> POST `chanson_post.php`
