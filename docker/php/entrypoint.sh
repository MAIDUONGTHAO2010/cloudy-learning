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

# If arguments are provided for a custom command (e.g. "composer install"), run them
# directly and exit. Do not short-circuit the normal PHP-FPM startup path, because we
# still need to ensure dependencies are installed before launching PHP-FPM.
if [ "$#" -gt 0 ]; then
    case "$1" in
        php-fpm)
            ;;
        -*)
            set -- php-fpm "$@"
            ;;
        *)
            exec "$@"
            ;;
    esac
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

# Generate application key if not set
if [ -f .env ] && grep -qE "^APP_KEY=\s*$" .env; then
    echo "APP_KEY is empty — generating..."
    php artisan key:generate --force
fi

# Run database migrations (opt-in via AUTO_MIGRATE=true, always on in non-production)
if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

# Create the public storage symlink if it doesn't exist
if [ ! -L public/storage ]; then
    php artisan storage:link --force
fi

# Start PHP-FPM
if [ "$#" -gt 0 ]; then
    exec "$@"
else
    exec php-fpm
fi
