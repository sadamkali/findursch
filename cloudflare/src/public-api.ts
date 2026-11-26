export interface PublicApiEnv {
  SUPABASE_URL?: string;
  SUPABASE_ANON_KEY?: string;
  PUBLIC_MEDIA_BASE_URL?: string;
  AUTH_SESSIONS?: { get(key: string): Promise<string | null> };
}

type JsonRecord = Record<string, unknown>;
type RestResult<T> = { rows: T[]; count: number | null };

const DEFAULT_SUPABASE_URL = "https://gpsfttpeikatxszswljo.supabase.co";
const DEFAULT_MEDIA_URL = "https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev";
const PUBLIC_SCHOOL_FILTER = {
  status: "eq.active",
  verification_status: "eq.verified",
};

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
      sessionStoreConfigured: Boolean(env.AUTH_SESSIONS),
      authSecretsConfigured: Boolean(env.SUPABASE_SECRET_KEY),
    });
  }

  if (path === "/api/v1/schools") return listSchools(url, env);
  if (path.startsWith("/api/v1/schools/")) {
    const slug = decodeURIComponent(path.slice("/api/v1/schools/".length));
    if (!slug || slug.includes("/")) return responseJson({ error: "not_found" }, 404);
    return schoolDetail(slug, env);
  }
  if (path === "/api/v1/locations") return locations(env);

  return responseJson({ error: "not_found" }, 404);
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
        data: data.map((school) => ({
          ...school,
          district_name: (school.districts as JsonRecord | null)?.name ?? null,
          district_slug: (school.districts as JsonRecord | null)?.slug ?? null,
          region_name: ((school.districts as JsonRecord | null)?.regions as JsonRecord | null)?.name ?? null,
          region_slug: ((school.districts as JsonRecord | null)?.regions as JsonRecord | null)?.slug ?? null,
          school_type: (school.school_types as JsonRecord | null)?.name ?? null,
          school_type_slug: (school.school_types as JsonRecord | null)?.slug ?? null,
        })),
        meta: {
          page,
          per_page: limit,
          total,
          total_pages: Math.ceil(total / limit),
        },
      },
      200,
      "public, max-age=30, s-maxage=60, stale-while-revalidate=300",
    );
  } catch {
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
      "admissions_open,admission_classes,admission_deadline,application_fee,admissions_contact",
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
      restGet<JsonRecord>(env, "school_curriculum", new URLSearchParams({
        select: "curricula(id,name,abbreviation)",
        school_id: "eq." + schoolId,
      })),
      restGet<JsonRecord>(env, "school_programs", new URLSearchParams({
        select: "id,name,level,duration,description",
        school_id: "eq." + schoolId,
        is_active: "eq.true",
        order: "name.asc",
      })),
      restGet<JsonRecord>(env, "school_facilities", new URLSearchParams({
        select: "notes,facilities(id,name,category)",
        school_id: "eq." + schoolId,
      })),
      restGet<JsonRecord>(env, "school_fees", new URLSearchParams({
        select: "id,category,label,amount,currency,period,academic_year,notes",
        school_id: "eq." + schoolId,
        is_active: "eq.true",
        order: "amount.asc",
      })),
      restGet<JsonRecord>(env, "media", new URLSearchParams({
        select: "id,model_id,collection_name,name,file_name,mime_type,order_column",
        model_type: "like.*School",
        model_id: "eq." + schoolId,
        collection_name: "in.(cover,badge,gallery)",
        mime_type: "like.image/*",
        order: "order_column.asc.nullslast,id.asc",
      })),
    ]);

    const [curriculumRows, programRows, facilityRows, feeRows, mediaRows] = related;
    const media = mediaRows.rows.map((item) => ({
      id: item.id,
      name: item.name,
      collection_name: item.collection_name,
      url: mediaUrl(env, item),
    }));

    return responseJson(
      {
        data: {
          ...school,
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
          fees: feeRows.rows,
          media,
        },
      },
      200,
      "public, max-age=30, s-maxage=60, stale-while-revalidate=300",
    );
  } catch {
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
  } catch {
    return responseJson({ error: "locations_unavailable" }, 503);
  }
}
