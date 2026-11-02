#!/bin/sh
set -e

rm -f /tmp/app-ready

ROLE="${CONTAINER_ROLE:-app}"

mkdir -p storage/app/public storage/app/private storage/logs \
    storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

if [ "${APP_ENV:-}" = "production" ]; then
    if [ -z "${APP_KEY:-}" ]; then
        echo "[entrypoint] APP_KEY is empty: set APP_KEY in .env" >&2
        exit 1
    fi
    if [ "${APP_DEBUG:-false}" = "true" ]; then
        echo "[entrypoint] WARNING: APP_DEBUG=true in production" >&2
    fi
else
    [ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist
    if [ "$ROLE" = "app" ] && [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
        php artisan key:generate --force
    fi
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${APP_ENV:-}" = "production" ]; then
    php artisan optimize
else
    php artisan optimize:clear
fi

if [ "$ROLE" = "app" ]; then
    touch /tmp/app-ready
fi

exec "$@"
