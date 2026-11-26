# FindURSCH Cloudflare Free migration

This is the first Cloudflare-native migration slice. It does not replace the Laravel application yet and does not change production DNS, production application records, or the existing Vercel production deployment. A narrowly scoped, read-only anonymous access policy set has now been enabled in the existing FindURSCH Supabase database.

## Target architecture

- Static public website: Vite-built HTML, CSS and JavaScript served by Cloudflare Pages.
- Public API: TypeScript Pages Functions calling the existing Supabase REST API with the anon role and RLS.
- Authentication and private API: to be ported as separate protected Worker routes using Supabase Auth and the existing role/permission data.
- Public images: the existing R2 media origin.
- Private documents: never exposed via public R2 URLs; secure delivery and watermark processing are a separate migration phase.

## First-slice routes

- GET /api/health — Pages Function health and whether Supabase URL/key are configured.
- GET /api/v1/schools — paginated public directory; only schools with status = active and verification_status = verified.
- GET /api/v1/schools/:slug — public profile and related public listing data.
- GET /api/v1/locations — active regions, districts, school types and curricula.

The API is read-only. It does not expose users, payment data, subscriptions, inquiries, admin operations or school documents.

## Authentication session foundation

A dedicated Cloudflare D1 database named `findursch-auth-sessions` is attached to the staging Pages Functions as `AUTH_DB`. It is located in the EU, has read replication disabled, and stores only opaque server-side auth sessions. Its `auth_sessions` table and expiry indexes are created; it contains no active sessions. D1 is included on Cloudflare Workers Free within its daily row-read/write and storage allowances. Browser clients receive only an opaque HttpOnly cookie; Supabase access and refresh tokens remain in D1.

## Pages staging

- Project: findursch-app-staging
- Staging hostname: https://findursch-app-staging.pages.dev
- Source branch: cloudflare-free-migration
- Root directory: cloudflare
- Build output: public-site/dist
- Preview deployments: disabled
- Pages Functions CPU limit: 10 ms
- No custom domain is attached.

The existing findursch-static project and static.findursch.riotinvestments.space remain unchanged. No production hostname or Vercel alias has been moved.

## Supabase setup and security verification

The REST API uses the Supabase anon role, not a service-role key and not a direct database connection. The project URL is https://gpsfttpeikatxszswljo.supabase.co.

The SQL in sql/public_api_anon_policies.sql was applied to the existing production FindURSCH database on 26 November 2026 after approval. It grants SELECT on only the public fields needed by the directory and adds 11 RLS policies across schools and their public reference/related tables.

Verification performed with SET LOCAL ROLE anon:
- 3 active + verified schools visible.
- 4 active regions, 128 active districts, 6 active school types and 3 active curricula visible.
- No SELECT access to public.users, password columns, school_documents, schools.owner_id, schools.registration_number, or private media metadata.
- RLS remains enabled on all 11 tables covered by the public API policies.

The Supabase security advisor still reports other tables with RLS enabled but no policies. Those tables were not included in the public API grants and remain outside this migration slice. Do not grant access to them as part of this change.

Cloudflare Pages project findursch-app-staging has SUPABASE_URL and PUBLIC_MEDIA_BASE_URL configured, with the legacy anon key stored as a Pages Function secret named SUPABASE_ANON_KEY. The service_role key is not used.

## Not yet migrated

- Parent registration/login, password reset and account flows.
- Admin and school-owner authentication, roles, permissions and write APIs.
- Sanctum-to-Supabase session transition and legacy bcrypt import.
- Articles, news, events, jobs, scholarships, reviews, saved schools and enquiries.
- School owner media uploads, private documents and PDF/DOCX watermark processing.
- Imports/exports, advertisements, billing/subscriptions and background processing.
- Remaining public Blade/Livewire pages, SEO metadata, sitemap and redirects.
- Separate Next.js dashboard migration (see dashboard repository's CLOUDFLARE_MIGRATION.md).

Do not direct production traffic to this staging project until all required flows have been ported and tested.
