FROM php:8.5-cli

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        curl \
        default-mysql-client \
        git \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

RUN { \
        echo "memory_limit=-1"; \
        echo "error_reporting=E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED"; \
        echo "display_errors=stderr"; \
    } > /usr/local/etc/php/conf.d/99-test-php.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp \
    && chmod +x /usr/local/bin/wp \
    && wp --allow-root --info
