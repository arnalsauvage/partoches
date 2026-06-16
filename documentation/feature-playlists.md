# 🎵 Feature : Playlists

## 📝 Description
Le module Playlist permet de créer des listes de lecture de morceaux, soit manuellement, soit dynamiquement selon des critères musicaux.

## 🏗️ Architecture (SOLID)
Refactorisé en Juin 2026 vers le pattern Service/Renderer.

| Composant | Rôle |
|-----------|------|
| `Playlist.php` | Fonctions de base et accès BDD. |
| `PlaylistFormService.php` | Préparation des données et gestion des actions. |
| `PlaylistFormRenderer.php` | Rendu HTML (Tabs, Formulaire, Table de morceaux). |

## 🚀 Modes de Fonctionnement
1.  **Mode Manuel** : L'utilisateur ajoute les morceaux un par un et définit leur ordre.
2.  **Mode Dynamique** : La playlist se remplit automatiquement selon des critères (ex: "Toutes les chansons en Am", "Toutes les chansons de la saison 2024").

## 🎨 Design
Utilise exclusivement le Design System Canopée via `playlistform.css`.
