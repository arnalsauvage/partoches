# 🎞️ Feature : Medias

## 📝 Description
Le module Medias est le coeur de l'affichage public du site. Il permet de visualiser l'ensemble des ressources (PDF, Images, MP3, Vidéos) rattachées aux chansons, exercices ou playlists.

## 🏗️ Architecture (SOLID)
Le module a été refactorisé pour séparer la logique de données du rendu visuel.

| Composant | Rôle |
|-----------|------|
| `Media.php` | Entité représentant un média en base de données (DTO pure). |
| `MediaRepository.php` | Accès aux données : requêtes SQL préparées (`media`, `liendocchanson`, etc.). |
| `MediaService.php` | Logique métier : scan des dossiers, synchronisation, contrôle d'accès audio (MP3). |
| `MediaRenderer.php` | Rendu visuel : cartes Canopée, gestion des badges et état restreint. |
| `listeMedias.php` | Contrôleur principal affichant la mosaïque de médias. |

## 🚀 Fonctionnalités Clés
- **Scan Dynamique** : Le site scanne les dossiers `data/chansons/` et `data/playlists/` pour détecter les nouveaux fichiers.
- **Filtrage par Type** : Visualisation ciblée (Partitions PDF, Audio, Vidéos YouTube/Vimeo).
- **Intégration Design System** : Utilisation des cartes "Canopée" pour une expérience utilisateur fluide et esthétique.
- **Mode Public/Privé** : Seuls les médias rattachés à des entités "Publiées" sont visibles par les invités.
- **Restriction Audio (MP3)** : La lecture et l'accès direct aux fichiers audio (`mp3`, `m4a`, `aac`) sont réservés aux utilisateurs connectés (`privilege >= 1`). Les invités non connectés voient une invitation Canopée leur proposant de se connecter pour débloquer les ressources audio.

## 🔒 Droits d'accès aux Médias Audio (MP3) & Protection Juridique
Afin de protéger l'association responsable du site contre l'aspiration automatisée par des bots et prévenir toute accusation de contrefaçon d'œuvres (droit d'auteur) sur les extraits, citations, pistes instrumentales et ralenties :
1. **Visiteurs invités (`privilege = 0`)** :
   - Les lecteurs `<audio>` et les liens directs vers les MP3 sont entièrement omis du DOM HTML généré.
   - Une alerte d'invitation Canopée est affichée : *"Ce document contient une ou plusieurs ressources audio, connectez-vous pour l'afficher !"* avec un bouton de redirection vers la page de connexion.
   - Les requêtes directes de téléchargement vers `getdoc.php` ciblant un fichier audio sont refusées pour les utilisateurs anonymes.
2. **Membres et Administrateurs (`privilege >= 1`)** :
   - Accès complet aux lecteurs audio HTML5 et aux téléchargements.

## 🛠️ Commandes Utiles
Pour forcer la reconstruction de la table des médias :
```php
require_once "MediaService.php";
MediaService::resetMediaTable();
```
