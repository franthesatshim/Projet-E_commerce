# DEUS COM NOS — boutique PHP / MySQL
Nouvelle implémentation **isolée** de l'application Laravel historique. Ne remplace aucun fichier existant.
## Installation XAMPP
1. Copier le dossier `deus-com-nos-php` dans `C:\xampp\htdocs\`.
2. Démarrer Apache et MySQL dans XAMPP.
3. Importer `schema.sql` dans phpMyAdmin (http://localhost/phpmyadmin).
4. Ouvrir http://localhost/deus-com-nos-php/index.php.
5. Créer un compte sur la page d'inscription. Pour attribuer le rôle administrateur, exécuter dans phpMyAdmin : `UPDATE users SET role='admin' WHERE email='votre-email@example.com';` puis se déconnecter/reconnecter.
## Photos
Ajouter des images dans `assets/products/`, puis renseigner leur chemin relatif (`assets/products/photo.jpg`) dans le panel admin. Les captures d'écran du dépôt historique ne sont **pas encore automatiquement reliées** au catalogue.
## Fonctionnalités
Catalogue mode, recherche, catégories, fiches produits, panier en session, création de compte, connexion, commandes avec transaction et contrôle du stock, suivi des commandes, gestion admin des produits et statuts.
Les paiements ne sont **pas** encaissés : la commande est enregistrée en attente. Intégrer un prestataire avant d'annoncer des paiements réels.
Configuration par variables d'environnement : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
