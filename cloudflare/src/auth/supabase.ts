import type {
  AuthEnv,
  LegacyAppUser,
  SupabaseTokenResponse,
} from "./types";

export class AuthConfigurationError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "AuthConfigurationError";
  }
}

export class SupabaseRequestError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly body: unknown,
  ) {
    super(message);
    this.name = "SupabaseRequestError";
  }
}

function baseUrl(env: AuthEnv): string {
  return (env.SUPABASE_URL || "https://gpsfttpeikatxszswljo.supabase.co").replace(/\/+$/, "");
}

export function assertAuthConfigured(env: AuthEnv): void {
  if (!env.SUPABASE_ANON_KEY) {
    throw new AuthConfigurationError("Supabase public key is not configured.");
  }
  if (!env.SUPABASE_SECRET_KEY) {
    throw new AuthConfigurationError("The server-only Supabase secret key has not been configured.");
  }
}

async function responseBody(response: Response): Promise<unknown> {
  return response.json().catch(() => null);
}

export async function adminRest<T>(
  env: AuthEnv,
  table: string,
  params: URLSearchParams,
  method: "GET" | "POST" | "PATCH" | "DELETE" = "GET",
  body?: unknown,
  prefer?: string,
): Promise<T> {
  assertAuthConfigured(env);
  const key = env.SUPABASE_SECRET_KEY as string;
  const url = baseUrl(env) + "/rest/v1/" + table + (params.size ? "?" + params.toString() : "");
  const headers = new Headers({
    apikey: key,
    Authorization: "Bearer " + key,
    Accept: "application/json",
  });
  if (body !== undefined) headers.set("Content-Type", "application/json");
  if (prefer) headers.set("Prefer", prefer);

  const response = await fetch(url, {
    method,
    headers,
    body: body === undefined ? undefined : JSON.stringify(body),
    cache: "no-store",
    signal: AbortSignal.timeout(8000),
  });
  const result = await responseBody(response);

  if (!response.ok) {
    throw new SupabaseRequestError(
      "Supabase database request failed.",
      response.status,
      result,
    );
  }

  return result as T;
}

export async function findLegacyUser(
  env: AuthEnv,
  identifier: string,
): Promise<LegacyAppUser | null> {
  assertAuthConfigured(env);
  const isEmail = identifier.includes("@");
  const values = isEmail
    ? [identifier.trim().toLowerCase()]
    : Array.from(new Set([
        identifier.trim(),
        identifier.trim().replace(/[\s().-]+/g, ""),
      ]));

  for (const value of values) {
    const params = new URLSearchParams({
      select: "id,name,email,phone,status,user_type,email_verified_at",
      limit: "1",
    });
    params.set(isEmail ? "email" : "phone", "eq." + value);
    const rows = await adminRest<LegacyAppUser[]>(env, "users", params);
    if (rows[0]) return rows[0];
  }
  return null;
}

export async function loadLegacyUser(
  env: AuthEnv,
  legacyUserId: number,
): Promise<LegacyAppUser | null> {
  const params = new URLSearchParams({
    select: "id,name,email,phone,status,user_type,email_verified_at",
    id: "eq." + String(legacyUserId),
    limit: "1",
  });
  const rows = await adminRest<LegacyAppUser[]>(env, "users", params);
  return rows[0] ?? null;
}

export interface UserAuthorization {
  roles: string[];
  permissions: string[];
}

export async function loadAuthorization(
  env: AuthEnv,
  legacyUserId: number,
): Promise<UserAuthorization> {
  const roleParams = new URLSearchParams({
    select: "role_id,roles(name)",
    model_type: "eq.App\\Models\\User",
    model_id: "eq." + String(legacyUserId),
  });
  const roleRows = await adminRest<Array<{
    role_id: number;
    roles: { name: string } | null;
  }>>(env, "model_has_roles", roleParams);

  const roleIds = Array.from(new Set(roleRows.map((row) => Number(row.role_id)).filter(Number.isSafeInteger)));
  const roles = Array.from(new Set(roleRows.map((row) => row.roles?.name).filter((name): name is string => Boolean(name))));

  const permissions = new Set<string>();
  if (roleIds.length > 0) {
    const params = new URLSearchParams({
      select: "role_id,permissions(name)",
      role_id: "in.(" + roleIds.join(",") + ")",
    });
    const rows = await adminRest<Array<{
      permissions: { name: string } | null;
    }>>(env, "role_has_permissions", params);
    for (const row of rows) {
      if (row.permissions?.name) permissions.add(row.permissions.name);
    }
  }

  const directParams = new URLSearchParams({
    select: "permissions(name)",
    model_type: "eq.App\\Models\\User",
    model_id: "eq." + String(legacyUserId),
  });
  const directRows = await adminRest<Array<{
    permissions: { name: string } | null;
  }>>(env, "model_has_permissions", directParams);
  for (const row of directRows) {
    if (row.permissions?.name) permissions.add(row.permissions.name);
  }

  return {
    roles,
    permissions: Array.from(permissions).sort(),
  };
}

export function resolveWorkspace(
  roles: string[],
  permissions: string[],
): UserAuthorization & {
  isAdmin: boolean;
  isSchoolOwner: boolean;
  isParent: boolean;
  redirectTo: "/admin" | "/owner" | "/parent";
} {
  const isAdmin = roles.includes("admin") || permissions.some((name) => name.startsWith("admin."));
  const isSchoolOwner = roles.includes("school_owner");
  const isParent = roles.includes("parent");

  return {
    roles,
    permissions,
    isAdmin,
    isSchoolOwner,
    isParent,
    redirectTo: isAdmin ? "/admin" : (isSchoolOwner ? "/owner" : "/parent"),
  };
}

export async function signInWithPassword(
  env: AuthEnv,
  email: string,
  password: string,
): Promise<SupabaseTokenResponse> {
  assertAuthConfigured(env);
  const response = await fetch(baseUrl(env) + "/auth/v1/token?grant_type=password", {
    method: "POST",
    headers: {
      apikey: env.SUPABASE_ANON_KEY as string,
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({ email, password }),
    cache: "no-store",
    signal: AbortSignal.timeout(10000),
  });
  const body = await responseBody(response);

  if (!response.ok) {
    throw new SupabaseRequestError("Sign-in was rejected by Supabase Auth.", response.status, body);
  }

  return body as SupabaseTokenResponse;
}

export async function refreshSupabaseTokens(
  env: AuthEnv,
  refreshToken: string,
): Promise<SupabaseTokenResponse> {
  if (!env.SUPABASE_ANON_KEY) {
    throw new AuthConfigurationError("Supabase public key is not configured.");
  }
  const response = await fetch(baseUrl(env) + "/auth/v1/token?grant_type=refresh_token", {
    method: "POST",
    headers: {
      apikey: env.SUPABASE_ANON_KEY,
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({ refresh_token: refreshToken }),
    cache: "no-store",
    signal: AbortSignal.timeout(10000),
  });
  const body = await responseBody(response);

  if (!response.ok) {
    throw new SupabaseRequestError("Session refresh was rejected.", response.status, body);
  }

  return body as SupabaseTokenResponse;
}

export async function getSupabaseUser(
  env: AuthEnv,
  accessToken: string,
): Promise<{
  id: string;
  app_metadata?: Record<string, unknown>;
} | null> {
  if (!env.SUPABASE_ANON_KEY) {
    throw new AuthConfigurationError("Supabase public key is not configured.");
  }
  const response = await fetch(baseUrl(env) + "/auth/v1/user", {
    headers: {
      apikey: env.SUPABASE_ANON_KEY,
      Authorization: "Bearer " + accessToken,
      Accept: "application/json",
    },
    cache: "no-store",
    signal: AbortSignal.timeout(8000),
  });
  if (!response.ok) return null;
  return await response.json() as { id: string; app_metadata?: Record<string, unknown> };
}

export async function readBooleanSetting(env: AuthEnv, key: string, fallback: boolean): Promise<boolean> {
  const params = new URLSearchParams({ select: "value", key: "eq." + key, limit: "1" });
  const rows = await adminRest<Array<{ value: string | null }>>(env, "settings", params);
  if (!rows[0] || rows[0].value === null) return fallback;
  return ["1", "true", "yes", "on"].includes(rows[0].value.toLowerCase());
}

export async function enforceLegacyAdminCountryRule(
  env: AuthEnv,
  legacyUserId: number,
  countryCode: string,
): Promise<"allowed" | "blocked"> {
  if (!/^[A-Z]{2}$/.test(countryCode)) return "allowed";

  const enabled = await readBooleanSetting(env, "block_new_country_signins", false);
  if (!enabled) return "allowed";

  const lookup = new URLSearchParams({
    select: "id,approved_at",
    user_id: "eq." + String(legacyUserId),
    country_code: "eq." + countryCode,
    limit: "1",
  });
  const known = await adminRest<Array<{ id: number; approved_at: string | null }>>(
    env,
    "admin_login_countries",
    lookup,
  );
  if (known[0]) return known[0].approved_at ? "allowed" : "blocked";

  const approvedLookup = new URLSearchParams({
    select: "id",
    user_id: "eq." + String(legacyUserId),
    approved_at: "not.is.null",
    limit: "1",
  });
  const approved = await adminRest<Array<{ id: number }>>(env, "admin_login_countries", approvedLookup);
  const autoApprove = approved.length === 0;
  await adminRest(
    env,
    "admin_login_countries",
    new URLSearchParams(),
    "POST",
    {
      user_id: legacyUserId,
      country_code: countryCode,
      first_seen_at: new Date().toISOString(),
      approved_at: autoApprove ? new Date().toISOString() : null,
      approved_by: autoApprove ? legacyUserId : null,
    },
    "return=minimal",
  );

  return autoApprove ? "allowed" : "blocked";
}
