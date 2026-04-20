#!/bin/sh
set -e

# Ensure storage and bootstrap/cache directories exist and are writable by www-data
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Install Composer dependencies if vendor directory is missing
if [ ! -f vendor/autoload.php ]; then
    echo "vendor/ not found — running composer install..."
    composer install --no-interaction --no-dev --optimize-autoloader
fi

# Start PHP-FPM
exec php-fpm
