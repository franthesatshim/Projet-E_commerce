# Compte rendu de travail — Projet E-commerce
**Date : vendredi 6 novembre 2026**  
**Dépôt GitHub :** https://github.com/franthesatshim/Projet-E_commerce  
**Branche consultée :** `main`  
**Rédigé à partir :** de l'historique GitHub visible et des échanges de travail de l'équipe dans ChatGPT.

---

## 1. Objectif de la séance

La séance a principalement porté sur la mise en place du travail collaboratif autour du projet e-commerce, le partage des ressources sur GitHub, la clarification des responsabilités de l'équipe et la préparation d'une première architecture de déploiement local.

Le dépôt GitHub sert d'espace commun pour centraliser les documents du projet et permettre aux membres de consulter les mêmes ressources.

## 2. Organisation de l'équipe

Les responsabilités évoquées pour le travail sont réparties entre quatre rôles :

| Rôle | Responsabilité principale |
|---|---|
| Scrum Master / Coordination | Organiser le travail, suivre l'avancement, coordonner les membres et les livrables. |
| MainDev | Développer l'application, définir la stack technique et fournir les éléments nécessaires à son exécution. |
| Designer | Concevoir l'interface et préparer les ressources graphiques. |
| SysAdmin | Préparer l'environnement de déploiement, gérer les services, le réseau, les accès, les sauvegardes et la documentation technique. |

**Responsabilité de SysAdmin :** préparer une solution de déploiement cohérente avec l'application réellement développée, sans imposer prématurément une technologie qui n'aurait pas été choisie par le MainDev.

## 3. Travail réalisé sur GitHub

L'historique du dépôt montre plusieurs opérations effectuées le 6 novembre :

- Initialisation du dépôt avec un fichier `README.md`.
- Ajout de documents de projet par téléversement.
- Ajout d'un fichier de conception graphique nommé `DESIGN.jpg`.
- Ajout et retrait de quelques fichiers de test au cours des manipulations du dépôt.
- Suppression de fichiers qui n'étaient pas destinés à rester dans le dépôt.
- Ajout de la documentation d'architecture de déploiement local.

Les fichiers visibles à la racine du dépôt au moment de la vérification sont notamment :

- `README.md` : présentation courte du dépôt.
- `Planning_Developpement_Ecommerce_Scrum (1).pdf` : document de planification Scrum.
- `excel E_Commerce.xlsx` : classeur du projet, notamment utilisé pour le Product Backlog.
- `excel produit.html` : fichier HTML lié aux produits.
- `DESIGN.jpg` : ressource de conception graphique.
- `ARCHITECTURE_DEPLOIEMENT_LOCAL.md` : proposition d'architecture technique.
- Le dossier `docs/` : espace de documentation du projet.

> L'historique GitHub confirme les ajouts, suppressions et commits, mais ne permet pas à lui seul de reconstituer chaque action effectuée oralement ou chaque manipulation locale de chaque membre.

## 4. Clarification de l'architecture de déploiement

En tant que SysAdmin, une proposition a été élaborée pour répondre à la demande de l'équipe : déployer une application e-commerce **monolithique sur un seul ordinateur**.

Le document correspondant a été ajouté au dépôt sous le nom :

`ARCHITECTURE_DEPLOIEMENT_LOCAL.md`

### Principe retenu

Un seul ordinateur héberge les composants nécessaires :

1. Un serveur HTTP (par exemple Apache, Nginx ou le serveur intégré au framework).
2. L'application e-commerce monolithique (interface, authentification, catalogue, panier, commandes et administration selon les fonctionnalités livrées).
3. Une base de données locale.
4. Les fichiers médias, comme les images des produits.
5. Les sauvegardes de la base de données et des médias.

Les utilisateurs accèdent à l'application avec un navigateur Web.

### Schéma logique simplifié

```text
Utilisateur(s)
     |
Navigateur Web
     |
http://localhost ou adresse IP locale
     |
Pare-feu du PC hôte
     |
Serveur HTTP
     |
Application e-commerce monolithique
     |
Base de données locale
     |
Fichiers médias et sauvegardes
```

### Deux modes d'accès envisagés

- **Mode local :** accès depuis le même ordinateur avec `localhost` ou `127.0.0.1`. C'est le mode recommandé pour commencer.
- **Mode réseau local (LAN) :** les autres membres peuvent accéder à l'application depuis leurs appareils connectés au même réseau privé, au moyen de l'adresse IP locale du PC hôte, si l'équipe en a besoin.

L'accès public depuis Internet n'est pas prévu dans cette première architecture.

## 5. Choix techniques : ce qui est décidé et ce qui reste à confirmer

L'architecture est une proposition indépendante de la technologie. La stack réelle du projet n'étant pas confirmée dans les fichiers consultés, les logiciels définitifs ne doivent pas encore être imposés.

Le MainDev doit confirmer :

- le langage de programmation ;
- le framework et sa version ;
- le système de gestion de base de données ;
- les dépendances et commandes de démarrage ;
- le schéma SQL ou les migrations ;
- le répertoire de stockage des images et fichiers envoyés par les utilisateurs.

**Exemple conditionnel :** si l'équipe confirme PHP et MySQL, XAMPP pourrait simplifier l'installation locale. Si elle retient Node.js, Java, Python ou une autre stack, il faudra installer les composants adaptés. XAMPP n'est donc pas présenté comme un choix déjà validé.

## 6. Sécurité et exploitation prévues

La proposition recommande notamment :

- de ne pas exposer le port de la base de données aux autres postes ;
- de n'ouvrir que les ports nécessaires au serveur Web ;
- de conserver les mots de passe et clés dans une configuration locale non versionnée ;
- de ne pas déposer de mots de passe, données privées ou sauvegardes sensibles dans GitHub ;
- de limiter les accès aux fichiers téléversés ;
- de conserver une copie des sauvegardes sur un autre support ;
- de tester régulièrement la restauration des données.

## 7. Procédure générale de déploiement envisagée

Une fois la stack confirmée par le MainDev, le SysAdmin pourra suivre les étapes suivantes :

1. Préparer et mettre à jour l'ordinateur hôte.
2. Installer Git et récupérer une version validée du dépôt.
3. Installer le langage, le runtime et les dépendances nécessaires.
4. Installer et démarrer le système de base de données choisi.
5. Créer la base et un compte applicatif avec des droits limités.
6. Configurer l'application avec les paramètres locaux, sans publier les secrets.
7. Initialiser la base à l'aide des scripts ou migrations fournis.
8. Configurer le stockage des images et fichiers.
9. Démarrer l'application et le serveur HTTP.
10. Tester l'accès depuis `localhost`, puis depuis le LAN si nécessaire.
11. Documenter les versions, ports, procédures de démarrage et résultats des tests.

Les commandes exactes seront ajoutées lorsque la technologie de l'application sera connue.

## 8. Tests de validation prévus

Le document d'architecture propose de vérifier :

- le démarrage correct des services ;
- l'ouverture de la page d'accueil ;
- la connexion entre l'application et la base de données ;
- l'affichage du catalogue ;
- le fonctionnement des parcours livrés (compte, panier et commande) ;
- l'accès depuis un autre poste si le LAN est activé ;
- l'absence d'exposition du port de la base de données ;
- la sauvegarde et la restauration ;
- le redémarrage de l'ordinateur et la reprise du service.

Ces tests sont des **tests à effectuer** lors du déploiement ; leur présence dans le document ne signifie pas qu'ils ont déjà été exécutés.

## 9. État du travail à la fin de la journée

| Élément | État |
|---|---|
| Dépôt GitHub partagé et ressources centralisées | Présent dans le dépôt |
| Documents de planification Scrum | Présents dans le dépôt |
| Ressource de design | Présente dans le dépôt |
| Rôles de l'équipe | Clarifiés dans les échanges |
| Proposition de déploiement monolithique local | Rédigée et ajoutée au dépôt |
| Stack technique définitive | À confirmer par le MainDev |
| Installation sur le PC hôte | À faire après validation de la stack |
| Tests de déploiement | À réaliser |
| Accès LAN | À décider selon le besoin de l'équipe |

## 10. Prochaines actions recommandées

### MainDev
- Confirmer la stack et les versions.
- Fournir le schéma de base de données ou les migrations.
- Expliquer la procédure d'installation et de démarrage.
- Identifier le dossier de stockage des médias.

### Scrum Master / Coordination
- Confirmer la version du code qui servira de base au déploiement.
- Suivre les éléments encore à valider et répartir les prochaines tâches.

### Designer
- Vérifier que les ressources graphiques sont bien accessibles dans le dépôt.
- Préparer les éléments visuels nécessaires à l'intégration.

### SysAdmin
- Adapter le document d'architecture à la stack confirmée.
- Préparer le PC hôte et les services nécessaires.
- Mettre en place les règles de pare-feu, les sauvegardes et la procédure de démarrage.
- Exécuter et consigner les tests de validation.

---

## Conclusion

Le travail du 6 novembre 2026 a permis de consolider l'espace de collaboration GitHub, de rendre disponibles plusieurs ressources du projet, de clarifier les responsabilités de l'équipe et de formaliser une première proposition de déploiement local pour une application monolithique sur un seul ordinateur.

Le principal livrable technique réalisé côté SysAdmin est le fichier `ARCHITECTURE_DEPLOIEMENT_LOCAL.md`. La prochaine étape importante est d'obtenir la confirmation de la stack par le MainDev afin de transformer cette proposition générale en procédure d'installation réellement exécutable.
