import type { AuthEnv, StoredAuthSession, SupabaseTokenResponse } from "./types";
import { SESSION_COOKIE } from "./types";
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
    if (index < 0 || part.slice(0, index).trim() !== name) continue;
    try {
      return decodeURIComponent(part.slice(index + 1).trim());
    } catch {
      return null;
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

function parseSession(row: Record<string, unknown>): StoredAuthSession | null {
  const workspace = String(row.workspace);
  if (workspace !== "admin" && workspace !== "owner" && workspace !== "parent") return null;
  const session: StoredAuthSession = {
    supabaseUserId: String(row.supabase_user_id || ""),
    legacyUserId: Number(row.legacy_user_id),
    accessToken: String(row.access_token || ""),
    refreshToken: String(row.refresh_token || ""),
    accessTokenExpiresAt: Number(row.access_token_expires_at),
    absoluteExpiresAt: Number(row.absolute_expires_at),
    createdAt: Number(row.created_at),
    remember: Number(row.remember) === 1,
    workspace,
  };
  if (
    !session.supabaseUserId ||
    !Number.isSafeInteger(session.legacyUserId) ||
    !session.accessToken ||
    !session.refreshToken ||
    !Number.isFinite(session.absoluteExpiresAt) ||
    !Number.isFinite(session.accessTokenExpiresAt)
  ) return null;
  return session;
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
  if (!env.AUTH_DB) throw new Error("Session database is unavailable.");
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
  const result = await env.AUTH_DB.prepare(
    "INSERT INTO auth_sessions (session_id, supabase_user_id, legacy_user_id, access_token, refresh_token, access_token_expires_at, absolute_expires_at, created_at, remember, workspace) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
  ).bind(
    id, value.supabaseUserId, value.legacyUserId, value.accessToken,
    value.refreshToken, value.accessTokenExpiresAt, value.absoluteExpiresAt,
    value.createdAt, value.remember ? 1 : 0, value.workspace,
  ).run();
  if (result.success === false) throw new Error("Session could not be persisted.");
  return { id, cookie: setCookie(id, lifetimeSeconds) };
}

export async function readStoredSession(
  request: Request,
  env: AuthEnv,
): Promise<SessionHandle | null> {
  if (!env.AUTH_DB) return null;
  const id = cookieValue(request, SESSION_COOKIE);
  if (!id || !/^[a-f0-9]{64}$/.test(id)) return null;

  const row = await env.AUTH_DB.prepare(
    "SELECT session_id, supabase_user_id, legacy_user_id, access_token, refresh_token, access_token_expires_at, absolute_expires_at, created_at, remember, workspace FROM auth_sessions WHERE session_id = ? LIMIT 1",
  ).bind(id).first<Record<string, unknown>>();
  if (!row) return null;

  const value = parseSession(row);
  if (!value || Date.now() >= value.absoluteExpiresAt) {
    await env.AUTH_DB.prepare("DELETE FROM auth_sessions WHERE session_id = ?").bind(id).run();
    return null;
  }

  if (Date.now() >= value.accessTokenExpiresAt - 90_000) {
    try {
      const fresh = await refreshSupabaseTokens(env, value.refreshToken);
      if (!fresh.access_token || !fresh.refresh_token || !fresh.user?.id || fresh.user.id !== value.supabaseUserId) {
        await env.AUTH_DB.prepare("DELETE FROM auth_sessions WHERE session_id = ?").bind(id).run();
        return null;
      }
      value.accessToken = fresh.access_token;
      value.refreshToken = fresh.refresh_token;
      value.accessTokenExpiresAt = Date.now() + Math.max(60, Number(fresh.expires_in || 3600)) * 1000;
      const update = await env.AUTH_DB.prepare(
        "UPDATE auth_sessions SET access_token = ?, refresh_token = ?, access_token_expires_at = ? WHERE session_id = ? AND absolute_expires_at > ?",
      ).bind(value.accessToken, value.refreshToken, value.accessTokenExpiresAt, id, Date.now()).run();
      if (update.success === false) throw new Error("Session refresh could not be persisted.");
    } catch {
      await env.AUTH_DB.prepare("DELETE FROM auth_sessions WHERE session_id = ?").bind(id).run();
      return null;
    }
  }
  return { id, value };
}

export async function destroyStoredSession(env: AuthEnv, id: string): Promise<void> {
  if (env.AUTH_DB && /^[a-f0-9]{64}$/.test(id)) {
    await env.AUTH_DB.prepare("DELETE FROM auth_sessions WHERE session_id = ?").bind(id).run();
  }
}

export function sessionCookieValue(request: Request): string | null {
  return cookieValue(request, SESSION_COOKIE);
}
