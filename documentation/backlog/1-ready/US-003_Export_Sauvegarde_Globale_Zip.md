# US-003 : Module de Sauvegarde Globale et Export ZIP

**Statut** : `1-ready`  
**Priorité** : `Haute`  
**Auteur** : `Product Owner / Agent AI`  

## 🎯 Énoncé Métier
En tant qu'**Administrateur du site Partoches**,  
Je veux **générer et télécharger en un clic une archive ZIP complète contenant la base de données SQL, tous les fichiers data (partitions, images, audios) et la configuration site (`params.ini`)**,  
Afin de **disposer d'une sauvegarde autonome intégrale et sécurisée de tout ce qui n'est pas versionné par Git**.

## 📋 Critères d'Acceptation (Gherkin)

```gherkin
Scénario: Export d'une sauvegarde complète au format ZIP
  Étant donné un utilisateur connecté avec le privilège Administrateur
  Quand il clique sur "Générer la sauvegarde complète (ZIP)" dans la page de paramétrage
  Alors le système génère un dump SQL complet de la base de données
  Et compresse dans un fichier archive ZIP :
    - Le dump SQL (db_dump.sql)
    - Le dossier complet des données (data/chansons, data/songbooks, etc.)
    - Le fichier de configuration (params.ini)
    - Un manifest.json contenant les métadonnées (date, taille, version)
  Et déclenche le téléchargement de l'archive `partoches-backup-YYYY-MM-DD.zip`.

Scénario: Restriction de l'accès à l'export aux non-administrateurs
  Étant donné un utilisateur non connecté ou membre sans privilège admin
  Quand il tente d'accéder au script de sauvegarde `/php/admin/backup_export.php`
  Alors le système refuse l'accès et redirige vers la page d'accueil ou de login.
```

## 🏗️ Impact Technique & Architecture
- **Service** : `src/public/php/admin/BackupService.php` (`generateDatabaseDump()`, `createBackupZip()`).
- **Contrôleur** : `src/public/php/admin/backup_export.php` (Téléchargement HTTP stream avec headers ZIP).
- **Extension PHP** : Utilisation de `ZipArchive` natif (activé en standard dans Docker & Hostinger).

## 🧪 Stratégie de Test
- **PHPUnit** : `tests/BackupServiceTest.php` (Vérification de la génération du dump SQL et de la structure du fichier ZIP).
- **Smoke Tests** : Validation du contrôleur `/php/admin/backup_export.php`.
- **Cypress E2E** : `cypress/e2e/16_sauvegarde_globale_zip.cy.js`.

---
### 👥 Matrice de Revue
- [x] **PO** : Validé
- [x] **PM** : Validé
- [x] **SM** : Validé
- [x] **Tech Lead** : Validé (Architecture SOLID)
- [x] **QA** : Validé (Tests unitaires & E2E définis)
- [x] **Dev** : Validé
