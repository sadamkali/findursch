# FindURSCH Cloudflare migration

This branch contains the Cloudflare-native migration of FindURSCH. It does not replace the Laravel application yet and does not change production DNS, the production hostname, or the existing Vercel deployment.

## Target architecture

- **GitHub:** source code, version control, CI checks and Cloudflare build triggers.
- **Cloudflare Pages + Functions:** public website, application API and server-side application logic.
- **Supabase PostgreSQL:** the existing production database and system of record. No database has been copied to Cloudflare.
- **Supabase Auth:** account identities, password authentication, refresh-token sessions, token rotation and session revocation.
- **Cloudflare R2:** existing public media origin. Private documents must remain protected and are a separate migration phase.

Cloudflare does not host a replacement PostgreSQL database and no D1 or KV database is required for application authentication sessions.

## First-slice routes

- `GET /api/health` — Pages Function health and configuration status.
- `GET /api/v1/schools` — paginated public directory; only schools with `status = active` and `verification_status = verified`.
- `GET /api/v1/schools/:slug` — public profile and related public listing data.
- `GET /api/v1/locations` — active regions, districts, school types and curricula.
- `POST /api/auth/login` — email or phone sign-in using the imported Supabase Auth identity.
- `GET /api/auth/me` — validates the Supabase Auth session and current authorization.
- `POST /api/auth/logout` — revokes the current Supabase Auth session where possible and clears the browser cookie.

The public directory API is read-only. It does not expose users, payment data, subscriptions, inquiries, admin operations or school documents.

## Authentication and data protection

The existing production Supabase project `findursch` remains the database and authentication provider. The public directory uses the anon key with carefully scoped RLS. Private legacy account, role, permission and settings lookups use the encrypted server-only Pages secret `SUPABASE_SECRET_KEY`; this is an API credential, not a copy or migration of the database.

### Legacy account import — verified complete

Read-only verification against production confirmed:

- 9 active legacy accounts in `public.users`.
- 9 Supabase Auth users with numeric trusted `app_metadata.legacy_user_id` mappings.
- The imported identities use the existing Laravel bcrypt password hashes.
- Existing `public.users` IDs, password hashes, role assignments, permissions and ownership records were not changed by the identity import.

The import script is idempotent and remains in the repository for controlled maintenance. The user's actual password sign-in and browser session lifecycle still require an end-to-end test.

### Session handling

Supabase Auth is the session authority and owns refresh-token sessions. The Cloudflare Pages Function stores the session in a sealed cookie rather than a Cloudflare database:

- Cookie name: `findursch_session`.
- Attributes: `HttpOnly`, `Secure`, `SameSite=Lax`, `Path=/`, and a bounded `Max-Age`.
- Access and refresh tokens are encrypted with AES-GCM. The encryption key is derived using HKDF from the server-only Supabase secret.
- Browser JavaScript cannot read the access or refresh tokens.
- Near expiry, Cloudflare refreshes through Supabase Auth, verifies the returned user identity and returns a newly encrypted cookie containing the rotated tokens.
- `/api/auth/me` validates the session with Supabase Auth and reloads the active legacy user and current roles/permissions from PostgreSQL.
- Logout requests Supabase Auth local-scope revocation and always clears the browser cookie.
- Rotating the server-only secret invalidates existing encrypted cookies, so users will need to sign in again.

No D1 or KV binding is used for sessions. Credential-attempt protections are handled by Supabase Auth's configured protections; Cloudflare Turnstile or edge rate-limit hardening can be added in a later security phase.

## Cloudflare staging

- Pages project: `findursch-app-staging`
- Staging hostname: https://findursch-app-staging.pages.dev
- Source repository: `sadamkali/findursch`
- Source branch: `cloudflare-free-migration`
- Root directory: `cloudflare`
- Build output: `public-site/dist`
- Pages Functions enabled.
- Encrypted production secrets: `SUPABASE_ANON_KEY`, `SUPABASE_SECRET_KEY`.
- Plaintext production variables: `SUPABASE_URL`, `PUBLIC_MEDIA_BASE_URL`.
- No custom domain is attached.

GitHub Actions builds the static site, type-checks TypeScript routes and validates the deployed staging API, including a no-account-match test that confirms private legacy-user lookup works using the server-only Supabase key.

## Supabase public API and security verification

The SQL in `sql/public_api_anon_policies.sql` was applied to the existing production FindURSCH database on 26 November 2026. It grants SELECT only on the public fields needed by the directory and adds 11 RLS policies across schools and their public reference/related tables.

Verification performed with the PostgreSQL `anon` role:
- 3 active + verified schools visible.
- 4 active regions, 128 active districts, 6 active school types and 3 active curricula visible.
- No SELECT access to `public.users`, password columns, `school_documents`, `schools.owner_id`, `schools.registration_number`, or private media metadata.
- RLS remains enabled on all 11 tables covered by the public API policies.

The remaining tables with RLS enabled but no policies were not opened to the anon role and remain outside this migration slice.

## Not yet migrated or fully verified

- End-to-end sign-in with a real migrated user and browser cookie lifecycle, including token refresh and logout.
- Administrator two-step email verification.
- Full admin and school-owner dashboards, including write APIs and moderation workflows.
- Parent registration/login, password reset and account flows.
- Articles, news, events, jobs, scholarships, reviews, saved schools and enquiries.
- School owner media uploads, private documents and PDF/DOCX watermark processing.
- Imports/exports, advertisements, billing/subscriptions and background processing.
- Remaining public Blade/Livewire pages, SEO metadata, sitemap and redirects.
- Separate Next.js dashboard migration (see the dashboard repository's `CLOUDFLARE_MIGRATION.md`).

Do not direct production traffic to this staging project until the required user flows and business rules have been ported and tested.
