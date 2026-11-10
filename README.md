# ShopNow — Plateforme e-commerce

Application e-commerce monolithique en **PHP 8.3+, Laravel 13 et MySQL 8+**. Interface française, catalogue, recherche et filtres, panier, comptes clients, favoris, avis modérés, commandes, suivi et administration. Les prix USD et CDF sont enregistrés séparément pour chaque produit : aucun taux de change arbitraire n'est appliqué.

## Installation locale (XAMPP ou PHP local)

1. Installer PHP 8.3+, Composer et MySQL 8+.
2. Activer les extensions PHP nécessaires : Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PDO, Session, Tokenizer et XML.
3. Cloner le dépôt et ouvrir le dossier du projet.
4. Installer les dépendances : \`composer install\`.
5. Copier \`.env.example\` vers \`.env\`, créer une base MySQL nommée \`ecommerce\`, puis configurer \`DB_USERNAME\` et \`DB_PASSWORD\`.
6. Générer la clé : \`php artisan key:generate\`.
7. Initialiser la base : \`php artisan migrate --seed\`.
8. Créer le lien des images : \`php artisan storage:link\`.
9. Démarrer : \`php artisan serve\`, puis ouvrir http://127.0.0.1:8000.

## Premier administrateur

Définir temporairement \`ADMIN_EMAIL\` et \`ADMIN_PASSWORD\` dans le fichier \`.env\` local non versionné, puis exécuter \`php artisan db:seed\`. Retirer ensuite ces deux variables du fichier. L'inscription publique crée uniquement des comptes clients. Ne jamais committer un mot de passe.

## Fonctionnalités

- Catalogue, catégories, recherche, filtres de prix et tri.
- Fiches produits, prix USD/CDF indépendants, état neuf/occasion et stock.
- Panier, quantités et choix de devise.
- Inscription, connexion, espace client, favoris et historique des commandes.
- Avis clients soumis à modération avant publication.
- Checkout avec adresse et méthodes de paiement manuelles (espèces à la livraison, Mobile Money ou virement).
- Administration protégée : indicateurs, produits, commandes, annulation avec restitution du stock et modération des avis.
- Création des commandes et décrémentation du stock dans une transaction SQL avec verrouillage des produits.

## Paiement, livraison et production

Aucun prestataire de paiement, SMS/e-mail transactionnel ou transporteur n'est encore connecté. Mobile Money et virement sont des demandes à confirmer manuellement ; aucun numéro de carte n'est collecté. Les frais de livraison sont à zéro tant que la politique tarifaire n'est pas définie. Les produits du seeder sont des exemples à remplacer.

Avant la mise en production : HTTPS, \`APP_DEBUG=false\`, sauvegardes, frais et règles de livraison, intégration du prestataire de paiement choisi, e-mails transactionnels, revue de sécurité et tests de bout en bout sur l'environnement réel.

## Tests

Lancer \`php artisan test\`. GitHub Actions exécute les tests sur SQLite et MySQL 8 avec PHP 8.3. Les tests couvrent le passage de commande, la vérification du stock, les accès administrateur, l'annulation et la modération des avis.
