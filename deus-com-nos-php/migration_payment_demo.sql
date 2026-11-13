USE deus_com_nos;
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(30) DEFAULT NULL, ADD COLUMN payment_status ENUM('en_attente','simule') NOT NULL DEFAULT 'en_attente', ADD COLUMN payment_reference VARCHAR(40) DEFAULT NULL;
