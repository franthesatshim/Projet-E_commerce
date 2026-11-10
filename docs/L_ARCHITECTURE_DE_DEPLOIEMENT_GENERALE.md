# L_ARCHITECTURE_DE_DEPLOIEMENT_GENERALE

## 1. Présentation du projet

Le projet consiste à développer une plateforme de commerce électronique (site e-commerce) permettant aux utilisateurs de consulter des produits, de gérer un panier et de passer des commandes.

Selon les dernières informations communiquées par l'équipe de développement, les technologies retenues sont :

- **Laravel** : framework PHP utilisé pour développer la partie serveur de l'application.
- **SQL** : langage utilisé pour gérer les données dans une base de données relationnelle. Le système de gestion de base de données (SGBD) exact, par exemple MySQL ou PostgreSQL, reste à confirmer par l'équipe.
- **HTML, CSS et JavaScript** : technologies utilisées pour construire et rendre interactives les interfaces web.
- **PHP** : langage sur lequel repose Laravel.

Cette architecture décrit l'organisation générale du déploiement. Certains choix techniques (SGBD exact, serveur web, hébergement et éventuels services complémentaires) devront être confirmés avec l'équipe de développement.

## 2. Objectif de l'architecture

L'objectif est de présenter les composants nécessaires au fonctionnement du site, leurs relations et le chemin suivi par une requête depuis le navigateur jusqu'à la base de données.

L'application est conçue comme une application web monolithique avec Laravel : les principales fonctionnalités métier sont regroupées dans un même projet applicatif. Le serveur web, l'application et la base de données peuvent être installés sur une même machine au départ, puis séparés sur plusieurs serveurs si les besoins évoluent.

## 3. Schéma général de l'architecture

```text
             UTILISATEUR
                  |
                  v
       Navigateur web (client)
        HTML / CSS / JavaScript
                  |
             HTTP / HTTPS
                  |
                  v
       SERVEUR WEB
       Apache ou Nginx
                  |
                  v
       APPLICATION LARAVEL
             (PHP)
                  |
       +----------+-----------+
       |          |           |
       v          v           v
   Routes      Contrôleurs   Modèles
   Laravel     Laravel       Eloquent
       |          |           |
       +----------+-----------+
                  |
                  v
        BASE DE DONNÉES SQL
       (SGBD à confirmer :
        MySQL, PostgreSQL, etc.)
                  |
                  v
       Données de l'application
       Utilisateurs, produits,
       commandes, paiements, etc.
```

Les fichiers envoyés par les utilisateurs (par exemple les images de produits) peuvent être conservés dans le stockage de l'application Laravel ou dans un stockage externe, selon les besoins du projet.

## 4. Rôle des principaux composants

| Composant | Rôle |
|---|---|
| Navigateur web | Permet aux utilisateurs d'accéder au site et d'utiliser ses fonctionnalités. |
| HTML / CSS / JavaScript | Construisent les pages, leur présentation et les interactions côté navigateur. |
| Apache ou Nginx | Reçoit les requêtes HTTP/HTTPS et les transmet à l'application Laravel. |
| PHP | Exécute le code de l'application Laravel. |
| Laravel | Gère les routes, les règles métier, l'authentification, les traitements et les échanges avec la base de données. |
| Eloquent ORM | Permet à Laravel de manipuler les données de la base SQL à travers les modèles. |
| Base de données SQL | Stocke les informations de manière structurée. |
| Composer | Gère les dépendances PHP du projet. |
| Artisan | Fournit les commandes utiles à l'administration et au développement de l'application. |

## 5. Organisation interne du projet Laravel

Une structure Laravel standard peut notamment contenir :

```text
projet-e-commerce/
│
├── app/
│   ├── Models/             # Modèles et relations avec les données
│   ├── Http/
│   │   ├── Controllers/    # Traitement des requêtes
│   │   └── Middleware/     # Contrôles intermédiaires
│
├── bootstrap/
├── config/                 # Configuration de Laravel
├── database/
│   ├── migrations/         # Structure et évolution des tables
│   ├── seeders/            # Données initiales ou de test
│   └── factories/
│
├── public/                 # Point d'entrée public du site
├── resources/
│   ├── views/              # Vues et interfaces
│   ├── css/
│   └── js/
│
├── routes/
│   ├── web.php             # Routes web
│   └── api.php             # Routes API, si utilisées
│
├── storage/                # Fichiers et journaux de l'application
├── tests/                  # Tests
├── vendor/                 # Dépendances installées par Composer
├── .env                    # Configuration locale et secrets
├── composer.json
└── artisan
```

Cette arborescence est indicative : elle peut varier selon la version de Laravel et les choix de l'équipe.

## 6. Fonctionnement d'une requête

Lorsqu'un utilisateur consulte une page ou effectue une action :

1. L'utilisateur ouvre le site dans son navigateur.
2. Le navigateur envoie une requête HTTP ou HTTPS au serveur web.
3. Le serveur web transmet la requête au point d'entrée public de Laravel.
4. Laravel identifie la route correspondante.
5. Le contrôleur exécute le traitement demandé et applique les règles métier.
6. Si nécessaire, le modèle Eloquent interroge ou modifie la base de données SQL.
7. Laravel prépare une réponse (page HTML ou réponse JSON selon le cas).
8. Le serveur web renvoie la réponse au navigateur.

**Important :** le navigateur ne doit jamais se connecter directement à la base de données SQL. Les échanges avec la base passent par le serveur et l'application Laravel.

## 7. Environnement de développement

Pour travailler localement, chaque développeur doit disposer d'un environnement compatible avec le projet.

Éléments nécessaires :

- PHP dans une version compatible avec la version de Laravel choisie ;
- Composer ;
- le SGBD SQL retenu par l'équipe ;
- un serveur web ou l'environnement de développement Laravel ;
- Git pour récupérer et partager le code source ;
- un éditeur de code, par exemple Visual Studio Code.

Laravel peut être exécuté localement avec son serveur de développement pour les tests. Pour un environnement plus proche de la production, l'équipe peut configurer Apache ou Nginx.

### Configuration de la base de données

Les paramètres de connexion sont définis dans le fichier `.env` de chaque environnement. Exemple indicatif pour une base MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_de_la_base
DB_USERNAME=utilisateur
DB_PASSWORD=mot_de_passe
```

Cet exemple ne signifie pas que MySQL est déjà définitivement choisi. Si l'équipe retient PostgreSQL ou un autre SGBD compatible, les paramètres devront être adaptés.

Les fichiers `.env` contenant des mots de passe ne doivent pas être publiés sur GitHub. Le dépôt doit contenir un fichier `.env.example` sans secrets réels.

## 8. Préparation et déploiement

Le déploiement consiste à installer et configurer l'application sur une machine ou un serveur accessible aux utilisateurs.

### Étapes générales

1. Préparer le serveur et installer une version compatible de PHP.
2. Installer et configurer Apache ou Nginx.
3. Installer Composer et les extensions PHP requises par le projet.
4. Installer ou rendre accessible le SGBD SQL retenu.
5. Récupérer le code source depuis le dépôt GitHub.
6. Installer les dépendances avec Composer.
7. Configurer les variables d'environnement dans le fichier `.env`.
8. Créer la base de données et exécuter les migrations Laravel prévues par l'équipe.
9. Configurer le serveur web pour que la racine publique du site pointe vers le dossier `public/` de Laravel.
10. Configurer les permissions nécessaires sur les dossiers `storage/` et `bootstrap/cache/`.
11. Tester les fonctionnalités principales et consulter les journaux en cas d'erreur.
12. Mettre en place les sauvegardes de la base de données et des fichiers importants.

Les commandes exactes dépendront de l'environnement choisi et des consignes du développeur principal. Elles devront être documentées avant le déploiement final.

## 9. Sécurité

Les mesures suivantes doivent être appliquées :

- Utiliser HTTPS pour protéger les échanges entre le navigateur et le serveur.
- Ne jamais exposer le fichier `.env` ni les identifiants de la base de données.
- Désactiver le mode débogage en production avec `APP_DEBUG=false`.
- Utiliser un compte SQL dédié à l'application avec uniquement les permissions nécessaires.
- Garder PHP, Laravel, les dépendances et le SGBD à jour.
- Vérifier et limiter les accès aux espaces administrateur.
- Valider les données reçues par les formulaires côté serveur.
- Protéger les fichiers téléversés et contrôler leur type et leur taille.
- Effectuer des sauvegardes régulières et tester leur restauration.
- Ne pas exposer le port de la base de données directement à Internet.

## 10. Sauvegarde et restauration

Les éléments à sauvegarder sont :

- la base de données SQL ;
- les fichiers téléversés et les documents conservés par l'application ;
- les fichiers de configuration nécessaires, en protégeant strictement les secrets ;
- les éléments utiles au redéploiement, notamment le code source versionné sur GitHub.

Une procédure de restauration doit être préparée et testée afin de vérifier qu'une sauvegarde permet réellement de remettre le site en service.

## 11. Responsabilités du SysAdmin

Le rôle du SysAdmin dans ce projet comprend notamment :

- préparer et maintenir l'environnement de développement ou de déploiement ;
- vérifier la compatibilité des versions de PHP, Laravel et du SGBD ;
- participer à la configuration du serveur web ;
- gérer les paramètres d'environnement sans publier les secrets ;
- vérifier les accès, les permissions et les ports réseau ;
- organiser les sauvegardes et vérifier leur restauration ;
- surveiller les erreurs et le fonctionnement général du site ;
- documenter les procédures d'installation, de déploiement et de maintenance.

## 12. Informations à confirmer avec l'équipe de développement

Avant de finaliser le déploiement, le SysAdmin doit obtenir les réponses aux questions suivantes :

1. Quelle version de Laravel sera utilisée ?
2. Quel SGBD SQL sera retenu exactement : MySQL, PostgreSQL ou autre ?
3. Quelle version de PHP sera requise ?
4. Le projet utilisera-t-il Blade, une API ou un frontend JavaScript séparé ?
5. Le site sera-t-il d'abord exécuté sur un seul ordinateur, sur un réseau local ou directement sur un serveur distant ?
6. Quel serveur web sera utilisé : Apache ou Nginx ?
7. Où seront stockées les images et les autres pièces jointes ?
8. Un service de paiement externe, un système d'e-mail ou une file de tâches sera-t-il intégré ?

Ces éléments permettront d'adapter précisément le schéma et les procédures au projet réel.

## 13. Critères de validation

L'architecture pourra être considérée comme opérationnelle lorsque :

- le site est accessible depuis un navigateur ;
- Laravel reçoit et traite correctement les requêtes ;
- la connexion à la base SQL fonctionne ;
- les données peuvent être enregistrées, consultées, modifiées et supprimées selon les droits prévus ;
- les fichiers nécessaires sont accessibles de manière sécurisée ;
- les erreurs sont journalisées ;
- les sauvegardes peuvent être restaurées ;
- les accès non autorisés sont correctement bloqués.

---

**Document préparé pour le rôle SysAdmin — Projet E-commerce.**

**Remarque :** cette architecture est une base générale fondée sur les technologies annoncées par l'équipe (Laravel et SQL). Elle devra être ajustée lorsque les versions, le SGBD exact et l'environnement d'hébergement auront été confirmés.
