# Architecture de déploiement local — ShopNow

## Stack retenue

L'équipe a validé une application web avec :
- Frontend : HTML, CSS et JavaScript (responsable : Jephte).
- Backend : PHP 8.1+ avec PDO (responsable : Kandu Gregory).
- Base de données : MySQL/MariaDB (responsable backend).
- Serveur local : Apache via XAMPP.

Le navigateur ne se connecte jamais directement à MySQL. Il appelle les endpoints PHP, qui valident les entrées, appliquent les règles métier et interrogent la base.

## Schéma logique

```text
Navigateur
   |
   | HTML / CSS / JavaScript
   v
Apache (XAMPP)
   |                            | fichiers frontend        \ HTTP JSON
   v                            v
frontend/                  backend/public/api/
                                |
                                v
                           PHP + PDO
                                |
                                v
                           MySQL/MariaDB
```

## Organisation du dépôt

- `frontend/` : pages, styles et JavaScript (Jephte).
- `backend/config/` : connexion et configuration serveur (Kandu).
- `backend/src/` : fonctions partagées, sessions, réponses JSON.
- `backend/public/api/` : points d'entrée HTTP PHP.
- `database/schema.sql` : schéma et données initiales.
- `docs/` : documentation d'équipe et déploiement.

## Installation XAMPP

1. Installer XAMPP et démarrer Apache et MySQL.
2. Placer le dépôt dans `C:\\xampp\\htdocs\\Projet-E_commerce\\`.
3. Importer `database/schema.sql` dans phpMyAdmin.
4. Définir les variables d'environnement PHP : `DB_HOST=127.0.0.1`, `DB_NAME=ecommerce_db`, `DB_USER=root`, `DB_PASS=` (configuration de développement XAMPP uniquement). En environnement partagé, créer un utilisateur MySQL dédié et un mot de passe robuste.
5. Accéder à `http://localhost/Projet-E_commerce/` ; le fichier racine redirige vers `frontend/`.

## Endpoints disponibles

- `GET /Projet-E_commerce/backend/public/api/products.php`
- `POST /Projet-E_commerce/backend/public/api/auth/register.php`
- `POST /Projet-E_commerce/backend/public/api/auth/login.php`
- `POST /Projet-E_commerce/backend/public/api/auth/logout.php`
- `POST /Projet-E_commerce/backend/public/api/orders/create.php`

Les routes POST attendent du JSON. L'authentification utilise une session PHP. Les commandes exigent une session client valide et sont enregistrées dans une transaction avec vérification du stock.

## Sécurité

- Garder les identifiants SQL hors du dépôt ; `.env.example` est un modèle et n'est pas chargé automatiquement par PHP.
- Le fichier `.gitignore` exclut les fichiers `.env`.
- Utiliser PDO et les requêtes préparées.
- Hacher les mots de passe avec les fonctions PHP adaptées.
- Ne jamais accepter un rôle administrateur fourni par le navigateur.
- Le dossier backend est protégé contre l'accès HTTP direct ; seul son sous-dossier public/API est exposé.
- En production, ajouter HTTPS, protection CSRF, limitation des tentatives, journalisation et tests de sécurité. Le socle actuel est destiné au développement local.

## Réseau local

Les appareils du même réseau peuvent consulter le site via l'adresse IPv4 du PC serveur, par exemple `http://IP-LOCALE/Projet-E_commerce/`. Autoriser Apache uniquement sur le réseau privé nécessaire. Ne jamais exposer le port MySQL 3306 au réseau public.

## Répartition

| Membre | Rôle | Dossiers |
|---|---|---|
| Jephte | Développeur frontend | `frontend/` |
| Kandu Gregory | Développeur backend | `backend/`, `database/` |
| Ensemble | Intégration | `docs/COLLABORATION_FRONTEND_BACKEND.md`, tests et Pull Requests |

## État

Le frontend est encore le prototype visuel et n'appelle pas encore les nouvelles API PHP. Le backend est un premier socle fonctionnel ; l'intégration frontend/backend et les protections de production restent à réaliser.
