# Collaboration Frontend / Backend

## Répartition

### Jephte — développeur frontend
- Préserver et faire évoluer le design dans `frontend/`.
- Développer les pages, formulaires et interactions HTML/CSS/JavaScript.
- Consommer les endpoints PHP ci-dessous avec `fetch`.
- Gérer les états de chargement et messages d'erreur.
- Ne jamais accéder directement à MySQL ni placer des identifiants SQL dans le navigateur.

### Kandu Gregory — développeur backend
- Maintenir le schéma relationnel dans `database/schema.sql`.
- Développer les endpoints PHP dans `backend/public/api/`.
- Gérer validation, authentification, autorisations, transactions et règles métier.
- Documenter les changements de contrat API.

## Base des routes

Sur XAMPP : `http://localhost/Projet-E_commerce/backend/public/api`.

Toutes les réponses sont JSON et contiennent un booléen `success`.

| Méthode | Route | Accès | Description |
|---|---|---|---|
| GET | `/health.php` | Public | Vérifie API et MySQL |
| GET | `/products.php` | Public | Produits actifs |
| GET | `/products.php?q=casque` | Public | Recherche |
| GET | `/products.php?category=audio` | Public | Filtre par slug |
| GET | `/categories.php` | Public | Catégories |
| POST | `/auth/register.php` | Public | Inscription client |
| POST | `/auth/login.php` | Public | Connexion |
| GET | `/auth/me.php` | Public | Session courante |
| POST | `/auth/logout.php` | Session | Déconnexion |
| POST | `/orders/create.php` | Session | Création de commande |
| GET | `/orders/index.php` | Session | Historique du client connecté |

## Exemples de requêtes

Inscription :
```json
{ "name": "Nom du client", "email": "client@example.com", "password": "motdepasse-securise" }
```

Connexion :
```json
{ "email": "client@example.com", "password": "motdepasse-securise" }
```

Commande :
```json
{
  "shipping_address": "Adresse complète",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

Le backend recalcule les prix depuis MySQL, vérifie le stock et enregistre la commande dans une transaction. Le frontend ne doit jamais envoyer un prix considéré comme fiable.

## Intégration JavaScript

Exemple d'appel GET de même origine :
```js
const response = await fetch('../backend/public/api/products.php', {
  credentials: 'same-origin',
  headers: { 'Accept': 'application/json' }
});
const result = await response.json();
if (!response.ok || !result.success) {
  throw new Error(result.message || 'Erreur lors du chargement');
}
const products = result.data;
```

Pour les POST JSON, ajouter `method: 'POST'`, `Content-Type: 'application/json'` et `body: JSON.stringify(payload)`. Les sessions utilisent un cookie PHP. Les erreurs HTTP (401, 403, 409, 422, 500, 503) doivent être traitées par l'interface sans afficher les détails techniques.

## Règles de travail

1. Les URLs doivent rester relatives à l'application afin de fonctionner sur localhost et sur le réseau local.
2. Toute nouvelle route doit préciser méthode, paramètres, réponse et codes HTTP.
3. Prévenir l'autre développeur avant de modifier un contrat API existant.
4. Garder les prix affichés comme indicatifs : le backend est la source de vérité.
5. Faire des commits ciblés et des Pull Requests vers `main`.

## Reste à faire

- Intégration du catalogue et des filtres JavaScript avec les API produits/catégories.
- Connexion des formulaires d'inscription/connexion et de l'état de session.
- Connexion du panier et du formulaire de commande.
- Ajouter protection CSRF aux opérations authentifiées, limitation des tentatives, tests et contrôles de sécurité.
- Construire une administration avec attribution contrôlée du rôle admin.
- Ajouter les fonctionnalités de favoris persistants si elles sont confirmées dans le périmètre.
- Intégrer un prestataire de paiement uniquement si le paiement en ligne est retenu.
