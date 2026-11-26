export interface LoginRateLimiterStub {
  fetch(input: string, init?: RequestInit): Promise<Response>;
}

export interface LoginRateLimiterNamespace {
  idFromName(name: string): unknown;
  get(id: unknown): LoginRateLimiterStub;
}

export interface AuthEnv {
  SUPABASE_URL?: string;
  SUPABASE_ANON_KEY?: string;
  SUPABASE_SECRET_KEY?: string;
  LOGIN_RATE_LIMIT_HMAC_KEY?: string;
  LOGIN_RATE_LIMITER?: LoginRateLimiterNamespace;
}

export interface SupabaseTokenResponse {
  access_token: string;
  refresh_token: string;
  expires_in: number;
  token_type?: string;
  user?: {
    id?: string;
    email?: string | null;
    app_metadata?: Record<string, unknown>;
  };
}

export interface LegacyAppUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  status: string;
  user_type: string;
  email_verified_at: string | null;
}

export interface StoredAuthSession {
  supabaseUserId: string;
  legacyUserId: number;
  accessToken: string;
  refreshToken: string;
  accessTokenExpiresAt: number;
  absoluteExpiresAt: number;
  createdAt: number;
  remember: boolean;
  workspace: "admin" | "owner" | "parent";
}

export const SESSION_COOKIE = "findursch_session";
