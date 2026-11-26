import { handlePublicApi } from "./public-api";
import type { AuthSessionDatabase } from "./auth/types";

interface AssetsBinding {
  fetch(request: Request): Promise<Response>;
}

export interface Env {
  ASSETS: AssetsBinding;
  SUPABASE_URL?: string;
  SUPABASE_ANON_KEY?: string;
  PUBLIC_MEDIA_BASE_URL?: string;
  AUTH_SESSIONS?: { get(key: string): Promise<string | null> };
}

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "GET, HEAD, OPTIONS",
  "Access-Control-Allow-Headers": "Content-Type, Authorization",
  "Access-Control-Max-Age": "86400",
};

function withCors(response: Response): Response {
  const headers = new Headers(response.headers);
  for (const [key, value] of Object.entries(corsHeaders)) {
    headers.set(key, value);
  }
  return new Response(response.body, {
    status: response.status,
    statusText: response.statusText,
    headers,
  });
}

export default {
  async fetch(request: Request, env: Env): Promise<Response> {
    const url = new URL(request.url);

    if (url.pathname.startsWith("/api/")) {
      if (request.method === "OPTIONS") {
        return new Response(null, { status: 204, headers: corsHeaders });
      }

      if (request.method !== "GET" && request.method !== "HEAD") {
        return withCors(
          new Response(JSON.stringify({ error: "method_not_allowed" }), {
            status: 405,
            headers: { "Content-Type": "application/json; charset=utf-8" },
          }),
        );
      }

      return withCors(await handlePublicApi(request, env));
    }

    return env.ASSETS.fetch(request);
  },
};
