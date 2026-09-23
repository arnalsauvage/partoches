-- 002_migrate_strum_ids.sql

-- 1. Ajout de la colonne idStrum si elle n'existe pas
SET @dbname = DATABASE();
SET @tablename = "lienstrumchanson";
SET @columnname = "idStrum";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT(11) AFTER idChanson")
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Migration des données (Mise à jour des IDs basée sur la chaîne 'strum')
-- Note : Cette étape peut être complexe en SQL pur sans boucle, mais on peut faire un UPDATE simple
UPDATE lienstrumchanson l
JOIN strum s ON BINARY l.strum = s.strum
SET l.idStrum = s.id
WHERE l.idStrum IS NULL OR l.idStrum = 0;
