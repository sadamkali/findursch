# Supabase public API permissions

The Cloudflare staging public directory reads the existing FindURSCH PostgreSQL database through the Supabase REST API using the anon/publishable key only.

## Before running this SQL

This file changes permissions and RLS policies in the live Supabase project. Do not run it until you have reviewed the exact grants below and approved enabling anonymous read-only access for the public school directory.

The script:
- Grants the anon database role SELECT on a defined list of public school, taxonomy, curriculum, programme, facility, fee and media columns only.
- Adds RLS policies allowing only active + verified school records and their associated public listing data.
- Limits public media to image files in cover, badge and gallery collections.
- Does not grant access to users, roles, permissions, payments, subscriptions, inquiries, school documents, imports, exports, or admin tables.
- Does not insert, update or delete application records.

## Apply

1. Open the findursch project in Supabase and use SQL Editor.
2. Review public_api_anon_policies.sql.
3. Run it only after approval. It is safe to re-run because the policy names are dropped and recreated; the column grants are additive.
4. Confirm the anon role can SELECT only the intended public columns and that hidden/pending schools remain invisible.

## Cloudflare Pages environment

Set the following in the findursch-app-staging Pages project, under Settings -> Variables and Secrets -> Production:

- SUPABASE_URL (plain text): https://gpsfttpeikatxszswljo.supabase.co
- SUPABASE_ANON_KEY (secret): the active legacy anon key from the Supabase project's API Keys page. Do not use service_role or a secret key.
- PUBLIC_MEDIA_BASE_URL (plain text): https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev

The key is used only by Pages Functions. It is not bundled into JavaScript or returned to visitors. Keep service-role keys out of Cloudflare Pages entirely.
