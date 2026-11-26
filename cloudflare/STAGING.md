# FindURSCH Cloudflare staging

- Cloudflare Pages project: findursch-app-staging
- Staging hostname: https://findursch-app-staging.pages.dev
- GitHub branch: cloudflare-free-migration
- Root directory: cloudflare
- Build command: npm install --no-audit --no-fund && npm run build:site
- Output: public-site/dist
- Preview deployments: disabled
- Pages Functions CPU limit: 10 ms
- D1 binding: `AUTH_DB` → `findursch-auth-sessions` (EU jurisdiction; read replication disabled)
- Supabase REST is used; Hyperdrive and PostgreSQL drivers are not required.
- Supabase URL and anon key are Pages Function environment values; the anon key is set as a secret and is never embedded in the frontend.
- Custom domains: none.

The production domain, Vercel production deployment, existing findursch-static project and its static-asset domain remain unchanged.
