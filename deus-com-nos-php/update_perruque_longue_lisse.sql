-- À exécuter une fois dans phpMyAdmin sur la base deus_com_nos.
-- Photo illustrative de cheveux longs et foncés, non photographie du produit exact.
UPDATE products
SET image = 'https://unsplash.com/photos/0yTLdZwDJqk/download?force=true&w=800'
WHERE name = 'Perruque longue lisse';
