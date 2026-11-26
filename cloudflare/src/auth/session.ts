import type { AuthEnv, StoredAuthSession, SupabaseTokenResponse } from "./types";
import { SESSION_COOKIE } from "./types";
import { refreshSupabaseTokens } from "./supabase";

const encoder = new TextEncoder();
const decoder = new TextDecoder();

function randomSessionId(): string {
  const bytes = crypto.getRandomValues(new Uint8Array(32));
  return Array.from(bytes, (value) => value.toString(16).padStart(2, "0")).join("");
}

function toBase64(bytes: Uint8Array): string {
  let binary = "";
  for (const byte of bytes) binary += String.fromCharCode(byte);
  return btoa(binary);
}

function fromBase64(value: string): ArrayBuffer {
  const binary = atob(value);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i += 1) bytes[i] = binary.charCodeAt(i);
  return bytes.buffer as ArrayBuffer;
}

async function deriveSessionEncryptionKey(env: AuthEnv): Promise<CryptoKey> {
  if (!env.SUPABASE_SECRET_KEY) throw new Error("The server-only session encryption key is unavailable.");
  const material = await crypto.subtle.importKey(
    "raw",
    encoder.encode(env.SUPABASE_SECRET_KEY),
    "HKDF",
    false,
    ["deriveKey"],
  );
  return crypto.subtle.deriveKey(
    {
      name: "HKDF",
      hash: "SHA-256",
      salt: encoder.encode("FindURSCH D1 auth session key v1"),
      info: encoder.encode("encrypt-supabase-session-tokens"),
    },
    material,
    { name: "AES-GCM", length: 256 },
    false,
    ["encrypt", "decrypt"],
  );
}

async function encryptToken(key: CryptoKey, value: string): Promise<string> {
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const encrypted = await crypto.subtle.encrypt(
    { name: "AES-GCM", iv },
    key,
    encoder.encode(value),
  );
  return toBase64(iv) + "." + toBase64(new Uint8Array(encrypted));
}

async function decryptToken(key: CryptoKey, value: string): Promise<string> {
  const parts = value.split(".");
  if (parts.length !== 2) throw new Error("Invalid encrypted session token.");
  const plaintext = await crypto.subtle.decrypt(
    { name: "AES-GCM", iv: fromBase64(parts[0]) },
    key,
    fromBase64(parts[1]),
  );
  return decoder.decode(plaintext);
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

async function parseSession(
  row: Record<string, unknown>,
  env: AuthEnv,
): Promise<StoredAuthSession | null> {
  const workspace = String(row.workspace);
  if (workspace !== "admin" && workspace !== "owner" && workspace !== "parent") return null;
  const key = await deriveSessionEncryptionKey(env);
  const session: StoredAuthSession = {
    supabaseUserId: String(row.supabase_user_id || ""),
    legacyUserId: Number(row.legacy_user_id),
    accessToken: await decryptToken(key, String(row.access_token_enc || "")),
    refreshToken: await decryptToken(key, String(row.refresh_token_enc || "")),
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
  adminTimeoutMinutes = 120,
): Promise<{ id: string; cookie: string }> {
  if (!env.AUTH_DB) throw new Error("Session database is unavailable.");
  if (!tokens.access_token || !tokens.refresh_token || !tokens.user?.id) {
    throw new Error("Supabase did not return a complete session.");
  }
  const now = Date.now();
  const lifetimeSeconds = workspace === "admin"
    ? Math.max(30, Math.floor(adminTimeoutMinutes)) * 60
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

  const key = await deriveSessionEncryptionKey(env);
  const encryptedAccessToken = await encryptToken(key, value.accessToken);
  const encryptedRefreshToken = await encryptToken(key, value.refreshToken);
  const id = randomSessionId();
  const result = await env.AUTH_DB.prepare(
    "INSERT INTO auth_sessions (session_id, supabase_user_id, legacy_user_id, access_token_enc, refresh_token_enc, access_token_expires_at, absolute_expires_at, created_at, remember, workspace) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
  ).bind(
    id,
    value.supabaseUserId,
    value.legacyUserId,
    encryptedAccessToken,
    encryptedRefreshToken,
    value.accessTokenExpiresAt,
    value.absoluteExpiresAt,
    value.createdAt,
    value.remember ? 1 : 0,
    value.workspace,
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
    "SELECT session_id, supabase_user_id, legacy_user_id, access_token_enc, refresh_token_enc, access_token_expires_at, absolute_expires_at, created_at, remember, workspace FROM auth_sessions WHERE session_id = ? LIMIT 1",
  ).bind(id).first<Record<string, unknown>>();
  if (!row) return null;

  let value: StoredAuthSession | null;
  try {
    value = await parseSession(row, env);
  } catch {
    value = null;
  }
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

      const key = await deriveSessionEncryptionKey(env);
      const encryptedAccessToken = await encryptToken(key, value.accessToken);
      const encryptedRefreshToken = await encryptToken(key, value.refreshToken);
      const update = await env.AUTH_DB.prepare(
        "UPDATE auth_sessions SET access_token_enc = ?, refresh_token_enc = ?, access_token_expires_at = ? WHERE session_id = ? AND absolute_expires_at > ?",
      ).bind(
        encryptedAccessToken,
        encryptedRefreshToken,
        value.accessTokenExpiresAt,
        id,
        Date.now(),
      ).run();
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


export interface LoginRateLimitResult {
  allowed: boolean;
  retryAfterSeconds: number;
}

async function hashRateLimitIp(env: AuthEnv, ip: string): Promise<string> {
  if (!env.SUPABASE_SECRET_KEY) throw new Error("Login rate-limit key is unavailable.");
  const key = await crypto.subtle.importKey(
    "raw",
    new TextEncoder().encode(env.SUPABASE_SECRET_KEY),
    { name: "HMAC", hash: "SHA-256" },
    false,
    ["sign"],
  );
  const signature = await crypto.subtle.sign("HMAC", key, new TextEncoder().encode("FindURSCH login rate limit v1:" + ip));
  return Array.from(new Uint8Array(signature), (value) => value.toString(16).padStart(2, "0")).join("");
}

/**
 * Fixed 60-second window, atomically incremented by D1.
 * Attempts 1-10 are admitted; attempt 11 is denied.
 * Only an HMAC of the connecting IP is stored, never the IP itself.
 */
export async function checkLoginRateLimit(
  env: AuthEnv,
  ipAddress: string,
): Promise<LoginRateLimitResult> {
  if (!env.AUTH_DB) throw new Error("Login rate-limit database is unavailable.");

  const now = Date.now();
  const ipHash = await hashRateLimitIp(env, ipAddress || "unknown");
  const row = await env.AUTH_DB.prepare(
    `INSERT INTO auth_login_attempts (ip_hash, window_started_at, attempts)
     VALUES (?, ?, 1)
     ON CONFLICT(ip_hash) DO UPDATE SET
       window_started_at = CASE
         WHEN auth_login_attempts.window_started_at <= ? THEN excluded.window_started_at
         ELSE auth_login_attempts.window_started_at
       END,
       attempts = CASE
         WHEN auth_login_attempts.window_started_at <= ? THEN 1
         ELSE auth_login_attempts.attempts + 1
       END
     RETURNING attempts, window_started_at`,
  ).bind(ipHash, now, now - 60_000, now - 60_000)
    .first<{ attempts: number; window_started_at: number }>();

  if (!row) throw new Error("Login attempt counter could not be updated.");
  if (Number(row.attempts) <= 10) return { allowed: true, retryAfterSeconds: 0 };

  const retryAfterSeconds = Math.max(
    1,
    Math.ceil((Number(row.window_started_at) + 60_000 - now) / 1000),
  );
  return { allowed: false, retryAfterSeconds };
}
