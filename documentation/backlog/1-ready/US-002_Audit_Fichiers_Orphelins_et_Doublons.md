# US-002 : Audit et Détection des Fichiers Orphelins & Doublons dans /data

**Statut** : `1-ready`  
**Priorité** : `Haute`  
**Auteur** : `Product Owner / Agent AI`  

## 🎯 Énoncé Métier
En tant qu'**Administrateur du site Partoches**,  
Je veux **auditer le répertoire `src/public/data/` pour détecter les fichiers orphelins (non référencés en BDD) et les fichiers doublons (empreintes MD5 identiques)**,  
Afin de **nettoyer le serveur, libérer de l'espace disque et garantir l'intégrité du catalogue**.

## 📋 Critères d'Acceptation (Gherkin)

```gherkin
Scénario: Détection des fichiers orphelins dans data/
  Étant donné un utilisateur connecté avec le privilège Administrateur
  Quand il accède à l'outil d'audit des fichiers orphelins
  Alors le système analyse l'arborescence src/public/data/
  Et affiche la liste des fichiers physiquement présents mais absents de la table document/media
  Et propose un bouton de suppression sécurisée.

Scénario: Détection des fichiers doublons par empreinte MD5
  Étant donné un utilisateur connecté avec le privilège Administrateur
  Quand il lance la détection de doublons sur le répertoire data/
  Alors le système calcule le hash MD5 de chaque fichier
  Et regroupe les fichiers possédant la même empreinte digitale
  Et affiche leur taille et leurs emplacements respectifs.
```

## 🏗️ Impact Technique & Architecture
- **Service** : `src/public/php/admin/DataAuditService.php` (`findOrphanFiles()`, `findDuplicateFiles()`, `deleteOrphanFiles()`).
- **Contrôleur** : `src/public/php/admin/audit_orphelins.php` (Sécurité `estAdmin()`).
- **Vue** : `src/public/php/admin/views/audit_orphelins_view.phtml` (Design System Canopée, Badges, Tables de résultat).

## 🧪 Stratégie de Test
- **PHPUnit** : `tests/DataAuditServiceTest.php` (Création de fichiers temporaires orphelins/doublons et vérification des résultats de détection).
- **Smoke Tests** : Statut HTTP 200 sur `/php/admin/audit_orphelins.php`.
- **Cypress E2E** : `cypress/e2e/15_audit_orphelins_et_doublons.cy.js`.

---
### 👥 Matrice de Revue
- [x] **PO** : Validé
- [x] **PM** : Validé
- [x] **SM** : Validé
- [x] **Tech Lead** : Validé (Architecture SOLID)
- [x] **QA** : Validé (Tests unitaires & E2E définis)
- [x] **Dev** : Validé
