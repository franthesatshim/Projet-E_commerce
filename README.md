# ShopNow — Projet e-commerce

Application e-commerce développée en équipe avec **HTML, CSS, JavaScript, PHP et MySQL/MariaDB**, sous XAMPP.

## Équipe et répartition

| Membre | Rôle | Responsabilités |
|---|---|---|
| Jephte | Développeur frontend | Interface utilisateur, intégration HTML/CSS/JavaScript, responsive design, appels aux API backend |
| Kandu Gregory | Développeur backend | PHP, architecture serveur, MySQL, authentification, règles métier, API, commandes et sécurité des données |

Les deux développeurs collaborent sur les contrats API et les tests d'intégration. Le frontend ne se connecte jamais directement à MySQL : il communique avec le backend PHP via HTTP/JSON.

## Technologies

- Frontend : HTML5, CSS3, JavaScript.
- Backend : PHP 8+ (PDO).
- Base de données : MySQL 8+ ou MariaDB compatible.
- Environnement local : XAMPP (Apache, PHP, MySQL/MariaDB, phpMyAdmin).
- Versionnement : Git et GitHub.

## Structure du dépôt

```text
Projet-E_commerce/
├── frontend/                 # Jephte — interface et interactions navigateur
│   ├── index.html
│   ├── styles.css
│   └── app.js
├── backend/                  # Kandu — logique serveur PHP
│   ├── config/
│   ├── src/
│   └── public/api/
├── database/
│   └── schema.sql            # Création des tables et données de démonstration
├── docs/
│   ├── ARCHITECTURE_DEPLOIEMENT_LOCAL.md
│   └── COLLABORATION_FRONTEND_BACKEND.md
├── .env.example
└── .gitignore
```

## Démarrage local avec XAMPP

1. Installer/démarrer Apache et MySQL depuis XAMPP.
2. Placer le dépôt dans `C:\\xampp\\htdocs\\Projet-E_commerce\\`.
3. Ouvrir phpMyAdmin et importer `database/schema.sql`.
4. Configurer les variables d'environnement PHP `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` selon votre installation locale. Les valeurs d'exemple sont dans `.env.example`. Le fichier n'est pas chargé automatiquement par PHP.
5. Ouvrir `http://localhost/Projet-E_commerce/` : la racine redirige vers l'interface `frontend/`.

API de départ (même origine) :
- `GET /Projet-E_commerce/backend/public/api/products.php`
- `POST /Projet-E_commerce/backend/public/api/auth/register.php`
- `POST /Projet-E_commerce/backend/public/api/auth/login.php`
- `POST /Projet-E_commerce/backend/public/api/auth/logout.php`
- `POST /Projet-E_commerce/backend/public/api/orders/create.php` (session connectée requise)

Les routes POST attendent un corps JSON avec l'en-tête `Content-Type: application/json`. Pour les commandes, le backend recalcule les prix depuis la base et vérifie le stock : ne faites jamais confiance au prix envoyé par le navigateur.

## État actuel et prochaine intégration

Le dossier `frontend/` contient encore le prototype d'interface précédent. Il utilise un catalogue JavaScript local et un panier/favoris en `localStorage`. **Il n'est pas encore connecté aux API PHP.** Jephte devra remplacer progressivement les données statiques par les appels API et connecter les formulaires de connexion/inscription et de commande.

Le backend contient une première base de connexion PDO, une API de lecture des produits, l'inscription/connexion/déconnexion par session et la création transactionnelle des commandes. C'est un socle de développement, pas une solution de paiement en ligne ni une mise en production finalisée.

## Sécurité et limites

- Les mots de passe sont hachés avec `password_hash()` et vérifiés avec `password_verify()`.
- Les requêtes SQL utilisent PDO et des paramètres préparés.
- Les secrets de configuration ne doivent jamais être commités.
- La création de compte attribue le rôle `customer`; ne permettez jamais au navigateur de choisir le rôle administrateur.
- Aucun paiement réel n'est intégré.
- Avant une mise en production : ajouter protection CSRF adaptée, limitation des tentatives de connexion, validation renforcée, journalisation, HTTPS, gestion d'administration et tests de sécurité.

## Collaboration Git

- Jephte travaille principalement dans `frontend/`.
- Kandu travaille principalement dans `backend/` et `database/`.
- Les changements de contrat API doivent être documentés dans `docs/COLLABORATION_FRONTEND_BACKEND.md`.
- Faire des commits petits et explicites, puis ouvrir une Pull Request vers `main`.
