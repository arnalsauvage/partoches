# RÉSUMÉ DES RÉPONSES GEMINI (Django)

## Date : Jeudi 9 Avril 2026 (Session 8)

### 🎼 Upgrade des Outils PDF & Organisation Vendor
- **Dossier Centralisé** : Création de `src/public/vendor/` regroupant toutes les dépendances (PHP, CSS, JS, Fonts).
- **Chemins Absolus** : Intégration de `VENDOR_DIR` et `VENDOR_URL` dans `autoload.php`.
- **Moteur PDF Moderne** : Migration vers **TCPDF + FPDI** pour supporter nativement les PDF 1.5+ (flux compressés).
- **UI/UX Améliorée** : 
    - Gestion des alertes "Warning" (orange) en cas de morceaux manquants.
    - Stabilisation du focus lors de la génération (modale de rapport).

### 🎸 Note de Django
"L'orchestre a maintenant une loge toute neuve et des instruments de pointe ! 🎷 Le son est pur, même avec les partitions les plus modernes. C'est du grand art, Arnal ! 🤘✨"

---

## Date : Mercredi 8 Avril 2026 (Session 7)

### 🐛 Bugfix & Robustesse : Chemins Absolus
- **Problème** : Régression sur les chemins d'upload (fichiers perdus dans `/data/`).
- **Solution** : Passage intégral en **chemins absolus** dans `autoload.php`, `params.php` et `Document.php` via `ROOT_DIR` et `PUBLIC_DATA_DIR`.

### 🖼️ Optimisation des Médias
- **Covers Discogs** : Les illustrations choisies via Discogs sont désormais téléchargées localement, redimensionnées (max 400x400) et converties en **WebP** pour une performance maximale.

### 🎨 Design System & UI (Canopée Style)
- **Songbook Form** : Modernisation du formulaire de création/édition des recueils.
- **Répertoire des Strums** : 
    - Remplacement du bleu par la palette **Bois Canopée** (Marron/Terre).
    - Agrandissement et espacement des boutons de gestion (Modifier/Supprimer) pour une meilleure ergonomie.
- **Administration** : Correction du décalage (padding-top) sur la page de paramétrage.

### 🎸 Note de Django
"La partition est impeccable, le son est chaud et boisé, et l'orchestre joue parfaitement en mesure ! 🎼 On a dompté les chemins, optimisé les images et donné un look d'enfer au répertoire des strums. C'est du grand art, Arnal ! 🎷🤘✨"

---

## Date : Lundi 6 Avril 2026 (Session 6)

### 🕵️‍♂️ Diagnostic & Audit (Quirks Mode)
- **Django Audit** : Création d'un script d'audit (`audit_fichiers.php`) permettant de générer un CSV avec les hash MD5 et tailles de tous les fichiers du projet.
- **Administration** : Ajout d'un bouton d'accès rapide dans le header de la page Paramétrage pour lancer l'audit.
- **Investigation** : Recherche sur l'erreur "Quirks Mode" et "$ is not defined" en production. L'audit permettra de comparer les fichiers prod/local et de débusquer d'éventuels caractères invisibles (BOM/Espaces) ou fichiers corrompus.

### 🎸 Note de Django
"Quand la prod fait des siennes, on sort les grands moyens ! 🕵️‍♂️ L'empreinte digitale des fichiers ne mentira pas. On va débusquer ce petit grain de sable qui fait grincer la partition. Rock'n'Roll Arnal ! 🎷🤘✨"
