# API HackR

Bienvenue sur l'API HackR. Ce projet est une API qui permet d'effectuer diverses actions liées à l'authentification, au hacking, aux logs et aux règles d'accès. Vous trouverez ci-dessous les instructions pour installer, configurer et utiliser cette API.

## Table des matières

- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Documentation de l'API](#documentation-de-lapi)
- [Tests](#tests)
- [Contribuer](#contribuer)
- [Licence](#licence)

## Installation

1. Clonez le dépôt :

    ```sh
    git clone https://github.com/votre-utilisateur/votre-repo.git
    cd votre-repo
    ```

2. Installez les dépendances via Composer :

    ```sh
    composer install
    ```

3. Installez les dépendances JavaScript via npm :

    ```sh
    npm install
    ```

4. Générez les fichiers front-end :

    ```sh
    npm run dev
    ```

## Configuration

1. Copiez le fichier [.env.example](http://_vscodecontentref_/0) en [.env](http://_vscodecontentref_/1) :

    ```sh
    cp .env.example .env
    ```

2. Configurez les variables d'environnement dans le fichier [.env](http://_vscodecontentref_/2) :

    ```sh
    APP_NAME=HackR
    APP_ENV=local
    APP_KEY=base64:...
    APP_DEBUG=true
    APP_URL=http://localhost

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=hackr
    DB_USERNAME=root
    DB_PASSWORD=

    EMAIL_ADDRESS=votre-email@gmail.com
    EMAIL_KEY=votre-mot-de-passe
    ```

3. Générez la clé de l'application :

    ```sh
    php artisan key:generate
    ```

4. Exécutez les migrations de la base de données :

    ```sh
    php artisan migrate
    ```

## Utilisation

Pour démarrer le serveur de développement, exécutez la commande suivante :

```sh
php artisan serve