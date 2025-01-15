# API HackR

Bienvenue sur l'API HackR. Ce projet est une API permettant d'effectuer diverses actions liées à l'authentification, au hacking, aux logs et aux règles d'accès. Vous trouverez ci-dessous les instructions pour utiliser cette API.

## Accéder à l'API

Rendez-vous à l'adresse suivante pour commencer à utiliser l'API :  
[https://robinson.berthet.angers.mds-project.fr](https://robinson.berthet.angers.mds-project.fr)

## Créer un utilisateur et s'authentifier

Pour tester l'API, il vous faudra suivre les étapes suivantes :

1. **Créer un utilisateur** ou **vous connecter** pour accéder aux routes de hacking.
2. **Utiliser les différentes fonctionnalités** disponibles (voir la section Fonctionnalités ci-dessous).

## Fonctionnalités

Voici une liste des fonctionnalités disponibles dans l'API. Ce tableau est utile pour obtenir les logs par fonctionnalité (ID) en tant qu'administrateur :

| ID  | Nom de la fonctionnalité              | Description                                                                                                                                                                                                                 |
|-----|---------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|  1  | Inscription                          | Inscription d'un utilisateur.                                                                                                                                                                                                |
|  2  | Connexion                            | Connexion d'un utilisateur.                                                                                                                                                                                                  |
|  3  | Me                                   | Permet d'obtenir les informations sur l'utilisateur connecté.                                                                                                                                                              |
|  4  | Email Checker                        | Vérification de l'email saisi.                                                                                                                                                                                               |
|  5  | Email Spam                           | Envoi d'un nombre d'emails spam à une personne définie.                                                                                                                                                                      |
|  6  | Déconnexion                          | Déconnexion d'un utilisateur.                                                                                                                                                                                                |
|  7  | Password Checker                     | Vérifie si un mot de passe est présent dans la liste des mots de passe les plus courants (10k-most-common.txt).                                                                                                           |
|  8  | Password Generator                   | Génération d'un mot de passe fort.                                                                                                                                                                                           |
|  9  | Récupérateur de Domaine              | Récupération des domaines et sous-domaines d'un nom de domaine.                                                                                                                                                             |
| 10  | Générateur d'Identité Fictive        | Génère une identité fictive grâce à la librairie Faker PHP.                                                                                                                                                                 |
| 11  | DDoS                                 | Lance une attaque en déni de service (DDoS).                                                                                                                                                                                 |
| 12  | Phishing                             | Phishing en récupérant une page de connexion existante.                                                                                                                                                                      |
| 13  | Get Data                             | Récupère les données du phishing.                                                                                                                                                                                             |
| 14  | Crawler d'Information                | Récupère des informations sur une personne donnée.                                                                                                                                                                           |
| 15  | Générateur d'une Personne Aléatoire  | Génère une image d'une personne qui n'existe pas (IA).                                                                                                                                                                       |

## Consulter les logs ou modifier les droits

Pour consulter les logs ou modifier les droits pour les méthodes de hacking, il faut être administrateur.

L'administrateur de l'API est le suivant :

- **Login**: robinson.berthet@gmail.com
- **Mot de passe**: abcd1234

## Tester l'API avec Postman

Pour tester l'API dans Postman, importez la collection qui se trouve à la racine du projet :  
`/API-HackR.postman_collection.json`

## Pré-requis pour installer l'api sur sa machine

Avant de commencer à utiliser l'API, assurez-vous que les conditions suivantes sont remplies :

- Avoir PHP et Composer installés sur votre machine et un serveur web (apache, nginx ou xampp pour windows).
- Avoir une base de données vide nommée `hackr` en MySQL.

## installer l'API en local

### Instructions

1. Clonez le projet depuis GitHub :
    `git clone https://github.com/RobinsonBerthet/TP_API_MBA1.git`

    `cd TP_API_MBA1`

2. Créez un fichier `.env` à la racine du projet et ajoutez-y les variables secrètes nécessaires.

3. Installez les dépendances :
    `composer install`

4. Générez la clé de l'application :
    `php artisan key:generate`

5. Exécutez les migrations et seeders :
    `php artisan migrate --seed`

6. Si vous testez le projet localement, lancez le serveur avec la commande suivante :
    `php artisan serve`
