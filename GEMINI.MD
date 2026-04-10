# 🎸 GEMINI.MD — Constitution du Projet Partoches
> Version 2.0 | Mise à jour : Avril 2026  
> ⚠️ Ce fichier ne peut être modifié que par décision explicite du PO,  
> documentée dans gemini-log.md avec la mention `[MODIF-CONSTITUTION]`.

---

## 0. 🚀 Protocole de Démarrage (CRITIQUE — Exécuter sans exception)

**À chaque début de session, dans cet ordre :**

1. Lire ce fichier (GEMINI.MD) intégralement
2. Lire `gemini-log.md` (racine)
3. **Rotation du journal (Automatisée) :**
   - L'agent doit impérativement lancer `docker exec -t site-partoches php /var/www/html/scripts/rotate_log.php`
   - Ce script archive les sessions passées dans `documentation/gemini-log/` et ne garde qu'un résumé de la session précédente dans `gemini-log.md`.
4. Résumer en 3 bullet points le contexte actuel **avant toute action**
5. Consulter `documentation/Backlog.md` pour les priorités
6. Signaler toute contradiction détectée entre le log et cette constitution

**Phrase d'amorce attendue du PO :**
> "Lis GEMINI.MD et gemini-log.md, résume le contexte, puis on attaque."

---

## 1. 🗺️ Identité du Projet

| Champ | Valeur |
|-------|--------|
| Nom | Partoches |
| Type | Application web — gestion de partitions / playlists / songbooks |
| Origine | 2018, en mutation SOLID depuis 2026 |
| Stack | PHP 8.2 / MariaDB 11.7 / Docker / HTML-CSS-JS vanilla |
| Environnement | Conteneur `site-partoches` |
| Encodage | UTF-8 sans BOM (obligatoire) |
| Conventions BDD | snake_case pour toutes les nouvelles colonnes |

---

## 2. 🏗️ Architecture des Dossiers

```
/src/public/php/
admin/          → Quartier technique (Params, Logs, BDD)
chanson/        → Gestion du catalogue
playlist/       → Listes manuelles & dynamiques
lib/            → Services et utilitaires partagés
[api, audit, document, liens, media, navigation, note, songbook, strum, todo, utilisateur] → Autres domaines métier

/src/public/css/
styles-communs.css      → Global (Menu, Footer)
composants-canopee.css  → Cartes et badges
params.css              → Administration

/src/public/js/
[feature].js            → Logique isolée par fonctionnalité

/src/data/database/migrations/
→ Scripts SQL numérotés (ALTER, CREATE uniquement)

/documentation/
Backlog.md              → Priorités produit
feature-xxx.md          → Doc par fonctionnalité majeure
gemini-log/             → Archives mensuelles du journal Gemini
```

---

## 3. 🏛️ Standards Architecturaux (Non négociables)

### 3.1 Séparation des Préoccupations (SOLID)

Toute fonctionnalité = 3 fichiers distincts :

| Couche | Fichier | Rôle |
|--------|---------|------|
| Contrôleur | `.php` | Reçoit la requête, sécurité, orchestration |
| Service | `.php` | Logique métier réutilisable |
| Vue | `.phtml` | Template HTML pur, variables injectées |

❌ **God Objects interdits** — un fichier = une responsabilité.

### 3.2 Rendu HTML

- Syntaxe **Heredoc** pour les blocs HTML significatifs
- Accumulation dans `$corps` ou `$html`
- **Un seul `echo` final** par page

### 3.3 Base de Données

- Toute modification de structure → fichier `.sql` numéroté dans `migrations/`
- Application via **Paramétrage > Diagnostic** (local et prod)
- **Requêtes préparées PDO obligatoires** — aucune concaténation de variable dans le SQL

---

## 4. 🎨 Design System (Non négociable)

🚫 **INTERDIT** : L'attribut `style="..."` dans tout fichier PHP/PHTML  
✅ **OBLIGATOIRE** : Classes CSS du Design System exclusivement  
✅ **OBLIGATOIRE** : Toute nouvelle règle visuelle → fichier CSS approprié

---

## 5. 🔒 Sécurité (Non négociable)

```
Ces règles s'appliquent à CHAQUE ligne de code produite.
L'agent doit les vérifier avant de soumettre tout snippet.
```

| Règle | Standard |
|-------|----------|
| Requêtes SQL | Requêtes préparées PDO **uniquement** |
| Données utilisateur | Jamais dans les logs, jamais en clair |
| Sessions | `session_regenerate_id(true)` après chaque login |
| Uploads | Validation MIME + taille + renommage systématique |
| Includes | `__DIR__` obligatoire pour tous les chemins |
| Sessions (init) | `if (session_status() === PHP_SESSION_NONE) session_start();` |

---

## 6. 📌 Décisions Architecturales Figées (ADR)

> Ces décisions ne peuvent être rouvertes que par le PO,  
> avec mention `[ADR-REVISION]` dans le log.

| #  | Date | Décision                     | Justification                                           | Statut |
|----|------|------------------------------|---------------------------------------------------------|--------|
| 01 | 2026-03 | PDO natif, pas d'ORM         | Simplicité + contrôle SQL direct                        | 🔒 Figé |
| 02 | 2026-03 | Pas de framework PHP         | Maîtrise totale, dette technique maîtrisée              | 🔒 Figé |
| 03 | 2026-03 | JS vanilla, pas de framework | Légèreté + cohérence avec la stack                      | 🔒 Figé |
| 04 | 2026-04 | Migration vers SOLID         | Remplacement progressif des God Objects                 | 🔒 Figé |
| 05 | 2026-04 | Dépendances identifiées      | Installation des composants externes dans public/vendor | 🔒 Figé |

---

## 7. 🧪 Qualité & Validation

### Tests

```bash
# Lancer les tests unitaires
docker exec -t site-partoches vendor/bin/phpunit tests/

# Smoke tests : vérifier les 23 pages critiques sans erreur PHP ou HTML (voir SongbookSmokeTest.php)
# Django Linter : zéro "Unexpected end tag" avant chaque commit
```

### Checklist avant chaque commit

- [ ] Linter passé (zéro erreur HTML)
- [ ] Aucun `style="..."` introduit
- [ ] Requêtes SQL en mode préparé
- [ ] Migration SQL créée si modification de BDD
- [ ] `gemini-log.md` mis à jour

---

## 8. 📋 Format du Backlog (Référence)


## 🔴 Urgent / Bloquant
- [ ] #XX - Description courte

## 🟡 Sprint en cours
- [ ] #XX - Description courte

## 🟢 Icebox (à planifier)
- [ ] #XX - Description courte

## ✅ Terminé (mois en cours)
- [x] #XX - Description courte
