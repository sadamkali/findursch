#!/bin/sh
set -eu

cd /app

# Secrets must be configured in the hosting environment, never in the image.
: "${APP_KEY:?APP_KEY is required. Set a Laravel application key in Vercel Environment Variables.}"
: "${DB_URL:?DB_URL is required. Set the Supabase PostgreSQL Session Pooler connection string in Vercel Environment Variables.}"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
if [ "$DB_CONNECTION" != "pgsql" ]; then
    echo "Startup error: FindurSch production requires DB_CONNECTION=pgsql." >&2
    exit 1
fi

echo "Checking FindurSch PostgreSQL migrations..."
php artisan migrate --force

echo "Seeding FindurSch reference data..."
php artisan db:seed --class=ProductionReferenceDataSeeder --force

echo "Starting FindurSch web server..."
exec frankenphp run --config /etc/caddy/Caddyfile
