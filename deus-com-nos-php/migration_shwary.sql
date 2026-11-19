-- Pour une base alimentée, après migration_netikash.sql et avant activation de Shwary.
-- Exécuter une fois. Le schéma des nouvelles installations inclut déjà cette colonne.
USE deus_com_nos;
ALTER TABLE payment_attempts ADD COLUMN is_sandbox TINYINT(1) NOT NULL DEFAULT 0 AFTER currency;
