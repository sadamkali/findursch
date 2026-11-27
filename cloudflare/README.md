# FindURSCH Cloudflare migration

This branch is the Cloudflare migration for the **public FindURSCH website only**. The public website and the management dashboard are separate GitHub repositories and separate applications:

- Public website: sadamkali/findursch — Laravel, Blade and Livewire source being ported here.
- Management dashboard: sadamkali/findursch-dashboard — separate Next.js application. It is not part of this repository's deployment and is not changed by this migration.

The migration branch does not modify main, the current Vercel production deployment, production aliases or DNS.

## Current public-site deployment

- Cloudflare Worker: findursch
- Temporary Worker URL: https://findursch.propipspath.workers.dev
- Source repository: sadamkali/findursch
- Migration branch: cloudflare-free-migration
- Worker entrypoint: src/index.ts
- Static assets: public-site/dist
- Build command: npm install --no-audit --no-fund && npm run build:site
- GitHub Actions checks: build, TypeScript and public API smoke tests.

The separate Cloudflare Pages project findursch-static remains the static asset host at static.findursch.riotinvestments.space. It is not the public website application.

## Target architecture

- **GitHub:** source code, version control and migration checks.
- **Cloudflare Workers:** public website assets and public read-only API.
- **Supabase PostgreSQL:** existing FindURSCH database and system of record. No database was copied to Cloudflare and no new application tables were created for this public-site slice.
- **Supabase Auth:** existing account authority. Public homepage and directory routes do not require private Supabase keys.
- **Cloudflare R2:** existing public media origin. Private school documents and fees circular access remain a separate protected workflow.
- **Cloudflare Pages findursch-static:** existing static delivery hostname, retained independently.

## Migrated public routes

- GET / — public homepage matching the Laravel/Blade homepage design and content hierarchy.
- GET /schools — paginated public school directory.
- GET /schools/:slug — public school profile.
- GET /api/health — Worker and public database configuration.
- GET /api/v1/homepage — homepage content collections.
- GET /api/v1/schools — active and verified public schools.
- GET /api/v1/schools/:slug — public school profile data.
- GET /api/v1/locations — active regions, districts, school types and curricula.

The homepage uses read-only, anonymous Supabase REST requests. Unavailable editorial sections degrade to empty content rather than breaking the homepage.

Other public Laravel routes (including full news/article detail, events, jobs, scholarships, contact, legal pages, registration and account features) are not yet ported. Until those routes are migrated, the Cloudflare frontend links them to the still-live Vercel public-site deployment. This is temporary and must be resolved before a production-domain cutover.

## Public data security

- Public school rows require status = active and verification_status = verified.
- Public school media is limited to images in the cover, badge and gallery collections for active, verified schools.
- Public articles require status = published, a publication date not in the future and an active category.
- School news, events and videos require an approved/published status and an active, verified school.
- Public jobs require a published status, a publication date not in the future and an unexpired (or absent) deadline.
- Advertisements are limited to approved fallback banners that are within their display dates.
- User, role, permission, owner id, registration numbers, private school documents, billing data and moderation metadata are not part of the public API.

### School fee privacy

The public directory and school profiles may show only estimated fee ranges from schools.fees_from and schools.fees_to. Itemised school_fees.amount, fee labels and application fees are not available to the anon role and are not returned by the Cloudflare public API. Exact school fees remain private and should be provided through the school's fees circular.

The existing Supabase migrations applied for the public content slice are:

- findursch_public_content_read_access_and_fee_privacy
- findursch_public_article_category_policy_dependency
- findursch_public_fallback_ad_read_access

The corresponding SQL documentation is in sql/public_api_anon_policies.sql and sql/public_content_anon_policies.sql. Those scripts are read-access/RLS changes only; they do not insert or change business records.

## Not yet migrated or fully verified

- Full Laravel public page catalogue, including article/news/event/job/scholarship detail pages, forms and SEO redirects.
- Real password sign-in, sealed browser session and refresh lifecycle on the deployed Worker.
- Cloudflare login rate-limit binding and secret in the deployed Worker.
- Private school document and fees-circular delivery.
- Full admin and school-owner dashboard APIs and workflows.
- Parent registration/account workflows.
- Imports, exports, advertisement billing, subscriptions, payments and background processing.
- The separate Next.js dashboard repository migration.

The current Worker configuration exposes the public Supabase anon key only. The health route reports private auth and login-rate-limit configuration as not-configured until those bindings are deliberately installed and tested. Do not treat authentication as migrated.

## Production safety

- Keep the public Vercel production deployment and alias available during this migration.
- Keep the dashboard Vercel project and sadamkali/findursch-dashboard repository separate and unchanged.
- Keep main unchanged.
- Keep findursch-static, its custom domain, R2 media and the existing Supabase project.
- Do not change production DNS or direct the public custom domain to this Worker until required routes, login, permissions, media and redirect behavior have passed end-to-end tests.
