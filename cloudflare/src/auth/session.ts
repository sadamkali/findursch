import type { AuthEnv, StoredAuthSession, SupabaseTokenResponse } from "./types";
import { SESSION_COOKIE, SESSION_KEY_PREFIX } from "./types";
import { refreshSupabaseTokens } from "./supabase";

function randomSessionId(): string {
  const bytes = crypto.getRandomValues(new Uint8Array(32));
  return Array.from(bytes, (value) => value.toString(16).padStart(2, "0")).join("");
}

function cookieValue(request: Request, name: string): string | null {
  const cookie = request.headers.get("Cookie");
  if (!cookie) return null;
  for (const part of cookie.split(";")) {
    const index = part.indexOf("=");
    if (index < 0) continue;
    if (part.slice(0, index).trim() === name) {
      return decodeURIComponent(part.slice(index + 1).trim());
    }
  }
  return null;
}

function setCookie(id: string, maxAge: number): string {
  return [
    SESSION_COOKIE + "=" + encodeURIComponent(id),
    "Path=/",
    "HttpOnly",
    "Secure",
    "SameSite=Lax",
    "Max-Age=" + String(maxAge),
  ].join("; ");
}

export function clearSessionCookie(): string {
  return [
    SESSION_COOKIE + "=",
    "Path=/",
    "HttpOnly",
    "Secure",
    "SameSite=Lax",
    "Max-Age=0",
  ].join("; ");
}

function kvKey(id: string): string {
  return SESSION_KEY_PREFIX + id;
}

export interface SessionHandle {
  id: string;
  value: StoredAuthSession;
}

export async function createStoredSession(
  env: AuthEnv,
  tokens: SupabaseTokenResponse,
  legacyUserId: number,
  workspace: "admin" | "owner" | "parent",
  remember: boolean,
): Promise<{ id: string; cookie: string }> {
  if (!env.AUTH_SESSIONS) throw new Error("Session storage is unavailable.");
  if (!tokens.access_token || !tokens.refresh_token || !tokens.user?.id) {
    throw new Error("Supabase did not return a complete session.");
  }

  const now = Date.now();
  const lifetimeSeconds = workspace === "admin"
    ? 120 * 60
    : (remember ? 30 * 24 * 60 * 60 : 24 * 60 * 60);
  const absoluteExpiresAt = now + lifetimeSeconds * 1000;
  const value: StoredAuthSession = {
    supabaseUserId: tokens.user.id,
    legacyUserId,
    accessToken: tokens.access_token,
    refreshToken: tokens.refresh_token,
    accessTokenExpiresAt: now + Math.max(60, Number(tokens.expires_in || 3600)) * 1000,
    absoluteExpiresAt,
    createdAt: now,
    remember,
    workspace,
  };

  const id = randomSessionId();
  await env.AUTH_SESSIONS.put(kvKey(id), JSON.stringify(value), {
    expirationTtl: lifetimeSeconds,
  });

  return { id, cookie: setCookie(id, lifetimeSeconds) };
}

export async function readStoredSession(
  request: Request,
  env: AuthEnv,
): Promise<SessionHandle | null> {
  if (!env.AUTH_SESSIONS) return null;
  const id = cookieValue(request, SESSION_COOKIE);
  if (!id || !/^[a-f0-9]{64}$/.test(id)) return null;

  const serialized = await env.AUTH_SESSIONS.get(kvKey(id));
  if (!serialized) return null;

  let value: StoredAuthSession;
  try {
    value = JSON.parse(serialized) as StoredAuthSession;
  } catch {
    await env.AUTH_SESSIONS.delete(kvKey(id));
    return null;
  }

  if (
    !value.supabaseUserId ||
    !Number.isSafeInteger(value.legacyUserId) ||
    !value.accessToken ||
    !value.refreshToken ||
    !value.absoluteExpiresAt ||
    Date.now() >= value.absoluteExpiresAt
  ) {
    await env.AUTH_SESSIONS.delete(kvKey(id));
    return null;
  }

  if (Date.now() >= value.accessTokenExpiresAt - 90_000) {
    try {
      const fresh = await refreshSupabaseTokens(env, value.refreshToken);
      if (!fresh.access_token || !fresh.refresh_token || !fresh.user?.id || fresh.user.id !== value.supabaseUserId) {
        await env.AUTH_SESSIONS.delete(kvKey(id));
        return null;
      }
      value = {
        ...value,
        accessToken: fresh.access_token,
        refreshToken: fresh.refresh_token,
        accessTokenExpiresAt: Date.now() + Math.max(60, Number(fresh.expires_in || 3600)) * 1000,
      };
      const remainingTtl = Math.max(60, Math.ceil((value.absoluteExpiresAt - Date.now()) / 1000));
      await env.AUTH_SESSIONS.put(kvKey(id), JSON.stringify(value), { expirationTtl: remainingTtl });
    } catch {
      await env.AUTH_SESSIONS.delete(kvKey(id));
      return null;
    }
  }

  return { id, value };
}

export async function destroyStoredSession(env: AuthEnv, id: string): Promise<void> {
  if (env.AUTH_SESSIONS && /^[a-f0-9]{64}$/.test(id)) {
    await env.AUTH_SESSIONS.delete(kvKey(id));
  }
}

export function sessionCookieValue(request: Request): string | null {
  return cookieValue(request, SESSION_COOKIE);
}
