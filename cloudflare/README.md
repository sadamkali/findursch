# FindURSCH Cloudflare Free migration

This is the first Cloudflare-native migration slice. It does not replace the Laravel application yet and does not change production DNS, Supabase data, or the existing Vercel production deployment.

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

## Supabase setup

The REST API uses the Supabase anon role, not a service-role key and not a direct database connection. The project URL is https://gpsfttpeikatxszswljo.supabase.co.

Review sql/public_api_anon_policies.sql and sql/README.md. The SQL is a proposed permissions change only; it has not been executed. It must be approved before the staging API can read the live school tables. Configure the active legacy anon key as a Pages Function secret named SUPABASE_ANON_KEY in Cloudflare. Never use service_role.

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
