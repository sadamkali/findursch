# FindURSCH Cloudflare staging

The separate Pages project is:

- Project: findursch-app-staging
- Staging hostname: https://findursch-app-staging.pages.dev
- Source branch: cloudflare-free-migration
- Root directory: cloudflare
- Build command: npm install --no-audit --no-fund && npm run build:site
- Output: public-site/dist
- Preview deployments: disabled
- Pages Functions CPU limit: 10 ms
- Custom domains: none

The production FindURSCH hostname, Vercel deployment, existing findursch-static Pages project and its static-assets custom domain are not changed.

Until the read-only Hyperdrive binding is configured, public API endpoints that need PostgreSQL intentionally return HTTP 503 with database_not_configured. The /api/health endpoint should remain available. Do not direct users or production traffic to this staging project.
