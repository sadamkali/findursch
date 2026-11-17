#!/bin/sh
set -eu

cd /app

echo "Checking database migrations..."
php artisan migrate --force

echo "Seeding FindurSch reference data..."
php artisan db:seed --class=ProductionReferenceDataSeeder --force

echo "Starting Laravel web server..."
exec frankenphp run --config /etc/caddy/Caddyfile
