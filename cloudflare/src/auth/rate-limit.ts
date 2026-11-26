import type { AuthEnv } from "./types";

const encoder = new TextEncoder();

function toHex(bytes: Uint8Array): string {
  return Array.from(bytes, (byte) => byte.toString(16).padStart(2, "0")).join("");
}

/**
 * Enforce the 10-attempts/60-second login throttle on Cloudflare.
 * The raw client IP never leaves this Pages Function: an HMAC digest is
 * used as the Durable Object name, and the DO stores only timestamps.
 */
export async function checkLoginRateLimit(
  env: AuthEnv,
  request: Request,
): Promise<{ allowed: boolean; retryAfterSeconds: number }> {
  const clientIp = request.headers.get("CF-Connecting-IP");
  if (!clientIp) throw new Error("Cloudflare did not provide the connecting IP address.");
  if (!env.LOGIN_RATE_LIMIT_HMAC_KEY) {
    throw new Error("The Cloudflare login rate-limit HMAC secret is not configured.");
  }
  if (!env.LOGIN_RATE_LIMITER) {
    throw new Error("The Cloudflare Durable Object login limiter is not bound.");
  }

  const hmacKey = await crypto.subtle.importKey(
    "raw",
    encoder.encode(env.LOGIN_RATE_LIMIT_HMAC_KEY),
    { name: "HMAC", hash: "SHA-256" },
    false,
    ["sign"],
  );
  const digest = new Uint8Array(
    await crypto.subtle.sign("HMAC", hmacKey, encoder.encode(clientIp)),
  );
  const ipDigest = toHex(digest);
  const objectId = env.LOGIN_RATE_LIMITER.idFromName("findursch-login:" + ipDigest);
  const stub = env.LOGIN_RATE_LIMITER.get(objectId);

  const response = await stub.fetch("https://login-rate-limiter.internal/check", {
    method: "POST",
    headers: { "Cache-Control": "no-store" },
  });
  if (!response.ok && response.status !== 429) {
    throw new Error("Cloudflare Durable Object login rate-limit check failed with status " + response.status);
  }

  const result = await response.json() as {
    allowed?: unknown;
    retryAfterSeconds?: unknown;
  };
  if (!result || typeof result.allowed !== "boolean") {
    throw new Error("Cloudflare Durable Object returned an invalid rate-limit response.");
  }
  const retryAfter = Number(result.retryAfterSeconds);
  return {
    allowed: result.allowed,
    retryAfterSeconds: Number.isFinite(retryAfter) ? Math.max(1, Math.floor(retryAfter)) : 60,
  };
}
