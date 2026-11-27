-- FindURSCH public school directory read access for Cloudflare Workers.
-- Re-running is safe: this grants only selected fields needed for public school
-- search, profile display and cards. Never use a service_role key in a Worker.

GRANT USAGE ON SCHEMA public TO anon;

GRANT SELECT (
  id, name, slug, tagline, description, founded_year, type_id, district_id,
  town, address, po_box, nearest_landmark, maps_url, phone, email, website,
  whatsapp, facebook_url, instagram_url, ownership, religious_affiliation,
  boarding_type, gender_policy, student_count_approx, admissions_open,
  admission_classes, admission_deadline, admissions_contact,
  admission_requirements, how_to_apply, fees_from, fees_to, rating_average,
  reviews_count, is_featured, featured_until, meta_title, meta_description,
  created_at, status, verification_status
) ON public.schools TO anon;

-- Exact application fees are not public. Only estimated school fee ranges
-- from fees_from/fees_to may be displayed on public pages.
REVOKE SELECT (application_fee) ON public.schools FROM anon;

GRANT SELECT (id, name, slug, region_id, is_active)
  ON public.districts TO anon;
GRANT SELECT (id, name, slug, is_active)
  ON public.regions TO anon;
GRANT SELECT (id, name, slug, "order", is_active)
  ON public.school_types TO anon;
GRANT SELECT (id, name, slug, abbreviation, is_active)
  ON public.curricula TO anon;
GRANT SELECT (school_id, curriculum_id)
  ON public.school_curriculum TO anon;
GRANT SELECT (
  id, model_type, model_id, collection_name, name, file_name,
  mime_type, order_column
) ON public.media TO anon;
GRANT SELECT (id, school_id, name, level, duration, description, is_active)
  ON public.school_programs TO anon;
GRANT SELECT (school_id, facility_id, notes)
  ON public.school_facilities TO anon;
GRANT SELECT (id, name, category, is_active)
  ON public.facilities TO anon;

-- Itemised fee records, amounts, periods and labels remain private.
REVOKE SELECT (
  id, school_id, category, label, amount, currency, period,
  academic_year, notes, is_active
) ON public.school_fees FROM anon;

DROP POLICY IF EXISTS findursch_public_anon_schools ON public.schools;
CREATE POLICY findursch_public_anon_schools ON public.schools
  FOR SELECT TO anon
  USING (status = 'active' AND verification_status = 'verified');

DROP POLICY IF EXISTS findursch_public_anon_districts ON public.districts;
CREATE POLICY findursch_public_anon_districts ON public.districts
  FOR SELECT TO anon USING (is_active = true);

DROP POLICY IF EXISTS findursch_public_anon_regions ON public.regions;
CREATE POLICY findursch_public_anon_regions ON public.regions
  FOR SELECT TO anon USING (is_active = true);

DROP POLICY IF EXISTS findursch_public_anon_school_types ON public.school_types;
CREATE POLICY findursch_public_anon_school_types ON public.school_types
  FOR SELECT TO anon USING (is_active = true);

DROP POLICY IF EXISTS findursch_public_anon_curricula ON public.curricula;
CREATE POLICY findursch_public_anon_curricula ON public.curricula
  FOR SELECT TO anon USING (is_active = true);

DROP POLICY IF EXISTS findursch_public_anon_school_curriculum ON public.school_curriculum;
CREATE POLICY findursch_public_anon_school_curriculum ON public.school_curriculum
  FOR SELECT TO anon
  USING (
    EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = school_id
        AND s.status = 'active'
        AND s.verification_status = 'verified'
    )
  );

DROP POLICY IF EXISTS findursch_public_anon_school_programs ON public.school_programs;
CREATE POLICY findursch_public_anon_school_programs ON public.school_programs
  FOR SELECT TO anon
  USING (
    is_active = true
    AND EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = school_id
        AND s.status = 'active'
        AND s.verification_status = 'verified'
    )
  );

DROP POLICY IF EXISTS findursch_public_anon_facilities ON public.facilities;
CREATE POLICY findursch_public_anon_facilities ON public.facilities
  FOR SELECT TO anon USING (is_active = true);

DROP POLICY IF EXISTS findursch_public_anon_school_facilities ON public.school_facilities;
CREATE POLICY findursch_public_anon_school_facilities ON public.school_facilities
  FOR SELECT TO anon
  USING (
    EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = school_id
        AND s.status = 'active'
        AND s.verification_status = 'verified'
    )
    AND EXISTS (
      SELECT 1 FROM public.facilities f
      WHERE f.id = facility_id AND f.is_active = true
    )
  );

-- Explicitly remove the previous public policy. This script must never make
-- exact fee amounts readable by anonymous visitors.
DROP POLICY IF EXISTS findursch_public_anon_school_fees ON public.school_fees;

DROP POLICY IF EXISTS findursch_public_anon_school_media ON public.media;
CREATE POLICY findursch_public_anon_school_media ON public.media
  FOR SELECT TO anon
  USING (
    model_type LIKE '%School'
    AND collection_name IN ('cover', 'badge', 'gallery')
    AND mime_type LIKE 'image/%'
    AND EXISTS (
      SELECT 1 FROM public.schools s
      WHERE s.id = model_id
        AND s.status = 'active'
        AND s.verification_status = 'verified'
    )
  );
