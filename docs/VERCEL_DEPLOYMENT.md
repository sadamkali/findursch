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
| `DB_URL` | The Supabase PostgreSQL **Session Pooler** connection URI, including its database username, password, host, port, database, and `sslmode=require`. |

The entrypoint also accepts `DATABASE_URL` or `POSTGRES_URL` as an alternative
to `DB_URL`. If more than one is present, `DB_URL` takes precedence.

The container sets `APP_ENV=production`, `APP_DEBUG=false`,
`DB_CONNECTION=pgsql`, and `DB_SSLMODE=require`. Set `APP_URL` to the
production domain when known; otherwise the entrypoint derives it from
Vercel's `VERCEL_URL`.

Never put real credentials in this repository, source files, or commit
messages. Do not paste secret values into support chats.

## Hobby-plan Git deployment requirement

This repository is private. On Vercel Hobby, the Git commit author must be the
owner of the Hobby project, and that GitHub identity must be connected to the
Vercel account under **Account Settings → Login Connections**. Installing or
granting the Vercel GitHub App access to the repository is not the same as
linking the GitHub login identity. Team invitations are not required for this
single-owner setup.

The current repository author is `sadamkali` with
`propipspath@gmail.com`; connect that GitHub account to the Vercel account
that owns the project.

## Startup behavior

The container validates `APP_KEY` and a PostgreSQL URL, runs Laravel
migrations, seeds only production reference data, and then starts FrankenPHP.
Demo users, demo schools, and demo reviews are not seeded in production.
