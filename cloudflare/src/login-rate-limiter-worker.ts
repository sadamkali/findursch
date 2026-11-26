interface SqlCursor<T extends Record<string, unknown>> {
  one(): T | null;
}

interface SqlStorage {
  exec<T extends Record<string, unknown> = Record<string, unknown>>(
    query: string,
    ...bindings: Array<string | number>
  ): SqlCursor<T>;
}

interface DurableObjectStorage {
  sql: SqlStorage;
  setAlarm(timestamp: number): Promise<void>;
}

interface DurableObjectState {
  storage: DurableObjectStorage;
  blockConcurrencyWhile(callback: () => Promise<void>): Promise<void>;
}

const WINDOW_MS = 60_000;
const MAX_ATTEMPTS = 10;

function json(data: unknown, status = 200): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      "Content-Type": "application/json; charset=utf-8",
      "Cache-Control": "no-store",
      "X-Content-Type-Options": "nosniff",
    },
  });
}

/**
 * One Durable Object instance is derived from each HMACed connecting IP.
 * Requests for that IP are serialized by Durable Objects, so the check and
 * insert cannot race when multiple login requests arrive concurrently.
 *
 * Only timestamps are stored. No raw IP, account identifier, password,
 * access token or refresh token is accepted by this internal endpoint.
 */
export class LoginRateLimiter {
  private readonly state: DurableObjectState;
  private readonly ready: Promise<void>;

  constructor(state: DurableObjectState) {
    this.state = state;
    this.ready = state.blockConcurrencyWhile(async () => {
      state.storage.sql.exec(
        "CREATE TABLE IF NOT EXISTS login_attempts (attempted_at INTEGER NOT NULL)",
      );
      state.storage.sql.exec(
        "CREATE INDEX IF NOT EXISTS login_attempts_attempted_at ON login_attempts(attempted_at)",
      );
    });
  }

  async fetch(request: Request): Promise<Response> {
    await this.ready;
    const url = new URL(request.url);
    if (url.pathname !== "/check") return json({ error: "not_found" }, 404);
    if (request.method !== "POST") return json({ error: "method_not_allowed" }, 405);

    const now = Date.now();
    const cutoff = now - WINDOW_MS;
    this.state.storage.sql.exec(
      "DELETE FROM login_attempts WHERE attempted_at <= ?",
      cutoff,
    );

    const row = this.state.storage.sql.exec<{
      attempt_count: number;
      oldest_attempt_at: number | null;
    }>(
      "SELECT COUNT(*) AS attempt_count, MIN(attempted_at) AS oldest_attempt_at FROM login_attempts",
    ).one();

    const attemptCount = Number(row?.attempt_count ?? 0);
    if (attemptCount >= MAX_ATTEMPTS) {
      const oldest = Number(row?.oldest_attempt_at ?? now);
      const retryAfterSeconds = Math.max(1, Math.ceil((oldest + WINDOW_MS - now) / 1000));
      return json({ allowed: false, retryAfterSeconds }, 429);
    }

    this.state.storage.sql.exec(
      "INSERT INTO login_attempts (attempted_at) VALUES (?)",
      now,
    );
    await this.state.storage.setAlarm(now + WINDOW_MS);
    return json({ allowed: true, retryAfterSeconds: 0 });
  }

  async alarm(): Promise<void> {
    await this.ready;
    const now = Date.now();
    const cutoff = now - WINDOW_MS;
    this.state.storage.sql.exec(
      "DELETE FROM login_attempts WHERE attempted_at <= ?",
      cutoff,
    );
    const row = this.state.storage.sql.exec<{
      oldest_attempt_at: number | null;
    }>(
      "SELECT MIN(attempted_at) AS oldest_attempt_at FROM login_attempts",
    ).one();
    const oldest = Number(row?.oldest_attempt_at);
    if (Number.isFinite(oldest) && oldest > 0) {
      await this.state.storage.setAlarm(oldest + WINDOW_MS);
    }
  }
}

export default {
  async fetch(): Promise<Response> {
    // The Worker exists only to host the Durable Object class. It is not an
    // application endpoint and should not be exposed as a public HTTP service.
    return json({ error: "not_found" }, 404);
  },
};
