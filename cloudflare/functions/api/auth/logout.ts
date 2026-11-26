import { handleAuthRequest } from "../../../src/auth/handlers";
import type { AuthEnv } from "../../../src/auth/types";

interface PagesContext {
  request: Request;
  env: AuthEnv;
}

export async function onRequest({ request, env }: PagesContext): Promise<Response> {
  return handleAuthRequest(request, env, "logout");
}
