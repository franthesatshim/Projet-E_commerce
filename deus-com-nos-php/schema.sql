SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS deus_com_nos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE deus_com_nos;
CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role ENUM('user','admin') NOT NULL DEFAULT 'user',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS categories (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL UNIQUE);
CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT PRIMARY KEY,category_id INT NULL,name VARCHAR(160) NOT NULL,description TEXT,price DECIMAL(12,2) NOT NULL,stock INT NOT NULL DEFAULT 0,image VARCHAR(500) DEFAULT NULL,active TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,total DECIMAL(12,2) NOT NULL,status ENUM('en_attente','confirmee','expediee','livree','annulee') NOT NULL DEFAULT 'en_attente',address TEXT NOT NULL,phone VARCHAR(30) DEFAULT NULL,payment_method VARCHAR(30) DEFAULT NULL,payment_status ENUM('en_attente','simule','autorise','paye','echoue','expire','annule','bloque') NOT NULL DEFAULT 'en_attente',payment_reference VARCHAR(40) DEFAULT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id));
CREATE TABLE IF NOT EXISTS payment_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  merchant_ref VARCHAR(64) NOT NULL UNIQUE,
  provider_id VARCHAR(191) DEFAULT NULL,
  provider_transaction VARCHAR(191) DEFAULT NULL,
  payment_url VARCHAR(1000) DEFAULT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  is_sandbox TINYINT(1) NOT NULL DEFAULT 0,
  status VARCHAR(24) NOT NULL DEFAULT 'initiating',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(order_id),
  CONSTRAINT fk_payment_attempt_order FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS order_items (id INT AUTO_INCREMENT PRIMARY KEY,order_id INT NOT NULL,product_id INT NOT NULL,quantity INT NOT NULL,unit_price DECIMAL(12,2) NOT NULL,FOREIGN KEY(order_id) REFERENCES orders(id),FOREIGN KEY(product_id) REFERENCES products(id));
CREATE TABLE IF NOT EXISTS invoices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL UNIQUE,
  customer_name VARCHAR(120) NOT NULL,
  customer_email VARCHAR(190) NOT NULL,
  delivery_address TEXT NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  paid_amount DECIMAL(12,2) NOT NULL,
  paid_currency CHAR(3) NOT NULL,
  payment_method VARCHAR(30) DEFAULT NULL,
  payment_reference VARCHAR(64) DEFAULT NULL,
  issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoice_order FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS invoice_items (
  invoice_id BIGINT UNSIGNED NOT NULL,
  line_number INT NOT NULL,
  product_name VARCHAR(160) NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  PRIMARY KEY(invoice_id, line_number),
  CONSTRAINT fk_invoice_item_invoice FOREIGN KEY(invoice_id) REFERENCES invoices(id)
) ENGINE=InnoDB;
INSERT IGNORE INTO categories(name) VALUES ('Vêtements'),('Perruques'),('Wax'),('Accessoires');
