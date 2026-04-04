# Structure du Projet Partoches 🎸 (Mise à jour 2026)

Ce document répertorie les fichiers PHP du projet classés par leur rôle dans le rendu visuel et la logique applicative.

## 🏛️ 1. L'Administration (Quartier Général)
Nouveau dossier centralisant la gestion technique du site.

| Page | Chemin | Description |
| :--- | :--- | :--- |
| **Console Admin** | `php/admin/params.php` | Contrôleur principal de l'administration. |
| **Admin Service** | `php/admin/AdminService.php` | Logique métier (Logs, SQL, Migrations). |
| **Vue Admin** | `php/admin/params_view.phtml` | Template HTML de la console. |
| **Ajax Admin** | `php/admin/params_ajax.php` | Handlers pour les fonctions asynchrones. |

## 🌟 2. Les Vues Principales (Pages complètes)
Ces fichiers génèrent une page HTML entière en utilisant `envoieHead()` et `envoieFooter()`.

| Page | Chemin | Description |
| :--- | :--- | :--- |
| **Accueil (Médias)** | `php/media/listeMedias.php` | Galerie des dernières publications. |
| **Liste Chansons** | `php/chanson/chanson_liste.php` | Répertoire complet des chansons avec filtres. |
| **Fiche Chanson** | `php/chanson/chanson_voir.php` | Détail d'un morceau (accords, paroles, etc.). |
| **Liste Playlists** | `php/playlist/playlist_liste.php` | Galerie moderne des listes de lecture. |
| **Voir Playlist** | `php/playlist/playlist_voir.php` | Consultation et tri des morceaux d'une playlist. |
| **Liste Songbooks** | `php/songbook/songbook_liste.php` | Gestion des recueils (mode admin/éditeur). |
| **Communauté** | `php/utilisateur/utilisateur_liste.php` | Liste des musiciens inscrits (cartes profil). |
| **Répertoire Strums** | `php/strum/strum_liste.php` | Galerie des rythmes et motifs de grattage. |

## 🛠️ 3. Les Formulaires (Vues d'édition)
- **Chanson** : `php/chanson/chanson_form.php`
- **Utilisateur** : `php/utilisateur/utilisateur_form.php`
- **Songbook** : `php/songbook/songbook_form.php`
- **Strum** : `php/strum/strum_form.php`
- **Playlist** : `php/playlist/playlist_form.php`

## 🎼 4. Les Composants & Librairies (Design System)
Éléments réutilisables et moteurs du site.

### Classes avec rendu visuel
- **ComposantsUI** (`lib/ComposantsUI.php`) : **(Nouveau)** Gabarit central des cartes Canopée.
- **Chanson** (`Chanson.php`) : `afficheCarteChanson()` (utilise ComposantsUI).
- **Playlist** (`playlist.php`) : `afficheCartePlaylist()` (utilise ComposantsUI).
- **Media** (`Media.php`) : `afficheComposantMedia()`.

### Librairies de rendu & Cœur
- **HTML** (`lib/html.php`) : Moteur de rendu (`envoieHead`, `envoieFooter`, `ancre`).
- **Menu** (`navigation/menu.php`) : La barre de navigation globale.
- **Cœur** : `lib/configMysql.php` (Init), `lib/mysql.php` (BDD), `lib/Chiffrement.php`.

---
*Document maintenu par Django (Gemini) - 4 Avril 2026*
