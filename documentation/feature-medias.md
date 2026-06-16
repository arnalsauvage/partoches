# 🎞️ Feature : Medias

## 📝 Description
Le module Medias est le coeur de l'affichage public du site. Il permet de visualiser l'ensemble des ressources (PDF, Images, MP3, Vidéos) rattachées aux chansons, exercices ou playlists.

## 🏗️ Architecture (SOLID)
Le module a été refactorisé pour séparer la logique de données du rendu visuel.

| Composant | Rôle |
|-----------|------|
| `Media.php` | Entité représentant un média en base de données. |
| `MediaService.php` | Logique métier : scan des dossiers, synchronisation BDD/Fichiers, calcul des types. |
| `MediaRenderer.php` | Rendu visuel : génération des cartes (thumbnails) et de la galerie. |
| `listeMedias.php` | Contrôleur principal affichant la mosaïque de médias. |

## 🚀 Fonctionnalités Clés
- **Scan Dynamique** : Le site scanne les dossiers `data/chansons/` et `data/playlists/` pour détecter les nouveaux fichiers.
- **Filtrage par Type** : Visualisation ciblée (Partitions PDF, Audio, Vidéos YouTube/Vimeo).
- **Intégration Design System** : Utilisation des cartes "Canopée" pour une expérience utilisateur fluide et esthétique.
- **Mode Public/Privé** : Seuls les médias rattachés à des entités "Publiées" sont visibles par les invités.

## 🛠️ Commandes Utiles
Pour forcer la reconstruction de la table des médias :
```php
require_once "MediaService.php";
MediaService::resetMediaTable();
```
