# 🤠 Réponses & Actions — Django

## Session du 13 Novembre 2026 — US-086 : Duplication d'une chanson en vue liste

### 🎯 Demande
Dans la liste des chansons (`chanson_liste.php?vue=liste`), permettre à l'administrateur de dupliquer une chanson en publiant tous ses attributs et liens associés, mais sans les documents attachés.

### 🛠️ Actions réalisées
1. **Service Métier (`Chanson.php`)** :
   - Ajout de la méthode statique `Chanson::dupliqueChanson(int $idChanson): int`.
   - Copie intégrale des métadonnées : titre, interprète, année, tempo, mesure, pulsation, tonalité, tonalité originale, pochette (`cover`), contributeur (`idUser`), publication à 1 (publié), date à ce jour, hits réinitialisés à 0.
   - Duplication des liens externes (`LienUrl`) et des rythmiques (`LienStrumChanson`).
   - Exclusion stricte des documents attachés (aucune entrée créée dans la table `document`, aucun fichier physique copié).
   - Verrouillage de sécurité : contrôle `estAdmin()`.

2. **Contrôleur (`chanson_post.php`)** :
   - Prise en charge du mode `mode=DUP`.
   - Vérification des privilèges administrateur via `estAdmin()`.
   - Redirection vers `chanson_form.php?id=$newId&msg=OK_DUP` avec message Toastr.

3. **Vue Liste (`chanson_liste.php`)** :
   - Ajout du bouton Dupliquer pour l'administrateur avec l'icône `glyphicon-duplicate`.
   - Groupement propre avec le bouton Supprimer via `<div class='btn-group btn-group-xs'>`.
   - Boîte de confirmation JS `confirm(...)` pour éviter les clics involontaires.
   - Respect strict du Design System (0 style inline ajouté).

4. **Notifications & Ergonomie (`ChansonFormNewRenderer.php`, `chanson_form_classic.php` & `chanson_liste.php`)** :
   - Prise en compte du paramètre `msg=OK_DUP` pour afficher un toast de succès Toastr.

5. **Tests (`tests/ChansonTest.php`)** :
   - Ajout du test unitaire `testDupliqueChanson` validant les attributs, les liens, les strums et l'absence de documents attachés.

6. **Déploiement & CI/CD** :
   - Commit `571d2b4` : `feat(chanson): duplication d'une chanson en vue liste pour l'admin [monmac]` poussé sur `origin/master`.
   - Pipeline GitHub Actions validé avec succès (Lint + tests PHPUnit).
   - Déploiement automatique FTP vers Hostinger (`partoches.canopee-musique.fr`) exécuté avec succès.
