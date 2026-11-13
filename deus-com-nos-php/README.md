# DEUS COM NOS — boutique PHP / MySQL
Nouvelle implémentation **isolée** de l'application Laravel historique. Ne remplace aucun fichier existant.
## Installation XAMPP
1. Copier le dossier `deus-com-nos-php` dans `C:\xampp\htdocs\`.
2. Démarrer Apache et MySQL dans XAMPP.
3. Importer `schema.sql` dans phpMyAdmin (http://localhost/phpmyadmin).
4. Ouvrir http://localhost/Projet-E_commerce/deus-com-nos-php/index.php.
5. Créer un compte sur la page d'inscription. Pour attribuer le rôle administrateur, exécuter dans phpMyAdmin : `UPDATE users SET role='admin' WHERE email='votre-email@example.com';` puis se déconnecter/reconnecter.
## Photos
Les photos WhatsApp déjà présentes dans `../Screenshots/` sont affichées dans une galerie de sélection dans le panel admin. Chaque photo peut être associée au produit correspondant au moment de sa création. Aucune image source n'est déplacée ni renommée. Les prix et les intitulés commerciaux doivent être saisis par l'administrateur. Les images supplémentaires peuvent aussi être placées dans `assets/products/` (chemin administrateur actuellement via sélection des captures).
## Fonctionnalités
Catalogue mode, recherche, catégories, fiches produits, panier en session, création de compte, connexion, commandes avec transaction et contrôle du stock, suivi des commandes, gestion admin des produits et statuts.
Les paiements ne sont **pas** encaissés : la commande est enregistrée en attente. Intégrer un prestataire avant d'annoncer des paiements réels.
Configuration par variables d'environnement : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

## Améliorations de gestion
- Consultation détaillée des commandes depuis le compte client.
- Formulaire d'édition prérempli depuis le panel admin.
- Création de catégories et consultation de la liste des utilisateurs.

## Avant mise en production
- Pas de paiement réel : les commandes sont enregistrées en attente.
- Ajouter HTTPS, sauvegardes, e-mails transactionnels et tests de sécurité.
- Le changement de statut d'une commande n'effectue pas de réapprovisionnement automatique en cas d'annulation.

## Paiement simulé
Le client choisit Orange Money, Airtel Money, Rawbank ou carte de démonstration. À la validation, la commande reçoit une référence DEMO et le statut `simule`. Aucun paiement réel n'est effectué, aucun PIN ou numéro de carte n'est collecté.

Nouvelle installation : importer `schema.sql`. Base déjà créée avec l'ancien schéma : importer une seule fois `migration_payment_demo.sql`.

## Catalogue de démonstration
Pour ajouter 22 produits de mode et leurs catégories à une base déjà existante, importer `seed_catalogue.sql` dans phpMyAdmin. Le script évite de recréer les produits portant le même nom. Les prix et stocks sont des exemples, **pas des données d'inventaire vérifiées**. Les images restent vides tant que l'administrateur n'a pas associé de vraies photos. Ne pas utiliser les articles de démonstration pour de vraies ventes sans les vérifier.
