# 🏷️ Feature : Tags & Catégories de Contenu

## 📝 Description
Ajouter un système de **tags de contenu** sur les chansons, strums, songbooks et playlists afin de :
- catégoriser le contenu par thème, forme, vocabulaire, époque, etc. ;
- proposer une **navigation par similarité** ;
- construire des **playlists dynamiques** à partir de critères de tags ;
- faciliter la **découverte** et l’**organisation** du répertoire.

## 🎯 Objectifs
- Centraliser la taxonomie du projet grâce à des catégories de tags.
- Rendre chaque ressource identifiable par un ou plusieurs tags.
- Offrir des parcours transverses : “chansons avec le même tag”, “strums associés à un tag”, etc.
- Garder une UX simple : hashtags familiers, navigation fluide, sans complexité admin démesurée.

## 🧱 Architecture proposée

### Entités
- **TagCategory** : catégorie sémantique d’un tag.
  - ex : `accords`, `theme`, `rythme`, `epoque`
  - attributs : `id`, `code`, `libelle`, `couleur`, `description`, `image`
- **Tag** : valeur concrète d’un tag.
  - ex : `cadence-flamenca`, `feminisme`, `calypso`, `annees-80`
  - attributs : `id`, `categorie_id`, `code`, `libelle`, `description`
- **RessourceTag** : liaison many-to-many entre une ressource et un tag.
  - supportée pour : `chanson`, `strum`, `songbook`, `playlist`

### Règles
- Un tag appartient à **une seule catégorie**.
- Une ressource peut avoir **plusieurs tags**.
- Les libellés sont **uniques par catégorie** et normalisés pour l’URL.

### Navigation & fonctionnalités
- Sur une fiche ressource : affichage des tags, accès rapide à la page de la catégorie.
- Sur une page tag / catégorie : liste des ressources associées + bouton “créer une playlist dynamique”.
- Sur playlist : possibilité de créer une **playlist figée** ou **dynamique** par critères de tags.

## 📚 User Stories (Tags)

### C1 — TagCategory — En tant qu’admin, je veux créer/modifier des catégories de tags pour structurer la taxonomie du projet.
- champs : `code`, `libelle`, `couleur`, `description`, `image`
- règle : libellé unique ; code utilisé pour les URLs
- IA : si libellé absent, proposer un libellé court (“accords”, “theme”, “rythme”, “epoque”) et un code slug

### C2 — TagCategory — En tant qu’utilisateur, je veux voir la liste des catégories et leurs tags associés pour comprendre la palette de filtres disponibles.
- vue : tableau simple par catégorie
- affichage : libellé, couleur, badge de comptage de tags

### C3 — Tag — En tant qu’admin, je veux créer/modifier un tag rattaché à une catégorie pour l’associer ensuite à des ressources.
- champs : `categorie_id`, `code`, `libelle`, `description`
- règle : code unique globalement ; libellé unique par catégorie

### T1 — Tag — En tant qu’utilisateur, je veux cliquer sur un tag sur une page de chanson/strum/songbook/playlist pour naviguer vers toutes les ressources portant ce tag.
- comportement : lien vers `tag.php?tag=<code>`
- affichage : compteur “X ressources”

### T2 — Tag — En tant qu’utilisateur, je veux accéder à la page d’une catégorie de tag pour filtrer globalement par axe sémantique.
- comportement : liste des tags de la catégorie + ressources associées
- UX : filtres rapides par tag inclus

### T3 — Tag — En tant qu’utilisateur, je veux pouvoir ajouter/retirer un tag existant sur une ressource pour l’enrichir sans la modifier en profondeur.
- actions : ajouter par recherche/complétion ; retirer un à un
- règle : pas de doublon sur une même ressource

### P1 — Playlist — En tant qu’utilisateur, je veux créer une **playlist dynamique** à partir d’un ou plusieurs tags pour qu’elle se mette à jour automatiquement quand le contenu évolue.
- exemple : playlist “Calypso années 80” = tag `calypso` + tag `annees-80`
- comportement : regénération à l’ouverture de la playlist

### P2 — Playlist — En tant qu’utilisateur, je veux pouvoir créer une **playlist figée** issue d’une recherche par tags pour figer une sélection.
- comportement : copie de conjonction de résultats ; sélections ultérieures possibles

### D1 — Découverte — En tant qu’utilisateur, je veux voir depuis une chanson “les ressources proches” partageant un tag pour découvrir du contenu similaire.
- layout : section “Ressources similaires”
- tri : pertinence par nombre de tags communs, puis fraîcheur

### A1 — Admin — En tant qu’admin, je veux importer/exporter des tags et leurs catégories pour administrer la taxonomie en masse.
- format : CSV simple
- règle : rejet des doublons sur code

## 🔗 Liens
- Feature liée : `feature-chansons.md`, `feature-strums.md`, `feature-playlists.md`
- Composants UI : composants de liste et fiches ressources existants

## 🚀 Livrable
- Entités `TagCategory`, `Tag`, `RessourceTag`
- Vues CRUD admin
- Intégration dans les fiches chanson/strum/songbook/playlist
- Pages de navigation par tag et par catégorie
- Mécanisme de playlist dynamique + figée
