import type { AuthEnv, LegacyAppUser } from "./types";
import { assertAuthConfigured, enforceLegacyAdminCountryRule, findLegacyUser, getSupabaseUser, loadAuthorization, loadLegacyUser, readBooleanSetting, resolveWorkspace, signInWithPassword } from "./supabase";
import { clearSessionCookie, createStoredSession, destroyStoredSession, readStoredSession, sessionCookieValue } from "./session";
import type { StoredAuthSession } from "./types";

function json(data: unknown, status = 200, headers?: HeadersInit): Response {
  const result = new Headers(headers);
  result.set("Content-Type", "application/json; charset=utf-8");
  result.set("Cache-Control", "no-store, private");
  result.set("X-Content-Type-Options", "nosniff");
  return new Response(JSON.stringify(data), { status, headers: result });
}

function requestIsSameOrigin(request: Request): boolean {
  const origin = request.headers.get("Origin");
  if (origin && origin !== new URL(request.url).origin) return false;
  const fetchSite = request.headers.get("Sec-Fetch-Site");
  return fetchSite !== "cross-site";
}

function publicUser(user: LegacyAppUser, auth: ReturnType<typeof resolveWorkspace>) {
  return {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone,
    roles: auth.roles,
    permissions: auth.permissions,
  };
}

async function resolveSessionIdentity(
  request: Request,
  env: AuthEnv,
): Promise<{
  id: string;
  session: StoredAuthSession;
  user: LegacyAppUser;
  auth: ReturnType<typeof resolveWorkspace>;
} | null> {
  const stored = await readStoredSession(request, env);
  if (!stored) return null;

  const authUser = await getSupabaseUser(env, stored.value.accessToken);
  if (!authUser || authUser.id !== stored.value.supabaseUserId) {
    await destroyStoredSession(env, stored.id);
    return null;
  }

  const legacyIdFromMetadata = Number(authUser.app_metadata?.legacy_user_id);
  if (!Number.isSafeInteger(legacyIdFromMetadata) || legacyIdFromMetadata !== stored.value.legacyUserId) {
    await destroyStoredSession(env, stored.id);
    return null;
  }

  const user = await loadLegacyUser(env, stored.value.legacyUserId);
  if (!user || user.status !== "active") {
    await destroyStoredSession(env, stored.id);
    return null;
  }

  const access = await loadAuthorization(env, user.id);
  const auth = resolveWorkspace(access.roles, access.permissions);
  if (!auth.isAdmin && !auth.isSchoolOwner && !auth.isParent) {
    await destroyStoredSession(env, stored.id);
    return null;
  }

  return { id: stored.id, session: stored.value, user, auth };
}

async function login(request: Request, env: AuthEnv): Promise<Response> {
  if (!requestIsSameOrigin(request)) return json({ message: "Cross-origin sign-in requests are not allowed." }, 403);
  if (!env.AUTH_SESSIONS) return json({ message: "The session store is not available." }, 503);

  let body: unknown;
  try {
    body = await request.json();
  } catch {
    return json({ message: "Enter your email or phone number and password." }, 400);
  }

  const data = body as Record<string, unknown>;
  const identifier = String(data.login ?? data.email ?? "").trim();
  const password = typeof data.password === "string" ? data.password : "";
  const remember = data.remember !== false;

  if (!identifier || identifier.length > 255 || !password || password.length > 1024) {
    return json({ message: "Enter your email or phone number and password." }, 422);
  }

  try {
    assertAuthConfigured(env);
    const legacyUser = await findLegacyUser(env, identifier);
    if (!legacyUser) return json({ message: "The supplied sign-in details are incorrect." }, 422);
    if (legacyUser.status !== "active") {
      return json({ message: "This account is not active. Contact FindURSCH administration." }, 403);
    }

    const access = await loadAuthorization(env, legacyUser.id);
    const auth = resolveWorkspace(access.roles, access.permissions);
    if (!auth.isAdmin && !auth.isSchoolOwner && !auth.isParent) {
      return json({ message: "This account does not have access to the FindURSCH workspace." }, 403);
    }

    if (auth.isAdmin) {
      const requireTwoStep = await readBooleanSetting(env, "require_two_step", false);
      if (requireTwoStep) {
        return json({ message: "Administrator two-step verification is not yet enabled on the Cloudflare sign-in service. Sign-in is blocked until that flow is migrated." }, 503);
      }

      if (!auth.roles.includes("admin")) {
        const country = (request.headers.get("CF-IPCountry") ?? "").toUpperCase().slice(0, 2);
        const countryResult = await enforceLegacyAdminCountryRule(env, legacyUser.id, country);
        if (countryResult === "blocked") {
          return json({ message: "Sign-in from this country is awaiting approval from the FindURSCH owner." }, 403);
        }
      }
    }

    const tokens = await signInWithPassword(env, legacyUser.email.toLowerCase(), password);
    const appMetadata = tokens.user?.app_metadata ?? {};
    const legacyId = Number(appMetadata.legacy_user_id);
    if (!tokens.user?.id || !Number.isSafeInteger(legacyId) || legacyId !== legacyUser.id) {
      return json({ message: "This account has not yet been migrated to Cloudflare sign-in. No existing password or account record was changed." }, 409);
    }

    const workspace = auth.isAdmin ? "admin" : (auth.isSchoolOwner ? "owner" : "parent");
    const created = await createStoredSession(env, tokens, legacyUser.id, workspace, remember);
    return json({
      user: publicUser(legacyUser, auth),
      redirectTo: auth.redirectTo,
    }, 200, { "Set-Cookie": created.cookie });
  } catch (error) {
    if (error instanceof Error && error.name === "AuthConfigurationError") {
      return json({ message: "Cloudflare sign-in is not fully configured. The server-only Supabase secret key is required." }, 503);
    }
    console.error("FindURSCH sign-in failed.");
    return json({ message: "Sign-in is temporarily unavailable. Please try again." }, 503);
  }
}

async function me(request: Request, env: AuthEnv): Promise<Response> {
  try {
    const identity = await resolveSessionIdentity(request, env);
    if (!identity) {
      return json({ authenticated: false }, 401, { "Set-Cookie": clearSessionCookie() });
    }
    return json({
      data: publicUser(identity.user, identity.auth),
      redirectTo: identity.auth.redirectTo,
    });
  } catch {
    return json({ authenticated: false, message: "Session validation is temporarily unavailable." }, 503);
  }
}

async function logout(request: Request, env: AuthEnv): Promise<Response> {
  if (!requestIsSameOrigin(request)) return json({ message: "Cross-origin sign-out requests are not allowed." }, 403);
  const id = sessionCookieValue(request);
  if (id) await destroyStoredSession(env, id);
  return json({ message: "Signed out." }, 200, { "Set-Cookie": clearSessionCookie() });
}

export async function handleAuthRequest(
  request: Request,
  env: AuthEnv,
  action: "login" | "me" | "logout",
): Promise<Response> {
  if (action === "login") {
    if (request.method !== "POST") return json({ message: "Method not allowed." }, 405, { Allow: "POST" });
    return login(request, env);
  }
  if (action === "me") {
    if (request.method !== "GET") return json({ message: "Method not allowed." }, 405, { Allow: "GET" });
    return me(request, env);
  }
  if (request.method !== "POST") return json({ message: "Method not allowed." }, 405, { Allow: "POST" });
  return logout(request, env);
}
