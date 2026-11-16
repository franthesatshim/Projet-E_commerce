SET NAMES utf8mb4;
-- Catalogue DEUS COM NOS : 35 produits dont chaque photo correspond au nom et à la description.
-- Les photos sont dans assets/products/ (copies optimisées des photos du dossier Screenshots, qui reste intact).
-- Import : phpMyAdmin > base deus_com_nos > Importer. Peut être relancé : les produits du même nom sont mis à jour, pas dupliqués.
--
-- ATTENTION : les prix (USD) et les stocks ci-dessous sont des valeurs de DEMONSTRATION.
-- Corrigez-les dans l'administration (Tableau de bord > Produits > Modifier) avant toute vraie vente.
USE deus_com_nos;

-- 1) Catalogue source (table temporaire)
DROP TEMPORARY TABLE IF EXISTS tmp_catalogue;
CREATE TEMPORARY TABLE tmp_catalogue (
  category VARCHAR(100) NOT NULL, name VARCHAR(160) NOT NULL, description TEXT, price DECIMAL(12,2) NOT NULL,
  stock INT NOT NULL, image VARCHAR(500) NOT NULL, rang INT NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO tmp_catalogue(category,name,description,price,stock,image,rang) VALUES
('Sacs','Sacs à main avec portefeuille assorti','Sac à main structuré à anses dorées, vendu avec un portefeuille assorti. Coloris visibles sur la photo : bleu, rouge, noir, beige et vert.',55.00,8,'assets/products/sac-portefeuille-assorti.jpg',0),
('Wax','Wax Vlisco S6945','Tissu wax Vlisco « Grand Super Wax », réf. S6945. L''étiquette indique « Printed by Vlisco in Holland ». Tons bleu, orange, rose et jaune, motifs de volutes et de peignes sur fond bleu. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,12,'assets/products/wax-vlisco-s6945.jpg',1),
('Perruques','Perruque bob lisse ombré caramel','Perruque carré lisse, dégradé du noir au brun caramel, coupe courte aux épaules. Matière, densité et taille de bonnet : nous contacter.',70.00,6,'assets/products/perruque-bob-ombre-caramel.jpg',2),
('Chaussures femme','Mules à talon bloc','Mules ouvertes à bout carré et talon bloc, ornées d''une boucle dorée. Coloris visibles sur la photo : jaune moutarde, rouge, camel, marron et noir. Réf. R7601-5. Pointures disponibles : nous contacter.',24.00,14,'assets/products/mules-talon-bloc-r7601.jpg',3),
('Chaussures enfant','Baskets blanches enfant à scratch','Baskets basses blanches pour enfant, fermeture à scratch, semelle épaisse blanche. Marque Crabkids.',22.00,15,'assets/products/baskets-blanches-enfant-scratch.jpg',4),
('Sacs','Sac à main à anneau doré avec portefeuille','Sac à main à rabat orné d''un anneau doré, vendu avec un portefeuille assorti. Coloris visibles sur la photo : vert pomme, rouge, noir, lilas et beige.',50.00,9,'assets/products/sac-anneau-dore-portefeuille.jpg',5),
('Wax','Wax Vlisco S6952','Tissu wax Vlisco « Grand Super Wax », réf. S6952. L''étiquette indique « Printed by Vlisco in Holland ». Tons orange rouille, lilas et beige, motifs graphiques de cailloux et de fleurs. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,10,'assets/products/wax-vlisco-s6952.jpg',6),
('Perruques','Perruque longue lisse noire','Perruque longue lisse noire avec raie centrale, effet lace, longueur jusqu''au milieu du dos. Matière, densité et taille de bonnet : nous contacter.',95.00,5,'assets/products/perruque-longue-lisse-noire.jpg',7),
('Chaussures femme','Escarpins pointus à talon aiguille','Escarpins élégants à bout pointu et talon aiguille. Coloris visibles sur la photo : crème, camel et noir (bout métallisé doré sur le camel et le noir). Réf. YQ6627. Pointures disponibles : nous contacter.',32.00,10,'assets/products/escarpins-talon-aiguille-yq6627.jpg',8),
('Sacs','Sac à main effet croco avec bandoulière','Sac à main structuré à effet croco, anses rigides et bandoulière amovible. Coloris visibles sur la photo : doré, rouille, mauve, bordeaux et gris.',38.00,7,'assets/products/sac-croco-bandouliere.jpg',9),
('Wax','Wax Vlisco S6937','Tissu wax Vlisco « Grand Super Wax », réf. S6937. L''étiquette indique « Printed by Vlisco in Holland ». Tons vert olive, crème et noir, motifs de feuilles et de volutes. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,12,'assets/products/wax-vlisco-s6937.jpg',10),
('Perruques','Perruque bob lisse noire','Perruque carré lisse noire avec lace sur le dessus, coupe courte aux épaules. Matière, densité et taille de bonnet : nous contacter.',65.00,8,'assets/products/perruque-bob-lisse-noire.jpg',11),
('Chaussures enfant','Chaussures d''école noires à lacets','Chaussures noires unies à lacets pour enfant, semelle épaisse noire, adaptées à l''uniforme scolaire. Marque Crabkids.',25.00,12,'assets/products/chaussures-ecole-noires-lacets.jpg',12),
('Sacs','Sac trapèze à bandes','Sac à main de forme trapèze avec bandes verticales et bandoulière fournie. Sept coloris visibles sur la photo : rouge, noir, mauve, beige, blanc et bleu marine, blanc et rouge, jaune.',40.00,6,'assets/products/sac-trapeze-bandes.jpg',13),
('Chaussures femme','Escarpins à talon bloc','Escarpins à bout pointu et talon bloc, très confortables au quotidien. Coloris visibles sur la photo : blanc, crème et noir. Réf. YP501. Pointures disponibles : nous contacter.',30.00,12,'assets/products/escarpins-talon-bloc-yp501.jpg',14),
('Wax','Wax Vlisco S6956','Tissu wax Vlisco « Grand Super Wax », réf. S6956. L''étiquette indique « Printed by Vlisco in Holland ». Tons rouge, rose, crème, jaune et turquoise, motifs de fleurs et de bandes ondulées. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,10,'assets/products/wax-vlisco-s6956.jpg',15),
('Perruques','Perruque ondulée noire volumineuse','Perruque noire aux grandes ondulations, effet volume, avec lace sur le dessus. Matière, densité et taille de bonnet : nous contacter.',90.00,5,'assets/products/perruque-ondulee-noire.jpg',16),
('Chaussures enfant','Baskets blanches enfant à lacets','Baskets basses blanches à lacets pour enfant, semelle épaisse blanche. Marque Crabkids.',22.00,14,'assets/products/baskets-blanches-enfant-lacets.jpg',17),
('Sacs','Sac à main croco bordeaux avec pochette','Sac à main bordeaux à effet croco, vendu avec une pochette assortie et une bandoulière. Autres coloris sur la photo : moutarde, mauve, rouille, vert olive et noir.',42.00,7,'assets/products/sac-croco-bordeaux-pochette.jpg',18),
('Wax','Wax Vlisco S6947','Tissu wax Vlisco « Grand Super Wax », réf. S6947. L''étiquette indique « Printed by Vlisco in Holland ». Tons turquoise, rose et bordeaux, motifs de coquillages et de volutes. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,12,'assets/products/wax-vlisco-s6947.jpg',19),
('Chaussures femme','Sandales compensées talon liège','Sandales type mules à talon compensé effet liège et boucle dorée. Coloris visibles sur la photo : noir, camel, rouge et beige. Réf. R2021-43. Pointures disponibles : nous contacter.',26.00,10,'assets/products/sandales-compensees-r2021.jpg',20),
('Perruques','Perruque lace front lisse noire','Perruque lisse noire avec lace frontal, longueur sous les épaules. Matière, densité et taille de bonnet : nous contacter.',85.00,6,'assets/products/perruque-lace-front-lisse.jpg',21),
('Sacs','Sac à rabat camel avec pochette','Sac à main à rabat et anse de dessus, camel, vendu avec une pochette assortie et une bandoulière. Autres coloris sur la photo : bordeaux, gris bleu, kaki et mauve.',45.00,6,'assets/products/sac-rabat-camel-pochette.jpg',22),
('Chaussures enfant','Chaussures d''école noires à scratch','Chaussures noires unies à double scratch pour enfant, semelle noire avec touche bleue. Marque Crabkids.',25.00,12,'assets/products/chaussures-ecole-noires-scratch.jpg',23),
('Wax','Wax Vlisco S6946','Tissu wax Vlisco « Grand Super Wax », réf. S6946. L''étiquette indique « Printed by Vlisco in Holland ». Tons violet, jaune et bleu, motifs de coquillages et de grecques. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,10,'assets/products/wax-vlisco-s6946.jpg',24),
('Chaussures femme','Escarpins slingback pointus','Escarpins à bout pointu et bride arrière (slingback), talon fin. Coloris visibles sur la photo : argent, noir et taupe. Réf. YQ6687. Pointures disponibles : nous contacter.',28.00,9,'assets/products/escarpins-slingback-yq6687.jpg',25),
('Sacs','Sac bandoulière à rabat avec portefeuille','Sac à rabat avec fermoir doré et bandoulière, vendu avec un portefeuille assorti. Coloris visibles sur la photo : vert olive, rouge, noir, blanc cassé et doré.',48.00,8,'assets/products/sac-rabat-fermoir-portefeuille.jpg',26),
('Wax','Wax Vlisco S6935','Tissu wax Vlisco « Grand Super Wax », réf. S6935. L''étiquette indique « Printed by Vlisco in Holland ». Tons bleu ciel, violet et vert, motifs de plumes et de volutes. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,12,'assets/products/wax-vlisco-s6935.jpg',27),
('Chaussures enfant','Ballerines noires à petit nœud','Ballerines noires unies ornées d''un petit nœud, adaptées à l''uniforme scolaire. Marque Crabkids.',20.00,10,'assets/products/ballerines-noires-noeud.jpg',28),
('Sacs','Sac à main à rabat et fermoir doré','Sac à main à rabat avec fermoir doré, vendu avec un portefeuille assorti. Coloris visibles sur la photo : rouge, noir, beige, vert olive et marron.',50.00,8,'assets/products/sac-rabat-fermoir-dore.jpg',29),
('Wax','Wax Vlisco S6940','Tissu wax Vlisco « Grand Super Wax », réf. S6940. L''étiquette indique « Printed by Vlisco in Holland ». Tons rose saumon et violet, motifs de plumes avec rehauts dorés et noirs. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,10,'assets/products/wax-vlisco-s6940.jpg',30),
('Wax','Wax Vlisco S6941','Tissu wax Vlisco « Grand Super Wax », réf. S6941. L''étiquette indique « Printed by Vlisco in Holland ». Tons bleu, crème et noir, motifs de plumes avec reflets vert doré. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,10,'assets/products/wax-vlisco-s6941.jpg',31),
('Wax','Wax Vlisco S6943','Tissu wax Vlisco « Grand Super Wax », réf. S6943. L''étiquette indique « Printed by Vlisco in Holland ». Tons turquoise, beige et marron, motifs de coquillages avec touches d''orange. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,12,'assets/products/wax-vlisco-s6943.jpg',32),
('Wax','Wax Vlisco S6950','Tissu wax Vlisco « Grand Super Wax », réf. S6950. L''étiquette indique « Printed by Vlisco in Holland ». Tons violet, rose, bleu et jaune, motifs de feuilles et de cailloux. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,10,'assets/products/wax-vlisco-s6950.jpg',33),
('Wax','Wax Vlisco S6951','Tissu wax Vlisco « Grand Super Wax », réf. S6951. L''étiquette indique « Printed by Vlisco in Holland ». Tons violet, rose, jaune et bleu, motifs graphiques sur fond jaune et violet. Vendu en coupon : métrage et prix du coupon à confirmer à la commande.',40.00,10,'assets/products/wax-vlisco-s6951.jpg',34);

-- 2) Catégories
INSERT IGNORE INTO categories(name) SELECT DISTINCT category FROM tmp_catalogue;

-- 3) Anciens produits de démonstration (photos Unsplash sans rapport avec le produit) :
--    supprimés s'ils n'ont jamais été commandés, sinon simplement masqués.
UPDATE products SET active=0 WHERE name IN ('Robe élégante femme', 'Ensemble casual femme', 'Chemise classique homme', 'Veste denim tendance', 'Perruque bouclée noire', 'Perruque longue lisse', 'Tissu wax coloré', 'Tenue imprimée wax', 'Baskets urbaines', 'Escarpins élégants', 'Sac à main chic', 'Sac bandoulière', 'Lunettes de soleil', 'Montre élégante', 'Robe longue élégante', 'Robe midi fleurie', 'Robe de soirée satinée', 'Ensemble deux pièces femme', 'Ensemble casual unisexe', 'Chemisier chic femme', 'T-shirt coton premium', 'Jean taille haute', 'Jupe midi plissée', 'Perruque naturelle bouclée', 'Perruque naturelle lisse', 'Perruque bob courte', 'Perruque tressée longue', 'Tissu wax imprimé floral', 'Tissu wax géométrique', 'Ensemble pagne wax coloré', 'Boucles d’oreilles dorées', 'Ceinture femme élégante', 'Sandales femme tendance', 'Escarpins classiques', 'Sac à main classique', 'Petit sac bandoulière')
  AND name NOT IN (SELECT name FROM tmp_catalogue);
-- Sécurité : aucun produit n'est supprimé. Les références historiques, commandes, prix et stocks restent conservés.
-- Les anciens articles de démonstration sont seulement masqués par la commande UPDATE ci-dessus.

-- 4) Produits déjà présents : mise à jour (catégorie, description, photo)
UPDATE products p
JOIN tmp_catalogue t ON t.name = p.name
JOIN categories c ON c.name = t.category
SET p.category_id = c.id, p.description = t.description, p.image = t.image, p.active = 1;

-- 5) Produits manquants : création (ordre d'affichage = colonne rang, 0 = le plus récent)
INSERT INTO products(category_id,name,description,price,stock,image,active,created_at)
SELECT c.id, t.name, t.description, t.price, t.stock, t.image, 1, NOW() - INTERVAL t.rang MINUTE
FROM tmp_catalogue t
JOIN categories c ON c.name = t.category
WHERE NOT EXISTS (SELECT 1 FROM products x WHERE x.name = t.name)
ORDER BY t.rang;

-- 6) Catégories historiques conservées (aucune suppression des catégories existantes).

DROP TEMPORARY TABLE tmp_catalogue;
