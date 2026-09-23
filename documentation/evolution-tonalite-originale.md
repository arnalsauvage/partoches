# 🎯 Plan d’évolution — Ajout du champ `tonalite_originale` sur `chanson`

## 📌 Contexte
On ajoute un nouveau champ **“tonalité originale”** à la table `chanson` pour stocker la tonalité d’enregistrement du morceau, distincte de la tonalité de jeu/transcription déjà présente (`tonalite`).

## 🧩 Règles métier sur `tonalite_originale`
- Format strict : **majeur** par défaut ou **mineur** uniquement
- Forme autorisée :
  1. Lettre de **A à G**
  2. Option : suffixe `b` ou `#`
  3. Option : suffixe `m` (mineur)
- Exemples valides : `A`, `C`, `F#`, `Bb`, `Am`, `F#m`
- Max **3 caractères** → `VARCHAR(3)`

## 🗺️ Périmètre
- **BDD** : ajout de la colonne + migration compatible existing data
- **Entité** : `Chanson.php`
- **API / POST** : `chanson_post.php`, formulaires classique et Django
- **Affichage** : `chanson_voir.php`, listing éventuel
- **Tests** : unitaires + filtres + listing + smoke

---

## 1. 🧱 Base de données & versioning

### Impact
- Nouvelle colonne nullable.
- Migration via le système existant `AdminService::runPendingMigrations()`.

### Actions
1. Créer `data/database/migrations/YYYYMMDD_HHMMSS_add_tonalite_originale_to_chanson.sql`

   ```sql
   ALTER TABLE `chanson`
     ADD COLUMN `tonalite_originale` VARCHAR(3) NULL DEFAULT NULL
     AFTER `tonalite`;
   ```

2. Rollback :
   ```sql
   ALTER TABLE `chanson` DROP COLUMN `tonalite_originale`;
   ```
3. Mettre à jour `dbPartoches.sql` si nécessaire.

---

## 2. 🧠 Entité métier — `src/public/php/chanson/Chanson.php`

### Impact
- Nouvelle propriété, getter/setter, mapping ligne BDD, constructeurs, INSERT/UPDATE.

### Actions
1. Ajouter :
   ```php
   private ?string $_tonaliteOriginale = null;
   ```
2. Getter/setter :
   ```php
   public function getTonaliteOriginale(): ?string { return $this->_tonaliteOriginale; }
   public function setTonaliteOriginale(?string $v): void { $this->_tonaliteOriginale = $v; }
   ```
3. Constructeurs : initialiser à `null` dans `__construct0`.
4. `mysqlRowVersObjet()` : lire `$row[?]` correspondant à `tonalite_originale`.
5. `save()` : ajouter `tonalite_originale` dans INSERT et UPDATE.

---

## 3. 🔄 API / endpoints

### Fichiers
- `src/public/php/chanson/chanson_post.php`
- `src/public/php/chanson/ChansonFormRenderer.php`
- `src/public/php/chanson/views/chanson_form_view.phtml`
- `src/public/php/chanson/chanson_form_classic.php` si utilisé

### Actions
1. Lecture POST :
   ```php
   $ftonaliteOriginale = $_POST['ftonalite_originale'] ?? null;
   ```
2. Modes INS/MAJ/MAJ_SONGBPM : transmettre au constructeur ou setter.

### Validation
- Appliquer la normalisation UI ou backend : `Am7` → `Am`, `Amin` → `Am`, etc.

---

## 4. 🖥️ Affichage

### Fichiers
- `src/public/php/chanson/views/chanson_voir_view.phtml`
- `src/public/php/chanson/chanson_liste.php`
- `src/public/php/chanson/ChansonRepository.php`

### Actions
1. **Fiche** : badge si valeur présente.
2. **Listing** :
   - Option : colonne “Tonalité originale”.
   - Ajouter `tonalite_originale` aux filtres valides.
3. **Repository** : gérer le filtre dans `search()` et `count()`.

---

## 5. 🧪 Tests

### Fichiers
- `tests/chansonTest.php`
- `tests/chansonFiltreTest.php`
- `tests/chansonListeTest.php`
- smoke tests

### Actions
1. Ajouter/valider valeurs : `A`, `F#`, `Bb`, `Am`, `G#m`.
2. Tester refus/normalisation : `Am7` → `Am`.
3. Vérifier migration sur base vierge et existante.

---

## 6. 📦 Order of delivery

| Étape | Livrable | Vérification |
|-------|----------|--------------|
| 1 | Migration SQL | run de migration OK |
| 2 | `Chanson.php` | tests unitaires OK |
| 3 | `chanson_post.php` + formulaires | INS/MAJ OK |
| 4 | `chanson_voir_view.phtml` | badge OK |
| 5 | `chanson_liste.php` + repository | filtre OK |
| 6 | Tests complets | suite OK |
| 7 | Smoke | pages OK |
