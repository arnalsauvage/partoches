# US-001 : Mise en place des tests End-to-End (E2E) avec Cypress

**Statut** : `3-livrees_et_testees`  
**Priorité** : `Haute`  
**Livrée le** : `23 Septembre 2026`  
**Auteurs** : `Ingénieur Test (QA), Tech Lead & Gemini`  

---

## 🎯 1. Énoncé Métier
En tant qu'**équipe projet (Dev, QA, PO)**,  
Je veux **une suite de tests End-to-End automatisés avec Cypress couvrant les parcours utilisateurs clés**,  
Afin de **valider visuellement et fonctionnellement l'application du point de vue d'un vrai navigateur, détecter les régressions d'interface et sécuriser les livraisons en production**.

---

## 👥 2. Matrice de Revue Multi-Profils (Validation des 6 Rôles)

| Profil | Avis & Validation | Statut |
| :--- | :--- | :---: |
| **Product Owner (PO)** | *"Essentiel pour garantir la stabilité du tunnel d'inscription utilisateur et de la lecture des médias."* | ✅ Validé |
| **Chef de Projet (PM)** | *"Aligné avec les objectifs de qualité du trimestre, effort maîtrisé (couverture ciblée des flux majeurs)."* | ✅ Validé |
| **Scrum Master (SM)** | *"Critères d'acceptation et tâches bien découpés. US terminée."* | ✅ Validé |
| **Tech Lead** | *"Installation de Cypress à la racine sans impacter le conteneur PHP Docker. Intégration npm propre."* | ✅ Validé |
| **Ingénieur Test (QA)** | *"Couverture initiale ciblée sur 4 parcours critiques : Login, Inscription, Catalogue Médias, Songbooks."* | ✅ Validé |
| **Développeur (Dev)** | *"Scénarios Gherkin explicites. Les sélecteurs CSS Canopée correspondent parfaitement."* | ✅ Validé |

---

## 📋 3. Critères d'Acceptation (Gherkin)

### Scénario 1 : Authentification utilisateur (Login modal & Déconnexion)
```gherkin
Étant donné un visiteur sur la page d'accueil "http://localhost:8080"
Quand il clique sur le bouton de connexion "#afficherPopup"
Et qu'il saisit le login "membre" et le mot de passe "membre123"
Et qu'il soumet le formulaire "#btn-login-submit"
Alors l'utilisateur est connecté et voit son profil et son statut "membre" dans la barre de navigation
```

### Scénario 2 : Tunnel d'inscription avec demande d'activation par e-mail
```gherkin
Étant donné un utilisateur sur "/php/utilisateur/utilisateur_inscription.php"
Quand il remplit le formulaire avec des informations valides (login, email, prénom, nom, mot de passe)
Et qu'il soumet le formulaire d'inscription
Alors il voit un message de confirmation "Compte créé avec succès !"
Et une invitation à valider son compte par e-mail
```

### Scénario 3 : Restriction des médias audio (MP3) pour les invités
```gherkin
Étant donné un visiteur anonyme sur "/php/media/listeMedias.php"
Alors les éléments de carte média de type MP3 affichent le badge "🔒 Connexion requise"
Et le clic sur une carte audio redirige vers la page de connexion ou la fiche chanson sans exposer le MP3 brut
```

### Scénario 4 : Navigation et affichage d'un Songbook
```gherkin
Étant donné un utilisateur sur "/php/songbook/songbook-portfolio.php"
Quand il clique sur un recueil de partitions
Alors le portfolio charge la vue détaillée du songbook avec la liste des morceaux et les options d'impression PDF
```

---

## 🏗️ 4. Impact Technique & Architecture

- **Stack ajoutée** : Cypress v13.x (Node.js / npm)
- **Arborescence créée** :
  ```
  / (racine du projet)
  ├── package.json                   → Dépendances dev (cypress)
  ├── cypress.config.js              → Config Cypress (baseUrl: http://localhost:8080)
  └── cypress/
      ├── e2e/
      │   ├── 01_auth.cy.js          → Scénarios Login / Logout
      │   ├── 02_inscription.cy.js   → Tunnel Inscription & Activation
      │   ├── 03_medias.cy.js        → Galerie médias & restrictions MP3
      │   └── 04_songbook.cy.js      → Navigation Songbooks & Morceaux
      └── support/
          ├── commands.js            → Commandes personnalisées (`cy.login()`)
          └── e2e.js                 → Configuration du runner E2E
  ```

---

## 🧪 5. Stratégie de Test (Pyramide des Tests)

- **Unitaires (PHPUnit)** : Validation de la logique métier serveur (`MediaService`, `Utilisateur`).
- **Smoke Tests (PHPUnit)** : Vérification du statut HTTP 200 et absence de Fatal Error sur 46 pages.
- **E2E (Cypress - Cette US)** : Validation du rendu réel dans Chrome/Firefox, interactions JS (popups, modals) et flux bout-en-bout.

---

## 🛠️ 6. Checklist des Tâches Techniques

- [x] Initialiser le projet Node `package.json` à la racine et installer Cypress (`npm i -D cypress`).
- [x] Créer `cypress.config.js` pointant vers `http://localhost:8080`.
- [x] Écrire la commande personnalisée `cy.login(user, pass)` dans `cypress/support/commands.js`.
- [x] Rédiger le spec `01_auth.cy.js` (Login modal, échec mot de passe, déconnexion).
- [x] Rédiger le spec `02_inscription.cy.js` (Formulaire avec ordre : Login, Email, Prénom/Nom, Pass).
- [x] Rédiger le spec `03_medias.cy.js` (Filtres, cartes Canopée, restriction MP3 pour invités).
- [x] Rédiger le spec `04_songbook.cy.js` (Consultation portfolio et liste des morceaux).
- [x] Documenter les commandes dans `documentation/politique-des-tests.md`.
