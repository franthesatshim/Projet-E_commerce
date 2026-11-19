SET NAMES utf8mb4;
USE deus_com_nos;

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

-- Émission pour les commandes déjà encaissées, à la date de la migration.
START TRANSACTION;
CREATE TEMPORARY TABLE invoice_backfill (order_id INT PRIMARY KEY) ENGINE=InnoDB;
INSERT INTO invoice_backfill (order_id)
SELECT o.id FROM orders o LEFT JOIN invoices i ON i.order_id = o.id
WHERE o.payment_status = 'paye' AND i.order_id IS NULL;

INSERT INTO invoices (order_id, customer_name, customer_email, delivery_address,
                      total, paid_amount, paid_currency, payment_method, payment_reference)
SELECT o.id, u.name, u.email, o.address, o.total, COALESCE(a.amount, o.total),
       COALESCE(a.currency, 'USD'), o.payment_method, o.payment_reference
FROM invoice_backfill b JOIN orders o ON o.id = b.order_id JOIN users u ON u.id = o.user_id
LEFT JOIN payment_attempts a ON a.merchant_ref = o.payment_reference
  AND a.order_id = o.id AND a.status = 'completed'
ORDER BY o.id
ON DUPLICATE KEY UPDATE order_id = invoices.order_id;

INSERT INTO invoice_items (invoice_id, line_number, product_name, quantity, unit_price)
SELECT i.id, oi.id, p.name, oi.quantity, oi.unit_price
FROM invoice_backfill b JOIN invoices i ON i.order_id = b.order_id
JOIN order_items oi ON oi.order_id = b.order_id JOIN products p ON p.id = oi.product_id
ON DUPLICATE KEY UPDATE line_number = invoice_items.line_number;
DROP TEMPORARY TABLE invoice_backfill;
COMMIT;
