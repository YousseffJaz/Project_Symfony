# Utilisation de l'image PHP 8.2 avec FPM
FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev

# Installation de Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Installation d'APCu
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Installation de Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Installation des extensions PHP nécessaires
RUN docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl \
    opcache \
    calendar

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du répertoire de travail
WORKDIR /var/www

# Copie des fichiers du projet 
COPY . .

# Copie des fichiers de configuration
COPY .env.test .env

# Installation des dépendances Composer
RUN composer install --no-interaction --optimize-autoloader

# Configuration des permissions
RUN chown -R www-data:www-data var \
    && chmod -R 777 var \
    && chown -R www-data:www-data bin \
    && chmod -R 777 bin

# Configuration de Xdebug
RUN echo "xdebug.mode=debug,develop" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.output_dir=/dev/null" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Configuration de PHP pour Sentry
RUN echo "zend.exception_ignore_args=Off" >> /usr/local/etc/php/conf.d/sentry.ini

# Configuration d'APCu
RUN echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini 