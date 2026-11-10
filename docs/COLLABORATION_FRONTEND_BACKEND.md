# Collaboration Frontend / Backend

## Responsabilités

### Jephte — Frontend
- Conserver et faire évoluer le design existant dans `frontend/`.
- Gérer les pages, composants visuels, formulaires et interactions JavaScript.
- Appeler les endpoints PHP en HTTP avec des réponses JSON.
- Afficher proprement les erreurs et états de chargement.
- Ne jamais accéder directement à MySQL et ne jamais contenir de mot de passe SQL dans JavaScript.

### Kandu Gregory — Backend
- Définir le schéma relationnel dans `database/schema.sql`.
- Développer les endpoints PHP dans `backend/public/api/`.
- Implémenter validation serveur, authentification, autorisations et règles métier.
- Garantir les transactions et la cohérence des stocks/commandes.
- Documenter les changements de contrat API.

## Contrat API initial

Toutes les réponses sont au format JSON : `{ "success": true|false, ... }`.

| Méthode | Endpoint | Accès | Fonction |
|---|---|---|---|
| GET | `/backend/public/api/products.php` | Public | Liste des produits actifs |
| GET | `/backend/public/api/products.php?q=casque` | Public | Recherche par nom/description |
| GET | `/backend/public/api/products.php?category=audio` | Public | Filtre par slug de catégorie |
| POST | `/backend/public/api/auth/register.php` | Public | Créer un compte client |
| POST | `/backend/public/api/auth/login.php` | Public | Ouvrir une session |
| POST | `/backend/public/api/auth/logout.php` | Session | Fermer la session |
| POST | `/backend/public/api/orders/create.php` | Session | Créer une commande |

### Exemple — inscription

```json
{
  "name": "Nom du client",
  "email": "client@example.com",
  "password": "motdepasse-securise"
}
```

### Exemple — connexion

```json
{
  "email": "client@example.com",
  "password": "motdepasse-securise"
}
```

### Exemple — commande

```json
{
  "shipping_address": "Adresse complète",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

Le client envoie uniquement les identifiants des produits et les quantités. Le backend récupère les prix officiels en base, verrouille les lignes de stock pendant la transaction, calcule le total et enregistre la commande.

## Règles d'intégration

1. Le frontend appelle les URLs relatives de l'application pour rester utilisable sur localhost et sur le réseau local.
2. Les requêtes POST envoient `Content-Type: application/json`.
3. Le navigateur conserve la session via le cookie HTTP ; les appels `fetch` doivent utiliser les paramètres de credentials adaptés à une application de même origine.
4. Les erreurs HTTP (400, 401, 403, 409, 422, 500) doivent être affichées sans exposer les détails SQL.
5. Les prix affichés dans le panier peuvent être indicatifs ; seul le total calculé par PHP fait foi.
6. Avant d'ajouter ou modifier un endpoint, prévenir l'autre développeur et mettre à jour ce document.

## Travail restant

- Connecter le catalogue JavaScript à l'API produits.
- Connecter inscription, connexion et déconnexion à la session PHP.
- Persister panier/favoris côté serveur pour les utilisateurs connectés, si retenu dans le périmètre.
- Finaliser l'historique et le suivi des commandes.
- Ajouter protection CSRF sur les actions authentifiées, limitation des tentatives, validations et tests.
- Définir le rôle administrateur de manière contrôlée et construire l'interface d'administration.
- Choisir et intégrer un prestataire de paiement uniquement si le paiement en ligne est demandé.
