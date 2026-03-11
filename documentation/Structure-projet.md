# Structure du Projet Partoches 🎸

Ce document répertorie les fichiers PHP du projet classés par leur rôle dans le rendu visuel et la logique applicative.

## 🌟 1. Les Vues Principales (Pages complètes)
Ces fichiers génèrent une page HTML entière en utilisant les fonctions `envoieHead()` et `envoieFooter()`.

| Page | Chemin | Description |
| :--- | :--- | :--- |
| **Accueil (Médias)** | `php/media/listeMedias.php` | Galerie des dernières publications (chansons, vidéos, partoches). |
| **Liste Chansons** | `php/chanson/chanson_liste.php` | Répertoire complet des chansons avec filtres. |
| **Fiche Chanson** | `php/chanson/chanson_voir.php` | Détail d'un morceau (accords, paroles, médias liés). |
| **Liste Songbooks** | `php/songbook/songbook_liste.php` | Gestion des recueils (mode admin/éditeur). |
| **Galerie Songbooks** | `php/songbook/songbook-portfolio.php` | Vue "mosaïque" des songbooks pour les membres. |
| **Voir Songbook** | `php/songbook/songbook_voir.php` | Sommaire et consultation d'un recueil spécifique. |
| **Communauté** | `php/utilisateur/utilisateur_liste.php` | Liste des musiciens inscrits (cartes profil). |
| **Répertoire Strums** | `php/strum/strum_liste.php` | Galerie des rythmes et motifs de grattage. |
| **Galerie des Liens** | `php/liens/lienurl_liste.php` | Liens externes et vidéos partagées. |
| **Documents** | `php/document/documents_voir.php` | Liste des fichiers attachés. |
| **Paramétrage** | `php/navigation/paramsEdit.php` | Console d'administration technique du site. |
| **Roadbook** | `php/todo/todo_admin.php` | Liste des tâches et évolutions (Markdown). |
| **Inspecteur Images** | `php/audit/imagesCheck.php` | Outil de nettoyage du filesystem. |

## 🛠️ 2. Les Formulaires (Vues d'édition)
Pages dédiées à la saisie et à la modification des données.

- **Chanson** : `php/chanson/chanson_form.php` (utilise `chanson_form_new.php`)
- **Utilisateur** : `php/utilisateur/utilisateur_form.php`
- **Songbook** : `php/songbook/songbook_form.php`
- **Strum** : `php/strum/strum_form.php`
- **Playlist** : `php/playlist/playlist_form.php`

## 🎼 3. Les Composants & Wrappers (Fragments HTML)
Classes et fonctions produisant des morceaux de code HTML réutilisables.

### Classes avec rendu visuel
- **Chanson** (`Chanson.php`) : `afficheCarteChanson()`, `afficheLigneTableau()`.
- **Utilisateur** (`Utilisateur.php`) : `afficheCarte()`, `getAvatarHtml()`.
- **Media** (`Media.php`) : `afficheComposantMedia()`.
- **Strum** (`Strum.php`) : `afficheCarte()`.
- **Songbook** (`Songbook.php`) : `afficheCarteSongbook()`.

### Librairies de rendu
- **HTML** (`lib/html.php`) : Fonctions de base (`ancre`, `image`, `affichePochette`, `envoieHead`, `envoieFooter`).
- **Tableaux** (`lib/tableHtml.php`) : Génération dynamique de tableaux HTML.
- **Formulaires** (`lib/formulaire.php`) : Génération de champs de saisie Bootstrap.
- **Menu** (`navigation/menu.php`) : La barre de navigation globale.

## 🎸 4. Les "Backstage" (Logique & API)
Fichiers traitant les données sans produire de rendu HTML direct (redirections, JSON, calculs).

- **Traitement POST** : `chanson_post.php`, `utilisateur_get.php`, `lienurlPost.php`, etc.
- **Ajax** : `chanson_publication_ajax.php`, `document_recherche_ajax.php`.
- **Services API** : `api/discogs_proxy.php`, `api/songbpm_proxy.php`.
- **Cœur** : `lib/mysql.php` (BDD), `lib/Image.php` (WebP), `lib/Chiffrement.php`.

---
*Document généré par Django (Gemini) - 10 Mars 2026*
