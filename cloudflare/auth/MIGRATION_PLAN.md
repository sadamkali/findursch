# FindURSCH authentication migration plan

This file records the verified Laravel authentication contract and the Cloudflare/Supabase replacement status. Production read-only verification confirmed that the nine active legacy accounts have matching Supabase Auth identities. Existing public.users records and password hashes remain unchanged.

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

## Supabase Auth identity migration — complete

Supabase Auth Admin API was used by the idempotent migration script to import the existing PHP/Laravel bcrypt `password_hash` values without ever requiring plaintext passwords.

Production read-only verification confirmed:
- 9 active rows in `public.users`.
- 9 Supabase Auth users with numeric trusted `app_metadata.legacy_user_id` values.
- The metadata link is stored in trusted app metadata, not user-editable metadata.
- No `public.users` password, `email_verified_at`, ID, role, permission or school ownership record was changed by the import.

The script remains available for controlled idempotent maintenance. It must never silently overwrite an Auth user with a conflicting email or legacy ID.

Supabase's secret API key is required only for trusted server-side lookups of private legacy users, roles, permissions and settings. Cloudflare Pages holds it as an encrypted server-side secret and sends it in the Supabase `apikey` header only. It is not a database copy or database migration.

## Supabase-managed session architecture

Supabase Auth owns account identity, refresh-token sessions, token rotation and session revocation. Cloudflare does not maintain a separate session database.

- The browser receives a `findursch_session` cookie with `HttpOnly`, `Secure`, `SameSite=Lax`, `Path=/` and bounded `Max-Age`.
- The cookie contents are AES-GCM authenticated-encrypted. The AES-256 key is derived using HKDF from the server-only `SUPABASE_SECRET_KEY`.
- Browser JavaScript cannot read the access or refresh tokens.
- Near expiry, the Pages Function refreshes through Supabase Auth, checks that the returned Supabase user ID matches the sealed session, then sets a newly encrypted cookie containing the rotated tokens.
- Each `/api/auth/me` call validates the Supabase Auth user and checks that the linked legacy `public.users` row remains active, then reloads current role and permission data.
- Logout requests Supabase Auth local-scope sign-out and always clears the browser cookie.
- Admin sessions follow the live `admin_session_timeout_minutes` setting with Laravel's 30-minute minimum (currently 120 minutes). Owner/parent sessions retain the existing 30-day remembered or 1-day non-remembered lifetime.
- No D1 or KV namespace is used for sessions. Password sign-in abuse controls are provided by Supabase Auth's configured protections; edge rate limiting and Turnstile can be added as a later hardening phase.
- Rotating the server-only secret invalidates existing encrypted session cookies, so affected users must sign in again.

## Authorization model

- Resolve the Supabase identity to `public.users` via the trusted `app_metadata.legacy_user_id`.
- Check the public user record still exists and has `status = 'active'` on every session validation.
- Load roles from `model_has_roles` + `roles`.
- Load permission names from `role_has_permissions` + `permissions`.
- Include direct user permission grants if they are added later.
- Treat `admin` role or any `admin.*` permission as admin access, matching Laravel.
- Do not accept `roles`, `permissions`, `user_type`, or legacy user IDs from browser request bodies or user metadata.
- Enforce record ownership and per-endpoint permissions for every write endpoint; authentication alone does not grant access to all records.

## Remaining migration work

- Test real password sign-in for representative admin and school-owner accounts in staging without changing the source passwords.
- Test cookie issuance, HttpOnly/Secure/SameSite attributes, session validation, access-token refresh, refresh-token rotation and logout revocation.
- Implement the administrator six-digit email verification flow before enabling the existing `require_two_step` setting.
- Port registration, password reset, profile/password changes and dashboard handoff endpoints.
- Port admin and school-owner read/write APIs with record ownership and per-permission checks.
- Port parent flows and all remaining public pages and business features.
- Migrate private document delivery, uploads, watermarking, exports, billing, notifications and background processing.
- Complete security and regression testing before directing production traffic to Cloudflare.
