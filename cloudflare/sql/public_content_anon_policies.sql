-- FindURSCH public homepage and editorial content access for Cloudflare Workers.
-- Only fields needed by the public website are granted. User ids, account data,
-- moderation metadata, billing and private documents are not exposed.

GRANT SELECT (id, name, slug, is_active)
  ON public.article_categories TO anon;
-- The RLS predicate reads is_active when it checks the category.
GRANT SELECT (is_active)
  ON public.article_categories TO anon;

GRANT SELECT (
  id, category_id, title, slug, excerpt, body, cover_image,
  read_time_minutes, status, is_featured, published_at,
  meta_title, meta_description, content_type
) ON public.articles TO anon;

DROP POLICY IF EXISTS findursch_public_anon_article_categories ON public.article_categories;
CREATE POLICY findursch_public_anon_article_categories ON public.article_categories
  FOR SELECT TO anon
  USING (is_active = true);

DROP POLICY IF EXISTS findursch_public_anon_articles ON public.articles;
CREATE POLICY findursch_public_anon_articles ON public.articles
  FOR SELECT TO anon
  USING (
    status = 'published'
    AND published_at IS NOT NULL
    AND published_at <= now()
    AND content_type IN ('news', 'blog', 'article')
    AND EXISTS (
      SELECT 1
      FROM public.article_categories ac
      WHERE ac.id = category_id
        AND ac.is_active = true
    )
  );

GRANT SELECT (
  id, school_id, type, title, description, venue, starts_at,
  ends_at, deadline, award, status, published_at, image_url, image_alt
) ON public.school_posts TO anon;

DROP POLICY IF EXISTS findursch_public_anon_school_posts ON public.school_posts;
CREATE POLICY findursch_public_anon_school_posts ON public.school_posts
  FOR SELECT TO anon
  USING (
    status IN ('approved', 'published')
    AND published_at IS NOT NULL
    AND published_at <= now()
    AND EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = school_id
        AND s.status = 'active'
        AND s.verification_status = 'verified'
    )
  );

GRANT SELECT (
  id, school_id, title, youtube_url, youtube_video_id, status
) ON public.school_videos TO anon;

DROP POLICY IF EXISTS findursch_public_anon_school_videos ON public.school_videos;
CREATE POLICY findursch_public_anon_school_videos ON public.school_videos
  FOR SELECT TO anon
  USING (
    status IN ('approved', 'published')
    AND EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = school_id
        AND s.status = 'active'
        AND s.verification_status = 'verified'
    )
  );

GRANT SELECT (
  id, slug, title, employer, category, location, employment_type,
  excerpt, description, salary, deadline, application_url,
  application_email, application_instructions, status, is_featured,
  published_at
) ON public.job_listings TO anon;

DROP POLICY IF EXISTS findursch_public_anon_job_listings ON public.job_listings;
CREATE POLICY findursch_public_anon_job_listings ON public.job_listings
  FOR SELECT TO anon
  USING (
    status = 'published'
    AND published_at IS NOT NULL
    AND published_at <= now()
    AND (deadline IS NULL OR deadline >= CURRENT_DATE)
  );

-- Public homepage advertising uses only approved fallback banners.
GRANT SELECT (
  id, placement_id, image_url, image_alt, destination_url,
  width, height, status, is_fallback, rotation_order, starts_at, ends_at
) ON public.site_ad_banners TO anon;

DROP POLICY IF EXISTS findursch_public_anon_fallback_ads ON public.site_ad_banners;
CREATE POLICY findursch_public_anon_fallback_ads ON public.site_ad_banners
  FOR SELECT TO anon
  USING (
    status = 'approved'
    AND is_fallback = true
    AND starts_at IS NOT NULL
    AND starts_at <= now()
    AND (ends_at IS NULL OR ends_at > now())
  );
