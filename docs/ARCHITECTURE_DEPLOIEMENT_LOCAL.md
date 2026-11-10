# Architecture globale de déploiement local — Site E-commerce

## 1. Objectif du déploiement

L'équipe prévoit de développer un site e-commerce avec **HTML, CSS et JavaScript**, puis de l'héberger localement sur **un seul ordinateur à l'aide de XAMPP**.

L'ordinateur choisi jouera le rôle de serveur local. Il hébergera les fichiers du site et, si l'application utilise une base de données, le service de base de données fourni par XAMPP.

Le site sera consulté avec un navigateur web. Pour une démonstration en réseau local, les autres appareils pourront accéder au site via l'adresse IP locale de cet ordinateur.

> **Point technique important :** HTML, CSS et JavaScript exécutés dans le navigateur suffisent à afficher un site, mais ne constituent pas à eux seuls un backend sécurisé pour gérer les comptes, les commandes et les données persistantes. Pour une véritable application e-commerce connectée à MySQL/MariaDB, il faudra un backend côté serveur (par exemple PHP avec XAMPP). Le navigateur ne doit jamais se connecter directement à MySQL. Cette architecture prévoit donc le serveur et la base de données, mais le MainDev doit confirmer si PHP sera utilisé et définir les traitements serveur.

## 2. Architecture globale

~~~text
                  UTILISATEURS
             (Chrome / Firefox / Edge)
                         |
                         | HTTP
                         | http://localhost
                         | ou http://IP-LOCALE
                         v
        +----------------------------------------+
        |           ORDINATEUR SERVEUR           |
        |                                        |
        |                 XAMPP                  |
        |                                        |
        |  +----------------------------------+  |
        |  | APACHE — Serveur Web             |  |
        |  | Port HTTP : 80                   |  |
        |  | (HTTPS 443 si configuré)         |  |
        |  +----------------+-----------------+  |
        |                   |                    |
        |                   v                    |
        |  +----------------------------------+  |
        |  | SITE E-COMMERCE                  |  |
        |  | HTML : structure des pages       |  |
        |  | CSS : présentation et design     |  |
        |  | JavaScript : interactions client |  |
        |  | PHP : backend éventuel à valider |  |
        |  +----------------+-----------------+  |
        |                   |                    |
        |           Requêtes serveur             |
        |           si backend PHP               |
        |                   |                    |
        |                   v                    |
        |  +----------------------------------+  |
        |  | MARIADB / MYSQL                  |  |
        |  | Port par défaut : 3306           |  |
        |  | Données : utilisateurs, produits,| |
        |  | catégories, paniers, commandes   |  |
        |  +----------------------------------+  |
        |                                        |
        |  +----------------------------------+  |
        |  | phpMyAdmin                       |  |
        |  | Administration de la base        |  |
        |  | (usage local et protégé)         |  |
        |  +----------------------------------+  |
        |                                        |
        |  +----------------------------------+  |
        |  | Fichiers du site / images / logs |  |
        |  +----------------------------------+  |
        |                                        |
        |  +----------------------------------+  |
        |  | Sauvegardes de la base et fichiers| |
        |  +----------------------------------+  |
        +----------------------------------------+
~~~

**Flux de fonctionnement :**

1. L'utilisateur ouvre le navigateur et saisit l'adresse du site.
2. Apache, démarré depuis XAMPP, reçoit la requête HTTP.
3. Apache sert les fichiers HTML, CSS et JavaScript.
4. JavaScript gère les interactions dans le navigateur.
5. Si une opération nécessite un traitement serveur (connexion, création de commande, enregistrement d'un produit), le navigateur envoie une requête au backend PHP, si celui-ci est retenu.
6. Le backend PHP communique avec MySQL/MariaDB et renvoie une réponse au navigateur.
7. Le navigateur affiche le résultat à l'utilisateur.

## 3. Composants de l'infrastructure

| Composant | Technologie prévue | Fonction |
|---|---|---|
| Ordinateur | Un PC Windows ou Linux compatible | Héberger le site et les services |
| Serveur web | Apache (XAMPP) | Recevoir les requêtes et servir le site |
| Structure des pages | HTML5 | Définir le contenu et les éléments |
| Présentation | CSS3 | Définir les styles et la mise en page |
| Interactions | JavaScript | Gérer les interactions côté navigateur |
| Backend | PHP si validé par le MainDev | Traiter les opérations côté serveur |
| Base de données | MariaDB/MySQL fourni avec XAMPP | Conserver les données du site |
| Administration BD | phpMyAdmin | Créer, consulter et sauvegarder la base |
| Navigateur | Chrome, Firefox ou Edge | Accéder au site |
| Versionnement | Git et GitHub | Partager et suivre le code source |

XAMPP regroupe notamment Apache, PHP, MariaDB et phpMyAdmin selon la version distribuée. Le panneau de contrôle XAMPP permet de démarrer et d'arrêter les services nécessaires.

## 4. Organisation des fichiers sur l'ordinateur serveur

Le dossier du site sera placé dans le répertoire web d'Apache, généralement :

- Windows : **C:\xampp\htdocs\Projet-E_commerce\**
- Linux : le répertoire **htdocs** de l'installation XAMPP, selon le chemin choisi.

Exemple d'organisation cible, à adapter aux fichiers réellement créés par le MainDev :

~~~text
C:\xampp\
└── htdocs\
    └── Projet-E_commerce\
        ├── index.html
        ├── pages\
        │   ├── produits.html
        │   ├── panier.html
        │   └── connexion.html
        ├── css\
        │   └── style.css
        ├── js\
        │   └── script.js
        ├── images\
        │   └── produits\
        ├── php\                 (si backend PHP retenu)
        │   ├── config.php
        │   └── ...
        └── database\            (scripts SQL de préparation)
            └── schema.sql
~~~

Cette arborescence est un exemple de classement, pas une obligation de renommer les fichiers déjà développés. Les secrets et mots de passe ne doivent pas être enregistrés dans un fichier publié sur GitHub.

## 5. Configuration réseau et adresses d'accès

### Accès depuis l'ordinateur serveur

- Site : **http://localhost/Projet-E_commerce/**
- phpMyAdmin : **http://localhost/phpmyadmin/** (administration locale uniquement)

### Accès depuis les autres ordinateurs du même réseau

1. Connecter le PC serveur et les appareils clients au même réseau local (Wi-Fi ou câble).
2. Trouver l'adresse IPv4 locale du PC serveur avec la commande **ipconfig** sous Windows.
3. Depuis un autre appareil, ouvrir **http://ADRESSE-IP/Projet-E_commerce/**, en remplaçant l'exemple par l'adresse IPv4 réelle.
4. Autoriser Apache dans le pare-feu Windows uniquement pour le réseau privé/local utilisé.

Le PC serveur doit rester allumé et Apache doit être démarré pour que les autres appareils accèdent au site. Une adresse IP locale peut changer ; une réservation DHCP ou une adresse fixe peut être prévue si nécessaire.

### Ports utilisés

| Service | Port habituel | Règle |
|---|---:|---|
| Apache HTTP | 80 | Autoriser uniquement les accès nécessaires |
| Apache HTTPS | 443 | À utiliser si HTTPS est configuré |
| MySQL/MariaDB | 3306 | Garder accessible uniquement depuis le PC serveur |
| phpMyAdmin | Via Apache | Ne pas rendre son accès public |

Ne pas exposer directement le port 3306 à Internet ni ouvrir des ports inutiles.

## 6. Installation et mise en service de XAMPP

1. Télécharger XAMPP depuis le site officiel Apache Friends : https://www.apachefriends.org/
2. Installer XAMPP sur le PC choisi.
3. Ouvrir le XAMPP Control Panel.
4. Démarrer **Apache**.
5. Démarrer **MySQL** (le service peut être nommé MySQL dans le panneau même si la distribution utilise MariaDB).
6. Vérifier Apache avec **http://localhost/**.
7. Vérifier phpMyAdmin avec **http://localhost/phpmyadmin/**.
8. Copier ou cloner le projet dans **C:\xampp\htdocs\Projet-E_commerce\**.
9. Ouvrir **http://localhost/Projet-E_commerce/** dans le navigateur.

Si Apache ne démarre pas, vérifier notamment si un autre programme utilise déjà le port 80 ou 443. Ne pas modifier les ports sans documenter le changement et mettre à jour l'adresse d'accès.

**Cas d'un site uniquement HTML/CSS/JavaScript :** Apache suffit pour servir les fichiers statiques. MySQL et phpMyAdmin ne sont pas nécessaires tant qu'aucun backend ne les utilise. Les données conservées uniquement dans le navigateur (par exemple localStorage) ne remplacent pas une base de données partagée et sécurisée.

## 7. Préparation de la base de données (si MySQL/MariaDB est utilisé)

1. Ouvrir phpMyAdmin depuis le PC serveur.
2. Créer une base de données dédiée au projet, avec un nom choisi par l'équipe (par exemple **ecommerce_db**).
3. Importer le fichier SQL ou exécuter les scripts fournis par le MainDev.
4. Créer un compte applicatif dédié avec des droits limités à cette base.
5. Configurer le backend PHP pour se connecter à la base avec ces paramètres.
6. Tester une lecture et une écriture depuis l'application.

Les tables exactes doivent provenir de la conception de la base et du code du MainDev. Elles ne sont pas inventées dans ce document.

## 8. Sécurité minimale

- Utiliser cette installation pour le développement et la démonstration locale, pas comme serveur public de production.
- Ne jamais connecter le JavaScript du navigateur directement à MySQL.
- Si PHP est utilisé, valider les données côté serveur et utiliser des requêtes préparées pour les accès SQL.
- Ne pas stocker les mots de passe en clair ; utiliser un mécanisme de hachage adapté côté serveur.
- Ne pas publier les identifiants de base de données dans le dépôt GitHub.
- Limiter phpMyAdmin à l'administration locale et protéger les comptes.
- Garder MySQL/MariaDB accessible uniquement depuis la machine locale.
- N'autoriser Apache sur le réseau que si le partage LAN est nécessaire.
- Ne pas utiliser les comptes et mots de passe par défaut pour une démonstration partagée.
- Maintenir une copie de sauvegarde sur un autre support.

## 9. Sauvegarde et restauration

Avant une démonstration ou une modification importante :

1. Exporter la base de données depuis phpMyAdmin (si elle existe).
2. Copier les fichiers du projet, notamment les images et autres fichiers ajoutés.
3. Stocker les sauvegardes dans un dossier qui n'est pas accessible par le serveur web.
4. Conserver une copie sur un support distinct du disque du PC serveur.
5. Tester périodiquement la restauration de la base et des fichiers.

Exemple de classement hors du dossier web :

~~~text
D:\Sauvegardes_Ecommerce\
├── base_ecommerce_YYYY-MM-DD.sql
└── fichiers_site_YYYY-MM-DD.zip
~~~

Le chemin ci-dessus est illustratif : utiliser un support réellement disponible.

## 10. Tests de validation du SysAdmin

| Vérification | Résultat attendu |
|---|---|
| XAMPP démarre | Apache et, si nécessaire, MySQL sont actifs |
| Page d'accueil | Le site s'ouvre depuis localhost |
| Fichiers CSS et JavaScript | Le style et les interactions se chargent correctement |
| Navigation | Les pages et liens fonctionnent |
| Accès LAN | Un appareil autorisé ouvre le site via l'IP locale |
| Base de données (si utilisée) | La connexion fonctionne et les données attendues sont présentes |
| Opérations e-commerce | Les scénarios produits, panier et commandes prévus sont testés |
| Sécurité | La base n'est pas accessible directement depuis le navigateur ou le LAN |
| Sauvegarde | L'export de la base et la copie des fichiers sont disponibles |
| Restauration | Une sauvegarde peut être restaurée |
| Redémarrage | Le site redevient accessible après redémarrage des services |

## 11. Répartition des responsabilités

| Rôle | Responsabilités |
|---|---|
| Scrum Master / coordination | Organiser les validations, suivre les tâches et les blocages |
| MainDev | Développer le site, préciser les besoins backend, fournir les scripts SQL et les instructions de lancement |
| Designer | Concevoir et vérifier l'interface, la cohérence visuelle et l'affichage responsive |
| SysAdmin | Installer XAMPP, configurer Apache et MySQL si requis, placer le projet dans htdocs, configurer le réseau, les sauvegardes et réaliser les tests de déploiement |

## 12. Points à confirmer par l'équipe

Avant de considérer l'architecture comme définitive, le MainDev doit confirmer :

1. Le site sera-t-il uniquement statique (HTML/CSS/JavaScript) ou utilisera-t-il PHP pour le backend ?
2. MySQL/MariaDB sera-t-il utilisé pour enregistrer les utilisateurs, produits et commandes ?
3. Quel fichier sera la page d'accueil : **index.html** ou **index.php** ?
4. Quel script SQL créera la base et ses tables, si une base est nécessaire ?
5. Quelles fonctionnalités doivent être opérationnelles pendant la démonstration ?
6. Le site sera-t-il consulté uniquement sur le PC serveur ou aussi depuis les ordinateurs du réseau local ?

## 13. Critères de réussite

Le déploiement local sera validé lorsque :

- le projet est placé dans le répertoire web de XAMPP ;
- Apache sert correctement le site ;
- les pages, styles et scripts fonctionnent ;
- MySQL/MariaDB est opérationnel uniquement si le projet l'utilise ;
- les fonctionnalités nécessitant un backend sont traitées côté serveur ;
- les utilisateurs autorisés peuvent accéder au site depuis le PC ou le réseau local prévu ;
- les sauvegardes et la procédure de restauration sont documentées.

---

**Responsable du document :** SysAdmin  
**Environnement visé :** HTML, CSS, JavaScript et XAMPP  
**Nature du déploiement :** local, monolithique, un seul ordinateur  
**Mise à jour :** à préciser après validation du backend et de la base de données par le MainDev.
