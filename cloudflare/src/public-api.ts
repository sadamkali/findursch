import { DatabaseUnavailableError, withDatabase } from "./db";

export interface PublicApiEnv {
  HYPERDRIVE?: { connectionString: string };
  PUBLIC_MEDIA_BASE_URL?: string;
}

type MediaRecord = {
  id: number;
  file_name: string;
  collection_name?: string;
  name?: string;
};

type SchoolRow = {
  id: number;
  name: string;
  slug: string;
  tagline: string | null;
  description: string | null;
  town: string | null;
  address: string | null;
  boarding_type: string;
  gender_policy: string;
  admissions_open: boolean;
  admission_deadline: string | null;
  fees_from: string | number | null;
  fees_to: string | number | null;
  rating_average: string | number;
  reviews_count: number;
  is_featured: boolean;
  district_name: string;
  district_slug: string;
  region_name: string;
  region_slug: string;
  school_type: string;
  school_type_slug: string;
  curricula: Array<{ id: number; name: string; abbreviation: string }>;
  cover_media: MediaRecord | null;
};

const json = (data: unknown, status = 200, cache = "no-store"): Response =>
  new Response(JSON.stringify(data), {
    status,
    headers: {
      "Content-Type": "application/json; charset=utf-8",
      "Cache-Control": cache,
      "X-Content-Type-Options": "nosniff",
    },
  });

const positiveInt = (value: string | null, fallback: number, max: number): number => {
  if (!value || !/^[0-9]+$/.test(value)) return fallback;
  const parsed = Number(value);
  return Number.isSafeInteger(parsed) && parsed > 0 ? Math.min(parsed, max) : fallback;
};

const optionalNumber = (value: string | null): number | undefined => {
  if (value === null || value.trim() === "") return undefined;
  const parsed = Number(value);
  return Number.isFinite(parsed) && parsed >= 0 && parsed <= 100_000_000
    ? parsed
    : undefined;
};

const mediaUrl = (env: PublicApiEnv, media: MediaRecord | null): string | null => {
  const base = env.PUBLIC_MEDIA_BASE_URL?.replace(/\/+$/, "");
  if (!base || !media) return null;
  return \`\${base}/\${media.id}/\${encodeURIComponent(media.file_name)}\`;
};

const publicSchoolVisibility = "s.status = 'active' AND s.verification_status = 'verified'";

export async function handlePublicApi(
  request: Request,
  env: PublicApiEnv,
): Promise<Response> {
  const url = new URL(request.url);
  const path = url.pathname.replace(/\/+$/, "") || "/";

  if (request.method !== "GET") {
    return json({ error: "method_not_allowed" }, 405);
  }

  if (path === "/api/health") {
    return json({
      service: "findursch-cloudflare-api",
      status: "ok",
      databaseConfigured: Boolean(env.HYPERDRIVE?.connectionString),
    });
  }

  if (path === "/api/v1/schools") {
    return listSchools(url, env);
  }

  if (path.startsWith("/api/v1/schools/")) {
    const slug = decodeURIComponent(path.slice("/api/v1/schools/".length));
    if (!slug || slug.includes("/")) return json({ error: "not_found" }, 404);
    return schoolDetail(slug, env);
  }

  if (path === "/api/v1/locations") {
    return locations(env);
  }

  return json({ error: "not_found" }, 404);
}

async function listSchools(url: URL, env: PublicApiEnv): Promise<Response> {
  const search = (url.searchParams.get("q") ?? "").trim().slice(0, 100);
  const type = url.searchParams.get("type");
  const district = url.searchParams.get("district");
  const region = url.searchParams.get("region");
  const page = positiveInt(url.searchParams.get("page"), 1, 10_000);
  const limit = positiveInt(url.searchParams.get("limit"), 12, 30);
  const feeMin = optionalNumber(url.searchParams.get("fee_min"));
  const feeMax = optionalNumber(url.searchParams.get("fee_max"));
  const admissionsOpen = url.searchParams.get("admissions_open");
  const boarding = url.searchParams.get("boarding");

  if (type && !/^[0-9]+$/.test(type)) return json({ error: "invalid_type" }, 400);
  if (district && !/^[0-9]+$/.test(district)) return json({ error: "invalid_district" }, 400);
  if (region && !/^[0-9]+$/.test(region)) return json({ error: "invalid_region" }, 400);
  if (admissionsOpen && !["true", "false", "1", "0"].includes(admissionsOpen)) {
    return json({ error: "invalid_admissions_open" }, 400);
  }

  const where = [publicSchoolVisibility];
  const values: unknown[] = [];
  const add = (value: unknown): string => {
    values.push(value);
    return \`$\${values.length}\`;
  };

  if (search) {
    const p = add(\`%\${search}%\`);
    where.push(\`(s.name ILIKE \${p} OR COALESCE(s.tagline, '') ILIKE \${p} OR COALESCE(s.description, '') ILIKE \${p} OR COALESCE(s.town, '') ILIKE \${p} OR COALESCE(s.address, '') ILIKE \${p})\`);
  }
  if (type) where.push(\`s.type_id = \${add(Number(type))}\`);
  if (district) where.push(\`s.district_id = \${add(Number(district))}\`);
  if (region) where.push(\`d.region_id = \${add(Number(region))}\`);
  if (feeMin !== undefined) where.push(\`s.fees_to >= \${add(feeMin)}\`);
  if (feeMax !== undefined) where.push(\`s.fees_from <= \${add(feeMax)}\`);
  if (admissionsOpen === "true" || admissionsOpen === "1") where.push("s.admissions_open = true");
  if (boarding) {
    if (!["day", "boarding", "both"].includes(boarding)) return json({ error: "invalid_boarding" }, 400);
    where.push(\`s.boarding_type = \${add(boarding)}\`);
  }

  const sort = url.searchParams.get("sort") ?? "newest";
  const sortSql: Record<string, string> = {
    newest: "created_at DESC NULLS LAST, id DESC",
    featured: "is_featured DESC, featured_until DESC NULLS LAST, created_at DESC NULLS LAST",
    name: "name ASC",
    rating: "rating_average DESC, reviews_count DESC",
    fee_low: "CASE WHEN fees_from IS NULL OR fees_from <= 0 THEN 1 ELSE 0 END, fees_from ASC",
    fee_high: "CASE WHEN fees_from IS NULL OR fees_from <= 0 THEN 1 ELSE 0 END, fees_from DESC",
  };
  const orderBy = sortSql[sort] ?? sortSql.newest;
  const limitParam = add(limit);
  const offsetParam = add((page - 1) * limit);

  try {
    const result = await withDatabase(env, (client) =>
      client.query<{ total: number; data: SchoolRow[] }>(
        \`WITH filtered AS (
          SELECT
            s.id, s.name, s.slug, s.tagline, s.description, s.town, s.address,
            s.boarding_type, s.gender_policy, s.admissions_open, s.admission_deadline,
            s.fees_from, s.fees_to, s.rating_average, s.reviews_count, s.is_featured,
            s.featured_until, s.created_at,
            d.name AS district_name, d.slug AS district_slug,
            r.name AS region_name, r.slug AS region_slug,
            st.name AS school_type, st.slug AS school_type_slug,
            COALESCE((
              SELECT json_agg(json_build_object(
                'id', c.id, 'name', c.name, 'abbreviation', c.abbreviation
              ) ORDER BY c.name)
              FROM school_curriculum sc
              JOIN curricula c ON c.id = sc.curriculum_id AND c.is_active = true
              WHERE sc.school_id = s.id
            ), '[]'::json) AS curricula,
            (
              SELECT json_build_object('id', m.id, 'file_name', m.file_name)
              FROM media m
              WHERE m.model_type = 'App\\\\Models\\\\School'
                AND m.model_id = s.id
                AND m.collection_name = 'cover'
                AND m.mime_type LIKE 'image/%'
              ORDER BY m.order_column NULLS LAST, m.id
              LIMIT 1
            ) AS cover_media
          FROM schools s
          JOIN districts d ON d.id = s.district_id
          JOIN regions r ON r.id = d.region_id
          JOIN school_types st ON st.id = s.type_id
          WHERE \${where.join(" AND ")}
        )
        SELECT
          (SELECT COUNT(*)::int FROM filtered) AS total,
          COALESCE((
            SELECT json_agg(to_jsonb(page_row))
            FROM (
              SELECT * FROM filtered
              ORDER BY \${orderBy}
              LIMIT \${limitParam} OFFSET \${offsetParam}
            ) AS page_row
          ), '[]'::json) AS data\`,
        values,
      ),
    );

    const row = result.rows[0] ?? { total: 0, data: [] };
    const data = (row.data ?? []).map((school) => ({
      ...school,
      image_url: mediaUrl(env, school.cover_media),
      cover_media: undefined,
    }));

    return json(
      {
        data,
        meta: {
          page,
          per_page: limit,
          total: Number(row.total ?? 0),
          total_pages: Math.ceil(Number(row.total ?? 0) / limit),
        },
      },
      200,
      "public, max-age=30, s-maxage=60, stale-while-revalidate=300",
    );
  } catch (error) {
    if (error instanceof DatabaseUnavailableError) {
      return json({ error: "database_not_configured" }, 503);
    }
    console.error("Public school directory query failed.");
    return json({ error: "directory_unavailable" }, 503);
  }
}

async function schoolDetail(slug: string, env: PublicApiEnv): Promise<Response> {
  try {
    const result = await withDatabase(env, (client) =>
      client.query(
        \`SELECT
          s.id, s.name, s.slug, s.tagline, s.description, s.founded_year,
          s.address, s.town, s.po_box, s.nearest_landmark, s.maps_url,
          s.phone, s.email, s.website, s.whatsapp, s.facebook_url, s.instagram_url,
          s.ownership, s.religious_affiliation, s.boarding_type, s.gender_policy,
          s.student_count_approx, s.admissions_open, s.admission_classes,
          s.admission_deadline, s.application_fee, s.admissions_contact,
          s.admission_requirements, s.how_to_apply, s.fees_from, s.fees_to,
          s.rating_average, s.reviews_count, s.meta_title, s.meta_description,
          json_build_object('id', d.id, 'name', d.name, 'slug', d.slug) AS district,
          json_build_object('id', r.id, 'name', r.name, 'slug', r.slug) AS region,
          json_build_object('id', st.id, 'name', st.name, 'slug', st.slug) AS type,
          COALESCE((
            SELECT json_agg(json_build_object(
              'id', c.id, 'name', c.name, 'abbreviation', c.abbreviation
            ) ORDER BY c.name)
            FROM school_curriculum sc
            JOIN curricula c ON c.id = sc.curriculum_id AND c.is_active = true
            WHERE sc.school_id = s.id
          ), '[]'::json) AS curricula,
          COALESCE((
            SELECT json_agg(json_build_object(
              'id', p.id, 'name', p.name, 'level', p.level,
              'duration', p.duration, 'description', p.description
            ) ORDER BY p.name)
            FROM school_programs p
            WHERE p.school_id = s.id AND p.is_active = true
          ), '[]'::json) AS programs,
          COALESCE((
            SELECT json_agg(json_build_object(
              'id', f.id, 'name', f.name, 'category', f.category, 'notes', sf.notes
            ) ORDER BY f.category, f.name)
            FROM school_facilities sf
            JOIN facilities f ON f.id = sf.facility_id AND f.is_active = true
            WHERE sf.school_id = s.id
          ), '[]'::json) AS facilities,
          COALESCE((
            SELECT json_agg(json_build_object(
              'id', fee.id, 'category', fee.category, 'label', fee.label,
              'amount', fee.amount, 'currency', fee.currency,
              'period', fee.period, 'academic_year', fee.academic_year,
              'notes', fee.notes
            ) ORDER BY fee.amount)
            FROM school_fees fee
            WHERE fee.school_id = s.id AND fee.is_active = true
          ), '[]'::json) AS fees,
          COALESCE((
            SELECT json_agg(json_build_object(
              'id', m.id, 'file_name', m.file_name, 'name', m.name,
              'collection_name', m.collection_name
            ) ORDER BY m.order_column NULLS LAST, m.id)
            FROM media m
            WHERE m.model_type = 'App\\\\Models\\\\School'
              AND m.model_id = s.id
              AND m.collection_name IN ('cover', 'badge', 'gallery')
              AND m.mime_type LIKE 'image/%'
          ), '[]'::json) AS media
        FROM schools s
        JOIN districts d ON d.id = s.district_id
        JOIN regions r ON r.id = d.region_id
        JOIN school_types st ON st.id = s.type_id
        WHERE s.slug = $1
          AND s.status = 'active'
          AND s.verification_status = 'verified'
        LIMIT 1\`,
        [slug],
      ),
    );

    const school = result.rows[0] as Record<string, unknown> | undefined;
    if (!school) return json({ error: "school_not_found" }, 404);

    const media = (school.media as MediaRecord[] | null) ?? [];
    const gallery = media.map((item) => ({
      id: item.id,
      name: item.name,
      collection_name: item.collection_name,
      url: mediaUrl(env, item),
    }));

    return json(
      { data: { ...school, media: gallery } },
      200,
      "public, max-age=30, s-maxage=60, stale-while-revalidate=300",
    );
  } catch (error) {
    if (error instanceof DatabaseUnavailableError) {
      return json({ error: "database_not_configured" }, 503);
    }
    console.error("Public school profile query failed.");
    return json({ error: "school_unavailable" }, 503);
  }
}

async function locations(env: PublicApiEnv): Promise<Response> {
  try {
    const result = await withDatabase(env, (client) =>
      client.query(
        \`SELECT
          COALESCE((
            SELECT json_agg(json_build_object('id', id, 'name', name, 'slug', slug) ORDER BY name)
            FROM regions WHERE is_active = true
          ), '[]'::json) AS regions,
          COALESCE((
            SELECT json_agg(json_build_object(
              'id', d.id, 'name', d.name, 'slug', d.slug,
              'region_id', d.region_id, 'region_name', r.name
            ) ORDER BY d.name)
            FROM districts d
            JOIN regions r ON r.id = d.region_id AND r.is_active = true
            WHERE d.is_active = true
          ), '[]'::json) AS districts,
          COALESCE((
            SELECT json_agg(json_build_object('id', id, 'name', name, 'slug', slug) ORDER BY "order", name)
            FROM school_types WHERE is_active = true
          ), '[]'::json) AS school_types,
          COALESCE((
            SELECT json_agg(json_build_object('id', id, 'name', name, 'slug', slug) ORDER BY name)
            FROM curricula WHERE is_active = true
          ), '[]'::json) AS curricula\`,
      ),
    );

    return json(
      { data: result.rows[0] ?? { regions: [], districts: [], school_types: [], curricula: [] } },
      200,
      "public, max-age=300, s-maxage=600, stale-while-revalidate=3600",
    );
  } catch (error) {
    if (error instanceof DatabaseUnavailableError) {
      return json({ error: "database_not_configured" }, 503);
    }
    console.error("Public locations query failed.");
    return json({ error: "locations_unavailable" }, 503);
  }
}
