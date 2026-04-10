# 📋 Backlog — Projet Partoches

## 🎼 Vision
Gérer, afficher et exporter des partitions pour ukulélé et plus de façon fluide, avec une architecture robuste et un design moderne.

---

## 🔴 Urgent / Bloquant
- [ ] **#04 — Mise en place rotation automatique des logs** (Mécanique de changement de jour pour `gemini-log.md`).
- [ ] **#05 — Sécurisation des dossiers sensibles** (Vérifier que `.git`, `src/data`, etc. ne sont pas accessibles par le web).

## 🟡 Sprint en cours
- [ ] **#06 — Audit et correction des liens morts** (Utiliser `check_liens.php` pour assainir la base de données).
- [ ] **#07 — Nettoyage automatique du dossier `/data/temp/`** (Script pour supprimer les PDF temporaires Ghostscript vieux de plus de 24h).

## 🟢 Icebox (à planifier)
- [ ] **#08 — Intégration du Design System Canopée** (Migration progressive de toutes les pages vers les nouveaux composants CSS).
- [ ] **#09 — Refactorisation des pages "Admin"** (Appliquer SOLID à `params.php` et aux outils de diagnostic).

## ✅ Terminé (Avril 2026)
- [x] **#01 — Correction ligne noire PDF** (Désactivation de l'en-tête TCPDF dans `SongbookPdf`).
- [x] **#02 — Migration TCPDF + FPDI + Ghostscript** (Support natif des PDF modernes v1.4+ sur Hostinger).
- [x] **#03 — Centralisation des logs Gemini** (Fusion de `session_log.md` et `REPONSES-GEMINI.md` dans `gemini-log.md`).
- [x] **#04 — Mise à jour de la Constitution GEMINI.MD** (Protocole de démarrage et standards consolidés).

---
*Django : "Mira Arnal, on a enfin une partition pour notre équipe ! On sait d'où on vient et où on va."* 🎷🤘✨
