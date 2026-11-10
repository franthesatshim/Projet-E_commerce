# Collaboration Frontend / Backend

## Responsabilités
- **Jephte — développeur frontend :** HTML/CSS/JavaScript, interface, responsive design, appels HTTP/JSON aux API.
- **Kandu Gregory — développeur backend :** PHP/PDO, MySQL, schéma, authentification, validation, commandes et sécurité serveur.

Le navigateur ne se connecte jamais directement à MySQL.

## Gestion des devises USD et CDF
Chaque produit expose deux champs indépendants : `price_usd` et `price_cdf`.
- La devise sélectionnée doit être `USD` ou `CDF`.
- Ne jamais calculer un prix CDF en multipliant automatiquement par un taux arbitraire.
- Si `price_cdf` est `null`, afficher « Prix CDF non disponible » et empêcher l'achat dans cette devise.
- Le panier doit garder une seule devise à la fois. Lors d'un changement de devise, recalculer les montants affichés avec le champ de prix correspondant.
- La création de commande envoie `currency` ; le serveur recalcule les prix et enregistre la devise sur la commande.

## Routes API
Base XAMPP : `http://localhost/Projet-E_commerce/backend/public/api`.

| Méthode | Route | Description |
|---|---|---|
| GET | `/health.php` | Vérification API et MySQL |
| GET | `/products.php` | Produits avec `price_usd`, `price_cdf` |
| GET | `/products.php?q=casque` | Recherche |
| GET | `/products.php?category=audio` | Filtre catégorie |
| GET | `/categories.php` | Catégories |
| POST | `/auth/register.php` | Inscription |
| POST | `/auth/login.php` | Connexion |
| GET | `/auth/me.php` | Session courante |
| POST | `/auth/logout.php` | Déconnexion |
| POST | `/orders/create.php` | Création de commande |
| GET | `/orders/index.php` | Historique du client |

## Contrat de commande
```json
{
  "currency": "USD",
  "shipping_address": "Adresse complète",
  "items": [
    { "product_id": 1, "quantity": 2 }
  ]
}
```
Remplacer `USD` par `CDF` pour une commande en francs congolais. Les prix et le total envoyés par le navigateur ne sont jamais considérés comme fiables.

## Exemple de chargement catalogue
```js
const response = await fetch('../backend/public/api/products.php', {
  credentials: 'same-origin',
  headers: { 'Accept': 'application/json' }
});
const result = await response.json();
if (!response.ok || !result.success) throw new Error(result.message || 'Erreur');
const products = result.data;
```

## Installation et migration
Pour une base neuve, importer `database/schema.sql`.
Pour une base déjà créée avec l'ancien champ `products.price`, sauvegarder la base puis exécuter une seule fois `database/migrations/20261110_add_usd_cdf_currency.sql`.

## À faire
- Connecter le catalogue, filtres, session et panier du frontend aux API.
- Saisir les prix CDF validés dans l'administration avant d'activer cette devise pour les ventes.
- Ajouter CSRF, limitation des tentatives, tests automatisés et durcissement production.
- Construire une interface d'administration sécurisée.
