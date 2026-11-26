-- FindURSCH Cloudflare public API read-only database role.
-- Run once in Supabase SQL Editor after reviewing the permissions below.
-- Replace the password placeholder with a long random password. Never commit
-- a real password to GitHub or paste it into chat.
--
-- This role deliberately has no BYPASSRLS, SUPERUSER, CREATE, UPDATE,
-- INSERT, DELETE or access to private school documents, user data or admin data.

CREATE ROLE findursch_cloudflare_ro
  LOGIN
  PASSWORD 'REPLACE_WITH_A_LONG_RANDOM_PASSWORD'
  NOSUPERUSER
  NOCREATEDB
  NOCREATEROLE
  NOINHERIT;

GRANT CONNECT ON DATABASE postgres TO findursch_cloudflare_ro;
GRANT USAGE ON SCHEMA public TO findursch_cloudflare_ro;

GRANT SELECT (
  id, name, slug, tagline, description, founded_year, type_id, district_id,
  town, address, po_box, nearest_landmark, maps_url, phone, email, website,
  whatsapp, facebook_url, instagram_url, ownership, religious_affiliation,
  boarding_type, gender_policy, student_count_approx, admissions_open,
  admission_classes, admission_deadline, application_fee, admissions_contact,
  admission_requirements, how_to_apply, fees_from, fees_to, rating_average,
  reviews_count, is_featured, featured_until, meta_title, meta_description,
  created_at, status, verification_status
) ON public.schools TO findursch_cloudflare_ro;

GRANT SELECT (id, name, slug, region_id, is_active) ON public.districts TO findursch_cloudflare_ro;
GRANT SELECT (id, name, slug, is_active) ON public.regions TO findursch_cloudflare_ro;
GRANT SELECT (id, name, slug, "order", is_active) ON public.school_types TO findursch_cloudflare_ro;
GRANT SELECT (id, name, slug, abbreviation, description, is_active) ON public.curricula TO findursch_cloudflare_ro;
GRANT SELECT (school_id, curriculum_id) ON public.school_curriculum TO findursch_cloudflare_ro;
GRANT SELECT (id, model_type, model_id, collection_name, name, file_name, mime_type, order_column) ON public.media TO findursch_cloudflare_ro;
GRANT SELECT (id, school_id, name, level, duration, description, is_active) ON public.school_programs TO findursch_cloudflare_ro;
GRANT SELECT (school_id, facility_id, notes) ON public.school_facilities TO findursch_cloudflare_ro;
GRANT SELECT (id, name, category, is_active) ON public.facilities TO findursch_cloudflare_ro;
GRANT SELECT (id, school_id, category, label, amount, currency, period, academic_year, notes, is_active) ON public.school_fees TO findursch_cloudflare_ro;

DROP POLICY IF EXISTS findursch_worker_public_schools ON public.schools;
CREATE POLICY findursch_worker_public_schools ON public.schools
  FOR SELECT TO findursch_cloudflare_ro
  USING (status = 'active' AND verification_status = 'verified');

DROP POLICY IF EXISTS findursch_worker_public_districts ON public.districts;
CREATE POLICY findursch_worker_public_districts ON public.districts
  FOR SELECT TO findursch_cloudflare_ro USING (true);

DROP POLICY IF EXISTS findursch_worker_public_regions ON public.regions;
CREATE POLICY findursch_worker_public_regions ON public.regions
  FOR SELECT TO findursch_cloudflare_ro USING (true);

DROP POLICY IF EXISTS findursch_worker_public_school_types ON public.school_types;
CREATE POLICY findursch_worker_public_school_types ON public.school_types
  FOR SELECT TO findursch_cloudflare_ro USING (true);

DROP POLICY IF EXISTS findursch_worker_public_curricula ON public.curricula;
CREATE POLICY findursch_worker_public_curricula ON public.curricula
  FOR SELECT TO findursch_cloudflare_ro USING (is_active = true);

DROP POLICY IF EXISTS findursch_worker_public_school_curriculum ON public.school_curriculum;
CREATE POLICY findursch_worker_public_school_curriculum ON public.school_curriculum
  FOR SELECT TO findursch_cloudflare_ro
  USING (EXISTS (
    SELECT 1 FROM public.schools s
    WHERE s.id = school_id AND s.status = 'active' AND s.verification_status = 'verified'
  ));

DROP POLICY IF EXISTS findursch_worker_public_school_programs ON public.school_programs;
CREATE POLICY findursch_worker_public_school_programs ON public.school_programs
  FOR SELECT TO findursch_cloudflare_ro
  USING (is_active = true AND EXISTS (
    SELECT 1 FROM public.schools s
    WHERE s.id = school_id AND s.status = 'active' AND s.verification_status = 'verified'
  ));

DROP POLICY IF EXISTS findursch_worker_public_facilities ON public.facilities;
CREATE POLICY findursch_worker_public_facilities ON public.facilities
  FOR SELECT TO findursch_cloudflare_ro USING (is_active = true);

DROP POLICY IF EXISTS findursch_worker_public_school_facilities ON public.school_facilities;
CREATE POLICY findursch_worker_public_school_facilities ON public.school_facilities
  FOR SELECT TO findursch_cloudflare_ro
  USING (
    EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = school_id AND s.status = 'active' AND s.verification_status = 'verified'
    )
    AND EXISTS (
      SELECT 1 FROM public.facilities f
      WHERE f.id = facility_id AND f.is_active = true
    )
  );

DROP POLICY IF EXISTS findursch_worker_public_school_fees ON public.school_fees;
CREATE POLICY findursch_worker_public_school_fees ON public.school_fees
  FOR SELECT TO findursch_cloudflare_ro
  USING (is_active = true AND EXISTS (
    SELECT 1 FROM public.schools s
    WHERE s.id = school_id AND s.status = 'active' AND s.verification_status = 'verified'
  ));

DROP POLICY IF EXISTS findursch_worker_public_school_media ON public.media;
CREATE POLICY findursch_worker_public_school_media ON public.media
  FOR SELECT TO findursch_cloudflare_ro
  USING (
    model_type LIKE '%School'
    AND collection_name IN ('cover', 'badge', 'gallery')
    AND mime_type LIKE 'image/%'
    AND EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = model_id AND s.status = 'active' AND s.verification_status = 'verified'
    )
  );
