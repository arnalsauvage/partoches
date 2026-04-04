# 🎶 Partoches Canopée

Bienvenue sur le projet **Partoches**, l'application web dédiée à la gestion et au partage de partitions pour les passionnés de musique (et particulièrement de Ukulélé ! 🎸). Initié en 2018, ce projet évolue vers des standards de développement modernes et une architecture robuste.

## 🏗️ Architecture & Choix Techniques

Le projet suit une mutation profonde vers les principes **SOLID** et une séparation stricte des préoccupations (SoC) :

*   **Logic (PHP 8.2)** : Découpage par domaines fonctionnels (`chanson`, `playlist`, `admin`, `utilisateur`). Chaque domaine est orchestré par un contrôleur qui délègue la logique technique à des **Services** dédiés.
*   **Data (MariaDB 11.7)** : La structure de la base de données est synchronisée entre le développement et la production via un moteur de **migrations SQL** automatisé.
*   **UI/UX (Design System)** : Utilisation d'un **Design System "Canopée"** centralisé. Fini le style inline ! Toute l'apparence est pilotée par des feuilles de style thématiques (`params.css`, `composants-canopee.css`) et des composants réutilisables (`ComposantsUI.php`).
*   **Containerisation** : Environnement de développement standardisé sous **Docker**, garantissant la parité entre les postes de travail et le serveur de production.

## 🗂️ Structure du Projet

*   `src/public/php/` : Cœur de l'application (Contrôleurs, Services).
*   `src/public/css/` : Garde-robe du projet (Design System Canopée).
*   `src/data/database/migrations/` : Historique des évolutions de la base de données.
*   `tests/` : Suite de tests automatisés pour garantir la non-régression.

## 🧪 Qualité & Validation

La stabilité du site est assurée par un protocole rigoureux :
*   **Smoke-Tests** : Une suite de 46 tests vérifie automatiquement le rendu HTML de chaque page du site pour garantir zéro "Parse Error".
*   **Tests Unitaires** : Validation de la logique métier (Chiffrement, Algorithmes, etc.) via PHPUnit.
*   **Linter Django** : Un script d'audit HTML interne traque les balises mal fermées et les erreurs de conformité W3C.

## 🤖 Collaboration avec l'IA (Gemini/Django)

Ce projet est activement maintenu et amélioré en collaboration avec **Gemini (alias Django)**, un agent IA spécialisé en ingénierie logicielle. 

Le fichier **`GEMINI.MD`** à la racine sert de "Partition de Référence" pour l'IA. Il contient les conventions de codage, les chartes graphiques et les protocoles de validation spécifiques à respecter pour chaque nouveau solo de code.

---
*🎼 "Accorder le code pour que la musique soit parfaite."* 🎷🤘✨
