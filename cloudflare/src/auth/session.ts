import type { AuthEnv, StoredAuthSession, SupabaseTokenResponse } from "./types";
import { SESSION_COOKIE } from "./types";
import { refreshSupabaseTokens } from "./supabase";

const encoder = new TextEncoder();
const decoder = new TextDecoder();
const COOKIE_AAD = "findursch-supabase-auth-cookie:v2";
const MAX_COOKIE_VALUE_LENGTH = 3600;

function toBase64Url(bytes: Uint8Array): string {
  let binary = "";
  for (const byte of bytes) binary += String.fromCharCode(byte);
  return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/g, "");
}

function fromBase64Url(value: string): ArrayBuffer {
  if (!/^[A-Za-z0-9_-]+$/.test(value)) throw new Error("Invalid session cookie encoding.");
  const padded = value.replace(/-/g, "+").replace(/_/g, "/") + "=".repeat((4 - value.length % 4) % 4);
  const binary = atob(padded);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i += 1) bytes[i] = binary.charCodeAt(i);
  return bytes.buffer as ArrayBuffer;
}

async function deriveCookieEncryptionKey(env: AuthEnv): Promise<CryptoKey> {
  if (!env.SUPABASE_SECRET_KEY) throw new Error("The server-only Supabase secret key is unavailable.");
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
      salt: encoder.encode("FindURSCH Supabase Auth cookie v2"),
      info: encoder.encode("encrypt-access-and-refresh-session-tokens"),
    },
    material,
    { name: "AES-GCM", length: 256 },
    false,
    ["encrypt", "decrypt"],
  );
}

async function sealSession(env: AuthEnv, session: StoredAuthSession): Promise<string> {
  const key = await deriveCookieEncryptionKey(env);
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const plaintext = encoder.encode(JSON.stringify(session));
  const encrypted = await crypto.subtle.encrypt(
    {
      name: "AES-GCM",
      iv,
      additionalData: encoder.encode(COOKIE_AAD),
    },
    key,
    plaintext,
  );
  const value = toBase64Url(iv) + "." + toBase64Url(new Uint8Array(encrypted));
  if (value.length > MAX_COOKIE_VALUE_LENGTH) {
    throw new Error("The encrypted sign-in session exceeds the safe browser cookie size.");
  }
  return value;
}

async function openSession(env: AuthEnv, value: string): Promise<StoredAuthSession | null> {
  const parts = value.split(".");
  if (parts.length !== 2 || value.length > MAX_COOKIE_VALUE_LENGTH) return null;
  const key = await deriveCookieEncryptionKey(env);
  const plaintext = await crypto.subtle.decrypt(
    {
      name: "AES-GCM",
      iv: fromBase64Url(parts[0]),
      additionalData: encoder.encode(COOKIE_AAD),
    },
    key,
    fromBase64Url(parts[1]),
  );
  const parsed = JSON.parse(decoder.decode(plaintext)) as Partial<StoredAuthSession>;
  const workspace = parsed.workspace;
  if (workspace !== "admin" && workspace !== "owner" && workspace !== "parent") return null;
  if (
    typeof parsed.supabaseUserId !== "string" ||
    !parsed.supabaseUserId ||
    !Number.isSafeInteger(parsed.legacyUserId) ||
    typeof parsed.accessToken !== "string" ||
    !parsed.accessToken ||
    typeof parsed.refreshToken !== "string" ||
    !parsed.refreshToken ||
    !Number.isFinite(parsed.accessTokenExpiresAt) ||
    !Number.isFinite(parsed.absoluteExpiresAt) ||
    !Number.isFinite(parsed.createdAt) ||
    typeof parsed.remember !== "boolean"
  ) return null;
  return parsed as StoredAuthSession;
}

function cookieValue(request: Request, name: string): string | null {
  const cookie = request.headers.get("Cookie");
  if (!cookie) return null;
  for (const part of cookie.split(";")) {
    const index = part.indexOf("=");
    if (index < 0 || part.slice(0, index).trim() !== name) continue;
    return part.slice(index + 1).trim();
  }
  return null;
}

function setCookie(value: string, maxAge: number): string {
  return [
    SESSION_COOKIE + "=" + value,
    "Path=/",
    "HttpOnly",
    "Secure",
    "SameSite=Lax",
    "Max-Age=" + String(Math.max(0, Math.floor(maxAge))),
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

export interface SessionHandle {
  value: StoredAuthSession;
  cookie?: string;
}

/**
 * Supabase Auth owns the real user session and refresh-token lifecycle.
 * Cloudflare holds only an authenticated-encrypted HttpOnly cookie envelope;
 * no session rows or auth tokens are stored in a Cloudflare database.
 */
export async function createStoredSession(
  env: AuthEnv,
  tokens: SupabaseTokenResponse,
  legacyUserId: number,
  workspace: "admin" | "owner" | "parent",
  remember: boolean,
  adminTimeoutMinutes = 120,
): Promise<{ cookie: string }> {
  if (!tokens.access_token || !tokens.refresh_token || !tokens.user?.id) {
    throw new Error("Supabase did not return a complete session.");
  }
  const now = Date.now();
  const lifetimeSeconds = workspace === "admin"
    ? Math.max(30, Math.floor(adminTimeoutMinutes)) * 60
    : (remember ? 30 * 24 * 60 * 60 : 24 * 60 * 60);
  const value: StoredAuthSession = {
    supabaseUserId: tokens.user.id,
    legacyUserId,
    accessToken: tokens.access_token,
    refreshToken: tokens.refresh_token,
    accessTokenExpiresAt: now + Math.max(60, Number(tokens.expires_in || 3600)) * 1000,
    absoluteExpiresAt: now + lifetimeSeconds * 1000,
    createdAt: now,
    remember,
    workspace,
  };
  const sealed = await sealSession(env, value);
  return { cookie: setCookie(sealed, lifetimeSeconds) };
}

/**
 * Reads the sealed cookie and refreshes through Supabase Auth when the access
 * token is close to expiry. The updated encrypted cookie is returned to the
 * browser so Supabase's rotating refresh token is not lost.
 */
export async function readStoredSession(
  request: Request,
  env: AuthEnv,
): Promise<SessionHandle | null> {
  const sealed = cookieValue(request, SESSION_COOKIE);
  if (!sealed) return null;

  let value: StoredAuthSession | null;
  try {
    value = await openSession(env, sealed);
  } catch (error) {
    if (!env.SUPABASE_SECRET_KEY) throw error;
    return null;
  }
  if (!value || Date.now() >= value.absoluteExpiresAt) return null;

  let cookie: string | undefined;
  if (Date.now() >= value.accessTokenExpiresAt - 90_000) {
    const fresh = await refreshSupabaseTokens(env, value.refreshToken);
    if (
      !fresh.access_token ||
      !fresh.refresh_token ||
      !fresh.user?.id ||
      fresh.user.id !== value.supabaseUserId
    ) return null;

    value.accessToken = fresh.access_token;
    value.refreshToken = fresh.refresh_token;
    value.accessTokenExpiresAt = Date.now() + Math.max(60, Number(fresh.expires_in || 3600)) * 1000;
    const remainingSeconds = Math.max(0, Math.floor((value.absoluteExpiresAt - Date.now()) / 1000));
    cookie = setCookie(await sealSession(env, value), remainingSeconds);
  }

  return { value, cookie };
}
