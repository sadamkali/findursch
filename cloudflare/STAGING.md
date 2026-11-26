# FindURSCH Cloudflare staging

- Cloudflare Pages project: `findursch-app-staging`
- Staging hostname: https://findursch-app-staging.pages.dev
- GitHub repository: `sadamkali/findursch`
- GitHub branch: `cloudflare-free-migration`
- Root directory: `cloudflare`
- Build command: `npm install --no-audit --no-fund && npm run build:site`
- Output: `public-site/dist`
- Preview deployments: disabled
- Pages Functions enabled
- Supabase project: `findursch` (`gpsfttpeikatxszswljo`, eu-west-1)
- Session strategy: Supabase Auth refresh-token session, carried in an AES-GCM encrypted HttpOnly cookie.
- No D1 or KV binding is required for authentication.
- Production variables: `SUPABASE_URL`, `PUBLIC_MEDIA_BASE_URL`
- Production encrypted Pages secrets: `SUPABASE_ANON_KEY`, `SUPABASE_SECRET_KEY`

## Current migration status

- The latest Pages deployment is built from `cloudflare-free-migration` and has Pages Functions enabled.
- Supabase remains the existing production PostgreSQL database. No PostgreSQL data has been moved to Cloudflare.
- Production verification confirmed 9 active legacy accounts and 9 Supabase Auth users with trusted numeric `legacy_user_id` mappings. The identity import did not modify `public.users` passwords or other legacy records.
- The Supabase secret is held as an encrypted Pages Function secret for private legacy-user/role lookups and server-side cookie encryption. It is not a database copy.
- The Cloudflare D1 session database is being removed after the new cookie-based authentication deployment is verified.
- Administrator two-step email verification is not yet migrated. The existing `require_two_step` setting was disabled at the time of the initial auth migration; if it is enabled, Cloudflare sign-in deliberately blocks administrator login.
- The nine users' real password sign-in and browser session lifecycle have not yet been tested end-to-end.
- Full admin, school-owner and parent dashboard functionality has not yet been ported. This remains an initial migration slice, not a complete Laravel replacement.

## Safety boundaries

- No production DNS, custom domain, Vercel alias or Vercel deployment has been changed.
- The existing `findursch-static` Pages project and its domain remain unchanged.
- Public API routes are GET-only and expose only records permitted by existing Supabase anon-role RLS policies.
- The encrypted Supabase secret remains server-side only. The browser receives an encrypted HttpOnly cookie, not Supabase tokens.
- Do not direct production traffic to this staging project until login/session lifecycle, role/permission checks, admin two-step flow, dashboards and required Laravel features have been migrated and tested.
