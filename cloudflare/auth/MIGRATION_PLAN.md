# FindURSCH authentication migration plan

This file records the verified Laravel authentication contract and the safe Cloudflare/Supabase replacement plan. It is documentation only: no users have been imported, passwords have not been changed, and no new authentication sessions have been issued.

## Verified legacy account state

Checked against the existing production Supabase project on 26 November 2026:

- 9 active accounts: 3 with `user_type = admin`, 6 with `user_type = school_owner`.
- All 9 accounts have an email address.
- 5 accounts also have a phone number.
- All 9 `email_verified_at` values are null. The current Laravel login does not reject accounts because of this field.
- All 9 password hashes use PHP/Laravel bcrypt `$2y$`.
- The Spatie permission model is used: `model_has_roles`, `roles`, `role_has_permissions`, and `permissions`.
- The existing data contains `admin` and `school_owner` role assignments. Some administrator-type accounts also carry the `school_owner` role; admin precedence must be retained.
- Role catalog also contains `administrator`, `editor`, and `support` roles with `admin.*` permissions. Preserve every role and permission, not just those currently assigned.
- There are no direct user permission assignments in `model_has_permissions`.

No passwords or password hashes are copied into this document or the browser.

## Existing authentication behavior to preserve

### Sign-in

- Identifier accepts an email address or phone number.
- Phone identifiers are normalized and matched against `public.users.phone`; all accounts have an email, so phone sign-in can resolve to the corresponding email identity before calling Supabase Auth.
- Password is verified against the existing hash.
- Inactive accounts are blocked.
- Workspace access is allowed for `admin`, `school_owner`, `parent`, or an account with an `admin.*` permission.
- An account with admin access must take precedence when both admin and school-owner roles are present.
- The `redirectTo` target is `/admin`, `/owner`, or `/parent` based on the effective role.
- Login rate limit: 10 attempts per minute in Laravel.

### Admin protection and session lifetime

Verified production settings:
- `require_two_step = 0` (six-digit admin email verification is currently disabled).
- `block_new_country_signins = 1`.
- `admin_login_countries` currently has no saved country rules. The first observed country is auto-approved by the Laravel implementation; a later unapproved country can be blocked and trigger an email alert.
- `admin_session_timeout_minutes = 120`.
- School-owner sessions last 30 days when remembered, otherwise 1 day.
- Admin sign-in alerts are enabled (`admin_signin_alerts = 1`).
- Admin password update code enforces a minimum of 10 characters (`max(10, configured value)`), while parent/owner registration requires at least 8 characters.

### Other auth endpoints in Laravel

The replacement must account for:
- `/api/auth/login`
- `/api/auth/verify-admin-code`
- `/api/auth/register` (parent or school owner)
- `/api/auth/register-parent`
- `/api/auth/register-school`
- `/api/auth/reset-password`
- `/api/auth/me`
- `/api/auth/profile`
- `/api/auth/password`
- `/api/auth/logout`
- `/api/auth/handoff`
- `/api/auth/create-dashboard-session-handoff`
- `/api/auth/exchange-dashboard-session`

The school-owner registration path is not a simple user insert. It validates location hierarchy, registration options, curricula, facilities, configurable custom fields, image uploads, and school-review settings. Those rules must be ported as a separate phase.

## Supabase Auth migration

Supabase Auth's Admin API supports a `password_hash` field on `POST /auth/v1/admin/users`. Current GoTrue tests include PHP `$2y$` bcrypt password hash fixtures, so the existing passwords can be retained without exposing plaintext passwords or forcing a reset. See:
- https://github.com/supabase/auth/blob/master/internal/api/admin.go
- https://github.com/supabase/auth/blob/master/internal/api/admin_test.go
- https://supabase.com/docs/guides/auth/password-security

Planned migration behavior:
1. Read the legacy user rows and their password hashes only from a trusted one-time migration script.
2. Create the corresponding Supabase Auth identity using the existing bcrypt `password_hash`; do not use a plaintext password and do not rewrite the `public.users.password` field.
3. Store the legacy numeric user ID in trusted `app_metadata.legacy_user_id`, never in user-editable metadata.
4. Preserve `public.users.email_verified_at` exactly. It is null for every current account. Before importing, verify the project's email-confirmation settings and choose a sign-in configuration that preserves the current Laravel behavior without falsely marking the legacy email verification field as complete.
5. Do not change existing `public.users` IDs, roles, permissions, school ownership references, or other business records.
6. Make the migration script idempotent: match existing Auth users by the trusted legacy ID/email, report conflicts, and never silently overwrite a different account.

Supabase's new secret keys (`sb_secret_...`) are opaque and are not JWTs. Send them in the `apikey` header only; never send them as `Authorization: Bearer`. The auth Worker follows this rule. A secret key is required for user import and server-side account lookups. Store it only as the Cloudflare Pages Function secret `SUPABASE_SECRET_KEY`, never in browser code or GitHub. The current staging project has only the anon key configured; no secret key has been added yet.

The import script creates Auth identities with `email_confirm: true` so their sign-in behavior matches the current Laravel application, which does not block login based on `public.users.email_verified_at`. It does not change that legacy column: it remains null and must continue to be used for any separate business/email-verification indicators. The import is deliberately a separate, one-time operation and has not been run.

## Cloudflare session architecture

A dedicated Cloudflare D1 database has been created for staging:

- Database: `findursch-auth-sessions`
- Database ID: `d9cf085f-35af-4b2e-be5b-991b4241c51f`
- Region: EU (`EEUR`), jurisdiction `eu`
- Binding: `AUTH_DB`, attached to `findursch-app-staging` production Pages Functions.
- Read replication is disabled so all session queries use the primary database.
- The `auth_sessions` table and expiry indexes have been created; no active sessions exist.

Planned session model:
- The browser receives only a cryptographically random opaque session ID in an HttpOnly, Secure, SameSite=Lax, host-only cookie.
- Supabase access and refresh tokens are retained server-side in the D1 `auth_sessions` table, not returned to browser JavaScript.
- Admin sessions use the live `admin_session_timeout_minutes` setting, preserving Laravel's 30-minute minimum (currently 120 minutes); owner sessions enforce the selected 30-day or 1-day lifetime.
- Login is rate-limited to 10 attempts per connecting IP per 60-second window using an atomic D1 upsert. Only an HMAC of the IP is stored. The limiter returns HTTP 429 with `Retry-After` after the tenth attempt.
- Session refresh, logout and session reads fail closed if D1 storage or token refresh is unavailable.
- KV quota is finite on Free. The code must handle write/read quota errors, avoid per-request writes, and never downgrade to a bearer token in localStorage.
- The service-role/secret key is used only by the trusted Pages Function for controlled user/role lookups. Public directory routes continue to use the anon key and RLS.

Cloudflare KV Free pricing and daily quotas: https://developers.cloudflare.com/kv/platform/pricing/

## Authorization model

- Resolve the Supabase identity to `public.users` via the trusted `app_metadata.legacy_user_id`.
- Check the public user record still exists and has `status = 'active'` on every session validation.
- Load roles from `model_has_roles` + `roles`.
- Load permission names from `role_has_permissions` + `permissions`.
- Include direct user permission grants if they are added later.
- Treat `admin` role or any `admin.*` permission as admin access, matching Laravel.
- Do not accept `roles`, `permissions`, `user_type`, or legacy user IDs from browser request bodies or user metadata.
- Enforce record ownership and per-endpoint permissions for every write endpoint; authentication alone does not grant access to all records.

## Not yet changed

- No Supabase Auth user has been created/imported.
- No existing password hash or legacy account record has been updated.
- No new email-confirmation or MFA setting has been changed.
- No `SUPABASE_SECRET_KEY`/service-role key has been added to Cloudflare.
- No login, registration, password reset, handoff, or admin/owner write API has been deployed.
- The public directory staging deployment and production website remain separate.
