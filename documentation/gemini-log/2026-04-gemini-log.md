## Session du 9 Avril 2026
### ✅ Ce qui a été fait
- Installation de **Ghostscript** dans le conteneur Docker.
- Migration vers **TCPDF + FPDI** pour le support natif des PDF modernes.
- Implémentation d'une conversion automatique des partitions en PDF 1.4 compatible via Ghostscript avant l'assemblage.
- Création du dossier `src/public/vendor/` centralisant les dépendances PHP et Frontend (Bootstrap, jQuery, Toastr).
- Mise à jour de l'autoloader et de `html.php` pour utiliser les constantes `VENDOR_URL` et `PUBLIC_URL`.
- Correction du bug du "voile sombre" (backdrop modal) par isolation de la modale et forçage du nettoyage en JS/CSS.
- Validation complète via 142 tests PHPUnit (réussis).

### 🔧 Décisions techniques prises
- Utilisation de Ghostscript en ligne de commande pour garantir la compatibilité universelle des fichiers importés.
- Abandon des chemins relatifs (`../../..`) dans les inclusions HTML au profit de constantes absolues.
- Désactivation du "fade" Bootstrap sur la modale de rapport pour fiabiliser la fermeture sous environnements instables.

### 🐛 Bugs résolus
- Erreur "Compression technique non supportée" (Rudy).
- Blocage du focus navigateur après génération PDF.
- Erreurs 404 sur les scripts Bootstrap/jQuery.

### 📋 Prochaines étapes
- Surveiller la taille du dossier `data/temp/` (bien que le nettoyage auto soit en place).
- Envisager de fusionner `upgrade-outils-pdf` dans `master` après vérification manuelle approfondie.

### 📝 Notes importantes
- Ghostscript est nécessaire dans l'environnement Docker (`apt-get install ghostscript`).
- Ne plus utiliser `/js/` ou `/css/` pour les bibliothèques tierces, préférer `/vendor/`.

## Session du 9 Avril 2026
### ✅ Ce qui a été fait
- Migration vers **TCPDF + FPDI + Ghostscript**.
- Validation de l'hébergement Hostinger.

### 🔧 Décisions techniques prises
- Utilisation de Ghostscript pour la compatibilité PDF 1.4.

