# 🚀 Politique & Procédure de Déploiement FTP (Projet Partoches)

Ce document répertorie la procédure de déploiement manuel par FTP pour envoyer les modifications sur le serveur de production (ex: Hostinger / Apache).

---

## 🎯 1. Périmètre & Règles d'Exclusion FTP

Lors d'un déploiement FTP, **seuls les fichiers applicatifs et de production doivent être transférés**.
Il faut impérativement **exclure** les fichiers de dev, de test, de conteneurisation et de documentation.

### 🟢 Fichiers et Dossiers à Transférer (Production)
- Everything inside `src/public/` (ou le dossier `public_html` du serveur FTP) :
  - `src/public/php/` (contrôleurs, services, vues)
  - `src/public/css/` (feuilles de style Canopée)
  - `src/public/js/` (scripts JavaScript vanilla)
  - `src/public/images/` (images d'interface et icônes)
  - `src/public/html/` (pages statiques)
  - `src/public/vendor/` (dépendances web front-end)
  - `src/autoload.php`
- `src/data/database/migrations/*.sql` (pour l'exécution des scripts de migration BDD)

### 🔴 Fichiers et Dossiers à EXCLURE (Dev / Local uniquement)
```
.git/
.github/
.vscode/
.idea/
node_modules/
cypress/
cypress.config.js
package.json
package-lock.json
tests/
documentation/
docker-compose.yml
Dockerfile
xdebug.ini
data/                  <-- Ne pas écraser les uploads et MP3 réels de prod !
src/data/conf/params.ini  <-- Ne pas écraser les accès BDD de prod !
```

---

## 🛠️ 2. Procédure de Déploiement avec FileZilla / WinSCP

### Étape A : Configuration des Filtres d'Exclusion
1. Dans **FileZilla** : Aller dans `Affichage` > `Filtres de nom de fichier` (ou `Ctrl + Y`).
2. Créer un nouveau filtre nommé **Partoches Dev Exclusions**.
3. Ajouter les règles de masque :
   - Dossiers : `tests`, `cypress`, `node_modules`, `documentation`, `.git`
   - Fichiers : `cypress.config.js`, `package.json`, `docker-compose.yml`, `Dockerfile`

### Étape B : Transfert des Fichiers
1. Connectez-vous à votre serveur FTP Hostinger.
2. Côté local : Naviguez dans `f:\Arnaud\projets-dev\partoches\src\public\`.
3. Côté distant : Naviguez dans le dossier web racine (`public_html` ou le dossier de l'application).
4. Transférez / Synchronisez uniquement les fichiers modifiés.

---

## ⚡ 3. Procédure Post-Déploiement (Base de Données)

Une fois les fichiers PHP/CSS transférés sur le serveur FTP :

1. Connectez-vous à la console d'administration sur le site en ligne :  
   `http://votre-domaine.fr/php/admin/params.php`
2. Rendez-vous dans la section **Diagnostic & Migrations SQL**.
3. Exécutez les migrations en attente (ex: `003_add_activation_token_to_utilisateur.sql` pour la fonctionnalité de confirmation par mail).
4. Testez la création d'un compte sur le site en ligne pour valider l'envoi d'e-mail d'activation.

---

## 🔄 4. Vers une Automatisation Future (GitHub Actions CI/CD)

À terme, la mise en place d'un workflow GitHub Actions (`.github/workflows/deploy.yml`) avec l'action `SamKirkland/FTP-Deploy-Action` permettra d'effectuer ce déploiement automatiquement à chaque `git push origin main`.
