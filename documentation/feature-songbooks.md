# 📚 Feature : Songbooks

## 📝 Description
Le module Songbook permet de regrouper des chansons dans des recueils thématiques, avec la possibilité de générer un export PDF complet (partitions + sommaire).

## 🏗️ Architecture
- **Entité** : `Songbook.php`.
- **Liaison** : `LienDocSongbook.php` (Gère la relation N-N entre Chansons/Documents et Songbooks).
- **Export** : `SongbookExportService.php` (Utilise TCPDF pour la génération).

## 🚀 Fonctionnalités Clés
- **Organisation par Glisser-Déposer** : Gestion de l'ordre des morceaux dans le recueil.
- **Multi-Formats** : Un songbook peut contenir des partitions PDF, des fichiers ChordPro ou de simples images.
- **Vue Portfolio** : Affichage esthétique des couvertures de songbooks pour le public.
- **Sommaire Automatique** : Génération d'une table des matières cliquable dans le PDF final.

## 🔒 Sécurité
Les songbooks peuvent être marqués comme "non publiés" pour rester en brouillon pendant leur constitution.
