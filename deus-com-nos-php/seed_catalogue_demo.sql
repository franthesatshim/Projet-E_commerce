-- Catalogue de DEMONSTRATION : prix USD et stocks fictifs.
-- Les photos sont hébergées par Unsplash ; une connexion Internet est nécessaire.
USE deus_com_nos;
INSERT IGNORE INTO categories(name) VALUES ('Vêtements'),('Perruques'),('Wax'),('Accessoires'),('Chaussures'),('Sacs');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Robe élégante femme','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',39,18,'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Vêtements' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Robe élégante femme');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Ensemble casual femme','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',45,15,'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Vêtements' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Ensemble casual femme');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Chemise classique homme','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',29,20,'https://images.unsplash.com/photo-1598032895397-b9472444bf93?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Vêtements' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Chemise classique homme');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Veste denim tendance','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',52,12,'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Vêtements' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Veste denim tendance');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Perruque bouclée noire','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',68,9,'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Perruques' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Perruque bouclée noire');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Perruque longue lisse','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',75,8,'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Perruques' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Perruque longue lisse');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Tissu wax coloré','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',22,30,'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Wax' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Tissu wax coloré');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Tenue imprimée wax','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',48,14,'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Wax' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Tenue imprimée wax');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Baskets urbaines','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',42,17,'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Chaussures' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Baskets urbaines');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Escarpins élégants','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',49,10,'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Chaussures' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Escarpins élégants');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Sac à main chic','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',55,11,'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Sacs' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Sac à main chic');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Sac bandoulière','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',34,14,'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Sacs' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Sac bandoulière');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Lunettes de soleil','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',18,25,'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Accessoires' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Lunettes de soleil');
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,'Montre élégante','Article de démonstration DEUS COM NOS : visuel illustratif, à remplacer par la photo exacte du produit.',38,13,'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80',1
FROM categories c WHERE c.name='Accessoires' AND NOT EXISTS (SELECT 1 FROM products WHERE name='Montre élégante');
