FROM node:20-alpine as node

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    bash \
    curl \
    git \
    libzip-dev \
    oniguruma-dev

# Copy all Node.js and npm files from the Node image
COPY --from=node /usr/local /usr/local

RUN ln -sf /usr/local/bin/node /usr/bin/node && \
    ln -sf /usr/local/bin/npm /usr/bin/npm && \
    ln -sf /usr/local/bin/npx /usr/bin/npx

# Install PHP extensions required by Laravel
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    mbstring \
    opcache \
    zip \
    pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Replace default PHP-FPM pool config with our tuned version
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

RUN mkdir -p bootstrap/cache storage && \
    chmod -R 775 bootstrap storage && \
    chown -R www-data:www-data bootstrap storage

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
