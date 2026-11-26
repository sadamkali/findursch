#!/usr/bin/env node

/**
 * One-time, idempotent FindURSCH legacy password-hash import.
 *
 * Required environment:
 *   SUPABASE_URL=https://gpsfttpeikatxszswljo.supabase.co
 *   SUPABASE_SECRET_KEY=sb_secret_...
 *
 * The key is sent in the apikey header only. It is never printed.
 * Existing public.users records and password hashes are never updated.
 */

const baseUrl = (process.env.SUPABASE_URL || "https://gpsfttpeikatxszswljo.supabase.co").replace(/\/+$/, "");
const secret = process.env.SUPABASE_SECRET_KEY || "";

if (!secret) {
  console.error("Missing SUPABASE_SECRET_KEY. Set it in your local shell; do not paste it into chat or commit it.");
  process.exit(2);
}

async function api(path, options = {}) {
  const headers = new Headers(options.headers || {});
  headers.set("apikey", secret);
  headers.set("Accept", "application/json");
  if (options.body !== undefined) headers.set("Content-Type", "application/json");

  const response = await fetch(baseUrl + path, {
    ...options,
    headers,
    body: options.body === undefined ? undefined : JSON.stringify(options.body),
    cache: "no-store",
    signal: AbortSignal.timeout(15000),
  });
  const text = await response.text();
  let payload = null;
  try { payload = text ? JSON.parse(text) : null; } catch { payload = text; }

  if (!response.ok) {
    const safeMessage = payload && typeof payload === "object"
      ? String(payload.msg || payload.message || payload.error_description || payload.error || "Request rejected")
      : "Request rejected";
    throw new Error("Supabase API returned HTTP " + response.status + ": " + safeMessage);
  }
  return payload;
}

async function loadLegacyUsers() {
  const params = new URLSearchParams({
    select: "id,name,email,password,status,user_type,email_verified_at",
    status: "eq.active",
    order: "id.asc",
    limit: "1000",
  });
  const users = await api("/rest/v1/users?" + params.toString());
  if (!Array.isArray(users)) throw new Error("Could not read the legacy account list.");
  return users;
}

async function loadAuthUsers() {
  const users = [];
  let page = 1;
  const perPage = 100;

  while (true) {
    const result = await api("/auth/v1/admin/users?page=" + page + "&per_page=" + perPage);
    const batch = Array.isArray(result?.users) ? result.users : [];
    users.push(...batch);
    if (batch.length < perPage) break;
    page += 1;
    if (page > 100) throw new Error("Auth user pagination exceeded the safety limit.");
  }
  return users;
}

function legacyIdOf(user) {
  const value = Number(user?.app_metadata?.legacy_user_id);
  return Number.isSafeInteger(value) ? value : null;
}

async function createAuthUser(user) {
  if (typeof user.email !== "string" || !user.email.trim()) {
    throw new Error("A legacy user row has no email address.");
  }
  if (typeof user.password !== "string" || !/^\$2y\$[0-9]{2}\$/.test(user.password)) {
    throw new Error("A legacy user row does not have a supported PHP bcrypt hash.");
  }

  const body = {
    email: user.email.trim().toLowerCase(),
    password_hash: user.password,
    email_confirm: true,
    app_metadata: {
      legacy_user_id: Number(user.id),
      legacy_user_type: String(user.user_type || ""),
    },
    user_metadata: {
      name: String(user.name || ""),
    },
  };

  await api("/auth/v1/admin/users", {
    method: "POST",
    body,
  });
}

async function main() {
  const [legacyUsers, authUsers] = await Promise.all([loadLegacyUsers(), loadAuthUsers()]);
  const byLegacyId = new Map();
  const byEmail = new Map();

  for (const authUser of authUsers) {
    const legacyId = legacyIdOf(authUser);
    if (legacyId !== null) byLegacyId.set(legacyId, authUser);
    const email = String(authUser.email || "").toLowerCase();
    if (email) byEmail.set(email, authUser);
  }

  let created = 0;
  let alreadyImported = 0;
  const conflicts = [];

  for (const user of legacyUsers) {
    const legacyId = Number(user.id);
    const email = String(user.email || "").toLowerCase();
    const existingById = byLegacyId.get(legacyId);
    const existingByEmail = byEmail.get(email);

    if (existingById) {
      if (String(existingById.email || "").toLowerCase() !== email) {
        conflicts.push({ legacyId, reason: "legacy ID already belongs to a different Auth email" });
      } else {
        alreadyImported += 1;
      }
      continue;
    }

    if (existingByEmail) {
      conflicts.push({ legacyId, reason: "email already exists in Auth without a matching trusted legacy_user_id" });
      continue;
    }

    try {
      await createAuthUser(user);
      created += 1;
      byEmail.set(email, { email, app_metadata: { legacy_user_id: legacyId } });
      byLegacyId.set(legacyId, { email, app_metadata: { legacy_user_id: legacyId } });
    } catch (error) {
      conflicts.push({
        legacyId,
        reason: error instanceof Error ? error.message : "Auth user creation failed",
      });
    }
  }

  console.log(JSON.stringify({
    totalActiveLegacyAccounts: legacyUsers.length,
    created,
    alreadyImported,
    conflicts,
    note: "No legacy public.users rows or password hashes were modified. Password hashes and secret keys were not printed.",
  }, null, 2));

  if (conflicts.length > 0) process.exitCode = 1;
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : "Legacy Auth import failed.");
  process.exitCode = 1;
});
