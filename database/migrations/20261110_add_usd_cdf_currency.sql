-- Migration à exécuter UNE SEULE FOIS sur une base créée avec l'ancien schema.sql.
-- Sauvegarder la base avant migration.
-- L'ancien champ products.price est interprété comme USD pour préserver les valeurs existantes.

ALTER TABLE products
    CHANGE COLUMN price price_usd DECIMAL(14,2) NOT NULL,
    ADD COLUMN price_cdf DECIMAL(18,2) NULL AFTER price_usd;

ALTER TABLE orders
    ADD COLUMN currency ENUM('USD', 'CDF') NOT NULL DEFAULT 'USD' AFTER user_id;

ALTER TABLE orders
    MODIFY total_amount DECIMAL(18,2) NOT NULL;

ALTER TABLE order_items
    MODIFY unit_price DECIMAL(18,2) NOT NULL;

-- Après migration, renseigner les prix CDF réels depuis les prix approuvés
-- par l'équipe (ou depuis un taux de change validé et daté).
-- Ne pas inventer ni coder en dur un taux de conversion.
