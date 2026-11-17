#!/bin/sh
set -eu

cd /app

# Secrets must be configured in the hosting environment, never in the image.
: "${APP_KEY:?APP_KEY is required. Generate one with php artisan key:generate --show and add it to Vercel Production Environment Variables.}"

# Laravel reads DB_URL. Accept common hosting-provider aliases and normalize
# them before the web server starts.
DB_URL="${DB_URL:-${DATABASE_URL:-${POSTGRES_URL:-}}}"
export DB_URL
: "${DB_URL:?DB_URL is required. Add the Supabase PostgreSQL Session Pooler URL as DB_URL, DATABASE_URL, or POSTGRES_URL in Vercel Production Environment Variables.}"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
export DB_CONNECTION
if [ "$DB_CONNECTION" != "pgsql" ]; then
    echo "Startup error: FindurSch production requires DB_CONNECTION=pgsql." >&2
    exit 1
fi

# Vercel supplies VERCEL_URL without a scheme. Use it when APP_URL is not set.
if [ -z "${APP_URL:-}" ] && [ -n "${VERCEL_URL:-}" ]; then
    APP_URL="https://${VERCEL_URL}"
    export APP_URL
fi

# IMPORTANT: Do not run migrations or seeders here. Vercel starts multiple
# isolated instances and enforces a short startup deadline. Running Artisan
# database setup here caused cold starts to exceed the port startup timeout
# and allowed concurrent instances to race on migrations. Run schema changes
# and one-time seeders once in a dedicated release step before deployment.

echo "Starting FindurSch web server..."
exec frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile
