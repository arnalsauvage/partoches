# 🎵 Architecture du Module Média (Refactoring Terminé)

Ce document archive les choix d'architecture réalisés lors de la refonte du module Média. La fonctionnalité de refactoring est **terminée** et le module suit désormais les principes **SOLID** et le standard "Django Style" du projet.

## 🎯 Objectifs de la Refonte (Atteints)

L'ancien système mélangeait l'accès à la base de données, la logique métier et le rendu HTML dans une seule et même classe (le fameux "God Object").
L'objectif était de découper ces responsabilités pour rendre le code :
1. **Maintenable** : Chaque fichier a un rôle unique et clair.
2. **Testable** : La séparation permet d'écrire des tests unitaires PHPUnit isolés.
3. **Évolutif** : Le changement du design (HTML/CSS) n'impacte pas les requêtes SQL.

## 🏗️ Nouvelle Architecture (Séparation des Préoccupations)

Le domaine "Média" (`src/public/php/media/`) est désormais architecturé autour de 5 piliers :

### 1. L'Entité : `Media.php`
- **Rôle** : Modèle de données pur (DTO / Entity).
- **Responsabilité** : Ne contient que les propriétés d'un média (`id`, `titre`, `type`, `lien`, `datePub`, etc.) et ses *getters/setters*.
- **Règle** : Ne fait **aucune** requête SQL et ne génère **aucun** HTML.

### 2. L'Accès aux Données : `MediaRepository.php`
- **Rôle** : Interface unique avec la base de données (Repository Pattern).
- **Responsabilité** : Exécute toutes les requêtes SQL (`SELECT`, `INSERT`, `DELETE`, `TRUNCATE`) liées à la table `media`.
- **Règle** : C'est le seul endroit autorisé à utiliser l'objet MySQL pour les médias. Retourne des objets `Media` hydratés.

### 3. La Logique Métier : `MediaService.php`
- **Rôle** : Le cerveau fonctionnel.
- **Responsabilité** : Gère les algorithmes complexes, comme la transformation d'un `Document` ou d'un `LienUrl` en objet `Media`, et la régénération complète du catalogue (batch processing).
- **Règle** : Coordonne les objets mais délègue le stockage au Repository.

### 4. Le Moteur de Rendu : `MediaRenderer.php`
- **Rôle** : Génération de l'interface utilisateur (View / Presenter).
- **Responsabilité** : Transforme un objet `Media` en une carte HTML formatée (utilise la palette "Canopée" et Bootstrap 3).
- **Règle** : Ne traite aucune logique métier, s'occupe exclusivement de la présentation.

### 5. Le Contrôleur/Vue : `listeMedias.php`
- **Rôle** : Point d'entrée pour l'utilisateur (Page d'accueil des médias).
- **Responsabilité** : Récupère les requêtes (filtres, pagination), appelle le Repository pour obtenir les données, et utilise le Renderer pour afficher la galerie.
- **Règle** : Logique d'orchestration minimale (pas de SQL brut).

## ✅ Bilan et Avantages
- **Sécurité et Propreté** : Les erreurs de type "Headers already sent" dues à des `echo` ou `die()` perdus dans les classes de données sont éliminées.
- **Performances** : La régénération du catalogue de médias est isolée dans un service testable et optimisé.
- **Design System** : Le Renderer s'intègre parfaitement avec notre nouvelle architecture CSS (`composants-canopee.css`).

---
*Refactoring réalisé et documenté par Django (Gemini) - Avril 2026*