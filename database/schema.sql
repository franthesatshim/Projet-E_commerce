CREATE DATABASE IF NOT EXISTS ecommerce_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ecommerce_db;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Les prix USD et CDF sont saisis indépendamment par l'administrateur.
-- NULL signifie que le prix dans cette devise n'a pas encore été défini.
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NULL,
    price_usd DECIMAL(14,2) NOT NULL,
    price_cdf DECIMAL(18,2) NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT chk_products_price_usd CHECK (price_usd >= 0),
    CONSTRAINT chk_products_price_cdf CHECK (price_cdf IS NULL OR price_cdf >= 0),
    INDEX idx_products_category_active (category_id, is_active),
    INDEX idx_products_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    currency ENUM('USD', 'CDF') NOT NULL DEFAULT 'USD',
    total_amount DECIMAL(18,2) NOT NULL,
    shipping_address VARCHAR(500) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_orders_user_date (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(180) NOT NULL,
    unit_price DECIMAL(18,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    CONSTRAINT chk_order_items_quantity CHECK (quantity > 0),
    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favorites (
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name, slug) VALUES
('Informatique', 'informatique'),
('Téléphones', 'telephones'),
('Audio', 'audio'),
('Électroménager', 'electromenager'),
('Mode', 'mode'),
('Accessoires', 'accessoires');

-- Les prix de démonstration sont fournis en USD.
-- Les prix CDF restent NULL jusqu'à leur saisie explicite (aucun taux de change n'est supposé).
INSERT IGNORE INTO products (category_id, name, slug, description, price_usd, price_cdf, stock, is_active)
SELECT c.id, p.name, p.slug, p.description, p.price_usd, NULL, p.stock, 1
FROM (
    SELECT 'Informatique' category, 'Ordinateur portable' name, 'ordinateur-portable' slug, 'Un ordinateur puissant et léger, idéal pour le travail, les études et le divertissement.' description, 699.00 price_usd, 15 stock
    UNION ALL SELECT 'Téléphones', 'Smartphone 5G', 'smartphone-5g', 'Un smartphone élégant, rapide et prêt pour la connectivité 5G.', 499.00, 20
    UNION ALL SELECT 'Audio', 'Casque sans fil', 'casque-sans-fil', 'Profitez d’un son immersif et d’un confort optimal au quotidien.', 129.00, 30
    UNION ALL SELECT 'Accessoires', 'Montre connectée', 'montre-connectee', 'Suivez votre activité et restez connecté directement depuis votre poignet.', 199.00, 12
    UNION ALL SELECT 'Mode', 'Baskets sport', 'baskets-sport', 'Des baskets polyvalentes pour accompagner vos journées actives.', 89.00, 25
    UNION ALL SELECT 'Accessoires', 'Sac à dos', 'sac-a-dos', 'Un sac pratique avec plusieurs compartiments pour vos déplacements.', 59.00, 35
    UNION ALL SELECT 'Électroménager', 'Machine à café', 'machine-a-cafe', 'Préparez facilement votre café préféré à la maison.', 99.00, 10
    UNION ALL SELECT 'Électroménager', 'Téléviseur LED', 'televiseur-led', 'Une image nette et des couleurs riches pour vos films et vos séries.', 299.00, 8
    UNION ALL SELECT 'Audio', 'Enceinte Bluetooth', 'enceinte-bluetooth', 'Emportez votre musique partout avec cette enceinte compacte.', 49.00, 40
    UNION ALL SELECT 'Informatique', 'Clavier mécanique', 'clavier-mecanique', 'Un clavier réactif et confortable pour travailler ou jouer.', 75.00, 18
    UNION ALL SELECT 'Accessoires', 'Appareil photo', 'appareil-photo', 'Capturez vos souvenirs avec précision et simplicité.', 349.00, 6
    UNION ALL SELECT 'Mode', 'Veste urbaine', 'veste-urbaine', 'Une veste polyvalente au style moderne.', 79.00, 14
) AS p
JOIN categories c ON c.name = p.category;
