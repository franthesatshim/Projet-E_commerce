# Intégration ShopNow — frontend PHP/MySQL

## Architecture locale
- L’interface est servie par PHP à l’adresse `http://localhost/Projet-E_commerce/`.
- Le frontend appelle les API PHP via `../backend/public/api`.
- Le navigateur ne communique jamais directement avec MySQL.
- Les produits, catégories, comptes, stocks et commandes proviennent de MySQL.
- Le panier et les favoris restent mémorisés dans le navigateur; le serveur recalcule toujours les prix et vérifie le stock lors de la commande.

## Installation XAMPP
1. Copier le dépôt dans `C:\xampp\htdocs\Projet-E_commerce`.
2. Démarrer Apache et MySQL.
3. Ouvrir phpMyAdmin et importer `database/schema.sql` dans une base neuve.
4. Si la base existe déjà, sauvegarder puis appliquer la migration USD/CDF décrite dans le README; ne pas importer le schéma neuf par-dessus une base existante.
5. Vérifier les identifiants dans `backend/config/database.php` ou définir `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
6. Ouvrir `http://localhost/Projet-E_commerce/` puis vérifier `http://localhost/Projet-E_commerce/backend/public/api/health.php`.

## Parcours reliés
- Catalogue : `GET products.php` et `GET categories.php`.
- Session : `POST auth/register.php`, `POST auth/login.php`, `GET auth/me.php`, `POST auth/logout.php`.
- Commandes : `POST orders/create.php`, `GET orders/index.php`.
- Une commande exige un utilisateur connecté et une adresse de livraison.
- USD et CDF sont deux prix saisis séparément. Un prix CDF NULL empêche l’achat dans cette devise.
- Aucun paiement bancaire ou Mobile Money réel n’est intégré.

## Limites à vérifier sur le poste local
Les fichiers sont reliés dans le dépôt, mais les scénarios d’exécution doivent être testés sous XAMPP avec une base MySQL réelle. Les images produits ne s’affichent que si un chemin d’image valide est renseigné dans `products.image_path`.
