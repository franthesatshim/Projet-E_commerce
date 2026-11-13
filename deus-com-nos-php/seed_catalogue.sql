-- Catalogue de démonstration DEUS COM NOS
-- Importer une seule fois via phpMyAdmin sur une base existante.
USE deus_com_nos;
INSERT IGNORE INTO categories(name) VALUES
('Robes'),('Ensembles'),('Hauts'),('Pantalons & Jupes'),('Perruques naturelles'),
('Perruques synthétiques'),('Wax africain'),('Accessoires de mode'),('Chaussures'),('Sacs');
-- Ne réinsère pas les articles si un produit du même nom existe déjà.
INSERT INTO products(category_id,name,description,price,stock,image,active)
SELECT c.id,p.name,p.description,p.price,p.stock,NULL,1
FROM (
SELECT 'Robes' category,'Robe longue élégante' name,'Robe longue féminine pour cérémonies et sorties. Article de démonstration : renseigner la photo réelle avant la vente.' description,35.00 price,12 stock
UNION ALL SELECT 'Robes','Robe midi fleurie','Robe midi à motif floral, coupe confortable. Modèle de démonstration.',28.00,15
UNION ALL SELECT 'Robes','Robe de soirée satinée','Robe de soirée satinée, style chic. Modèle de démonstration.',48.00,8
UNION ALL SELECT 'Ensembles','Ensemble deux pièces femme','Ensemble coordonné haut et pantalon. Modèle de démonstration.',42.00,10
UNION ALL SELECT 'Ensembles','Ensemble casual unisexe','Ensemble décontracté pour le quotidien. Modèle de démonstration.',32.00,14
UNION ALL SELECT 'Hauts','Chemisier chic femme','Chemisier élégant à porter au bureau ou en sortie. Modèle de démonstration.',22.00,18
UNION ALL SELECT 'Hauts','T-shirt coton premium','T-shirt polyvalent en coton. Modèle de démonstration.',15.00,25
UNION ALL SELECT 'Pantalons & Jupes','Jean taille haute','Jean femme taille haute, coupe moderne. Modèle de démonstration.',30.00,16
UNION ALL SELECT 'Pantalons & Jupes','Jupe midi plissée','Jupe midi plissée pour tenues habillées. Modèle de démonstration.',25.00,12
UNION ALL SELECT 'Perruques naturelles','Perruque naturelle bouclée','Perruque à boucles souples, modèle de démonstration ; matière et longueur à confirmer.',95.00,6
UNION ALL SELECT 'Perruques naturelles','Perruque naturelle lisse','Perruque lisse style classique, modèle de démonstration ; matière à confirmer.',110.00,5
UNION ALL SELECT 'Perruques synthétiques','Perruque bob courte','Coupe bob courte, modèle de démonstration.',38.00,9
UNION ALL SELECT 'Perruques synthétiques','Perruque tressée longue','Perruque style tresses longues, modèle de démonstration.',55.00,7
UNION ALL SELECT 'Wax africain','Tissu wax imprimé floral','Tissu wax à motif floral, prix indicatif par coupon ; métrage à confirmer.',24.00,20
UNION ALL SELECT 'Wax africain','Tissu wax géométrique','Tissu wax à motifs géométriques, prix indicatif par coupon ; métrage à confirmer.',26.00,18
UNION ALL SELECT 'Wax africain','Ensemble pagne wax coloré','Tissu pagne wax coloré, prix indicatif ; dimensions à confirmer.',29.00,14
UNION ALL SELECT 'Accessoires de mode','Boucles d’oreilles dorées','Boucles d’oreilles fantaisie dorées. Modèle de démonstration.',10.00,25
UNION ALL SELECT 'Accessoires de mode','Ceinture femme élégante','Ceinture pour robe ou pantalon. Modèle de démonstration.',12.00,22
UNION ALL SELECT 'Chaussures','Sandales femme tendance','Sandales de ville pour femme. Modèle de démonstration.',25.00,15
UNION ALL SELECT 'Chaussures','Escarpins classiques','Escarpins élégants, pointure à préciser. Modèle de démonstration.',40.00,10
UNION ALL SELECT 'Sacs','Sac à main classique','Sac à main pour le quotidien. Modèle de démonstration.',34.00,12
UNION ALL SELECT 'Sacs','Petit sac bandoulière','Sac compact à bandoulière. Modèle de démonstration.',22.00,17
) p JOIN categories c ON c.name=p.category
WHERE NOT EXISTS (SELECT 1 FROM products existing WHERE existing.name=p.name);
