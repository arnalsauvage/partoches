# 🧰 Feature : Outils & Administration

## 📝 Description
Ce module regroupe les fonctions transverses, la configuration technique et les outils de diagnostic du site.

## 🚀 Outils Inclus
- **Paramétrage (`params.php`)** : Gestion des variables globales (Nom du site, Logo, Mode Debug).
- **Audit (`global_audit.php`)** : Vérification de l'intégrité de la base de données et des fichiers orphelins.
- **Nettoyage Automatique** : Script de suppression des fichiers temporaires GS (Ghostscript) de plus de 24h.
- **Mise à Jour BDD** : Système de migrations SQL numérotées appliquées via l'interface d'admin.

## 🛠️ Scripts de Maintenance
```bash
# Rotation des logs Gemini
php scripts/rotate_log.php

# Audit des fichiers
php src/public/php/admin/audit_fichiers.php
```
