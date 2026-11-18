# FindURSCH — Vercel deployment

FindURSCH is a Laravel 12 application served by FrankenPHP. Deploy the root
Dockerfile with Vercel's **Container** framework preset; do not deploy it as a
standalone Vite static site.

## Vercel project settings

- Git repository: `sadamkali/findursch`
- Production branch: `main`
- Root directory: repository root (`./`)
- Framework preset: `Container`
- Build/runtime definition: root `Dockerfile`, `Caddyfile`, and
  `docker-entrypoint.sh`

The repository's `vercel.json` also sets `framework` to `container`.

## Required Production environment variables

Add these in **Project → Settings → Environment Variables**, with the
**Production** target selected:

| Name | Value |
| --- | --- |
| `APP_KEY` | A Laravel application key generated with `php artisan key:generate --show`. Keep it secret and stable; do not regenerate it on every deployment. |
| `APP_URL` | The canonical public HTTPS URL, for example `https://findursch.vercel.app`. |
| `DB_URL` | The Supabase PostgreSQL **Session Pooler** connection URI, including its database username, password, host, port, database, and `sslmode=require`. |

The entrypoint also accepts `DATABASE_URL` or `POSTGRES_URL` as an
alternative to `DB_URL`. If more than one is present, `DB_URL` takes
precedence.

The container sets `APP_ENV=production`, `APP_DEBUG=false`,
`DB_CONNECTION=pgsql`, and `DB_SSLMODE=require`. If `APP_URL` is absent,
the entrypoint derives it from Vercel's `VERCEL_URL`.

Never put real credentials in this repository, source files, or commit
messages. Do not paste secret values into support chats.

## Hobby-plan Git deployment requirement

This repository is private. On Vercel Hobby, the Git commit author must be the
owner of the Hobby project, and that GitHub identity must be connected to the
Vercel account under **Account Settings → Login Connections**. Installing or
granting the Vercel GitHub App access to the repository is not the same as
linking the GitHub login identity. Team invitations are not required for this
single-owner setup.

## Database release procedure

**Do not run migrations or seeders from `docker-entrypoint.sh`.** Vercel can
start several isolated instances concurrently and expects the container to
listen on `$PORT` within a short startup window. Running migrations and
reference seeders during every cold start caused repeated work, migration
races, and startup timeouts.

Run database changes as a **single, one-off release step** before switching
traffic to the new application version:

```sh
php artisan migrate --force
php artisan db:seed --class=ProductionReferenceDataSeeder --force
```

Run those commands from a trusted release runner that has the same production
environment variables and network access to the Supabase Session Pooler. Do
not run them per web request or per Vercel container instance.

The production reference seeder is idempotent and creates shared
reference data only: regions, districts, school types, curricula, facilities,
plans, article categories, settings, and access roles. It does not create
school listings, school owners, or reviews. Public directory results are
restricted to schools with an active status and verified status. New school
profiles must be submitted and approved through the school-management workflow
before they appear in public search. Run the reference seeder when initializing
a database or intentionally updating reference data; the web server does not
reseed on startup.

## Container startup

The entrypoint only validates required secrets, normalizes the database URL,
sets the fallback application URL, and starts FrankenPHP immediately. This
keeps Vercel cold starts within the platform's port startup deadline.
