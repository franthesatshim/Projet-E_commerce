# ShopNow — E-commerce Laravel

Application e-commerce monolithique en **PHP 8.2+, Laravel 12 et MySQL 8+**. Interface française, catalogue, recherche/filtres, panier de session, comptes clients, favoris, avis modérés, commandes, suivi et administration. Les prix USD et CDF sont stockés séparément pour chaque produit : aucun taux de change arbitraire.

## Installation locale (XAMPP ou PHP local)
1. Installer PHP 8.2+ et extensions Laravel (OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo), Composer et MySQL.
2. Cloner le dépôt puis se placer sur la branche `feature/laravel-ecommerce`.
3. Installer : `composer install`.
4. Copier `.env.example` vers `.env`, créer une base MySQL `ecommerce`, puis configurer DB_USERNAME et DB_PASSWORD.
5. Générer la clé : `php artisan key:generate`.
6. Initialiser : `php artisan migrate --seed`.
7. Lier les images : `php artisan storage:link`.
8. Démarrer : `php artisan serve`, puis ouvrir http://127.0.0.1:8000.

## Premier administrateur
Définir temporairement `ADMIN_EMAIL` et `ADMIN_PASSWORD` dans le fichier .env local non versionné, puis exécuter `php artisan db:seed`. Supprimer ensuite ces variables du .env. Ne jamais committer de mot de passe. L'inscription publique crée uniquement des comptes customer.

## Fonctions
- Catalogue, recherche, catégories, filtres prix et tri.
- Fiches produit, stock, prix USD/CDF indépendants.
- Panier, quantités et sélection de devise.
- Inscription, connexion, espace client, historique et détails de commandes.
- Favoris et avis clients en attente de modération.
- Checkout, adresses et méthodes de paiement manuelles (espèces à la livraison, Mobile Money ou virement).
- Administration protégée : indicateurs, ajout/désactivation de produits, suivi commandes et validation manuelle des paiements.
- Décrémentation du stock dans une transaction SQL avec verrouillage des produits.

## À configurer avant production
Aucun prestataire de paiement, SMS/e-mail transactionnel, transporteur ou taux de change automatique n'est intégré. Mobile Money/virement sont des demandes à confirmer manuellement ; aucun numéro de carte n'est collecté. Les frais de livraison sont à zéro tant que la politique tarifaire n'est pas définie. Les produits du seeder sont des exemples à remplacer. Avant mise en production : HTTPS, APP_DEBUG=false, sauvegardes, frais et règles de livraison, prestataire de paiement, e-mails, revue de sécurité et tests sur environnement réel.

## Vérification après installation
Exécuter `php artisan route:list`, `php artisan migrate:status` et `php artisan test`. L'environnement GitHub utilisé pour écrire les fichiers ne fournit pas de serveur PHP/MySQL ; ces commandes n'ont donc pas été exécutées ici. Vérifier également le démarrage Composer et l'intégralité des parcours sur votre PC après installation.
