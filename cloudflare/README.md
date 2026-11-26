# FindURSCH Cloudflare Free migration

This folder is the first Cloudflare-native migration slice. It does not replace the Laravel application yet and it does not change production DNS, Supabase data, or the existing Vercel production deployment.

## Target architecture

- Static public website: Vite-built HTML, CSS and JavaScript served by Cloudflare Workers Static Assets.
- Public API: a TypeScript Worker using the existing PostgreSQL schema through Hyperdrive.
- Authentication: planned Supabase Auth integration, with the existing `public.users` records and roles retained as the application source of truth.
- Public images: the existing public R2 media origin.
- Private documents and school-owner/admin operations: separate authenticated migration phases; do not make private files public.

## Current first-slice routes

- `GET /api/health` — Worker health and whether a Hyperdrive binding has been configured.
- `GET /api/v1/schools` — paginated public directory; only schools with `status = 'active'` and `verification_status = 'verified'` are returned.
- `GET /api/v1/schools/:slug` — public school profile and related public listing data.
- `GET /api/v1/locations` — active regions, districts, school types and curricula.

The public API is read-only. No endpoint in this slice writes to PostgreSQL.

## Local development

From this folder:

```powershell
npm install
npm run dev:site
```

In a second terminal, build the static site and type-check the Worker:

```powershell
npm run build:site
npm run typecheck
```

## PostgreSQL / Hyperdrive setup

The Worker deliberately does not contain a database password or a Hyperdrive ID. Before enabling database routes:

1. In the Supabase dashboard, open the existing `findursch` project and retrieve its **Direct connection** details. Cloudflare's Hyperdrive guidance for Supabase says to use the direct connection string rather than the Supabase pooler string, because Hyperdrive supplies connection pooling.
2. Prefer a dedicated database login restricted to the tables and operations required by this application. Do not put a database password in GitHub or in this README.
3. Create a Hyperdrive configuration in the Cloudflare dashboard using that connection. The Cloudflare account already has the `propipspath` Workers subdomain.
4. Add the returned Hyperdrive ID to the `hyperdrive` array in `wrangler.jsonc`, using the binding name `HYPERDRIVE`.
5. From this folder run `npx wrangler deploy`. This deploys only to the Worker name configured here and its `workers.dev` staging hostname.

**Important database finding:** the existing Supabase `public` tables have RLS enabled, but only a small number of explicit policies. The Worker must enforce all application authorization on the server. Do not expose a Supabase service key or a PostgreSQL connection string to the browser. Do not run Laravel migrations or any schema-changing SQL as part of this first slice.

## R2 media

The existing school media table uses Spatie Media Library's default path generator and the configured public R2 origin. The API only generates read URLs for public cover/gallery images. Private school documents are excluded.

## Not yet migrated

- Supabase Auth sign-in and legacy password-hash compatibility validation.
- Parent and school-owner registration, password recovery and account flows.
- Sanctum-to-Supabase session replacement and one-time dashboard handoff.
- All admin and school-owner write APIs, permission checks and audit trails.
- Imports/exports, advertising, billing/subscriptions, reviews and enquiries.
- Private document access and PDF/DOCX watermarking.
- SEO rendering, sitemap generation and the remaining public content routes.
- The separate Next.js dashboard migration (see its own repository's `CLOUDFLARE_MIGRATION.md`).

Do not point the production hostname at this Worker until these flows have been implemented and tested.
