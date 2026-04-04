-- 999_align_local_with_prod.sql
-- Script pour aligner la base locale sur la structure de la Prod

-- 1. Table playlist
ALTER TABLE `playlist` 
  CHANGE COLUMN `idUser` `id_utilisateur` INT(11) NOT NULL,
  CHANGE COLUMN `date` `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
  MODIFY COLUMN `type` VARCHAR(50) DEFAULT 'manuelle';

-- 2. Table lienchansonplaylist
ALTER TABLE `lienchansonplaylist` 
  CHANGE COLUMN `idChanson` `id_chanson` INT(11) NOT NULL,
  CHANGE COLUMN `idPlaylist` `id_playlist` INT(11) NOT NULL;

-- 3. Suppression des colonnes 'tags' en local si on veut être 100% raccord (ou on les garde pour la suite)
-- ALTER TABLE `chanson` DROP COLUMN `tags`;
-- ALTER TABLE `songbook` DROP COLUMN `tags`;
-- ALTER TABLE `strum` DROP COLUMN `tags`;
