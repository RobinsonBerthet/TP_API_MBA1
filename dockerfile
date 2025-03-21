# Utiliser l'image PHP 8.2 avec FPM
FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer depuis une image officielle pour éviter les erreurs d'installation
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application avec les bons droits directement
COPY --chown=www-data:www-data . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Changer les permissions du dossier `storage` et `bootstrap/cache`
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod 664 /var/www/html/storage/logs/laravel.log

RUN usermod -u 1000 www-data

# Exposer le port utilisé par PHP-FPM
EXPOSE 8080
