# Étape 1 : Utiliser l'image PHP 8.2
FROM php:8.2

# Étape 2 : Installer les dépendances nécessaires
RUN apt-get update -y && apt-get install -y \
    openssl \
    zip \
    unzip \
    git \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    mariadb-client \
    && docker-php-ext-install pdo_mysql mbstring zip gd  # Ajout de 'zip' ici

# Étape 3 : Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Étape 4 : Définir le répertoire de travail
WORKDIR /app

# Étape 5 : Copier les fichiers de l'application dans le conteneur
COPY . .

# Étape 6 : Modifier les permissions
RUN chown -R www-data:www-data /app

# Étape 7 : Installer les dépendances de l'application
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Étape 8 : Installer les paquets nécessaires
RUN composer require php-open-source-saver/jwt-auth
RUN composer require simplesoftwareio/simple-qrcode
RUN composer require phpoffice/phpspreadsheet

# Étape 9 : Exposer le port 8181
EXPOSE 8181

# Étape 10 : Lancer les commandes artisan et démarrer le serveur
CMD php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider" && \
    php artisan storage:link && \
    php artisan key:generate && \
    php artisan migrate:fresh && \
    php artisan db:seed && \
    php artisan jwt:secret && \
    php artisan vendor:publish --provider="SimpleSoftwareIO\QrCode\QrCodeServiceProvider" && \
    php artisan serve --host=0.0.0.0 --port=8181
