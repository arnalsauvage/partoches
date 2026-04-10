# 🎼 Feature : Chasse aux Fausses Notes (Audit des Liens)

## 🎯 Vision (Polo Style)
"Un lien mort, c'est une corde cassée !" 
L'objectif est d'identifier toutes les URLs invalides (404, timeouts, erreurs serveurs) stockées en base de données pour garantir une expérience utilisateur sans aucune fausse note.

## 🏗️ Architecture Technique (Tyla & Dédée)

### 1. Périmètre de l'Audit
Le scan doit couvrir les tables suivantes :
- `Lien` : URLs externes (YouTube, tablatures, etc.).
- `Media` : Fichiers images, PDF, MP3.
- `Chanson` : Liens éventuellement présents dans les descriptions ou métadonnées.

### 2. Mécanique de Vérification
- Utilisation de **cURL** en PHP.
- Mode `CURLOPT_NOBODY = true` (HEAD request uniquement) pour économiser la bande passante et le temps CPU sur Hostinger.
- Gestion des timeouts (max 5s par lien) pour éviter de bloquer le script.

### 3. Interface d'Administration
- Une nouvelle page `src/public/php/admin/audit_liens.php`.
- Utilisation du **Design System Canopée** pour le tableau des résultats.
- Filtres : "Tous", "Morts (404)", "Erreurs (500+)", "Inaccessibles".

## 🛠️ Plan d'Action

1. [ ] **Analyse** : Étudier `check_liens.php` (existant) et identifier les tables/colonnes contenant des URLs.
2. [ ] **Service** : Créer `src/public/php/lib/LienAuditService.php` (Logique SOLID).
3. [ ] **Interface** : Développer la page d'administration et le contrôleur.
4. [ ] **Traitement par lots** : Implémenter une mécanique AJAX pour scanner les liens sans timeout serveur.
5. [ ] **Actions de correction** : Ajouter des boutons "Éditer" ou "Supprimer" directement dans le rapport.

---
*Dernière mise à jour : 10 Avril 2026*
