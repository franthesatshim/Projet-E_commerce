# ShopNow — Projet e-commerce

Application e-commerce d'équipe : **HTML, CSS, JavaScript, PHP 8.1+ et MySQL/MariaDB**, développée sous XAMPP.

## Équipe et responsabilités

| Membre | Rôle | Dossiers principaux |
|---|---|---|
| Jephte | Développeur frontend | `frontend/` |
| Kandu Gregory | Développeur backend | `backend/`, `database/` |

Le navigateur communique avec PHP via HTTP/JSON. Il ne se connecte jamais directement à MySQL.

## Technologies
- Frontend : HTML5, CSS3, JavaScript.
- Backend : PHP 8.1+, PDO.
- Base : MySQL 8+ ou MariaDB compatible.
- Local : XAMPP (Apache, PHP, MySQL/MariaDB, phpMyAdmin).
- Versionnement : Git/GitHub.

## Structure

```text
Projet-E_commerce/
├── frontend/                 # Jephte : interface et interactions navigateur
│   ├── index.html
│   ├── styles.css
│   └── app.js
├── backend/                  # Kandu : logique serveur PHP
│   ├── config/
│   ├── src/
│   └── public/api/
├── database/schema.sql       # Tables et données de démonstration
├── docs/                     # Architecture et contrat frontend/backend
├── .env.example
└── .gitignore
```

## Installation locale (XAMPP)

1. Placer le dépôt dans `C:\\xampp\\htdocs\\Projet-E_commerce\\`.
2. Démarrer Apache et MySQL depuis XAMPP.
3. Importer `database/schema.sql` dans phpMyAdmin.
4. Définir dans l'environnement PHP : `DB_HOST=127.0.0.1`, `DB_NAME=ecommerce_db`, `DB_USER=root`, `DB_PASS=` (configuration XAMPP locale uniquement). `.env.example` est un modèle ; PHP ne le charge pas automatiquement.
5. Ouvrir `http://localhost/Projet-E_commerce/`.

## API disponibles

Base locale : `http://localhost/Projet-E_commerce/backend/public/api`

| Méthode | Route | Accès | Description |
|---|---|---|---|
| GET | `/health.php` | Public | Vérifie l'API et la connexion MySQL |
| GET | `/products.php` | Public | Liste des produits actifs |
| GET | `/products.php?q=casque` | Public | Recherche |
| GET | `/products.php?category=audio` | Public | Filtre par slug de catégorie |
| GET | `/categories.php` | Public | Liste des catégories et nombre de produits |
| POST | `/auth/register.php` | Public | Crée un compte client |
| POST | `/auth/login.php` | Public | Ouvre une session |
| GET | `/auth/me.php` | Public | État de la session et utilisateur courant |
| POST | `/auth/logout.php` | Session | Ferme la session |
| POST | `/orders/create.php` | Session | Enregistre une commande |
| GET | `/orders/index.php` | Session | Historique des commandes du client connecté |

Les routes POST attendent du JSON avec `Content-Type: application/json`. Pour créer une commande, envoyer uniquement `product_id` et `quantity` par article, plus `shipping_address`. PHP recalcule les prix depuis MySQL et contrôle le stock.

## État du développement

Le backend comprend une connexion PDO, les API produits/catégories, inscription/connexion/déconnexion, lecture de session, création et historique des commandes, ainsi qu'une route de diagnostic.

**Le frontend reste le prototype précédent et n'est pas encore intégré aux API.** Son catalogue et son panier/favoris utilisent encore JavaScript et `localStorage`. Jephte doit connecter progressivement les écrans aux routes documentées.

Ce dépôt est un socle de développement local. Aucun paiement réel n'est intégré et les tests sur une instance XAMPP réelle restent à effectuer.

## Sécurité — travaux avant mise en production

- Mots de passe hachés via `password_hash()`, vérifiés via `password_verify()`.
- Requêtes SQL paramétrées avec PDO.
- Configuration secrète exclue du dépôt ; ne jamais commiter de vrai mot de passe.
- Le rôle administrateur ne doit jamais être attribué depuis une requête publique.
- À compléter avant production : protection CSRF des actions de session, limitation des tentatives de connexion, tests automatisés, HTTPS, journalisation, sauvegardes, gestion d'administration et audit de sécurité.

## Git et collaboration

- Jephte travaille dans `frontend/`.
- Kandu travaille dans `backend/` et `database/`.
- Toute modification de route ou de format JSON doit être documentée dans `docs/COLLABORATION_FRONTEND_BACKEND.md`.
- Utiliser des branches de fonctionnalité et des Pull Requests vers `main`.
