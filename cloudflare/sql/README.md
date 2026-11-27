# Supabase public API permissions

The Cloudflare public website reads the existing FindURSCH PostgreSQL project through Supabase REST using the anon key and narrowly scoped row/column security.

## Applied public read access

The following read-only Supabase migrations were applied to the existing project findursch (gpsfttpeikatxszswljo):

- findursch_public_content_read_access_and_fee_privacy
- findursch_public_article_category_policy_dependency
- findursch_public_fallback_ad_read_access

They expose only published public content, active categories, approved school posts and videos linked to active + verified schools, published jobs and approved fallback banners. The scripts create RLS policies and column grants only; they do not add application tables or alter business records.

## School fee privacy

Public pages can show estimated schools.fees_from and schools.fees_to values. Anonymous users cannot select itemised school_fees.amount or the schools.application_fee field. The public_api_anon_policies.sql script explicitly revokes those old fee permissions and removes the previous public itemised-fee policy.

Do not restore public fee-table access. Exact fees belong in the protected school fees circular/document workflow.

## Public RLS coverage

- Schools: active + verified only.
- Regions, districts, school types and curricula: active only.
- School curricula, programmes, facilities and media: visible only when linked to active + verified schools; media is limited to cover, badge and gallery images.
- Articles: published, already within publication time and linked to an active category.
- School posts and videos: approved/published and linked to active + verified schools.
- Jobs: published, within publication time and not past deadline.
- Ads: approved fallback banners within their scheduled display window.

The public API does not query users, roles, permissions, payments, subscriptions, enquiries, private documents or moderation metadata.

## Deployment

The public website is currently served by Cloudflare Worker findursch at https://findursch.propipspath.workers.dev. The findursch-static Pages project is a separate static media-asset host.

The Worker uses:
- SUPABASE_URL (plain-text project URL)
- SUPABASE_ANON_KEY (server-side Worker secret)
- PUBLIC_MEDIA_BASE_URL (public R2 origin)

The public homepage and directory do not need SUPABASE_SECRET_KEY, a service-role key or any private account credential. Authentication and the login-rate-limit binding are separate migration work and must not be described as active until the Worker is configured and tested.
