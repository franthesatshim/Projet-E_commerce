# Architecture de déploiement local — Projet E-commerce

> **Statut : proposition d'infrastructure pour la démonstration locale.**  
> Cette architecture décrit le déploiement cible demandé à l'équipe. Elle ne confirme pas encore la technologie réellement utilisée par l'application : le MainDev doit valider le langage, le framework, le moteur de base de données et les commandes de lancement avant l'installation définitive.

## 1. Objectif et périmètre

Déployer l'application e-commerce sous forme **monolithique sur un seul ordinateur**. Cet ordinateur héberge l'application et sa base de données. Les membres de l'équipe et les utilisateurs de démonstration y accèdent depuis un navigateur, sur le même ordinateur ou via le réseau local (LAN).

Cette solution convient à un projet académique, aux tests et à une démonstration sur un réseau local. Elle n'est pas une architecture de production exposée directement à Internet.

## 2. Architecture logique

```text
                 UTILISATEURS
          (navigateur sur le PC / LAN)
                       |
                       | HTTP (80) ou HTTPS (443)
                       v
       +--------------------------------------+
       |       ORDINATEUR SERVEUR LOCAL       |
       |                                      |
       |  +--------------------------------+  |
       |  | Serveur Web                    |  |
       |  | Apache / Nginx / serveur adapté|  |
       |  +---------------+----------------+  |
       |                  |                   |
       |  +---------------v----------------+  |
       |  | APPLICATION MONOLITHIQUE       |  |
       |  | Interface + Backend + logique  |  |
       |  | métier + authentification      |  |
       |  +---------------+----------------+  |
       |                  |                   |
       |  +---------------v----------------+  |
       |  | Base de données                |  |
       |  | MySQL / MariaDB / moteur validé|  |
       |  +--------------------------------+  |
       |                                      |
       |  +--------------------------------+  |
       |  | Fichiers téléversés et logs    |  |
       |  +--------------------------------+  |
       |                                      |
       |  +--------------------------------+  |
       |  | Sauvegardes locales            |  |
       |  +--------------------------------+  |
       +--------------------------------------+
```

**Principe :** le navigateur envoie les requêtes au serveur web. Celui-ci transmet les demandes à l'application monolithique. L'application applique les règles métier et lit/écrit les données dans la base. Le navigateur ne se connecte jamais directement à la base de données.

## 3. Composants à prévoir

| Composant | Rôle | Décision à confirmer |
|---|---|---|
| Ordinateur serveur | Héberge tous les composants | PC disponible, alimentation et espace disque |
| Système d'exploitation | Exécute l'environnement | Windows ou Linux selon le PC de démonstration |
| Serveur web | Reçoit les requêtes HTTP/HTTPS | Apache, Nginx ou serveur intégré selon la stack |
| Application | Interface, backend et logique métier dans un même projet | Langage/framework du MainDev |
| Base de données | Stocke utilisateurs, produits, commandes, etc. | MySQL/MariaDB ou autre moteur choisi |
| Stockage | Conserve images et documents envoyés | Dossier persistant hors des fichiers temporaires |
| Sauvegarde | Permet de restaurer les données | Export DB + copie des fichiers téléversés |

### Environnement indicatif

Si le MainDev confirme **PHP + MySQL**, XAMPP (Apache, PHP et MySQL/MariaDB selon la version) peut être une solution simple sur Windows. Si le projet utilise une autre stack, il faudra installer l'environnement correspondant plutôt que d'imposer XAMPP.

## 4. Vue physique et accès réseau

- **Sur le même ordinateur :** ouvrir l'application avec `http://localhost` ou l'adresse configurée.
- **Depuis un autre appareil du même réseau :** utiliser l'adresse IP locale du PC serveur, par exemple `http://192.168.x.x` (remplacer par l'adresse réelle).
- Le PC serveur doit rester allumé et connecté au réseau pendant la démonstration.
- Réserver l'accès entrant au port web nécessaire (80 ou 443) et au réseau local autorisé.
- Le port de la base de données (souvent 3306 pour MySQL) reste accessible uniquement depuis la machine locale ; ne pas l'exposer au LAN ou à Internet sans nécessité et configuration sécurisée.

## 5. Ressources matérielles indicatives

Pour une petite démonstration avec peu d'utilisateurs simultanés :

- Minimum pratique : processeur 2 cœurs, 4 Go de RAM et espace SSD disponible.
- Recommandé : processeur 4 cœurs, 8 Go de RAM et SSD.
- Prévoir de l'espace supplémentaire pour la base de données, les images produits, les journaux et les sauvegardes.

Ces valeurs sont des repères de départ, pas des exigences mesurées de l'application.

## 6. Organisation des fichiers proposée

À adapter à la structure réellement produite par le MainDev. Ne pas déplacer ou renommer les dossiers du code sans son accord.

```text
Projet-E_commerce/
├── README.md
├── docs/
│   └── ARCHITECTURE_DEPLOIEMENT_LOCAL.md
├── application/              # code réel de l'application (à confirmer)
├── database/                 # scripts SQL / migrations (si utilisés)
├── storage/                  # fichiers persistants (si nécessaires)
└── .env.example              # modèle sans secrets, si la stack utilise .env
```

Les dossiers `application/`, `database/`, `storage/` et le fichier `.env.example` sont une **structure cible illustrative** : ne les créer que lorsque leur contenu et leur utilité auront été confirmés avec le MainDev. Les fichiers réels de l'application doivent rester conformes au dépôt.

## 7. Étapes de déploiement

1. **Valider la stack avec le MainDev :** langage, framework, version requise, base de données, dépendances et commande de démarrage.
2. **Préparer le PC :** vérifier le système, les ressources, l'espace disque et les droits d'installation.
3. **Récupérer le projet :** cloner le dépôt GitHub sur le PC serveur et choisir une version/branche stable pour la démonstration.
4. **Installer les dépendances :** suivre les instructions officielles du framework et celles ajoutées par le MainDev au dépôt.
5. **Configurer l'environnement :** renseigner les paramètres locaux dans un fichier de configuration non versionné. Ne jamais publier mots de passe, clés ou secrets sur GitHub.
6. **Préparer la base :** créer la base et un utilisateur applicatif dédié ; exécuter les migrations ou le script SQL fourni par le MainDev.
7. **Configurer le serveur web :** faire pointer le serveur vers le point d'entrée public de l'application ; interdire l'accès web aux fichiers de configuration et aux sauvegardes.
8. **Démarrer les services :** démarrer le serveur web et la base, puis lancer l'application selon la stack.
9. **Tester :** parcourir les scénarios fonctionnels et les contrôles de sécurité de la section 9.
10. **Documenter :** noter les versions installées, les commandes réellement utilisées, l'adresse d'accès et la procédure de redémarrage.

## 8. Sécurité et sauvegardes

- Utiliser des mots de passe forts pour les comptes administrateur et base de données.
- Créer un compte de base de données réservé à l'application, avec les seuls droits nécessaires ; éviter le compte administrateur/root dans la configuration applicative.
- Ne pas publier les fichiers de configuration contenant des secrets ; ajouter les fichiers locaux sensibles à `.gitignore`.
- Ne pas exposer phpMyAdmin ou l'interface d'administration de la base au réseau sans contrôle d'accès.
- Autoriser uniquement les ports nécessaires dans le pare-feu.
- Conserver les sauvegardes hors du dossier servi publiquement par le serveur web.
- Avant chaque démonstration importante, effectuer une sauvegarde de la base et des fichiers téléversés.
- Tester au moins une restauration : une sauvegarde non testée n'est pas une garantie de récupération.

Pour un usage régulier, définir une fréquence (par exemple quotidienne pendant la période de développement) et conserver au moins une copie sur un autre support. Les sauvegardes présentes uniquement sur le même disque ne protègent pas contre la panne de ce disque.

## 9. Plan de tests de validation

| Test | Résultat attendu |
|---|---|
| Démarrage du PC serveur et des services | Serveur web et base de données démarrent sans erreur |
| Accès depuis le PC serveur | La page d'accueil s'affiche |
| Accès depuis un appareil LAN autorisé | L'application est accessible via l'IP locale |
| Connexion à la base | L'application lit et écrit les données attendues |
| Catalogue produits | Les produits s'affichent correctement |
| Panier | Ajout, modification et suppression d'un article fonctionnent |
| Commande | Le scénario de commande prévu par le backlog fonctionne |
| Authentification | Les rôles et accès sont contrôlés côté serveur |
| Fichiers téléversés | Les images/fichiers restent disponibles après redémarrage |
| Sauvegarde/restauration | Les données peuvent être restaurées |
| Redémarrage | L'application revient après redémarrage des services |

Les scénarios doivent être ajustés aux fonctionnalités effectivement prévues dans le Product Backlog.

## 10. Répartition des responsabilités

| Rôle | Responsabilités liées au déploiement |
|---|---|
| Scrum Master / coordination | Planifier les validations, suivre les blocages et faire confirmer les livrables |
| MainDev | Confirmer la stack, fournir le code exécutable, les dépendances, la configuration exemple et les scripts/migrations de base |
| Designer | Vérifier l'affichage et l'utilisabilité sur les écrans ciblés |
| SysAdmin | Préparer le PC, installer et configurer l'environnement, gérer le réseau, les accès, les sauvegardes et exécuter les tests de déploiement |

## 11. Informations à obtenir avant le déploiement réel

Le SysAdmin doit demander au MainDev de confirmer :

1. Quel langage et quel framework sont utilisés ?
2. Quel moteur et quelle version de base de données ?
3. Existe-t-il un fichier de dépendances (par exemple `composer.json`, `package.json`, `requirements.txt`) ?
4. Existe-t-il un script SQL, des migrations ou des données de démonstration ?
5. Quelle est la commande exacte pour lancer l'application ?
6. Quel est le dossier public / point d'entrée du serveur web ?
7. Quels paramètres doivent être définis dans le fichier de configuration ?
8. Quels comptes de test et quels rôles sont nécessaires ?

## 12. Critères de fin

Le déploiement local est considéré prêt lorsque l'application démarre sur le PC retenu, est accessible aux utilisateurs autorisés, utilise sa base de données, passe les scénarios fonctionnels prioritaires, résiste à un redémarrage et dispose d'une sauvegarde restaurable.

---
**Responsable de cette proposition :** SysAdmin  
**Document à réviser :** après confirmation de la stack et de la structure réelle du code par le MainDev.
