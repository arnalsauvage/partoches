# 🤠 Réponses & Actions — Django

## Session du 13 Novembre 2026 — US-087 : Affichage des playlists associées sur la page chanson (`chanson_voir`)

### 🎯 Demande
Sur la page d'une chanson (`chanson_voir.php`), afficher les playlists dans lesquelles cette chanson est citée : vignette de la playlist, titre, et clic sur la vignette menant vers la playlist.

### 🛠️ Actions réalisées
1. **Couche Persistance (`ChansonRepository.php`)** :
   - Ajout de `ChansonRepository::getPlaylists(int $idChanson): ?mysqli_result`.
   - Requête préparée joignant `playlist` et `lienchansonplaylist` pour récupérer les playlists contenant le morceau.

2. **Couche Service (`ChansonService.php`)** :
   - Injection de la liste des playlists associées dans le tableau `$viewData`.
   - Ajout du helper `ChansonService::getPlaylistThumbnailUrl(array $pl): string` pour résoudre l'URL de la vignette (mosaïques `mosaic_*.jpg`, images téléversées, fallbacks BDD ou placeholder).

3. **Couche Vue (`chanson_voir_view.phtml`)** :
   - Ajout d'une section « Playlists associées » en grille responsive (2 à 6 colonnes selon écran).
   - Vignette cliquable avec effet de survol Canopée, titre tronqué et redirection directe vers `playlist_voir.php?id={id}`.
   - Placeholder visuel harmonisé avec icône de liste musicale si aucune image n'est configurée pour la playlist.
   - Masquage automatique de la section si le morceau n'est cité dans aucune playlist.

4. **Design System Canopée (`styles-communs.css`)** :
   - Classes CSS dédiées : `.playlist-thumb`, `.playlist-img-voir`, `.playlist-thumb-placeholder`, `.playlist-caption-title`.
   - Zéro attribut `style="..."` (conformité Constitution).

5. **Tests Unitaires (`tests/ChansonTest.php`)** :
   - Ajout de `testChansonPlaylists()` validant la détection des playlists, la résolution de vignettes et le nettoyage complet.

## Session du 13 Novembre 2026 — US-086 : Formulaire modal de duplication sélective d'une chanson

### 🎯 Demande
Remplacer la duplication directe par un formulaire interactif (modale) permettant à l'administrateur de choisir ce qu'il souhaite copier :
- **Informations générales** : conservées avec possibilité d'éditer le titre.
- **Fichiers attachés (documents)** : liste à cocher, décochés par défaut (avec boutons Tout cocher / Tout décocher).
- **Rythmiques (strums)** : idem, décochés par défaut (avec boutons Tout cocher / Tout décocher).
- **Liens et vidéos (lienurl)** : idem, décochés par défaut (avec boutons Tout cocher / Tout décocher).

### 🛠️ Actions réalisées
1. **Modale AJAX (`chanson_dupliquer_modal_ajax.php`)** :
   - Contrôle d'accès strict `estAdmin()`.
   - Champ de saisie pour modifier le titre de la chanson dupliquée (pré-rempli avec le nom actuel).
   - Panneaux récapitulatifs clairs avec compteurs d'éléments.
   - Listes à cocher (Bootstrap 3) avec boutons « Tout cocher » et « Tout décocher » pour chaque section : documents attachés, rythmiques et liens web/vidéos.
   - Respect strict du Design System Canopée (zéro attribut `style="..."`).

2. **Logique JavaScript (`src/public/js/chanson_dupliquer.js`)** :
   - `ouvrirModalDupliquer(idChanson)` : ouverture asynchrone de la modale avec spinner de chargement et gestion d'erreurs AJAX.
   - `cocherTous(classeCheck, etat)` : bascule dynamique de sélection pour chaque groupe de cases à cocher.

3. **Service Métier (`Chanson::dupliqueChanson` dans `Chanson.php`)** :
   - Mise à jour de la signature :
     `public static function dupliqueChanson(int $idChanson, array $selectedDocIds = [], array $selectedStrumIds = [], array $selectedLienIds = [], ?string $nouveauNom = null): int`
   - Application du nouveau titre s'il est spécifié.
   - Copie sélective en base et physique des fichiers attachés vers le dossier de la nouvelle chanson (`src/public/data/chansons/{newId}/`).
   - Copie sélective des liens URLs (`LienUrl`) et des rythmiques (`LienStrumChanson`).

4. **Contrôleur (`chanson_post.php`)** :
   - Réception et filtrage des tableaux d'identifiants (`doc_ids`, `strum_ids`, `lien_ids`) et du titre (`nom`).
   - Appel sécurisé de `Chanson::dupliqueChanson(...)`.

5. **Vue Liste (`chanson_liste.php`)** :
   - Déclencheur du bouton Dupliquer mis à jour avec `onclick="ouvrirModalDupliquer({$_id});"`.
   - Intégration du squelette HTML de la modale `#modalDupliquerChanson` et du script `chanson_dupliquer.js`.

6. **Styles Design System (`styles-communs.css`)** :
   - Ajout des classes utilitaires `.dupliquer-btn-group-toggle`, `.dupliquer-item-label`, `.modal-duplication-loading`.

7. **Tests Unitaires (`tests/ChansonTest.php`)** :
   - Mise à jour du test `testDupliqueChanson()` : validation du comportement par défaut (rien n'est copié en dehors des métadonnées) et de la copie sélective personnalisée (fichiers, strums, liens, nouveau titre).

---

## Session du 13 Novembre 2026 — US-086 : Duplication d'une chanson en vue liste

### 🎯 Demande
Dans la liste des chansons (`chanson_liste.php?vue=liste`), permettre à l'administrateur de dupliquer une chanson en publiant tous ses attributs et liens associés, mais sans les documents attachés.

### 🛠️ Actions réalisées
1. **Service Métier (`Chanson.php`)** :
   - Ajout de la méthode statique `Chanson::dupliqueChanson(int $idChanson): int`.
   - Copie intégrale des métadonnées : titre, interprète, année, tempo, mesure, pulsation, tonalité, tonalité originale, pochette (`cover`), contributeur (`idUser`), publication à 1 (publié), date à ce jour, hits réinitialisés à 0.
   - Duplication des liens externes (`LienUrl`) et des rythmiques (`LienStrumChanson`).
   - Exclusion stricte des documents attachés (aucune entrée créée dans la table `document`, aucun fichier physique copié).
   - Verrouillage de sécurité : contrôle `estAdmin()`.

2. **Contrôleur (`chanson_post.php`)** :
   - Prise en charge du mode `mode=DUP`.
   - Vérification des privilèges administrateur via `estAdmin()`.
   - Redirection vers `chanson_form.php?id=$newId&msg=OK_DUP` avec message Toastr.

3. **Vue Liste (`chanson_liste.php`)** :
   - Ajout du bouton Dupliquer pour l'administrateur avec l'icône `glyphicon-duplicate`.
   - Groupement propre avec le bouton Supprimer via `<div class='btn-group btn-group-xs'>`.
   - Boîte de confirmation JS `confirm(...)` pour éviter les clics involontaires.
   - Respect strict du Design System (0 style inline ajouté).

4. **Notifications & Ergonomie (`ChansonFormNewRenderer.php`, `chanson_form_classic.php` & `chanson_liste.php`)** :
   - Prise en compte du paramètre `msg=OK_DUP` pour afficher un toast de succès Toastr.

5. **Tests (`tests/ChansonTest.php`)** :
   - Ajout du test unitaire `testDupliqueChanson` validant les attributs, les liens, les strums et l'absence de documents attachés.

6. **Déploiement & CI/CD** :
   - Commit `571d2b4` : `feat(chanson): duplication d'une chanson en vue liste pour l'admin [monmac]` poussé sur `origin/master`.
   - Pipeline GitHub Actions validé avec succès (Lint + tests PHPUnit).
   - Déploiement automatique FTP vers Hostinger (`partoches.canopee-musique.fr`) exécuté avec succès.
