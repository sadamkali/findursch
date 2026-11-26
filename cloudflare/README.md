# FindURSCH Cloudflare Free migration

This branch contains the first Cloudflare-native migration slice for FindURSCH. It does not replace the Laravel application yet and does not change production DNS, production application records, or the existing Vercel production deployment.

## Target architecture

- Static public website: Vite-built HTML, CSS and JavaScript served by Cloudflare Pages.
- Public API: TypeScript Pages Functions calling the existing Supabase REST API with the anon role and RLS.
- Authentication and private API: Cloudflare Pages Functions using Supabase Auth and the existing role/permission data.
- Public images: the existing Cloudflare R2 media origin.
- Sessions and login throttling: Cloudflare D1, storing opaque session IDs and encrypted Supabase tokens.
- Private documents: never exposed via public R2 URLs; secure delivery and watermark processing remain a separate migration phase.

## First-slice routes

- `GET /api/health` — Pages Function health and configuration status.
- `GET /api/v1/schools` — paginated public directory; only schools with status = active and verification_status = verified.
- `GET /api/v1/schools/:slug` — public profile and related public listing data.
- `GET /api/v1/locations` — active regions, districts, school types and curricula.
- `POST /api/auth/login` — email or phone sign-in using the imported Supabase Auth identity.
- `GET /api/auth/me` — validates the opaque session cookie and current authorization.
- `POST /api/auth/logout` — destroys the server-side session and clears the cookie.

The public directory API is read-only. It does not expose users, payment data, subscriptions, inquiries, admin operations or school documents.

## Authentication and data protection

The existing production Supabase project `findursch` remains the system of record. The app reads public directory data with the anon role and RLS. Private legacy account, role, permission and settings lookups use the encrypted server-only Pages secret `SUPABASE_SECRET_KEY`; that key is never embedded in browser code or committed to GitHub.

A dedicated Cloudflare D1 database named `findursch-auth-sessions` is attached to the staging Pages Functions as `AUTH_DB`. It is located in the EU, has read replication disabled, and stores only opaque server-side auth sessions. Supabase access and refresh tokens are AES-GCM encrypted before they are written to D1; the AES-256 key is derived with HKDF from the server-only Supabase secret. Rotating or removing that secret invalidates stored sessions. Login attempts are rate-limited to 10 per connecting IP per 60-second window using an atomic D1 upsert; only an HMAC of the IP is stored.

A one-time, idempotent import script is prepared for the nine active legacy accounts. It imports supported PHP bcrypt password hashes into Supabase Auth and tags each identity with its existing legacy user ID. It does not modify `public.users` or reset existing passwords. The import has **not** yet been run. Never paste the secret key into chat or commit it to the repository; supply it only to the local import command through a hidden terminal prompt.

The existing `require_two_step` admin setting is currently disabled. Administrator two-step verification has not yet been ported; the Cloudflare login handler deliberately blocks admin login if that setting is enabled rather than silently bypassing it.

## Cloudflare staging

- Pages project: `findursch-app-staging`
- Staging hostname: https://findursch-app-staging.pages.dev
- Source branch: `cloudflare-free-migration`
- Root directory: `cloudflare`
- Build output: `public-site/dist`
- D1 binding: `AUTH_DB`
- Production encrypted secrets: `SUPABASE_ANON_KEY`, `SUPABASE_SECRET_KEY`
- No custom domain is attached.

GitHub Actions builds the static site, type-checks the TypeScript routes and validates the deployed staging API, including a no-account-match test that confirms private legacy-user lookup works using the server-only Supabase key.

## Supabase setup and security verification

The public REST API uses the Supabase anon role and carefully scoped RLS policies. The private authentication routes use the server-only secret key. The secret key is sent in the `apikey` header only, in line with the Supabase API-key format.

The SQL in `sql/public_api_anon_policies.sql` was applied to the existing production FindURSCH database on 26 November 2026 after approval. It grants SELECT on only the public fields needed by the directory and adds 11 RLS policies across schools and their public reference/related tables.

Verification performed with `SET LOCAL ROLE anon`:
- 3 active + verified schools visible.
- 4 active regions, 128 active districts, 6 active school types and 3 active curricula visible.
- No SELECT access to `public.users`, password columns, `school_documents`, `schools.owner_id`, `schools.registration_number`, or private media metadata.
- RLS remains enabled on all 11 tables covered by the public API policies.

The remaining tables with RLS enabled but no policies were not opened to the anon role and remain outside this migration slice.

## Not yet migrated

- Completion and end-to-end verification of the legacy password-hash import and real account sign-in.
- Administrator two-step email verification.
- Full admin and school-owner dashboards, including write APIs and moderation workflows.
- Parent registration/login, password reset and account flows.
- Articles, news, events, jobs, scholarships, reviews, saved schools and enquiries.
- School owner media uploads, private documents and PDF/DOCX watermark processing.
- Imports/exports, advertisements, billing/subscriptions and background processing.
- Remaining public Blade/Livewire pages, SEO metadata, sitemap and redirects.
- Separate Next.js dashboard migration (see the dashboard repository's `CLOUDFLARE_MIGRATION.md`).

Do not direct production traffic to this staging project until all required user flows and business rules have been ported and tested.
