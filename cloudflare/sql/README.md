# Supabase public API permissions

The Cloudflare staging public directory reads the existing FindURSCH PostgreSQL database through the Supabase REST API using the anon/publishable key only.

## Applied status — 26 November 2026

The SQL in public_api_anon_policies.sql has been applied to the existing production project findursch after explicit approval.

It:
- Grants the anon database role SELECT on a defined list of public school, taxonomy, curriculum, programme, facility, fee and media columns only.
- Adds RLS policies allowing only active + verified school records and their associated public listing data.
- Limits public media to image files in cover, badge and gallery collections.
- Does not grant access to users, roles, permissions, payments, subscriptions, inquiries, school documents, imports, exports, or admin tables.
- Does not insert, update or delete application records.

## Verification

A transaction using SET LOCAL ROLE anon returned:
- 3 active + verified schools.
- 4 active regions.
- 128 active districts.
- 6 active school types.
- 3 active curricula.

Privilege checks confirmed anon cannot select public.users, public.school_documents, user passwords, schools.owner_id, schools.registration_number or media.custom_properties. RLS remained enabled on each of the 11 tables covered by this change.

The SQL is safe to re-run because it drops and recreates only its specifically named policies; column grants are additive. Do not change or remove other application policies without a separate review.

## Cloudflare Pages environment

The findursch-app-staging Pages project has these Production environment values configured:

- SUPABASE_URL (plain text): https://gpsfttpeikatxszswljo.supabase.co
- SUPABASE_ANON_KEY (secret): active legacy anon key.
- PUBLIC_MEDIA_BASE_URL (plain text): https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev

The key is used only by Pages Functions. It is not bundled into JavaScript or returned to visitors. The service_role key is not used.
