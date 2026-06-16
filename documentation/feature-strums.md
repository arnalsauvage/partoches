# 🎸 Feature : Strums (Rythmiques)

## 📝 Description
La "Boîte à Strum" est un outil pédagogique permettant de visualiser et d'écouter des motifs rythmiques pour ukulélé et guitare.

## 🏗️ Architecture
- **Modèle** : `Strum.php` (Gère les propriétés `bas`, `haut`, `muet`, `pince`).
- **Vue** : `strum_liste.php` et `strum_form.php`.
- **Composant Interactif** : `html/boiteAstrum/` (Application JS Vanilla isolée).

## 🚀 Fonctionnalités Clés
- **Éditeur Visuel** : Création de motifs rythmiques via une interface intuitive (Flèches Haut/Bas).
- **Lecteur Audio** : Lecture en boucle avec tempo ajustable.
- **Assignation** : Un strum peut être lié à plusieurs chansons.
- **Export** : Génération de diagrammes pour les songbooks PDF.

## 🎨 Conventions de Saisie
- `B` : Bas
- `H` : Haut
- `X` : Étouffé
- `P` : Pincé
- `-` : Silence
