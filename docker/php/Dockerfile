FROM node:20-alpine as node

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    bash \
    curl \
    git

# Copy all Node.js and npm files from the Node image
COPY --from=node /usr/local /usr/local

RUN ln -sf /usr/local/bin/node /usr/bin/node && \
    ln -sf /usr/local/bin/npm /usr/bin/npm && \
    ln -sf /usr/local/bin/npx /usr/bin/npx

# Install PHP PostgreSQL extensions
RUN docker-php-ext-install pdo_pgsql pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN mkdir -p bootstrap/cache storage && \
    chmod -R 775 bootstrap storage && \
    chown -R www-data:www-data bootstrap storage

EXPOSE 9000
