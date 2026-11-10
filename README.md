# ShopNow — Projet e-commerce

Application d'équipe : **HTML, CSS, JavaScript, PHP 8.1+ et MySQL/MariaDB**, développée sous XAMPP.

## Équipe
| Membre | Rôle | Dossiers |
|---|---|---|
| Jephte | Développeur frontend | `frontend/` |
| Kandu Gregory | Développeur backend | `backend/`, `database/` |

Le navigateur communique avec PHP via HTTP/JSON ; il ne se connecte jamais directement à MySQL.

## Technologies
- Frontend : HTML5, CSS3, JavaScript.
- Backend : PHP 8.1+, PDO.
- Base : MySQL 8+ ou MariaDB compatible.
- Local : XAMPP.

## Devises
L'application prend en charge **USD** et **CDF**.
- Chaque produit possède un prix USD et un prix CDF distincts.
- Les prix CDF sont initialement NULL dans les données de démonstration : ils doivent être renseignés/validés par l'équipe avant d'accepter une commande en CDF.
- Aucun taux de change n'est supposé ni codé en dur.
- Une commande conserve sa devise, son total et ses prix unitaires historiques ; elle ne mélange jamais USD et CDF.

## Structure
```text
Projet-E_commerce/
├── frontend/                 # Jephte : interface navigateur
├── backend/                  # Kandu : PHP, API, sessions, règles métier
├── database/schema.sql       # Schéma neuf et données de démonstration
├── database/migrations/      # Migrations pour les bases existantes
└── docs/                     # Architecture et contrat d'API
```

## Installation neuve sous XAMPP
1. Placer le dépôt dans `C:\\xampp\\htdocs\\Projet-E_commerce\\`.
2. Démarrer Apache et MySQL.
3. Importer `database/schema.sql` dans phpMyAdmin.
4. Configurer les variables PHP DB_HOST, DB_NAME, DB_USER et DB_PASS (voir `.env.example`; ce modèle n'est pas chargé automatiquement).
5. Ouvrir `http://localhost/Projet-E_commerce/`.

## Mise à jour d'une base existante
Faire une sauvegarde puis exécuter une seule fois `database/migrations/20261110_add_usd_cdf_currency.sql` dans phpMyAdmin. Cette migration reprend les anciens prix comme USD et ajoute un champ CDF non renseigné. Vérifier le résultat avant de reprendre les tests.

## API
Base locale : `http://localhost/Projet-E_commerce/backend/public/api`.

- `GET /health.php` : état de l'API et MySQL.
- `GET /products.php` : produits avec `price_usd` et `price_cdf`.
- `GET /categories.php` : catégories.
- `POST /auth/register.php`, `POST /auth/login.php`, `GET /auth/me.php`, `POST /auth/logout.php`.
- `POST /orders/create.php` : commande authentifiée, JSON contenant `currency: "USD"` ou `"CDF"`, `shipping_address` et `items`.
- `GET /orders/index.php` : historique avec devise de chaque commande.

Le backend choisit le prix dans la devise demandée, recalcule les totaux à partir de MySQL et vérifie le stock. Si le prix de la devise choisie est absent, la commande est refusée avec un message explicite.

## État
Les API backend sont en cours de construction. Le frontend reste un prototype et n'est pas encore connecté aux API. Aucun paiement réel n'est intégré. Tests XAMPP, CSRF, limitation des tentatives, tests automatisés, HTTPS et audit restent nécessaires avant production.

## Collaboration Git
Jephte intervient principalement dans `frontend/`. Kandu intervient principalement dans `backend/` et `database/`. Les changements de contrat sont consignés dans `docs/COLLABORATION_FRONTEND_BACKEND.md`. Utiliser des branches et Pull Requests vers `main`.
