import { handlePublicApi } from "../../src/public-api";

interface PagesEnv {
  SUPABASE_URL?: string;
  SUPABASE_ANON_KEY?: string;
  PUBLIC_MEDIA_BASE_URL?: string;
  AUTH_SESSIONS?: { get(key: string): Promise<string | null> };
}

interface PagesContext {
  request: Request;
  env: PagesEnv;
}

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "GET, OPTIONS",
  "Access-Control-Allow-Headers": "Content-Type, Authorization",
  "Access-Control-Max-Age": "86400",
};

export async function onRequest({ request, env }: PagesContext): Promise<Response> {
  if (request.method === "OPTIONS") {
    return new Response(null, { status: 204, headers: corsHeaders });
  }

  const response = await handlePublicApi(request, env);
  const headers = new Headers(response.headers);
  for (const [name, value] of Object.entries(corsHeaders)) {
    headers.set(name, value);
  }

  return new Response(response.body, {
    status: response.status,
    statusText: response.statusText,
    headers,
  });
}
