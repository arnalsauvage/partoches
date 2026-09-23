# US-000 : Inscription Utilisateur & Validation par E-mail

**Statut** : `3-livrees_et_testees`  
**Priorité** : `Haute`  
**Livrée le** : `23 Septembre 2026`  
**Auteurs** : `Product Owner & Gemini`  

---

## 🎯 Énoncé Métier
1. **En tant que visiteur anonyme**,  
   Je veux **pouvoir me créer un compte membre en renseignant mes informations (login, email, prénom, nom, mot de passe) et en confirmant mon e-mail via un lien d'activation**,  
   Afin d'**accéder aux fonctionnalités réservées aux membres (lectures audio MP3)**.

2. **En tant qu'organisation responsable du site (Partoches / Canopée)**,  
   Je veux **restreindre l'accès public aux ressources audio (extraits de morceaux, citations, versions instrumentales et pistes ralenties) derrière un compte membre actif validé par e-mail**,  
   Afin de **me protéger contre l'aspiration/lecture intempestive par des bots automatisés et réduire drastiquement le risque d'être accusé de contrefaçon au titre du droit d'auteur**.

---

## 📋 Critères d'Acceptation Validés
- [x] Le formulaire affiche les 10 champs configurés dans l'ordre exact : Login, Email, Prénom & Nom, Mot de passe & Confirmation, Photo (optionnel), Site Web (optionnel), Signature (optionnel), Statut Membre.
- [x] L'inscription crée un compte en attente (`est_actif = 0`, `privilege = 0`).
- [x] La connexion via le formulaire standard est bloquée tant que le compte n'est pas activé.
- [x] Un e-mail d'activation est envoyé avec un jeton d'activation sécurisé (32 hex).
- [x] Le clic sur l'URL d'activation (`utilisateur_activation.php?token=...`) active le compte (`est_actif = 1`, `privilege = 1`) et connecte l'utilisateur.

---

## 🧪 Validation & Tests Effectués
- **PHPUnit** : `tests/UtilisateurInscriptionTest.php` (4/4 tests OK, 18 assertions).
- **Smoke Tests** : Validé sur `utilisateur_inscription.php` et `utilisateur_activation.php` (HTTP 200).
- **Design System** : Rendu Canopée responsive, zéro style inline HTML.
