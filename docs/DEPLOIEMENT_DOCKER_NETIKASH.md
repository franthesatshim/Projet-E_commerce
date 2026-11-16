# DEUS COM NOS — Docker + Netikash (PHP/MySQL)

## 1. Ce qui est prêt, et ce qui ne l'est pas

Le code intègre un **vrai parcours de paiement hébergé** Netikash (pas une simulation) : OAuth2 client credentials, demande de paiement, redirection vers Netikash, retour sur la commande, webhook signé, vérification serveur-à-serveur et rapprochement du statut. **Il ne peut pas recevoir d'argent avant** la création d'un compte marchand actif, l'ajout des clés correctes, la configuration du callback et des essais réels/sandbox contrôlés.

Les modes proposés au client sont : **paiement à la livraison** (conservé) ou **Netikash** (seulement si activé). Les opérateurs disponibles sont choisis sur la page hébergée Netikash ; ne pas promettre Orange Money/Airtel Money/etc. tant que le compte marchand n'a pas confirmé leur disponibilité. Les prix de la boutique sont en **USD**. Aucune conversion CDF n'a été inventée.

## 2. Lancer l'application en local avec Docker Desktop

Prérequis : Docker Desktop installé, Docker Compose v2, Git. Depuis le répertoire contenant `Dockerfile` et `docker-compose.yml` :

```powershell
Copy-Item .env.example .env
notepad .env
# Modifiez les deux mots de passe MySQL avant le premier démarrage.
docker compose up -d --build
docker compose ps
```

Ouvrez **http://localhost:3000/index.php** (conteneur Apache écoute sur le port 80, exposé sur `127.0.0.1:3000`). Ce projet Docker sert directement `deus-com-nos-php` à la racine : ne pas rajouter `/deus-com-nos-php` à l'URL. XAMPP doit libérer le port 3000 ou changer `WEB_PORT` dans `.env`.

Au premier démarrage seulement, le conteneur MySQL importe `schema.sql`, puis `catalogue.sql`. Il crée **une nouvelle base** stockée dans le volume nommé `mysql_data`. **Les comptes et commandes qui se trouvent dans XAMPP ne sont pas automatiquement transférés.** Ne remplacez pas une base existante sans sauvegarde préalable.

```powershell
# logs / diagnostic
docker compose logs -f web
docker compose logs -f db
# arrêt sans perdre les données
docker compose down
# reconstruire après modifications du code
docker compose up -d --build
```

**Ne lancez pas `docker compose down -v`** sur un site contenant des commandes : cela supprimerait les volumes MySQL et des images uploadées.

### Restaurer une base MySQL XAMPP existante

Exportez d'abord `deus_com_nos` avec phpMyAdmin (SQL) et conservez l'archive hors du dépôt. Importez le dump dans la nouvelle base Docker, après vérification des collisions d'identifiants et du schéma. Une fois la copie restaurée, exécutez **`migration.sql` puis `migration_netikash.sql`** dans la base restaurée. N'importez pas `schema.sql` ou `catalogue.sql` une seconde fois sur la base réelle. Vérifiez les commandes et utilisateurs avant tout paiement. Si vous ne maîtrisez pas l'import, utilisez un environnement de test pour valider la procédure avant de toucher au site réel.

### Volumes et sécurité

- `mysql_data`: base, clients et commandes (persistant).
- `product_uploads`: photos envoyées dans l'administration (persistant).
- La base MySQL n'a **aucun port public** dans Compose.
- Le service PHP n'écoute que sur `127.0.0.1:3000`. Pour mettre le site sur Internet, utilisez un **serveur/VPS** et un **reverse proxy HTTPS** (Nginx, Caddy, Traefik, etc.) devant Docker. Ne publiez pas directement le port HTTP à tout Internet.
- Les secrets sont dans `.env`, **jamais sur GitHub**. Conservez un fichier de sauvegarde hors du serveur et une procédure de restauration.

## 3. Activer le paiement Netikash

1. Créer/configurer un compte marchand sur **https://gateway.netikash.com/login** et obtenir les accès API : `client_id` et `client_secret` dans « Clés et Application ».
2. Utiliser une vraie URL publique **HTTPS**, par exemple `https://boutique.example.com`, pointant vers la **racine de l'application PHP** déployée. Mettre cette URL dans `APP_URL` (sans `index.php`). Le site local `http://localhost:3000` ne fonctionne pas comme URL publique de webhook.
3. Dans `.env`, configurer :

```ini
APP_URL=https://boutique.example.com
NETIKASH_ENABLED=1
NETIKASH_CLIENT_ID=<votre_client_id>
NETIKASH_CLIENT_SECRET=<votre_client_secret>
NETIKASH_WEBHOOK_SECRET=<cle_signature_callback_destination>
NETIKASH_WEBHOOK_SIGNATURE_MODE=raw_base64
```

4. Dans les **Callback Destinations** Netikash, créer une destination HTTPS vers `https://boutique.example.com/netikash_webhook.php`, pour l'organisation / l'application correspondant aux identifiants. Événements recommandés : `payment.success,payment.cashed,payment.failed,payment.expired,payment.locked,payment.canceled`. Conserver **en secret** le `signing_key` renvoyé.
5. Vérifier avec Netikash le **format réel de signature** de votre environnement : la section `/documentation?q=webhook` spécifie `HMAC-SHA256(rawBody)` encodé en **Base64**, tandis que `/examples` montre `HMAC-SHA256(timestamp + '.' + rawBody)` encodé en **hex**. L'application accepte la sélection explicite `raw_base64` (par défaut) ou `timestamp_hex`. Ne jamais désactiver la validation. Pour les deux modes, le `X-Timestamp` doit être récent (10 min).
6. Faire des essais avec les identifiants de test/sandbox **fournis par Netikash**, dans une boutique avec produits fictifs et montant faible. Les URLs d'API utilisées suivent les exemples officiels :
   - `POST https://accounts.netikash.com/oauth2/token`
   - `POST https://api.netikash.com/api/v1/trs/transactions/requests/cli-payments`
   - `GET https://api.netikash.com/api/v1/trs/transactions/requests/{id}`
7. Tester une confirmation, un refus, une expiration, un callback répété et un retour navigateur sans paiement. Ne déployer en production que lorsque le statut `paye` s'affiche **après une vérification Netikash**.

**Une demande au statut `approved` est enregistrée `autorise` ; seul `completed` donne `paye`**, pour attendre le règlement effectif indiqué par les docs. Une commande en ligne non payée ne peut pas être expédiée par l'administrateur. Un paiement douteux n'est pas automatiquement réessayé, pour éviter des transactions dupliquées : contacter le support et rapprocher sur le portail Netikash.

### URLs à retenir

- Boutique Docker : `http://localhost:3000/index.php` (test sans paiement en ligne).
- Retour client Netikash : `https://boutique.example.com/index.php?page=order&id=123` (c'est une redirection, **pas** une preuve de paiement).
- Webhook sécurisé : `https://boutique.example.com/netikash_webhook.php` (Netikash envoie **POST**).
- Documentation : https://developer.netikash.com/documentation
- Parcours de paiement : https://developer.netikash.com/payment-flow
- Webhooks : https://developer.netikash.com/documentation?q=webhook

## 4. Migration pour XAMPP existant (sans Docker)

Sauvegarder d'abord la base `deus_com_nos` via phpMyAdmin. Ensuite, importer dans cette base :

1. `deus-com-nos-php/migration.sql` (anciennes colonnes).
2. `deus-com-nos-php/migration_netikash.sql` (nouveaux états de paiement et table `payment_attempts`).

Sans cela, la fonction de paiement en ligne renverra une erreur SQL. La migration n'efface ni compte ni commande.

Les variables d'environnement (ou la configuration Apache) sont également nécessaires sur XAMPP. Ne placez pas les secrets dans des fichiers publiquement accessibles. Sur XAMPP local, **gardez `NETIKASH_ENABLED=0`** tant que le site n'est pas accessible en HTTPS et que le webhook n'est pas configuré.

## 5. Validation technique

```powershell
# Dans l'image Docker installée :
docker compose exec web php -l actions.php
docker compose exec web php -l netikash_webhook.php
docker compose exec web php -m
# Tests unitaires simples du protocole côté machine avec PHP CLI :
php deus-com-nos-php/tests/netikash_test.php
```

**Ce qui reste à valider** : build Docker réel sur votre poste, connectivité à l'API avec vos clés, signature du webhook de votre organisation et paiement de test. Les identifiants et le site HTTPS réel appartiennent à l'exploitant, pas à GitHub.
