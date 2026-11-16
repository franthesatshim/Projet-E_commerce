SET NAMES utf8mb4;
-- Mise à jour d'une base DEJA CREEE avec une ancienne version du site.
-- Peut être importée plusieurs fois sans risque (chaque colonne n'est ajoutée que si elle manque).
USE deus_com_nos;

DROP PROCEDURE IF EXISTS ajouter_colonne_si_absente;
DELIMITER //
CREATE PROCEDURE ajouter_colonne_si_absente(IN col VARCHAR(64), IN definition VARCHAR(255))
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = col) THEN
    SET @sql = CONCAT('ALTER TABLE orders ADD COLUMN ', col, ' ', definition);
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END//
DELIMITER ;

CALL ajouter_colonne_si_absente('payment_method', 'VARCHAR(30) DEFAULT NULL');
CALL ajouter_colonne_si_absente('payment_status', "ENUM('en_attente','simule') NOT NULL DEFAULT 'en_attente'");
CALL ajouter_colonne_si_absente('payment_reference', 'VARCHAR(40) DEFAULT NULL');
CALL ajouter_colonne_si_absente('phone', 'VARCHAR(30) DEFAULT NULL');   -- téléphone du client pour la livraison

DROP PROCEDURE ajouter_colonne_si_absente;
