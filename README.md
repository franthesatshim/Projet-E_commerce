# Projet E-commerce

Application e-commerce front-end réalisée en **HTML, CSS et JavaScript natif**.

## Lancer le projet

Aucune installation ni compilation n'est nécessaire :

1. Cloner ou télécharger le dépôt.
2. Ouvrir le fichier `index.html` dans un navigateur récent.
3. Pour un environnement de développement, utiliser l'extension Live Server de VS Code ou un serveur HTTP local.

## Fonctionnalités disponibles

- Page d'accueil et navigation responsive.
- Catégories de produits.
- Catalogue avec recherche, filtres par catégorie et prix, tri.
- Fiches détaillées des produits.
- Ajout au panier, modification des quantités et suppression.
- Favoris.
- Panier et formulaire de commande de démonstration.
- Persistance du panier et des favoris avec `localStorage`.

## Structure

```text
Projet-E_commerce/
├── index.html
├── styles.css
├── app.js
├── docs/
└── ...
```

## Limites de cette version

Cette livraison est un prototype front-end : les produits sont définis dans `app.js`, les données du panier et des favoris restent dans le navigateur. L'authentification, la base de données partagée, la gestion réelle des stocks et les paiements nécessitent un backend et une intégration sécurisée. Le formulaire de commande ne déclenche aucune transaction.

Les visuels produits sont représentés par des icônes/illustrations provisoires afin que l'interface fonctionne sans service d'images externe.

## Personnalisation

- Modifier le catalogue dans le tableau `products` de `app.js`.
- Modifier les couleurs et la mise en page dans `styles.css`.
- Modifier les sections et les textes dans `index.html`.
