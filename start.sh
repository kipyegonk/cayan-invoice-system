#!/bin/sh
set -e

# Create any missing tables (safe to re-run; skips ones that already exist)
php artisan migrate --force

php artisan config:cache

# 5-minute invoice sync, running in the background and logging to Render's logs
php artisan schedule:work &

# Web server (Render provides $PORT)
export PHP_CLI_SERVER_WORKERS=4
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
