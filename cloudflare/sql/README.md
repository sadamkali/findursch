# Supabase to Cloudflare Hyperdrive (read-only staging role)

This SQL prepares a least-privilege database login for the Cloudflare public API.

## Permissions

- Creates findursch_cloudflare_ro without superuser, database creation, role creation or RLS bypass.
- Grants SELECT only on columns used by the public directory and public school profiles.
- Adds RLS SELECT policies for verified, active schools and their public related data.
- Does not grant access to users, roles, permissions, inquiries, payments, subscriptions, school documents, imports, exports or admin tables.
- Does not change, delete, or migrate application records.

## Apply only after approval

1. Open Supabase project findursch and go to SQL Editor.
2. Paste create_public_api_role.sql.
3. Replace REPLACE_WITH_A_LONG_RANDOM_PASSWORD with a new random password. Do not commit it or paste it in chat.
4. Review the SQL and run it once. It requires a database role that can create roles and manage grants/policies.
5. Keep the password private; use the role name findursch_cloudflare_ro when creating Hyperdrive.

The script is a first-time setup script and is not run by this repository or by Cloudflare deployments.

## Hyperdrive setup

After the database role exists, create a Hyperdrive configuration for the Supabase Direct PostgreSQL connection, not the Supavisor pooler. Use the host, port and database shown by Supabase Direct connection details, the findursch_cloudflare_ro user, the password you set, and required TLS/SSL.

Bind the Hyperdrive configuration to the Cloudflare Pages project as HYPERDRIVE for the production environment. Do not put database credentials in build variables or source files.
