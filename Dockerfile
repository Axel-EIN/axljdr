# Dev/test image for this Symfony 5.3 app. Not used for production deployment
# (prod runs on a plain Apache+PHP host via symfony/apache-pack).
# PHP 8.1 is required by composer.lock (psr/cache, psr/log, psr/link need >=8.0;
# laminas/laminas-code caps it at <8.2), even though composer.json's own ">=7.2.5"
# constraint suggests otherwise — the lock file was regenerated against a newer PHP.
FROM php:8.1-apache

# System libraries needed by the PHP extensions below (gd needs libjpeg/libpng/libwebp,
# intl needs libicu, zip needs libzip). git/unzip are required by Composer.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
    && rm -rf /var/lib/apt/lists/*

# ext-gd is used by App\Service\ImageNormalizer (imagecreatefromjpeg/png/gif/webp).
# ext-pdo_mysql is required by Doctrine DBAL. ext-intl/zip are required by Symfony/Composer.
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd intl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Point Apache's docroot at public/, as required by the Symfony skeleton.
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

# public/.htaccess redirects HTTP -> HTTPS (production hardening we keep as-is), so the
# dev container needs a working TLS endpoint too. Self-signed cert is fine for local testing.
RUN openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /etc/ssl/private/axljdr-selfsigned.key \
        -out /etc/ssl/certs/axljdr-selfsigned.crt \
        -subj "/CN=localhost"
COPY docker/apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN a2enmod ssl && a2ensite default-ssl

# Recreate www-data with the host user's UID/GID so files written by Apache/Composer
# inside the container (vendor/, var/cache, public/assets/img/...) stay owned by the
# host user on the bind-mounted project directory, instead of becoming root-owned.
ARG HOST_UID=1000
ARG HOST_GID=1000
RUN groupmod -g ${HOST_GID} www-data \
    && usermod -u ${HOST_UID} -g ${HOST_GID} www-data

WORKDIR /var/www/html
