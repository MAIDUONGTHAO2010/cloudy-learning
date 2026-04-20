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

# Only fix ownership/permissions if the directories are not already owned by www-data
OWNER=$(stat -c '%U' storage 2>/dev/null || stat -f '%Su' storage 2>/dev/null)
if [ "$OWNER" != "www-data" ]; then
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
fi

# If arguments are provided (e.g. "composer install"), run them directly and exit.
# This is used by "docker compose run workspace composer install" in CI/CD so that
# vendor is installed on the host bind-mount before the main container starts,
# preventing a delayed PHP-FPM startup that would cause a 502 error.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# Install Composer dependencies if vendor directory is missing
if [ ! -f vendor/autoload.php ]; then
    echo "vendor/ not found — running composer install..."
    if [ "${APP_ENV:-production}" = "production" ]; then
        composer install --no-interaction --no-dev --optimize-autoloader
    else
        composer install --no-interaction --optimize-autoloader
    fi
fi

# Start PHP-FPM
exec php-fpm
