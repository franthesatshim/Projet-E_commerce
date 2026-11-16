-- POUR UNE BASE EXISTANTE : faites une sauvegarde avant import.
-- Importer d'abord migration.sql (ajoute payment_status si absent), puis ce fichier.
-- Le script n'efface ni comptes, ni commandes, ni anciennes tentatives.
SET NAMES utf8mb4;
USE deus_com_nos;
ALTER TABLE orders MODIFY COLUMN payment_status
    ENUM('en_attente','simule','autorise','paye','echoue','expire','annule','bloque')
    NOT NULL DEFAULT 'en_attente';
CREATE TABLE IF NOT EXISTS payment_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  merchant_ref VARCHAR(64) NOT NULL UNIQUE,
  provider_id VARCHAR(191) DEFAULT NULL,
  provider_transaction VARCHAR(191) DEFAULT NULL,
  payment_url VARCHAR(1000) DEFAULT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  status VARCHAR(24) NOT NULL DEFAULT 'initiating',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(order_id),
  CONSTRAINT fk_payment_attempt_order FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB;
