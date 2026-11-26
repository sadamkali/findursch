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
- `SUPABASE_SECRET_KEY` has been added to the Cloudflare Pages production secrets. The next deployment smoke test verifies that the running Worker can use it to read the private legacy user table.
- The legacy Auth user import has **not** been run. The nine active Laravel accounts and their password hashes remain in `public.users`; no passwords or user rows have been changed.
- The one-time import script is idempotent and imports supported existing PHP bcrypt hashes into Supabase Auth while attaching the trusted `legacy_user_id` metadata. It must be run from a trusted local shell with the secret key entered locally; the key must never be pasted into chat or committed to GitHub.
- Administrator two-step email verification is not yet migrated. If the existing `require_two_step` setting is enabled, Cloudflare sign-in deliberately blocks administrator login rather than bypassing it.
- Full admin, school-owner and parent dashboard functionality has not yet been ported. This is a first migration slice, not a complete Laravel replacement.

## Safety boundaries

- No production DNS, custom domain, Vercel alias or Vercel deployment has been changed.
- The existing `findursch-static` Pages project and its domain remain unchanged.
- Public API routes are GET-only and expose only records permitted by the existing Supabase anon-role RLS policies.
- Authentication uses a server-only Supabase secret for private legacy user/role lookups. Supabase tokens are encrypted before storage in D1; browsers receive only an opaque HttpOnly cookie.
- Do not direct production traffic to this staging project until the legacy Auth import, login flows, role/permission checks, admin two-step flow, and required Laravel features have been migrated and tested.
