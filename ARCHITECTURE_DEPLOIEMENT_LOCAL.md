# Architecture de déploiement local — Projet E-commerce

> **Statut : proposition technique à valider par l'équipe.**  
> Ce document décrit une cible de déploiement pour une application e-commerce **monolithique exécutée sur un seul ordinateur**. Le dépôt consulté contient actuellement un README de présentation, un document de planification Scrum, un classeur de backlog, un fichier HTML lié aux produits et une image de design. La stack technique de l'application (langage, framework et SGBD) n'est pas encore confirmée dans les fichiers consultés. Les choix ci-dessous sont donc volontairement indépendants de la technologie ; ils devront être précisés avec le MainDev avant l'installation.

## 1. Objectif et périmètre

Mettre à disposition de l'équipe une instance locale de l'application pour le développement intégré, les démonstrations et les tests fonctionnels.

- Un seul ordinateur héberge l'application et sa base de données.
- L'application reste **monolithique** : interface, logique métier et accès aux données sont livrés et exécutés comme une seule application.
- Un navigateur Web sert de client.
- L'accès peut être limité à l'ordinateur hôte (`localhost`) ou ouvert au réseau local (LAN) pour les tests de l'équipe.
- Aucun hébergement Internet, cluster, microservice ou infrastructure cloud n'est prévu dans cette première version.

Cette architecture convient à un projet universitaire et à une démonstration locale. Elle n'est pas une architecture de production publique.

## 2. Architecture logique cible

```text
                  UTILISATEUR(S)
             Navigateur Web (Chrome/Firefox)
                         |
             http://localhost ou IP-LAN
                         |
              Pare-feu de l'ordinateur
                         |
                 SERVEUR WEB / HTTP
              (Apache, Nginx ou serveur
               intégré au framework)
                         |
             APPLICATION E-COMMERCE
                    MONOLITHE
          +--------------------------------+
          | Interface / vues               |
          | Authentification et sessions  |
          | Catalogue et catégories       |
          | Panier et commandes           |
          | Gestion / administration      |
          | Accès aux données              |
          +--------------------------------+
                         |
                Connexion locale privée
                         |
                  BASE DE DONNÉES
                (MySQL/MariaDB ou autre
                 SGBD validé par MainDev)
                         |
            +------------+-------------+
            |                          |
       Fichiers médias             Sauvegardes
       (images produits)           (DB + médias)
```

**Flux d'une requête :** le navigateur envoie une requête HTTP au serveur local ; le serveur la transmet à l'application monolithique ; l'application applique les règles métier et lit/écrit les données dans la base ; la réponse est renvoyée au navigateur.

## 3. Composants de l'infrastructure

| Composant | Responsabilité | Décision |
|---|---|---|
| Ordinateur hôte | Exécuter tous les services | Un seul PC, Windows ou Linux selon l'environnement disponible |
| Navigateur | Accéder à l'interface | Chrome ou Firefox |
| Serveur HTTP | Recevoir les requêtes et servir l'application | À choisir selon la stack du MainDev (Apache/Nginx/serveur du framework) |
| Runtime / backend | Exécuter le monolithe | Langage et version à confirmer |
| SGBD | Persister utilisateurs, produits, commandes, etc. | MySQL/MariaDB proposé si compatible avec le modèle de données ; à confirmer |
| Git | Récupérer le code validé | Git + dépôt GitHub de l'équipe |
| Stockage | Conserver les images et fichiers téléversés | Répertoire local hors du code exécutable si possible |
| Sauvegarde | Restaurer l'état du projet | Export de la base + copie des médias |

### Exemple de stack possible (non encore confirmée)

Si le MainDev confirme **PHP + MySQL**, XAMPP peut simplifier l'installation locale (Apache, PHP et MySQL/MariaDB selon la version du paquet). Si le projet utilise Node.js, Java, Python ou un autre environnement, il faudra installer le runtime et le serveur adaptés. **Ne pas installer ou figer XAMPP avant la validation de la stack par le MainDev.**

## 4. Modes d'accès

### Mode A — même ordinateur

- Adresse : `http://localhost` ou `http://127.0.0.1`.
- Seul l'utilisateur du PC accède à l'application.
- C'est le mode recommandé pour le premier démarrage et le dépannage.

### Mode B — réseau local de l'équipe

- Le PC hôte et les postes des équipiers doivent être sur le même réseau Wi-Fi ou Ethernet.
- Les équipiers utilisent l'adresse IP locale du PC hôte, par exemple `http://192.168.1.10` (exemple fictif à remplacer par l'adresse réelle).
- Le serveur HTTP écoute sur l'interface réseau appropriée.
- Le pare-feu autorise uniquement le port HTTP/HTTPS choisi depuis le réseau privé.
- Le port du SGBD ne doit pas être exposé aux postes clients ; seule l'application sur le PC hôte se connecte à la base.

Ne pas ouvrir l'application à Internet par redirection de ports sur le routeur.

## 5. Ports et règles réseau

Les ports ci-dessous sont des valeurs courantes, pas une configuration déjà présente dans le dépôt.

| Service | Port courant | Exposition recommandée |
|---|---:|---|
| HTTP | 80 ou port du framework | Localhost ; LAN seulement si nécessaire |
| HTTPS | 443 | À configurer si HTTPS est requis sur le LAN |
| MySQL/MariaDB | 3306 | Localhost uniquement |
| PostgreSQL (si retenu) | 5432 | Localhost uniquement |

Règles :
1. N'exposer que le port HTTP/HTTPS nécessaire.
2. Lier la base de données à `127.0.0.1` lorsque possible.
3. Ne pas autoriser les connexions entrantes vers le SGBD depuis le LAN.
4. Ne pas désactiver entièrement le pare-feu.
5. Ne pas utiliser cette configuration locale comme configuration de production publique.

## 6. Organisation recommandée du projet sur le PC hôte

La structure réelle devra suivre celle du code livré par le MainDev. Exemple d'organisation de déploiement :

```text
ecommerce-deploy/
├── app/                 # code de l'application monolithique
├── config/              # configuration non secrète / modèles
├── database/
│   ├── schema.sql       # structure fournie par MainDev
│   └── seed.sql         # données de démonstration, si fournies
├── storage/
│   └── uploads/         # images et fichiers ajoutés par l'application
├── backups/
│   ├── database/        # exports SQL
│   └── uploads/         # copies des médias
└── docs/                # procédures d'installation et d'exploitation
```

Ne pas déplacer ou renommer les répertoires internes de l'application sans accord du MainDev. Les secrets (mots de passe, clés, identifiants) doivent être stockés dans un fichier local non versionné ou dans le mécanisme de configuration prévu par le framework.

## 7. Procédure de déploiement

### Pré-requis à obtenir du MainDev

Avant le déploiement, demander et valider :
- langage, framework et versions exactes ;
- système de base de données et version ;
- commande de démarrage ou configuration du serveur Web ;
- fichier modèle de configuration (sans secret) ;
- script de création/migration de la base (`schema.sql`, migrations, etc.) ;
- éventuelles dépendances et commande d'installation ;
- dossier où l'application stocke les images et téléversements ;
- compte de démonstration ou procédure de création d'un compte administrateur.

### Étapes d'installation

1. **Préparer le PC** : mettre le système à jour, vérifier l'espace disque, créer un compte utilisateur dédié si possible et synchroniser l'heure.
2. **Installer Git** et cloner le dépôt :
   ```bash
   git clone https://github.com/franthesatshim/Projet-E_commerce.git
   cd Projet-E_commerce
   ```
3. **Récupérer une version intégrable** : utiliser la branche ou le commit validé par le MainDev/Scrum Master, plutôt qu'une branche de travail incomplète.
4. **Installer le runtime et les dépendances** selon le guide fourni par le MainDev.
5. **Installer et démarrer le SGBD** retenu, puis créer une base et un utilisateur applicatif avec des droits limités.
6. **Configurer l'application** avec l'adresse locale de la base, le nom de la base et les identifiants locaux. Ne jamais committer ces secrets.
7. **Initialiser la base** avec les migrations ou le script SQL fourni par le MainDev.
8. **Configurer les médias** et vérifier que le processus applicatif peut lire/écrire dans le dossier prévu.
9. **Démarrer le serveur** selon la commande ou le service correspondant à la stack.
10. **Tester** depuis le navigateur sur `localhost`, puis depuis un autre poste si le mode LAN est nécessaire.
11. **Documenter** les versions installées, les ports, la procédure de démarrage/arrêt et les résultats de test.

Les commandes exactes ne sont pas indiquées ici, car elles dépendent de la stack qui n'est pas encore confirmée.

## 8. Sécurité minimale

- Créer un compte SGBD dédié à l'application ; ne pas utiliser `root` dans la configuration applicative.
- Utiliser des mots de passe robustes et ne jamais les placer dans Git.
- Ajouter les fichiers de configuration secrets, exports contenant des données personnelles et sauvegardes au `.gitignore`.
- Restreindre les permissions d'accès au dossier des téléversements ; empêcher l'exécution de scripts dans ce dossier.
- Limiter les ports ouverts au strict nécessaire.
- Ne pas exposer le panneau d'administration du SGBD sur le réseau.
- Utiliser uniquement des données fictives pour les démonstrations.
- Mettre à jour le système, le runtime, le framework et le SGBD.
- Pour tout accès LAN, utiliser un réseau privé de confiance ; HTTPS est à prévoir si des identifiants réels circulent.

## 9. Sauvegarde et restauration

Pendant le développement :
- faire un export de la base après chaque évolution importante du schéma ou avant une démonstration ;
- sauvegarder les images/fichiers téléversés en même temps que la base ;
- conserver au moins une copie sur un autre support (clé USB ou disque externe) ;
- tester périodiquement la restauration sur une base de test.

Une copie située uniquement sur le même disque que l'application ne protège pas contre la panne de ce disque. Ne pas versionner les sauvegardes contenant des données privées.

## 10. Tests de validation du déploiement

| ID | Test | Résultat attendu |
|---|---|---|
| INF-01 | Démarrer le PC et les services | Les services requis démarrent sans erreur |
| INF-02 | Ouvrir l'adresse locale | La page d'accueil s'affiche |
| INF-03 | Connexion application → SGBD | La connexion réussit |
| INF-04 | Consulter le catalogue | Les produits de démonstration s'affichent |
| INF-05 | Tester les fonctions livrées (panier/commande/compte) | Les parcours prévus par le backlog fonctionnent |
| INF-06 | Accès depuis un poste LAN, si activé | L'application est accessible depuis le réseau privé |
| INF-07 | Vérifier l'exposition du SGBD | Le port du SGBD n'est pas accessible depuis un poste client |
| INF-08 | Sauvegarder puis restaurer | Les données et médias de test sont récupérables |
| INF-09 | Redémarrer l'ordinateur | La procédure de redémarrage permet de relancer l'application |

Noter pour chaque test : date, version/commit testé, résultat (OK/Échec), problème rencontré et personne ayant vérifié.

## 11. Responsabilités de l'équipe

| Rôle | Contribution au déploiement |
|---|---|
| Scrum Master / coordination | Planifier la livraison intégrable, suivre les blocages et valider la démonstration |
| MainDev | Fournir le code exécutable, les dépendances, la configuration type, le schéma/migrations SQL et la procédure de lancement |
| Designer | Fournir les ressources graphiques optimisées et vérifier le rendu de l'interface sur le navigateur |
| SysAdmin | Préparer le PC, installer et configurer les services, gérer les ports/permissions, déployer, sauvegarder et rédiger les procédures |

## 12. Points à valider avant de figer l'architecture

- [ ] Langage et framework confirmés par le MainDev.
- [ ] SGBD et version confirmés.
- [ ] Système d'exploitation du PC hôte connu.
- [ ] Accès uniquement sur le PC ou également sur le LAN décidé par l'équipe.
- [ ] Schéma SQL / migrations disponibles.
- [ ] Procédure de démarrage reproductible.
- [ ] Dossier de stockage des images et téléversements confirmé.
- [ ] Scénarios de test validés avec le backlog.

## 13. Décision proposée

Adopter pour le projet une **architecture monolithique locale à un seul ordinateur**, avec un serveur HTTP, un seul processus/application backend et une base de données locale. Commencer par un accès `localhost`, puis activer l'accès LAN uniquement si la démonstration collective l'exige.

Le choix définitif des logiciels (par exemple XAMPP si PHP/MySQL est confirmé) sera fait après validation de la stack par le MainDev. Cette décision évite de déployer une infrastructure incompatible avec le code réel.
