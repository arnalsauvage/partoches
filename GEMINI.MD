# Projet Partoches

## Description

Ce projet est une application web PHP initiée en 2018. D'après la structure des fichiers, son objectif principal est la gestion de partitions et de chansons ("partoches"), probablement pour des musiciens. Il semble inclure la gestion d'utilisateurs, de playlists, de songbooks, et la possibilité de visualiser/télécharger des documents (potentiellement des PDF de partitions).

## Structure des dossiers principaux

- **`/` (Racine)** : Contient le point d'entrée (`index.php`), la configuration Docker (`docker-compose.yml`), les dépendances PHP (`vendor`), et les fichiers de configuration du serveur web (`.htaccess`).
- **`/conf`** : Fichiers de configuration de l'application (ex: `params.ini` pour les connexions à la base de données).
- **`/css`** : Contient les feuilles de style CSS, principalement basées sur Bootstrap 3.
- **`/data`** : Stockage des données brutes, comme les fichiers de chansons et les songbooks.
- **`/html`** : Contient des fragments de code HTML (header, footer) et des pages HTML statiques.
- **`/images`** : Icônes, images pour la navigation et avatars des utilisateurs.
- **`/js`** : Fichiers JavaScript, utilisant jQuery, Bootstrap.js, et des scripts personnalisés.
- **`/php`** : Cœur de l'application. C'est ici que réside toute la logique métier.
    - `chanson/`: Gestion des chansons (recherche, affichage, formulaire).
    - `document/`: Gestion des documents associés.
    - `lib/`: Bibliothèque de fonctions et classes utilitaires (connexion MySQL, génération PDF, etc.).
    - `navigation/`: Scripts pour le menu, le login, le footer.
    - `playlist/`: Gestion des playlists.
    - `songbook/`: Gestion des songbooks.
    - `utilisateur/`: Gestion des utilisateurs (création, connexion, etc.).
- **`/tests`** : Contient les tests unitaires (PHPUnit) pour valider le bon fonctionnement du code.
- **`/vendor`** : Dépendances gérées par Composer (ex: PHPUnit, etc.).
- **`/vignettes`** : Probablement pour les miniatures d'images ou de documents.

## Technologies identifiées

- **Backend** : PHP (version probablement >= 5.x ou 7.x, vu l'époque).
- **Frontend** : HTML, CSS (Bootstrap 3), JavaScript (jQuery).
- **Base de données** : MySQL (supposé d'après `configMysql.php` et `dbPartoches.sql`).
- **Tests** : PHPUnit.
- **Dépendances** : Gérées avec Composer.
- **Serveur** : Apache (supposé d'après `.htaccess`).
- **Conteneurisation** : Docker (`docker-compose.yml`).

## Conventions de codage et Bonnes pratiques

### 🎸 Rendu HTML (Heredoc)
Pour éviter les erreurs de guillemets imbriqués et améliorer la lisibilité, privilégier systématiquement la syntaxe **Heredoc** pour les blocs HTML importants. 
Cela permet d'injecter des variables PHP directement sans concaténation (`.`).

```php
$html = <<<HTML
    <div class="ma-carte">
        <h2 style="color: $couleur;">$titre</h2>
        <p>Texte "entre guillemets" sans soucis.</p>
    </div>
HTML;
```

### 🎼 Accumulation de sortie
Ne pas multiplier les `echo`. Il est préférable d'accumuler tout le contenu de la page dans une variable unique (généralement `$html` ou `$sortie`) et de l'afficher une seule fois à la fin du script. Cela facilite la maintenance, le débuggage et permet d'éventuelles modifications de headers (redirections) avant l'envoi au navigateur.

### 🌲 Palette Canopée (Style)
Respecter la palette de couleurs "Canopée" pour les nouveaux développements :
- **Marron foncé** : `#2b1d1a` (Fonds, titres)
- **Marron clair/Bois** : `#D2B48C` (Bordures, éléments décoratifs)
- **Accent/Boutons** : `#8B4513` (Éléments d'action)
- **Fond/Beige** : `#F5F5DC` (Fond de page principal)
- **Framework** : Bootstrap 3.x (Thumbnails, Well, Labels).

### 🚫 Balise de fermeture PHP
Ne **jamais** utiliser la balise de fermeture `?>` à la fin d'un fichier purement PHP. Cela évite l'insertion accidentelle d'espaces ou de retours à la ligne qui pourraient provoquer des erreurs de type "Headers already sent".


