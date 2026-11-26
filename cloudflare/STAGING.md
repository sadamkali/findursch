# FindURSCH Cloudflare staging

- Cloudflare Pages project: `findursch-app-staging`
- Staging hostname: https://findursch-app-staging.pages.dev
- GitHub repository: `sadamkali/findursch`
- GitHub branch: `cloudflare-free-migration`
- Root directory: `cloudflare`
- Build command: `npm install --no-audit --no-fund && npm run build:site`
- Output: `public-site/dist`
- Preview deployments: disabled
- Pages Functions CPU limit: 10 ms
- D1 binding: `AUTH_DB` → `findursch-auth-sessions` (EU jurisdiction; read replication disabled)
- D1 tables: `auth_sessions` (encrypted session tokens) and `auth_login_attempts` (HMAC-only IP rate-limit records)
- Production environment variables: `SUPABASE_URL`, `PUBLIC_MEDIA_BASE_URL`
- Production encrypted Pages secrets: `SUPABASE_ANON_KEY`, `SUPABASE_SECRET_KEY`

## Current migration status

- The latest Pages deployment is built from `cloudflare-free-migration` and has Pages Functions enabled.
- The existing Supabase project `findursch` remains the production database.
- `SUPABASE_SECRET_KEY` is configured as an encrypted Cloudflare Pages production secret.
- The legacy Supabase Auth import is **complete**. Production database verification confirmed 9 active legacy accounts, 9 corresponding Supabase Auth users, 9 email/password identities, 9 stored password hashes and 9 trusted legacy metadata mappings. All 9 Supabase Auth email identities are confirmed. The importer did not update `public.users` or reset legacy passwords.
- The temporary GitHub Actions one-time import workflow and trigger marker have been removed after the import. The idempotent import script remains in the repository.
- Administrator two-step email verification is not yet migrated. The existing `require_two_step` setting was disabled at the time of migration; if it is enabled, Cloudflare sign-in deliberately blocks administrator login.
- The nine users' full credential sign-in and session lifecycle have not yet been tested with real passwords.
- Full admin, school-owner and parent dashboard functionality has not yet been ported. This remains an initial migration slice, not a complete Laravel replacement.

## Safety boundaries

- No production DNS, custom domain, Vercel alias or Vercel deployment has been changed.
- The existing `findursch-static` Pages project and its domain remain unchanged.
- Public API routes are GET-only and expose only records permitted by the existing Supabase anon-role RLS policies.
- Authentication uses a server-only Supabase secret for private legacy user/role lookups. Supabase tokens are encrypted before storage in D1; browsers receive only an opaque HttpOnly cookie.
- Do not direct production traffic to this staging project until the login and session lifecycle, role/permission checks, admin two-step flow, dashboards and required Laravel features have been migrated and tested.
