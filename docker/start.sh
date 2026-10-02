#!/bin/sh
set -eu

# Render's generated secrets are not guaranteed to use Laravel's key format.
# Derive a stable 32-byte key from the stored secret on every container start.
if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be configured." >&2
    exit 1
fi
export COREFLOW_APP_KEY_SEED="${APP_KEY}"
export APP_KEY="$(php -r 'echo "base64:".base64_encode(hash("sha256", getenv("COREFLOW_APP_KEY_SEED"), true));')"
unset COREFLOW_APP_KEY_SEED

APP_PORT="${PORT:-10000}"
sed -ri "s/^Listen [0-9]+/Listen ${APP_PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${APP_PORT}>/" /etc/apache2/sites-available/*.conf

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

php artisan migrate --force
php artisan optimize

php artisan queue:work --tries=3 --backoff=60 --timeout=90 --sleep=3 &
php artisan schedule:work &

exec apache2-foreground
