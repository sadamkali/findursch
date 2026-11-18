# FindurSch — Public Website & Laravel API

This repository owns the **public FindurSch website and Laravel backend**. It is not the home of the administrator or school-owner dashboard interfaces.

## Application boundaries

- Public school discovery website: Laravel + Livewire (school listings, school profiles, search, comparison, education articles, contact and legal pages).
- Backend and persistence: Laravel JSON APIs, the existing Supabase PostgreSQL database, authentication, authorization, validation and business rules.
- Administrator and school-owner workspaces: **only** in [sadamkali/findursch-dashboard](https://github.com/sadamkali/findursch-dashboard), built with Next.js.
- The public `/admin/*` and `/owner/*` entry points redirect to the standalone Next.js dashboard. They must never render Laravel/Filament admin or owner interfaces.
- `/api/admin/*` and `/api/owner/*` remain backend API routes consumed by the separate dashboard. These are not dashboard pages and must remain protected by Laravel role middleware.

## Development rules

1. Do not add Filament panels, Livewire administrator dashboards or Livewire school-owner workspaces to this repository.
2. Implement all administrator and school-owner dashboard UI, navigation, pages and interactions in `sadamkali/findursch-dashboard`.
3. Keep Laravel business logic and data access on the server. Do not expose database credentials or service-role secrets to browser code.
4. Preserve existing production users, school records, roles, subscriptions, payments and other application data when making code changes.
5. Keep the public school-discovery frontend in this repository. Parent self-service account pages may remain here until their migration to the separate dashboard is complete.

## Dashboard access

- Administrator: `/admin` in the Next.js dashboard — requires the Laravel `admin` role.
- School owner: `/owner` in the Next.js dashboard — requires the Laravel `school_owner` role.
- Admin and owner API requests are authenticated by Laravel Sanctum and authorized server-side.

## Deployment

The Laravel application is deployed as the public website/backend on Vercel. The separate Next.js dashboard has its own Vercel project and communicates with this application through the protected JSON API. Configure the public website and dashboard URLs through environment variables rather than hard-coding future custom domains.
