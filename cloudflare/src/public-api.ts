export interface PublicApiEnv {
  SUPABASE_URL?: string;
  SUPABASE_ANON_KEY?: string;
  SUPABASE_SECRET_KEY?: string;
  LOGIN_RATE_LIMIT_HMAC_KEY?: string;
  LOGIN_RATE_LIMITER?: unknown;
  PUBLIC_MEDIA_BASE_URL?: string;
}

type JsonRecord = Record<string, unknown>;
type RestResult<T> = { rows: T[]; count: number | null };

const DEFAULT_SUPABASE_URL = "https://gpsfttpeikatxszswljo.supabase.co";
const DEFAULT_MEDIA_URL = "https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev";
const PUBLIC_SCHOOL_FILTER = {
  status: "eq.active",
  verification_status: "eq.verified",
};
const PUBLIC_CACHE = "public, max-age=30, s-maxage=60, stale-while-revalidate=300";

function responseJson(data: unknown, status = 200, cacheControl = "no-store"): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      "Content-Type": "application/json; charset=utf-8",
      "Cache-Control": cacheControl,
      "X-Content-Type-Options": "nosniff",
    },
  });
}

function configured(env: PublicApiEnv): boolean {
  return Boolean((env.SUPABASE_URL || DEFAULT_SUPABASE_URL) && env.SUPABASE_ANON_KEY);
}

function supabaseUrl(env: PublicApiEnv): string {
  return (env.SUPABASE_URL || DEFAULT_SUPABASE_URL).replace(/\/+$/, "");
}

async function restGet<T>(
  env: PublicApiEnv,
  table: string,
  params: URLSearchParams,
  exactCount = false,
): Promise<RestResult<T>> {
  const key = env.SUPABASE_ANON_KEY;
  if (!key) throw new Error("Supabase public API key is not configured.");

  const queryString = params.toString();
  const url = supabaseUrl(env) + "/rest/v1/" + table + (queryString ? "?" + queryString : "");
  const headers = new Headers({
    apikey: key,
    Authorization: "Bearer " + key,
    Accept: "application/json",
  });
  if (exactCount) headers.set("Prefer", "count=exact");

  const response = await fetch(url, {
    method: "GET",
    headers,
    signal: AbortSignal.timeout(7_000),
  });

  if (!response.ok) {
    const errorText = await response.text().catch(() => "");
    console.error("Supabase REST request failed", {
      table,
      status: response.status,
      detail: errorText.slice(0, 400),
    });
    throw new Error("Supabase REST request failed with status " + response.status);
  }

  const rows = (await response.json()) as T[];
  const contentRange = response.headers.get("Content-Range");
  const totalPart = contentRange?.split("/")[1];
  const count = totalPart && totalPart !== "*" ? Number(totalPart) : null;

  return {
    rows: Array.isArray(rows) ? rows : [],
    count: Number.isFinite(count) ? count : null,
  };
}

function positiveInt(value: string | null, fallback: number, max: number): number {
  if (!value || !/^[0-9]+$/.test(value)) return fallback;
  const parsed = Number(value);
  return Number.isSafeInteger(parsed) && parsed > 0 ? Math.min(parsed, max) : fallback;
}

function optionalAmount(value: string | null): number | undefined {
  if (value === null || value.trim() === "") return undefined;
  const amount = Number(value);
  return Number.isFinite(amount) && amount >= 0 && amount <= 100_000_000
    ? amount
    : undefined;
}

function cleanSearch(value: string): string {
  return value
    .replace(/[(),*\\]/g, " ")
    .replace(/[^\p{L}\p{N}\s.'-]/gu, " ")
    .replace(/\s+/g, " ")
    .trim()
    .slice(0, 80);
}

function mediaUrl(env: PublicApiEnv, media: JsonRecord | null | undefined): string | null {
  if (!media || typeof media.id !== "number" || typeof media.file_name !== "string") return null;
  const base = (env.PUBLIC_MEDIA_BASE_URL || DEFAULT_MEDIA_URL).replace(/\/+$/, "");
  return base + "/" + media.id + "/" + encodeURIComponent(media.file_name);
}

function mediaQuery(ids: number[]): URLSearchParams {
  const params = new URLSearchParams();
  params.set("select", "id,model_id,collection_name,name,file_name,mime_type,order_column");
  params.set("model_type", "like.*School");
  params.set("model_id", "in.(" + ids.join(",") + ")");
  params.set("collection_name", "in.(cover,badge,gallery)");
  params.set("mime_type", "like.image/*");
  params.set("order", "order_column.asc.nullslast,id.asc");
  return params;
}

async function attachCoverImages<T extends JsonRecord>(
  env: PublicApiEnv,
  schools: T[],
): Promise<Array<T & { image_url: string | null }>> {
  if (!schools.length) return [];
  const ids = schools
    .map((school) => Number(school.id))
    .filter((id) => Number.isSafeInteger(id) && id > 0);
  if (!ids.length) return schools.map((school) => ({ ...school, image_url: null }));

  const media = await restGet<JsonRecord>(env, "media", mediaQuery(ids));
  const coverBySchool = new Map<number, JsonRecord>();

  for (const item of media.rows) {
    const id = Number(item.model_id);
    if (item.collection_name === "cover" && !coverBySchool.has(id)) {
      coverBySchool.set(id, item);
    }
  }

  return schools.map((school) => ({
    ...school,
    image_url: mediaUrl(env, coverBySchool.get(Number(school.id))),
  }));
}

function schoolForPublicPage(school: JsonRecord & { image_url?: string | null }): JsonRecord {
  const district = (school.districts as JsonRecord | null) ?? null;
  const type = (school.school_types as JsonRecord | null) ?? null;
  const curricula = Array.isArray(school.school_curriculum)
    ? (school.school_curriculum as JsonRecord[])
        .map((item) => item.curricula)
        .filter(Boolean)
    : [];
  return {
    ...school,
    district_name: district?.name ?? null,
    district_slug: district?.slug ?? null,
    region_name: ((district?.regions as JsonRecord | null)?.name) ?? null,
    region_slug: ((district?.regions as JsonRecord | null)?.slug) ?? null,
    school_type: type?.name ?? null,
    school_type_slug: type?.slug ?? null,
    curricula,
  };
}

async function optionalRows(
  env: PublicApiEnv,
  table: string,
  params: URLSearchParams,
  exactCount = false,
): Promise<RestResult<JsonRecord>> {
  try {
    return await restGet<JsonRecord>(env, table, params, exactCount);
  } catch (error) {
    console.warn("Optional public homepage content is unavailable", {
      table,
      message: error instanceof Error ? error.message : "unknown error",
    });
    return { rows: [], count: 0 };
  }
}

export async function handlePublicApi(
  request: Request,
  env: PublicApiEnv,
): Promise<Response> {
  const url = new URL(request.url);
  const path = url.pathname.replace(/\/+$/, "") || "/";

  if (request.method !== "GET") {
    return responseJson({ error: "method_not_allowed" }, 405);
  }

  if (path === "/api/health") {
    return responseJson({
      service: "findursch-cloudflare-api",
      status: "ok",
      databaseConfigured: configured(env),
      dataSource: "supabase-rest",
      sessionStrategy: env.SUPABASE_SECRET_KEY ? "supabase-auth-encrypted-cookie" : "not-configured",
      loginRateLimitStrategy: env.LOGIN_RATE_LIMIT_HMAC_KEY && env.LOGIN_RATE_LIMITER ? "cloudflare-durable-object-sliding-window-10-per-60s" : "not-configured",
      loginRateLimitConfigured: Boolean(env.LOGIN_RATE_LIMIT_HMAC_KEY && env.LOGIN_RATE_LIMITER),
      authSecretsConfigured: Boolean(env.SUPABASE_SECRET_KEY),
    });
  }

  if (path === "/api/v1/homepage") return homepage(env);
  if (path === "/api/v1/schools") return listSchools(url, env);
  if (path.startsWith("/api/v1/schools/")) {
    const slug = decodeURIComponent(path.slice("/api/v1/schools/".length));
    if (!slug || slug.includes("/")) return responseJson({ error: "not_found" }, 404);
    return schoolDetail(slug, env);
  }
  if (path === "/api/v1/locations") return locations(env);

  return responseJson({ error: "not_found" }, 404);
}

async function homepage(env: PublicApiEnv): Promise<Response> {
  if (!configured(env)) {
    return responseJson({ error: "database_not_configured" }, 503);
  }

  const now = new Date().toISOString();
  const today = now.slice(0, 10);
  const publicSchoolsParams = new URLSearchParams({
    select: [
      "id,name,slug,tagline,description,town,boarding_type,gender_policy",
      "admissions_open,fees_from,fees_to,rating_average,reviews_count",
      "is_featured,featured_until,created_at,type_id",
      "districts!inner(id,name,slug,region_id,regions(id,name,slug))",
      "school_types(id,name,slug)",
      "school_curriculum(curricula(id,name,abbreviation))",
    ].join(","),
    status: PUBLIC_SCHOOL_FILTER.status,
    verification_status: PUBLIC_SCHOOL_FILTER.verification_status,
    order: "is_featured.desc,featured_until.desc.nullslast,created_at.desc",
    limit: "12",
  });
  const schoolTypesParams = new URLSearchParams({
    select: "id,name,slug,order",
    is_active: "eq.true",
    order: "order.asc,name.asc",
  });
  const newsParams = new URLSearchParams({
    select: "id,title,slug,excerpt,cover_image,published_at,is_featured,content_type,category:article_categories!inner(id,name,slug)",
    status: "eq.published",
    content_type: "eq.news",
    published_at: "lte." + now,
    order: "is_featured.desc,published_at.desc",
    limit: "4",
  });
  const insightParams = new URLSearchParams({
    select: "id,title,slug,excerpt,body,cover_image,read_time_minutes,published_at,is_featured,content_type,category:article_categories!inner(id,name,slug)",
    status: "eq.published",
    content_type: "in.(blog,article)",
    published_at: "lte." + now,
    order: "is_featured.desc,published_at.desc",
    limit: "4",
  });
  const partnerNewsParams = new URLSearchParams({
    select: "id,school_id,title,description,image_url,image_alt,published_at,school:schools!inner(id,name,slug)",
    type: "eq.news",
    status: "in.(approved,published)",
    published_at: "lte." + now,
    order: "published_at.desc",
    limit: "1",
  });
  const eventParams = new URLSearchParams({
    select: "id,school_id,title,description,venue,starts_at,ends_at,published_at,school:schools!inner(id,name,slug)",
    type: "eq.event",
    status: "in.(approved,published)",
    published_at: "lte." + now,
    starts_at: "gte." + now,
    order: "starts_at.asc",
    limit: "3",
  });
  const scholarshipParams = new URLSearchParams({
    select: "id",
    type: "eq.bursary",
    status: "in.(approved,published)",
    published_at: "lte." + now,
    or: "(deadline.is.null,deadline.gte." + today + ")",
    limit: "1",
  });
  const jobParams = new URLSearchParams({
    select: "id,slug,title,employer,location,category,employment_type,excerpt,salary,deadline,application_url,is_featured,published_at",
    status: "eq.published",
    published_at: "lte." + now,
    or: "(deadline.is.null,deadline.gte." + today + ")",
    order: "is_featured.desc,published_at.desc",
    limit: "3",
  });
  const videoParams = new URLSearchParams({
    select: "id,school_id,title,youtube_url,youtube_video_id,school:schools!inner(id,name,slug)",
    status: "in.(approved,published)",
    order: "id.desc",
    limit: "4",
  });
  const adParams = new URLSearchParams({
    select: "id,placement_id,image_url,image_alt,destination_url,width,height,rotation_order",
    status: "eq.approved",
    is_fallback: "eq.true",
    starts_at: "lte." + now,
    or: "(ends_at.is.null,ends_at.gt." + now + ")",
    order: "rotation_order.asc,id.asc",
    limit: "12",
  });

  try {
    const [schoolResult, typeResult, newsResult, insightResult, partnerNewsResult, eventsResult, scholarshipResult, jobsResult, videoResult, adsResult] =
      await Promise.all([
        restGet<JsonRecord>(env, "schools", publicSchoolsParams),
        optionalRows(env, "school_types", schoolTypesParams),
        optionalRows(env, "articles", newsParams),
        optionalRows(env, "articles", insightParams),
        optionalRows(env, "school_posts", partnerNewsParams),
        optionalRows(env, "school_posts", eventParams),
        optionalRows(env, "school_posts", scholarshipParams, true),
        optionalRows(env, "job_listings", jobParams),
        optionalRows(env, "school_videos", videoParams),
        optionalRows(env, "site_ad_banners", adParams),
      ]);
    const imageSchools = await attachCoverImages(env, schoolResult.rows).catch((error) => {
      console.warn("Homepage school cover images are unavailable", error);
      return schoolResult.rows.map((school) => ({ ...school, image_url: null }));
    });
    const allSchools = imageSchools.map(schoolForPublicPage);
    const featuredSchools = allSchools.slice(0, 9);
    const spotlightSchool = allSchools[3] ?? allSchools[0] ?? null;
    const schoolTypeCounts = new Map<number, number>();
    for (const school of allSchools) {
      const typeId = Number(school.type_id);
      if (Number.isSafeInteger(typeId)) schoolTypeCounts.set(typeId, (schoolTypeCounts.get(typeId) ?? 0) + 1);
    }
    const schoolTypes = typeResult.rows.map((type) => ({
      ...type,
      public_listing_count: schoolTypeCounts.get(Number(type.id)) ?? 0,
    }));

    return responseJson(
      {
        data: {
          featured_schools: featuredSchools,
          spotlight_school: spotlightSchool,
          school_types: schoolTypes,
          news_articles: newsResult.rows,
          insight_articles: insightResult.rows,
          partner_school_news: partnerNewsResult.rows[0] ?? null,
          upcoming_events: eventsResult.rows,
          scholarship_count: scholarshipResult.count ?? scholarshipResult.rows.length,
          latest_jobs: jobsResult.rows,
          videos: videoResult.rows,
          ads: adsResult.rows,
        },
      },
      200,
      "public, max-age=30, s-maxage=60, stale-while-revalidate=300",
    );
  } catch (error) {
    console.error("Public homepage query failed", error);
    return responseJson({ error: "homepage_unavailable" }, 503);
  }
}

async function listSchools(url: URL, env: PublicApiEnv): Promise<Response> {
  if (!configured(env)) {
    return responseJson({ error: "database_not_configured" }, 503);
  }

  const search = cleanSearch(url.searchParams.get("q") || "");
  const type = url.searchParams.get("type");
  const district = url.searchParams.get("district");
  const region = url.searchParams.get("region");
  const page = positiveInt(url.searchParams.get("page"), 1, 10_000);
  const limit = positiveInt(url.searchParams.get("limit"), 12, 30);
  const feeMin = optionalAmount(url.searchParams.get("fee_min"));
  const feeMax = optionalAmount(url.searchParams.get("fee_max"));
  const admissionsOpen = url.searchParams.get("admissions_open");
  const boarding = url.searchParams.get("boarding");

  if (type && !/^[0-9]+$/.test(type)) return responseJson({ error: "invalid_type" }, 400);
  if (district && !/^[0-9]+$/.test(district)) return responseJson({ error: "invalid_district" }, 400);
  if (region && !/^[0-9]+$/.test(region)) return responseJson({ error: "invalid_region" }, 400);
  if (admissionsOpen && !["true", "false", "1", "0"].includes(admissionsOpen)) {
    return responseJson({ error: "invalid_admissions_open" }, 400);
  }
  if (boarding && !["day", "boarding", "both"].includes(boarding)) {
    return responseJson({ error: "invalid_boarding" }, 400);
  }

  const params = new URLSearchParams();
  params.set(
    "select",
    [
      "id,name,slug,tagline,description,town,address,boarding_type,gender_policy",
      "admissions_open,admission_deadline,fees_from,fees_to,rating_average,reviews_count",
      "is_featured,created_at,featured_until,district_id,type_id",
      "districts!inner(id,name,slug,region_id,regions(id,name,slug))",
      "school_types(id,name,slug)",
      "school_curriculum(curricula(id,name,abbreviation))",
    ].join(","),
  );
  params.set("status", PUBLIC_SCHOOL_FILTER.status);
  params.set("verification_status", PUBLIC_SCHOOL_FILTER.verification_status);
  params.set("limit", String(limit));
  params.set("offset", String((page - 1) * limit));

  if (search) {
    const like = "*" + search + "*";
    params.set(
      "or",
      "(name.ilike." + like +
        ",tagline.ilike." + like +
        ",description.ilike." + like +
        ",town.ilike." + like +
        ",address.ilike." + like + ")",
    );
  }
  if (type) params.set("type_id", "eq." + type);
  if (district) params.set("district_id", "eq." + district);
  if (region) params.set("districts.region_id", "eq." + region);
  if (feeMin !== undefined) params.set("fees_to", "gte." + feeMin);
  if (feeMax !== undefined) params.set("fees_from", "lte." + feeMax);
  if (admissionsOpen === "true" || admissionsOpen === "1") params.set("admissions_open", "eq.true");
  if (admissionsOpen === "false" || admissionsOpen === "0") params.set("admissions_open", "eq.false");
  if (boarding) params.set("boarding_type", "eq." + boarding);

  const sort = url.searchParams.get("sort") || "newest";
  const sortValues: Record<string, string> = {
    newest: "created_at.desc",
    featured: "is_featured.desc,featured_until.desc.nullslast,created_at.desc",
    name: "name.asc",
    rating: "rating_average.desc,reviews_count.desc",
    fee_low: "fees_from.asc.nullslast",
    fee_high: "fees_from.desc.nullslast",
  };
  params.set("order", sortValues[sort] || sortValues.newest);

  try {
    const result = await restGet<JsonRecord>(env, "schools", params, true);
    const data = await attachCoverImages(env, result.rows);
    const total = result.count ?? data.length;

    return responseJson(
      {
        data: data.map(schoolForPublicPage),
        meta: {
          page,
          per_page: limit,
          total,
          total_pages: Math.ceil(total / limit),
        },
      },
      200,
      PUBLIC_CACHE,
    );
  } catch (error) {
    console.error("Public directory query failed", error);
    return responseJson({ error: "directory_unavailable" }, 503);
  }
}

async function schoolDetail(slug: string, env: PublicApiEnv): Promise<Response> {
  if (!configured(env)) {
    return responseJson({ error: "database_not_configured" }, 503);
  }

  const params = new URLSearchParams();
  params.set(
    "select",
    [
      "id,name,slug,tagline,description,founded_year,address,town,po_box",
      "nearest_landmark,maps_url,phone,email,website,whatsapp,facebook_url,instagram_url",
      "ownership,religious_affiliation,boarding_type,gender_policy,student_count_approx",
      "admissions_open,admission_classes,admission_deadline,admissions_contact",
      "admission_requirements,how_to_apply,fees_from,fees_to,rating_average,reviews_count",
      "meta_title,meta_description",
      "districts(id,name,slug,regions(id,name,slug))",
      "school_types(id,name,slug)",
    ].join(","),
  );
  params.set("slug", "eq." + slug);
  params.set("status", PUBLIC_SCHOOL_FILTER.status);
  params.set("verification_status", PUBLIC_SCHOOL_FILTER.verification_status);
  params.set("limit", "1");

  try {
    const schoolResult = await restGet<JsonRecord>(env, "schools", params);
    const school = schoolResult.rows[0];
    if (!school) return responseJson({ error: "school_not_found" }, 404);

    const schoolId = Number(school.id);
    const related = await Promise.all([
      optionalRows(env, "school_curriculum", new URLSearchParams({
        select: "curricula(id,name,abbreviation)",
        school_id: "eq." + schoolId,
      })),
      optionalRows(env, "school_programs", new URLSearchParams({
        select: "id,name,level,duration,description",
        school_id: "eq." + schoolId,
        is_active: "eq.true",
        order: "name.asc",
      })),
      optionalRows(env, "school_facilities", new URLSearchParams({
        select: "notes,facilities(id,name,category)",
        school_id: "eq." + schoolId,
      })),
      optionalRows(env, "media", new URLSearchParams({
        select: "id,model_id,collection_name,name,file_name,mime_type,order_column",
        model_type: "like.*School",
        model_id: "eq." + schoolId,
        collection_name: "in.(cover,badge,gallery)",
        mime_type: "like.image/*",
        order: "order_column.asc.nullslast,id.asc",
      })),
    ]);

    const [curriculumRows, programRows, facilityRows, mediaRows] = related;
    const media = mediaRows.rows.map((item) => ({
      id: item.id,
      name: item.name,
      collection_name: item.collection_name,
      url: mediaUrl(env, item),
    }));

    const { application_fee: _privateApplicationFee, ...publicSchool } = school;
    return responseJson(
      {
        data: {
          ...publicSchool,
          district: (school.districts as JsonRecord | null) ?? null,
          region: ((school.districts as JsonRecord | null)?.regions as JsonRecord | null) ?? null,
          type: (school.school_types as JsonRecord | null) ?? null,
          curricula: curriculumRows.rows
            .map((row) => row.curricula)
            .filter(Boolean),
          programs: programRows.rows,
          facilities: facilityRows.rows
            .map((row) => row.facilities
              ? { ...(row.facilities as JsonRecord), notes: row.notes }
              : null)
            .filter(Boolean),
          media,
        },
      },
      200,
      PUBLIC_CACHE,
    );
  } catch (error) {
    console.error("Public school profile query failed", { slug, error });
    return responseJson({ error: "school_unavailable" }, 503);
  }
}

async function locations(env: PublicApiEnv): Promise<Response> {
  if (!configured(env)) {
    return responseJson({ error: "database_not_configured" }, 503);
  }

  try {
    const [regions, districts, schoolTypes, curricula] = await Promise.all([
      restGet<JsonRecord>(env, "regions", new URLSearchParams({
        select: "id,name,slug",
        is_active: "eq.true",
        order: "name.asc",
      })),
      restGet<JsonRecord>(env, "districts", new URLSearchParams({
        select: "id,name,slug,region_id",
        is_active: "eq.true",
        order: "name.asc",
      })),
      restGet<JsonRecord>(env, "school_types", new URLSearchParams({
        select: "id,name,slug",
        is_active: "eq.true",
        order: "order.asc,name.asc",
      })),
      restGet<JsonRecord>(env, "curricula", new URLSearchParams({
        select: "id,name,slug,abbreviation",
        is_active: "eq.true",
        order: "name.asc",
      })),
    ]);

    return responseJson(
      {
        data: {
          regions: regions.rows,
          districts: districts.rows,
          school_types: schoolTypes.rows,
          curricula: curricula.rows,
        },
      },
      200,
      "public, max-age=300, s-maxage=600, stale-while-revalidate=3600",
    );
  } catch (error) {
    console.error("Public locations query failed", error);
    return responseJson({ error: "locations_unavailable" }, 503);
  }
}
