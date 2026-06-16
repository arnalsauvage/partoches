# 👥 Feature : Utilisateurs & Sécurité

## 📝 Description
Gère les profils des musiciens, l'authentification et le système de permissions fines (RBAC).

## 🏗️ Architecture
- **Modèle** : `Utilisateur.php` (Classe avec typage strict PHP 8.2).
- **Contrôleur** : `utilisateur_get.php` (Traitement sécurisé des actions).
- **Vue** : `utilisateur_liste.php` (Cartes Canopée).

## 🔐 Système de Privilèges
| Niveau | Libellé | Droits |
|---|---|---|
| 0 | **Invité** | Consultation publique uniquement. |
| 1 | **Membre** | Consultation + Modification de son propre profil. |
| 2 | **Éditeur** | Gestion des chansons, playlists et strums. |
| 3 | **Admin** | Gestion complète (utilisateurs, paramètres, suppression). |

## 🚀 Sécurité Implémentée
- **Chiffrement** : Utilisation de la classe `Chiffrement` (OpenSSL) pour les mots de passe.
- **Protection Cross-User** : Un utilisateur ne peut pas modifier ou supprimer un profil tiers (sauf Admin).
- **Auto-Protection** : Un administrateur ne peut pas se supprimer lui-même depuis l'interface de liste.
