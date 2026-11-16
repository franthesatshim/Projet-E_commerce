# DEUS COM NOS — boutique PHP / MySQL

Boutique en ligne de chaussures, sacs, tissus wax et perruques, développée en PHP 8.2+ et MySQL, sans framework.
Le client peut choisir **le paiement à la livraison** ou **Netikash (paiement en ligne réel)** après activation de l’intégration et configuration du webhook sécurisé.

## Installation (XAMPP)

1. Copier le dépôt dans `C:\xampp\htdocs\Projet-E_commerce` (ou utiliser `git pull` dans ce dossier).
2. Démarrer **Apache** et **MySQL** dans XAMPP. Si Apache utilise le port **3000**, adapter les URLs ci-dessous.
3. Dans phpMyAdmin (http://localhost/phpmyadmin), importer **dans cet ordre** :
   1. `deus-com-nos-php/schema.sql` (crée la base et les tables)
   2. `deus-com-nos-php/catalogue.sql` (35 produits avec leurs photos)
4. Ouvrir http://localhost/Projet-E_commerce/deus-com-nos-php/index.php
5. Créer un compte sur la page d'inscription, puis le passer administrateur dans phpMyAdmin :
   `UPDATE users SET role='admin' WHERE email='votre-email@example.com';`
   Se déconnecter puis se reconnecter : le lien « Administration » apparaît.

### Mise à jour d'une base déjà existante (IMPORTANT)

1. Dans **phpMyAdmin → deus_com_nos → Exporter → Rapide → SQL**, sauvegarder la base de données **avant toute importation**.
2. Importer `migration.sql` pour ajouter le téléphone et les champs de paiement manquants sans supprimer de données.
3. Importer `catalogue.sql` pour **ajouter les 35 nouveaux produits**. Si un produit de même nom existe déjà, sa catégorie, sa description et sa photo sont mises à jour ; **son prix et son stock ne sont pas écrasés**. Les anciens articles aux noms de démonstration connus sont **masqués mais jamais supprimés**.
4. Les **comptes, commandes, lignes de commande et catégories historiques sont conservés**. Aucun `DROP` de table réelle ni `DELETE` de produit/client/commande n'est lancé.
5. Ne pas importer `schema.sql` en guise de migration : il sert aux installations neuves (ses `CREATE TABLE IF NOT EXISTS` ne mettent pas à niveau des colonnes existantes).
6. En local avec Apache sur le port 3000 : `http://localhost:3000/Projet-E_commerce/deus-com-nos-php/index.php` ; selon la configuration du `DocumentRoot`, l'URL peut aussi être `http://localhost:3000/deus-com-nos-php/index.php`.


Configuration par variables d'environnement : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
(valeurs par défaut : `127.0.0.1`, `deus_com_nos`, `root`, mot de passe vide).

## Organisation du code

| Fichier | Rôle |
|---|---|
| `index.php` | Point d'entrée : routage, données communes, panier |
| `actions.php` | Tous les formulaires (inscription, connexion, panier, commande, administration) |
| `config.php` | Connexion BDD, session sécurisée, fonctions utilitaires |
| `views/` | Une vue par page (`home`, `shop`, `product`, `cart`, `admin`…), `_layout.php` (en-tête/pied), `_card.php` (carte produit) |
| `style.css` | Styles |
| `assets/products/` | Photos des produits (voir ci-dessous) |
| `schema.sql` / `migration.sql` / `catalogue.sql` | Base de données |

## Photos des produits

- Les photos du catalogue sont dans `assets/products/` : copies optimisées (format 4:5, moins de 200 Ko) des photos du dossier `Screenshots/`, qui reste **intact**.
- Chaque photo correspond au nom et à la description du produit. Les prix en dirhams ajoutés par le fournisseur sur deux photos (mules, sandales compensées) ont été effacés ; les références (R7601-5, YQ6627…) sont conservées.
- Dans l'administration, on peut **envoyer une nouvelle photo** (JPG, PNG, WebP, 5 Mo), choisir une photo déjà présente, ou coller l'URL d'une photo Unsplash/Pexels. Les photos envoyées sont enregistrées dans `assets/products/` sous un nom généré (`up-….jpg`) et ne sont pas suivies par Git.
- Si l'envoi échoue, vérifier `upload_max_filesize` et `post_max_size` dans `php.ini`.

## Prix et stocks de démonstration

Les prix (USD) et les stocks de `catalogue.sql` sont **fictifs**. Les corriger dans
Administration → Produits → Modifier avant toute vraie vente. Les descriptions décrivent ce que montrent les photos ;
les pointures, matières et métrages sont à confirmer par la boutique.

## Fonctionnalités

- Collection avec recherche, catégories, tri par prix, fiches produit avec produits similaires.
- Panier conservé en session (les articles masqués ou épuisés sont retirés automatiquement).
- Compte client, commande avec **téléphone et adresse de livraison**, suivi du statut (en attente, confirmée, expédiée, livrée, annulée).
- Commande transactionnelle avec contrôle du stock ; l'annulation par l'administrateur remet les articles en stock une seule fois.
- Administration : tableau de bord, produits (création, modification préremplie, photo, masquer/afficher), catégories, commandes (client, téléphone, adresse, articles, statut), utilisateurs.

## Sécurité (déjà en place)

Mots de passe hachés, jeton CSRF sur tous les formulaires, requêtes préparées, sessions `HttpOnly` + `SameSite`,
envois de photos vérifiés (type réel de l'image, nom généré, exécution de scripts interdite dans `assets/products/`).

## Avant une mise en production

- Activer HTTPS, créer un utilisateur MySQL dédié (pas `root`), mettre en place des sauvegardes.
- L'intégration Netikash est disponible, mais reste **désactivée sans les clés marchandes, le webhook et une URL HTTPS publique**. Faire des essais avant une mise en production.
- Ajouter des e-mails de confirmation de commande et une limitation des tentatives de connexion.
- Vérifier les droits sur les photos de personnes (perruques) et de marques (Crabkids, Vlisco).

## Paiement réel Netikash et déploiement Docker

**Guide d’installation pas à pas :** [docs/DEPLOIEMENT_DOCKER_NETIKASH.md](../docs/DEPLOIEMENT_DOCKER_NETIKASH.md).

- Deux parcours : **paiement à la livraison** (toujours actif) et **paiement hébergé Netikash** (activable via variables d'environnement).
- Le site conserve les prix en **USD**. Le client choisit les moyens réellement disponibles dans l'interface Netikash : aucun opérateur n'est simulé.
- Contrôle du montant côté serveur, requête OAuth2, validation HTTPS, références uniques et enregistrement des tentatives.
- Un webhook n'est accepté qu'avec une signature HMAC vérifiée, un timestamp récent et une **vérification serveur** de la transaction.
- Seul le statut Netikash `completed` permet de marquer une commande `paye`. `approved` est `autorise`, sans autoriser l'expédition.
- Pour une **ancienne base** : sauvegarde, puis `migration.sql` et `migration_netikash.sql`. Pour une **nouvelle installation Docker**, les scripts `schema.sql` et `catalogue.sql` sont chargés automatiquement au premier démarrage.
- Pour Docker, copier `.env.example` vers `.env`, modifier les mots de passe, puis `docker compose up -d --build`.
- **Les identifiants Netikash ne doivent jamais être publiés dans GitHub.** Le paiement réel reste désactivé avec `NETIKASH_ENABLED=0`, valeur par défaut.
