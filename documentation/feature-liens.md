# 🔗 Feature : Liens (LienUrl)

## 📝 Description
Le module Liens centralise toutes les ressources externes (YouTube, Vimeo, Cloud, sites tiers) rattachées aux chansons ou au site en général.

## 🚀 Fonctionnalités Clés
- **Détection Automatique** : Identification du type de lien (Vidéo, Audio, Site) via l'URL.
- **Embed Intelligent** : Génération automatique des lecteurs (iFrames) YouTube/Vimeo.
- **Compteur de Hits** : Suivi de la popularité des ressources externes.
- **Audit de Santé** : Script `check_liens.php` pour détecter les liens morts (404).

## 🛠️ Structure BDD
Table `lienurl` :
- `url` : L'adresse cible.
- `titre` : Libellé affiché.
- `type` : Catégorie (vidéo, audio, autre).
- `idTable` / `nomTable` : Polymorphisme pour lier à Chanson, Playlist, etc.
