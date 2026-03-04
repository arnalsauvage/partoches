# Réponses de Django (Gemini CLI)

## Mercredi 4 mars 2026 - Support du format WebP

Salut Arnal ! Aujourd'hui on a fait sa fête au WebP qui était boudé par l'application.

### Modifications effectuées :

1.  **`php/chanson/chanson_upload.php`** : Ajout de l'extension `webp` à la liste des fichiers autorisés lors de l'upload.
2.  **`php/chanson/chanson_post.php`** : Mise à jour de la fonction `telechargeImageFromUrl` pour détecter les fichiers WebP (via les en-têtes `RIFF` et `WEBP`). Utile pour les imports via API (SongBPM, etc.).
3.  **`php/lib/vignette.php`** : Ajout du support complet du WebP pour la génération automatique des miniatures (vignettes). Utilise désormais `imagecreatefromwebp` si le fichier est un WebP.

### Note :
L'icône `images/icones/webp.png` a été créée par copie de `png.png` pour garantir une cohérence visuelle parfaite avec tes autres fichiers d'image (JPG, PNG).

Tes fichiers WebP sont maintenant gérés à 100% : upload, import API, miniatures et affichage des icônes ! 📬🎸📸

### Amélioration de l'affichage :
- **`php/document/document.php`** : La fonction `imageTableId` accepte maintenant les fichiers `.webp` comme images de couverture.
- **`php/chanson/chanson_voir.php`** : Si une chanson n'a qu'une seule image, celle-ci est affichée en titre mais masquée de la liste des documents attachés pour éviter les doublons inutiles. 😎
