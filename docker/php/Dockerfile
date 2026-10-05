FROM php:8.4-apache

ARG USER_UID=1000
ARG USER_GID=1000

# System packages: MySQL client (used by bin/db-* scripts), git/unzip for Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client git unzip gzip libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli intl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Non-root dev user; Apache runs as this user too (see APACHE_RUN_USER in docker-compose.yml)
RUN groupadd --gid ${USER_GID} dev \
    && useradd --uid ${USER_UID} --gid ${USER_GID} --create-home --shell /bin/bash dev

# Apache: document root is ./www, enable mod_rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/root/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && a2enmod rewrite

# PHP config (development)
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY php-dev.ini "$PHP_INI_DIR/conf.d/99-dev.ini"

# Fake sendmail: writes outgoing mail to files instead of sending it
COPY fake_sendmail /usr/local/bin/fake_sendmail
RUN chmod +x /usr/local/bin/fake_sendmail

WORKDIR /var/www/root
