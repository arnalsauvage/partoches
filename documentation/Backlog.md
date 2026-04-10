# 📋 Backlog — Projet Partoches

## 🎼 Vision
Gérer, afficher et exporter des partitions pour ukulélé et plus de façon fluide, avec une architecture robuste et un design moderne.

---

## 🔴 Urgent / Bloquant
- [x] ** bugfix : suppression d'un morceau dans edition songbook ko** dans la page de gestion de songbook  http://localhost:8080/php/songbook/songbook_form.php?id=119
dans "Sommaire des morceaux" , si je clique sur la croix pour supprimer un morceau, on obtient
- Appel avec mode = SUPPRDOC, id = 119, idDoc = 175 idSongbook = 119
  ( ! ) Fatal error: Uncaught Error: Call to undefined function supprimeLienIdDocIdSongbook() in /var/www/html/src/public/php/songbook/songbook_get.php on line 81
  ( ! ) Error: Call to undefined function supprimeLienIdDocIdSongbook() in /var/www/html/src/public/php/songbook/songbook_get.php on line 81
- [x] ** bugfix : la croix de suppression d'un morceau dans edition songbook ko** dans la page de gestion de songbook  http://localhost:8080/php/songbook/songbook_form.php?id=119
  est vraiment trop proche du bouton déplacer... Il faudrait l'aligner à droite dans le li où elle est : 
<li class="ui-state-default sb-sortable-item ui-sortable-handle" data-index="84" data-position="1">
            <span>
                <i class="glyphicon glyphicon-menu-hamburger text-muted" style="margin-right: 15px;" aria-hidden="true"></i>
                <strong>1.</strong> New-York-avec-toi-v3.pdf
            </span>
            <a href="songbook_get.php?id=24&amp;idDoc=84&amp;mode=SUPPRDOC" class="btn btn-link btn-xs text-danger" title="Retirer du recueil" aria-label="Retirer New-York-avec-toi-v3.pdf du recueil" style="margin-left: auto;">
                <i class="glyphicon glyphicon-remove" aria-hidden="true"></i>
            </a>
        </li>

## 🟡 Sprint en cours

## 🟢 Icebox (à planifier)
- [ ] **#08 — Intégration du Design System Canopée** (Migration progressive de toutes les pages vers les nouveaux composants CSS).
- [ ] **#09 — Refactorisation des pages "Admin"** (Appliquer SOLID à `params.php` et aux outils de diagnostic).

## ✅ Terminé (Avril 2026)
- [x] **#01 — Correction ligne noire PDF** (Désactivation de l'en-tête TCPDF dans `SongbookPdf`).
- [x] **#02 — Migration TCPDF + FPDI + Ghostscript** (Support natif des PDF modernes v1.4+ sur Hostinger).
- [x] **#03 — Centralisation des logs Gemini** (Fusion de `session_log.md` et `REPONSES-GEMINI.md` dans `gemini-log.md`).
- [x] **#04 — Mise à jour de la Constitution GEMINI.MD** (Protocole de démarrage et standards consolidés).
- [x] **#04 — Mise en place rotation automatique des logs** (Mécanique de changement de jour pour `gemini-log.md`).
- [x] **#05 — Sécurisation des dossiers sensibles** (Vérifier que `.git`, `src/data`, etc. ne sont pas accessibles par le web).
- [x] **#06 — Audit et correction des liens morts** (Utiliser `check_liens.php` pour assainir la base de données).
- [x] **#07 — Nettoyage automatique du dossier `/data/temp/`** (Script pour supprimer les PDF temporaires Ghostscript vieux de plus de 24h).

---
*Django : "Mira Arnal, on a enfin une partition pour notre équipe ! On sait d'où on vient et où on va."* 🎷🤘✨
