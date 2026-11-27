# FindURSCH public website — Cloudflare migration

- Cloudflare Worker: findursch
- Temporary URL: https://findursch.propipspath.workers.dev
- GitHub repository: sadamkali/findursch
- GitHub branch: cloudflare-free-migration
- Worker entrypoint: cloudflare/src/index.ts
- Static build: cloudflare/public-site/dist
- Build command from cloudflare/: npm install --no-audit --no-fund && npm run build:site
- Database: existing Supabase project findursch (gpsfttpeikatxszswljo, eu-west-1)
- Public media: existing Cloudflare R2 public origin.
- No production custom domain is attached to this Worker.

The Pages project findursch-static is separate and remains the static asset delivery host at static.findursch.riotinvestments.space. The old findursch-app-staging.pages.dev staging hostname is no longer the active public-site test target.

## Current migration status

- The public Worker serves the port of the original Laravel homepage shell and shared design CSS.
- Homepage school cards, news, insights, school posts, events, scholarships, jobs, videos and approved fallback advertisements are read from the existing Supabase database through the anon key and RLS.
- Directory and school-profile API routes remain read-only.
- Public school cards/profiles show only estimated fee ranges. Exact fee amounts and application fees are blocked from anonymous access.
- The public website build and TypeScript checks run in GitHub Actions.
- Public API smoke tests target https://findursch.propipspath.workers.dev.

## Still outstanding

- Full public Blade route parity for articles/news, events, jobs, scholarships, contact, legal pages, registration and account flows. Until ported, those links temporarily open the live Vercel public website.
- End-to-end sign-in/session testing and binding of the private auth secret.
- Deployment of the login-rate-limit Durable Object binding and HMAC secret.
- Protected school document and fees-circular delivery.
- Complete SEO, sitemap, canonical URLs and redirects.
- Full end-to-end mobile/desktop browser review.

## Safety boundaries

- No production DNS, custom domain, Vercel alias or Vercel deployment has been changed.
- The separate sadamkali/findursch-dashboard repository and its Vercel deployment remain untouched.
- The existing findursch-static Pages project and its custom domain remain unchanged.
- Supabase remains the existing system of record; no database copy, new application table or application data migration was performed by this frontend port.
- Dashboard/admin/owner pages must not be merged into this public website repository.
